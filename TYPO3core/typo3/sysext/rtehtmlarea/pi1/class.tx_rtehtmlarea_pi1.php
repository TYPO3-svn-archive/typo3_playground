<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Stanislas Rolland <stanislas.rolland(arobas)fructifor.ca>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Spell checking plugin 'tx_rtehtmlarea_pi1' for the htmlArea RTE extension.
 *
 * @author Stanislas Rolland <stanislas.rolland(arobas)fructifor.ca>
 *
 * TYPO3 CVS ID: $Id: class.tx_rtehtmlarea_pi1.php 1886 2006-12-20 13:38:31Z mundaun $
 *
 */
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_rtehtmlarea_pi1 extends tslib_pibase {
	var $cObj;  // The backReference to the mother cObj object set at call time
	var $prefixId = 'tx_rtehtmlarea_pi1';  // Same as class name
	var $scriptRelPath = 'pi1/class.tx_rtehtmlarea_pi1.php';  // Path to this script relative to the extension dir.
	var $extKey = 'rtehtmlarea'; // The extension key.
	var $conf = array();
	var $siteUrl;
	var $charset = 'utf-8';
	var $parserCharset = 'utf-8';
	var $result;
	var $text;
	var $misspelled = array();
	var $suggestedWords;
	var $wordCount = 0;
	var $suggestionCount = 0;
	var $suggestedWordCount = 0;
	var $pspell_link;
	var $pspellMode = 'normal';
	var $dictionary;
	var $AspellDirectory;
	var $pspell_is_available;
	var $forceCommandMode = 0;
	var $filePrefix = 'rtehtmlarea_';
	var $uploadFolder = 'uploads/tx_rtehtmlarea/';
	var $userUid;
	var $personalDictsArg = '';

	/**
	 * Main class of Spell Checker plugin for Typo3 CMS
	 *
	 * @param	string		$content: content to be displayed
	 * @param	array		$conf: TS setup for the plugin
	 * @return	string		content produced by the plugin
	 */
	function main($conf) {
		global $TYPO3_CONF_VARS, $TYPO3_DB;

		$this->conf = $conf;
		$this->tslib_pibase();
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;  // Disable caching
			// Setting start time
		$time_start = microtime(true);
		$this->pspell_is_available = in_array('pspell', get_loaded_extensions());
		$this->AspellDirectory = trim($TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['AspellDirectory'])? trim($TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['AspellDirectory']) : '/usr/bin/aspell';
		$this->forceCommandMode = (trim($TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['forceCommandMode']))? trim($TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['forceCommandMode']) : 0;
		$safe_mode_is_enabled = ini_get('safe_mode');
		if($safe_mode_is_enabled && !$this->pspell_is_available ) echo('Configuration problem: Spell checking cannot be performed');
		if($safe_mode_is_enabled && $this->forceCommandMode) echo('Configuration problem: Spell checking cannot be performed in command mode');
		if(!$safe_mode_is_enabled && (!$this->pspell_is_available || $this->forceCommandMode)) {
			$AspellVersionString = explode('Aspell', shell_exec( $this->AspellDirectory.' -v'));
			$AspellVersion = substr( $AspellVersionString[1], 0, 4);
			if( doubleval($AspellVersion) < doubleval('0.5') && (!$this->pspell_is_available || $this->forceCommandMode)) echo('Configuration problem: Aspell version ' . $AspellVersion . ' too old. Spell checking cannot be performed in command mode');
		}

			// Setting the list of dictionaries
		if(!$safe_mode_is_enabled && (!$this->pspell_is_available || $this->forceCommandMode)) {
			$dictionaryList = shell_exec( $this->AspellDirectory.' dump dicts');
			$dictionaryList = implode(',', t3lib_div::trimExplode(chr(10), $dictionaryList, 1));
		}
		if( empty($dictionaryList) ) {
			$dictionaryList = trim($TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['dictionaryList']);
		}
		if( empty($dictionaryList) ) {
			$dictionaryList = 'en';
		}
		$dictionaryArray = t3lib_div::trimExplode(',', $dictionaryList, 1);

		$defaultDictionary = trim($TYPO3_CONF_VARS['EXTCONF'][$this->extKey]['defaultDictionary']);
		if(!$defaultDictionary || !in_array($defaultDictionary, $dictionaryArray)) {
			$defaultDictionary = 'en';
		}

			// Get the defined sys_language codes
		$languageArray = array();
		$tableA = 'sys_language';
		$tableB = 'static_languages';
		$selectFields = $tableA . '.uid,' . $tableB . '.lg_iso_2,' . $tableB . '.lg_country_iso_2';
		$table = $tableA . ' LEFT JOIN ' . $tableB . ' ON ' . $tableA . '.static_lang_isocode=' . $tableB . '.uid';
		$whereClause = '1=1 ';
		$whereClause .= ' AND ' . $tableA . '.hidden != 1';
		$res = $TYPO3_DB->exec_SELECTquery($selectFields, $table, $whereClause);
		while($row = $TYPO3_DB->sql_fetch_assoc($res))    {
			$languageArray[] = strtolower($row['lg_iso_2']).($row['lg_country_iso_2']?'_'.$row['lg_country_iso_2']:'');
		}
		if(!in_array($defaultDictionary, $languageArray)) {
			$languageArray[] = $defaultDictionary;
		}
		foreach ($dictionaryArray as $key => $dict) {
			$lang = explode('-', $dict);
			if( !in_array(substr($dict, 0, 2), $languageArray) || !empty($lang[1])) {
				unset($dictionaryArray[$key]);
			} else {
				$dictionaryArray[$key] = $lang[0];
			}
		}
		uasort($dictionaryArray, 'strcoll');
		$dictionaryList = implode(',', $dictionaryArray);

			// Setting the dictionary
		$this->dictionary = t3lib_div::_POST('dictionary');
		if( empty($this->dictionary) || !in_array($this->dictionary, $dictionaryArray)) {
			$this->dictionary = $defaultDictionary;
		}
		$dictionaries = substr_replace($dictionaryList, '@'.$this->dictionary, strpos($dictionaryList, $this->dictionary), strlen($this->dictionary));

		//$locale = setlocale(LC_ALL, $this->dictionary);

			// Setting the pspell suggestion mode
		$this->pspellMode = t3lib_div::_POST('pspell_mode')?t3lib_div::_POST('pspell_mode'): $this->pspellMode;
			// Now sanitize $this->pspellMode
		$this->pspellMode = t3lib_div::inList('ultra,fast,normal,bad-spellers',$this->pspellMode)?$this->pspellMode:'normal';
		switch($this->pspellMode) {
			case 'ultra':
			case 'fast':
				$pspellModeFlag = PSPELL_FAST;
				break;
			case 'bad-spellers':
				$pspellModeFlag = PSPELL_BAD_SPELLERS;
				break;
			case 'normal':
			default:
				$pspellModeFlag = PSPELL_NORMAL;
				break;
		}

			// Setting the charset
		if( t3lib_div::_POST('pspell_charset') ) $this->charset = trim(t3lib_div::_POST('pspell_charset'));
		if(strtolower($this->charset) == 'iso-8859-1') $this->parserCharset = strtolower($this->charset);
		$internal_encoding = mb_internal_encoding(strtoupper($this->parserCharset));
		//$regex_encoding = mb_regex_encoding (strtoupper($this->parserCharset));
			// However, we are going to work only in the parser charset
		if($this->pspell_is_available && !$this->forceCommandMode) {
			$this->pspell_link = pspell_new($this->dictionary, '', '', $this->parserCharset, $pspellModeFlag);
		}

			// Setting the path to user personal dicts, if any
		if (t3lib_div::_POST('enablePersonalDicts') == 'true' && $GLOBALS['TSFE']->beUserLogin)	{
			$this->userUid = 'BE_' . $GLOBALS['BE_USER']->user['uid'];
			if ($this->userUid) {
				$this->personalDictPath = t3lib_div::getFileAbsFileName($this->uploadFolder . $this->userUid);
				if (!is_dir($this->personalDictPath)) {
					t3lib_div::mkdir($this->personalDictPath);
				}
					// escape here for later use
				$this->personalDictsArg = ' --home-dir=' . escapeshellarg($this->personalDictPath);
			}
		}

		$cmd = t3lib_div::_POST('cmd');
		if ($cmd == 'learn' && !$safe_mode_is_enabled) {
				// Only availble for BE_USERS, die silently if someone has gotten here by accident
			if(!$GLOBALS['TSFE']->beUserLogin) die('');
				// Updating the personal word list
			$to_p_dict = t3lib_div::_POST('to_p_dict');
			$to_p_dict = $to_p_dict ? $to_p_dict : array();
			$to_r_list = t3lib_div::_POST('to_r_list');
			$to_r_list = $to_r_list ? $to_r_list : array();
			header('Content-Type: text/plain; charset=' . strtoupper($this->parserCharset));
			header('Pragma: no-cache');
			//print_r($to_r_list);
			if($to_p_dict || $to_r_list) {
				$tmpFileName = t3lib_div::tempnam($this->filePrefix);
				if($filehandle = fopen($tmpFileName,'wb')) {
					foreach ($to_p_dict as $personal_word) {
						$cmd = '&' . $personal_word . "\n";
						echo $cmd;
						fwrite($filehandle, $cmd, strlen($cmd));
					}
					foreach ($to_r_list as $replace_pair) {
						$cmd = '$$ra ' . $replace_pair[0] . ' , ' . $replace_pair[1] . "\n";
						echo $cmd;
						fwrite($filehandle, $cmd, strlen($cmd));
					}
					$cmd = "#\n";
					echo $cmd;
					fwrite($filehandle, $cmd, strlen($cmd));
					fclose($filehandle);
						// $this->personalDictsArg has already been escapeshellarg()'ed above, it is an optional paramter and might be empty here
					$AspellCommand = 'cat ' . escapeshellarg($tmpFileName) . ' | ' . $this->AspellDirectory . ' -a --mode=none' . $this->personalDictsArg . ' --lang=' . escapeshellarg($this->dictionary) . ' --encoding=' . escapeshellarg($this->parserCharset) . ' 2>&1';
					print $AspellCommand . "\n";
					print shell_exec($AspellCommand);
					t3lib_div::unlink_tempfile($tmpFileName);
					echo('Personal word list was updated.');
				} else {
					echo('SpellChecker tempfile open error.');
				}
			} else {
				echo('Nothing to add to the personal word list.');
			}
			flush();
			exit();
		} else {
				// Check spelling content
				// Initialize output
			$this->result = '<?xml version="1.0" encoding="' . $this->parserCharset . '"?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . substr($this->dictionary, 0, 2) . '" lang="' . substr($this->dictionary, 0, 2) . '">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=' . $this->parserCharset . '" />
<link rel="stylesheet" type="text/css" media="all" href="spell-check-style.css" />
<script type="text/javascript">
/*<![CDATA[*/
<!--
';

				// Getting the input content
			$content = t3lib_div::_POST('content');

				// Parsing the input HTML
			$parser = xml_parser_create(strtoupper($this->parserCharset));
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
			xml_set_object($parser, &$this);
			if( !xml_set_element_handler( $parser, 'startHandler', 'endHandler')) echo('Bad xml handler setting');
			if( !xml_set_character_data_handler ( $parser, 'spellCheckHandler')) echo('Bad xml handler setting');
			if( !xml_set_default_handler( $parser, 'defaultHandler')) echo('Bad xml handler setting');
			if(! xml_parse($parser,'<?xml version="1.0" encoding="' . $this->parserCharset . '"?><spellchecker> ' . mb_ereg_replace('&nbsp;', ' ', $content) . ' </spellchecker>')) echo('Bad parsing');
			if( xml_get_error_code($parser)) {
				die('Line '.xml_get_current_line_number($parser).': '.xml_error_string(xml_get_error_code($parser)));
			}
			xml_parser_free($parser);
			if($this->pspell_is_available && !$this->forceCommandMode) {
				pspell_clear_session ($this->pspell_link);
			}
			$this->result .= 'var suggested_words = {' . $this->suggestedWords . '};
';

				// Calculating parsing and spell checkting time
			$time = number_format(microtime(true) - $time_start, 2, ',', ' ');

				// Insert spellcheck info
			$this->result .= 'var spellcheck_info = { "Total words":"'.$this->wordCount.'","Misspelled words":"'.sizeof($this->misspelled).'","Total suggestions":"'.$this->suggestionCount.'","Total words suggested":"'.$this->suggestedWordCount.'","Spelling checked in":"'.$time.'" };
// -->
/*]]>*/
</script>
</head>
';
			$this->result .= '<body onload="window.parent.finishedSpellChecking();">';
			$this->result .= preg_replace('/'.preg_quote('<?xml').'.*'.preg_quote('?>').'['.preg_quote(chr(10).chr(13).chr(32)).']*/', '', $this->text);
			$this->result .= '<div id="HA-spellcheck-dictionaries">'.$dictionaries.'</div>';

				// Closing
			$this->result .= '
</body></html>';

				// Outputting
			echo $this->result;
		}

	}  // end of function main

	function startHandler($xml_parser, $tag, $attributes) {
		switch($tag) {
			case 'spellchecker':
				break;
			case 'br':
			case 'BR':
			case 'img':
			case 'IMG':
			case 'hr':
			case 'HR':
			case 'area':
			case 'AREA':
				$this->text .= '<'. mb_strtolower($tag) . ' ';
				foreach( $attributes as $key => $val) {
					$this->text .= $key . '="' . $val . '" ';
				}
				$this->text .= ' />';
				break;
			default:
				$this->text .= '<'. mb_strtolower($tag) . ' ';
				foreach( $attributes as $key => $val) {
					$this->text .= $key . '="' . $val . '" ';
				}
				$this->text .= '>';
				break;
		}
		return;
	}

	function endHandler($xml_parser, $tag) {
		switch($tag) {
			case 'spellchecker':
				break;
			case 'br':
			case 'BR':
			case 'img':
			case 'IMG':
			case 'hr':
			case 'HR':
			case 'input':
			case 'INPUT':
			case 'area':
			case 'AREA':
				break;
			default:
				$this->text .= '</' . $tag . '>';
				break;
		}
		return;
	}

	function spellCheckHandler($xml_parser, $string) {
		$incurrent=array();
		$stringText = $string;
		$words = mb_split('\W+', $stringText);
		while( list(,$word) = each($words) ) {
			$word = mb_ereg_replace(' ', '', $word);
			if( $word && !is_numeric($word)) {
				if($this->pspell_is_available && !$this->forceCommandMode) {
					if (!pspell_check($this->pspell_link, $word)) {
						if(!in_array($word, $this->misspelled)) {
							if(sizeof($this->misspelled) != 0 ) {
								$this->suggestedWords .= ',';
							}
							$suggest = array();
							$suggest = pspell_suggest($this->pspell_link, $word);
							if(sizeof($suggest) != 0 ) {
								$this->suggestionCount++;
								$this->suggestedWordCount += sizeof($suggest);
							}
							$this->suggestedWords .= '"'.$word.'":"'.implode(',',$suggest).'"';
							$this->misspelled[] = $word;
							unset($suggest);
						}
						if( !in_array($word, $incurrent) ) {
							$stringText = mb_ereg_replace('\b'.$word.'\b', '<span class="HA-spellcheck-error">'.$word.'</span>', $stringText);
							$incurrent[] = $word;
						}
					}
				} else {
					$tmpFileName = t3lib_div::tempnam($this->filePrefix);
					if(!$filehandle = fopen($tmpFileName,'wb')) echo('SpellChecker tempfile open error');
					if(!fwrite($filehandle, $word)) echo('SpellChecker tempfile write error');
					if(!fclose($filehandle)) echo('SpellChecker tempfile close error');
					$AspellCommand = 'cat ' . escapeshellarg($tmpFileName) . ' | ' . $this->AspellDirectory . ' -a check --mode=none --sug-mode=' . escapeshellarg($this->pspellMode) . $this->personalDictsArg . ' --lang=' . escapeshellarg($this->dictionary) . ' --encoding=' . escapeshellarg($this->parserCharset) . ' 2>&1';
					$AspellAnswer = shell_exec($AspellCommand);
					$AspellResultLines = array();
					$AspellResultLines = t3lib_div::trimExplode(chr(10), $AspellAnswer, 1);
					if(substr($AspellResultLines[0],0,6) == 'Error:') echo("{$AspellAnswer}");
					t3lib_div::unlink_tempfile($tmpFileName);
					if(substr($AspellResultLines['1'],0,1) != '*') {
						if(!in_array($word, $this->misspelled)) {
							if(sizeof($this->misspelled) != 0 ) {
								$this->suggestedWords .= ',';
							}
							$suggest = array();
							$suggestions = array();
							if (substr($AspellResultLines['1'],0,1) == '&') {
								$suggestions = t3lib_div::trimExplode(':', $AspellResultLines['1'], 1);
								$suggest =  t3lib_div::trimExplode(',', $suggestions['1'], 1);
							}
							if (sizeof($suggest) != 0) {
								$this->suggestionCount++;
								$this->suggestedWordCount += sizeof($suggest);
							}
							$this->suggestedWords .= '"'.$word.'":"'.implode(',',$suggest).'"';
							$this->misspelled[] = $word;
							unset($suggest);
							unset($suggestions);
						}
						if (!in_array($word, $incurrent)) {
							$stringText = mb_ereg_replace('\b'.$word.'\b', '<span class="HA-spellcheck-error">'.$word.'</span>', $stringText);
							$incurrent[] = $word;
						}
					}
				unset($AspellResultLines);
				}
				$this->wordCount++;
			}
		}
		$this->text .= $stringText;
		unset($incurrent);
		return;
	}

	function defaultHandler($xml_parser, $string) {
		$this->text .= $string;
		return;
	}

} // end of class

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rtehtmlarea/pi1/class.tx_rtehtmlarea_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rtehtmlarea/pi1/class.tx_rtehtmlarea_pi1.php']);
}

?>