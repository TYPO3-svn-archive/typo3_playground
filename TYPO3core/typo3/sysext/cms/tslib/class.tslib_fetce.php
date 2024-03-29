<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Form-data processing
 * included from index_ts.php
 *
 * $Id$
 * Revised for TYPO3 3.6 June/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   83: class tslib_feTCE
 *  100:     function start($data,$FEData)
 *  187:     function checkDoublePostExist($table,$doublePostField,$key)
 *  200:     function calcDoublePostKey($array)
 *  212:     function includeScripts()
 *  232:     function execNEWinsert($table, $dataArr)
 *  258:     function clear_cacheCmd($cacheCmd)
 *  274:     function getConf($table)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



















/**
 * Form-data processing class.
 * Used by the FE_DATA object found in TSref. Quite old fashioned and used only by a few extensions, like good old 'tt_guest' and 'tt_board'
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tslib
 * @deprecated
 * @link http://typo3.org/doc.0.html?&tx_extrepmgm_pi1[extUid]=270&tx_extrepmgm_pi1[tocEl]=342&cHash=fdf55adb3b
 */
class tslib_feTCE	{

	var $extScripts=array();
	var $extScriptsConf=array();
	var $newData=array();
	var $extraList = 'pid';

	/**
	 * Starting the processing of user input.
	 * Traverses the input data and fills in the array, $this->extScripts with references to files which are then included by includeScripts() (called AFTER start() in tslib_fe)
	 * These scripts will then put the content into the database.
	 *
	 * @param	array		Input data coming from typ. $_POST['data'] vars
	 * @param	array		TypoScript configuration for the FEDATA object, $this->config['FEData.']
	 * @return	void
	 * @see tslib_fe::fe_tce(), includeScripts()
	 */
	function start($data,$FEData)	{
		reset($data);
		while(list($table,$id_arr)=each($data))	{
			t3lib_div::loadTCA($table);
			if (is_array($id_arr))	{
				$sep=$FEData[$table.'.']['separator']?$FEData[$table.'.']['separator']:chr(10);
				reset($id_arr);
				while(list($id,$field_arr)=each($id_arr))	{
					$this->newData[$table][$id]=Array();
					if (strstr($id,'NEW'))	{		// NEW
							// Defaults:
						if ($FEData[$table.'.']['default.'])	{
							$this->newData[$table][$id] = $FEData[$table.'.']['default.'];
						}
						if ($FEData[$table.'.']['autoInsertPID']) {
							$this->newData[$table][$id]['pid'] = intval($GLOBALS['TSFE']->page['uid']);
						}
							// Insert external data:
						if (is_array($field_arr))	{
							reset($field_arr);
							while(list($field,$value)=each($field_arr))	{
								if ($FEData[$table.'.']['allowNew.'][$field])	{
									if (is_array($value)) {
										$this->newData[$table][$id][$field] = implode($sep,$value);
									} else {
										$this->newData[$table][$id][$field] = $value;
									}
								}
							}
						}
							// Double post check
						$dPC_field = $FEData[$table.'.']['doublePostCheck'];
						if (is_array($this->newData[$table][$id]) && $dPC_field) {
							$doublePostCheckKey = $this->calcDoublePostKey($this->newData[$table][$id]);
							if ($this->checkDoublePostExist($table,$dPC_field,$doublePostCheckKey))	{
								unset($this->newData[$table][$id]);	// Unsetting the whole thing, because it's not going to be saved.
								$GLOBALS['TT']->setTSlogMessage('"FEData": Submitted record to table $table was doublePosted (key: $doublePostCheckKey). Nothing saved.',2);
							} else {
								$this->newData[$table][$id][$dPC_field] = $doublePostCheckKey;	// Setting key value
								$this->extraList.=','.$dPC_field;
							}
						}
					} else {		// EDIT
							// Insert external data:
						if (is_array($field_arr))	{
							reset($field_arr);
							while(list($field,$value)=each($field_arr))	{
								if ($FEData[$table.'.']['allowEdit.'][$field])	{
									if (is_array($value)) {
										$this->newData[$table][$id][$field] = implode($sep,$value);
									} else {
										$this->newData[$table][$id][$field] = $value;
									}
								}
							}
						}
							// Internal Override
						if (is_array($FEData[$table.'.']['overrideEdit.']))	{
							reset($FEData[$table.'.']['overrideEdit.']);
							while(list($field,$value)=each($FEData[$table.'.']['overrideEdit.']))	{
								$this->newData[$table][$id][$field] = $value;
							}
						}
					}
					if ($FEData[$table.'.']['userIdColumn']) {
						$this->newData[$table][$id][$FEData[$table.'.']['userIdColumn']] = intval($GLOBALS['TSFE']->fe_user->user['uid']);
					}
				}
				$incFile = $GLOBALS['TSFE']->tmpl->getFileName($FEData[$table.'.']['processScript']);
				if ($incFile)	{
					$this->extScripts[$table]=$incFile;
					$this->extScriptsConf[$table]=$FEData[$table.'.']['processScript.'];
				}
			}
		}
	}

	/**
	 * Checking if a "double-post" exists already.
	 * "Double-posting" is if someone refreshes a page with a form for the message board or guestbook and thus submits the element twice. Checking for double-posting prevents the second submission from being stored. This is done by saving the first record with a MD5 hash of the content - if this hash exists already, the record cannot be saved.
	 *
	 * @param	string		The database table to check
	 * @param	string		The fieldname from the database table to search
	 * @param	string		The value to search for.
	 * @return	integer		The number of found rows. If zero then no "double-post" was found and its all OK.
	 * @access private
	 */
	function checkDoublePostExist($table,$doublePostField,$key)	{
		$where = $doublePostField.'='.$key;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)', $table, $where);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		return $row[0];
	}

	/**
	 * Creates the double-post hash value from the input array
	 *
	 * @param	array		The array with key/values to hash
	 * @return	integer		And unsigned 32bit integer hash
	 * @access private
	 */
	function calcDoublePostKey($array)	{
		ksort($array);	// Sorting by key
		$doublePostCheckKey = hexdec(substr(md5(serialize($array)),0,8));	// Making key
		return $doublePostCheckKey;
	}

	/**
	 * Includes the submit scripts found in ->extScripts (filled in by the start() function)
	 *
	 * @return	void
	 * @see tslib_fe::fe_tce(), includeScripts()
	 */
	function includeScripts()	{
		reset($this->extScripts);
		while(list($incFile_table,$incFile)=each($this->extScripts))	{
			if (@is_file($incFile) && $GLOBALS['TSFE']->checkFileInclude($incFile))	{
				include($incFile);	// Always start the incFiles with a check of the object fe_tce.  is_object($this);
				$GLOBALS['TT']->setTSlogMessage('Included '.$incFile,0);
			} else $GLOBALS['TT']->setTSlogMessage('"'.$incFile.'" was not found!',2);
		}
	}

	/**
	 * Method available to the submit scripts for creating insert queries.
	 * Automatically adds tstamp, crdate, cruser_id field/value pairs.
	 * Will allow only field names which are either found in $TCA[...][columns] OR in the $this->extraList
	 * Executes an insert query!
	 *
	 * @param	string		The table name for which to create the insert statement
	 * @param	array		Array with key/value pairs being field/values (already escaped)
	 * @return	void
	 */
	function execNEWinsert($table, $dataArr)	{
		$extraList=$this->extraList;
		if ($GLOBALS['TCA'][$table]['ctrl']['tstamp'])	{$field=$GLOBALS['TCA'][$table]['ctrl']['tstamp']; $dataArr[$field]=time(); $extraList.=','.$field;}
		if ($GLOBALS['TCA'][$table]['ctrl']['crdate'])	{$field=$GLOBALS['TCA'][$table]['ctrl']['crdate']; $dataArr[$field]=time(); $extraList.=','.$field;}
		if ($GLOBALS['TCA'][$table]['ctrl']['cruser_id'])	{$field=$GLOBALS['TCA'][$table]['ctrl']['cruser_id']; $dataArr[$field]=0; $extraList.=','.$field;}

		unset($dataArr['uid']);	// uid can never be set
		$insertFields = array();

		foreach($dataArr as $f => $v)	{
			if (t3lib_div::inList($extraList,$f) || isset($GLOBALS['TCA'][$table]['columns'][$f]))	{
				$insertFields[$f] = $v;
			}
		}

		$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
	}

	/**
	 * Clear cache for page id.
	 * If the page id is the current page, then set_no_cache() is called (so page caching is disabled)
	 *
	 * @param	integer		The page id for which to clear the cache
	 * @return	void
	 * @see tslib_fe::set_no_cache()
	 */
	function clear_cacheCmd($cacheCmd)	{
		$cacheCmd = intval($cacheCmd);
		if ($cacheCmd)	{
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', 'page_id='.intval($cacheCmd));
			if ($cacheCmd == intval($GLOBALS['TSFE']->id))	{	// Setting no_cache true if the cleared-cache page is the current page!
				$GLOBALS['TSFE']->set_no_cache();
			}
		}
	}

	/**
	 * Return TypoScript configuration for a table name
	 *
	 * @param	string		The table name for which to return TypoScript configuration (From TS: FEData.[table])
	 * @return	array		TypoScript properties from FEData.[table] - if exists.
	 */
	function getConf($table)	{
		return $this->extScriptsConf[$table];
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_fetce.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_fetce.php']);
}

?>