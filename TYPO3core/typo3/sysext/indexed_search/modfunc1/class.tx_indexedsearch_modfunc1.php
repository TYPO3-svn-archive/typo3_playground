<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2001-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module extension (addition to function menu) 'Indexed search' for the 'indexed_search' extension.
 *
 * @author    Kasper Sk�rh�j <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  106: class tx_indexedsearch_modfunc1 extends t3lib_extobjbase
 *  120:     function modMenu()
 *  144:     function main()
 *
 *              SECTION: Drawing table of indexed pages
 *  248:     function drawTableOfIndexedPages()
 *  299:     function indexed_info($data, $firstColContent)
 *  386:     function printPhashRow($row,$grouping=0,$extraGrListRows)
 *  527:     function printPhashRowHeader()
 *  582:     function returnNumberOfColumns()
 *
 *              SECTION: Details display, phash row
 *  618:     function showDetailsForPhash($phash)
 *  737:     function listWords($ftrows,$header, $stopWordBoxes=FALSE, $page='')
 *  787:     function listMetaphoneStat($ftrows,$header)
 *  824:     function linkWordDetails($string,$wid)
 *  836:     function linkMetaPhoneDetails($string,$metaphone)
 *  846:     function flagsMsg($flags)
 *
 *              SECTION: Details display, words / metaphone
 *  877:     function showDetailsForWord($wid)
 *  936:     function showDetailsForMetaphone($metaphone)
 *
 *              SECTION: Helper functions
 * 1007:     function printRemoveIndexed($phash,$alt)
 * 1020:     function printReindex($resultRow,$alt)
 * 1035:     function linkDetails($string,$phash)
 * 1044:     function linkList()
 * 1055:     function showPageDetails($string,$id)
 * 1065:     function printExtraGrListRows($extraGrListRows)
 * 1082:     function printRootlineInfo($row)
 * 1116:     function makeItemTypeIcon($it,$alt='')
 * 1141:     function utf8_to_currentCharset($string)
 *
 *              SECTION: Reindexing
 * 1173:     function reindexPhash($phash, $pageId)
 * 1227:     function getUidRootLineForClosestTemplate($id)
 *
 *              SECTION: SQL functions
 * 1270:     function removeIndexedPhashRow($phashList,$clearPageCache=1)
 * 1314:     function getGrListEntriesForPhash($phash,$gr_list)
 * 1334:     function processStopWords($stopWords)
 * 1354:     function processPageKeywords($pageKeywords, $pageUid)
 *
 * TOTAL FUNCTIONS: 30
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require_once(PATH_t3lib.'class.t3lib_pagetree.php');
require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once(t3lib_extMgm::extPath('indexed_search').'class.indexer.php');


	// ... all for the rootline!
require_once (PATH_t3lib."class.t3lib_page.php");
require_once (PATH_t3lib."class.t3lib_tstemplate.php");
require_once (PATH_t3lib."class.t3lib_tsparser_ext.php");

	// Keywords mgm:
require_once (PATH_t3lib."class.t3lib_tcemain.php");



/**
 * Indexing class for TYPO3 frontend
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_indexedsearch
 */
class tx_indexedsearch_modfunc1 extends t3lib_extobjbase {

		// Internal, dynamic:
	var $allPhashListed = array();		// phash values accumulations for link to clear all
	var $external_parsers = array();	// External content parsers - objects set here with file extensions as keys.
	var $iconFileNameCache = array();	// File extensions - icon map/cache.
	var $indexerObj;					// Indexer object


	/**
	 * Initialize menu array internally
	 *
	 * @return	void
	 */
	function modMenu()	{
		global $LANG;

		return array (
			'depth' => array(
				0 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_0'),
				1 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_1'),
				2 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_2'),
				3 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_3'),
				999 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_infi'),
			),
			'type' => array(
				0 => 'Overview',
				1 => 'Technical Details',
				2 => 'Words and content',
			)
		);
	}

	/**
	 * Produces main content of the module
	 *
	 * @return	string		HTML output
	 */
	function main()	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $LANG,$TYPO3_CONF_VARS;

			// Return if no page id:
		if ($this->pObj->id<=0)		return;

			// Initialize max-list items
		$this->maxListPerPage = t3lib_div::_GP('listALL') ? 100000 : 100;

			// Processing deletion of phash rows:
		if (t3lib_div::_GP('deletePhash'))	{
			$this->removeIndexedPhashRow(t3lib_div::_GP('deletePhash'));
		}

			// Processing stop-words:
		if (t3lib_div::_POST('_stopwords'))	{
			$this->processStopWords(t3lib_div::_POST('stopWord'));
		}

			// Processing stop-words:
		if (t3lib_div::_POST('_pageKeywords'))	{
			$this->processPageKeywords(t3lib_div::_POST('pageKeyword'), t3lib_div::_POST('pageKeyword_pageUid'));
		}

			// Initialize external document parsers:
			// Example configuration, see ext_localconf.php of this file!
		if (is_array($TYPO3_CONF_VARS['EXTCONF']['indexed_search']['external_parsers']))	{
			foreach($TYPO3_CONF_VARS['EXTCONF']['indexed_search']['external_parsers'] as $extension => $_objRef)	{
				$this->external_parsers[$extension] = &t3lib_div::getUserObj($_objRef);

					// Init parser and if it returns false, unset its entry again:
				if (!$this->external_parsers[$extension]->softInit($extension))	{
					unset($this->external_parsers[$extension]);
				}
			}
		}

			// Initialize indexer if we need it (metaphone display does...)
		$this->indexerObj = &t3lib_div::makeInstance('tx_indexedsearch_indexer');

			// Set CSS styles specific for this document:
		$this->pObj->content = str_replace('/*###POSTCSSMARKER###*/','
			TABLE.c-list TR TD { white-space: nowrap; vertical-align: top; }
		',$this->pObj->content);


			// Check if details for a phash record should be shown:
		if (t3lib_div::_GET('phash'))	{

				// Show title / function menu:
			$theOutput.=$this->pObj->doc->spacer(5);
			$theOutput.=$this->pObj->doc->section('Details for a single result row:',$this->showDetailsForPhash(t3lib_div::_GET('phash')),0,1);
		} elseif (t3lib_div::_GET('wid'))	{

				// Show title / function menu:
			$theOutput.=$this->pObj->doc->spacer(5);
			$theOutput.=$this->pObj->doc->section('Details for a word:',$this->showDetailsForWord(t3lib_div::_GET('wid')),0,1);
		} elseif (t3lib_div::_GET('metaphone'))	{

				// Show title / function menu:
			$theOutput.=$this->pObj->doc->spacer(5);
			$theOutput.=$this->pObj->doc->section('Details for metaphone value:',$this->showDetailsForMetaphone(t3lib_div::_GET('metaphone')),0,1);
		} elseif (t3lib_div::_GET('reindex'))	{

				// Show title / function menu:
			$theOutput.=$this->pObj->doc->spacer(5);
			$theOutput.=$this->pObj->doc->section('Reindexing...',$this->reindexPhash(t3lib_div::_GET('reindex'),t3lib_div::_GET('reindex_id')),0,1);
		} else {	// Detail listings:
				// Depth function menu:
			$h_func = t3lib_BEfunc::getFuncMenu($this->pObj->id,'SET[type]',$this->pObj->MOD_SETTINGS['type'],$this->pObj->MOD_MENU['type'],'index.php');
			$h_func.= t3lib_BEfunc::getFuncMenu($this->pObj->id,'SET[depth]',$this->pObj->MOD_SETTINGS['depth'],$this->pObj->MOD_MENU['depth'],'index.php');

				// Show title / function menu:
			$theOutput.=$this->pObj->doc->spacer(5);
			$theOutput.=$this->pObj->doc->section($LANG->getLL('title'),$h_func,0,1);

			$theOutput.=$this->drawTableOfIndexedPages();
		}

        return $theOutput;
    }











	/*******************************
	 *
	 * Drawing table of indexed pages
	 *
	 ******************************/

	/**
	 * Produces a table with indexing information for each page.
	 *
	 * @return	string		HTML output
	 */
	function drawTableOfIndexedPages()	{
		global $BACK_PATH;

			// Drawing tree:
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
		$tree->init('AND '.$perms_clause);

		$HTML = '<img src="'.$BACK_PATH.t3lib_iconWorks::getIcon('pages',$this->pObj->pageinfo).'" width="18" height="16" align="top" alt="" />';
		$tree->tree[] = Array(
			'row' => $this->pObj->pageinfo,
			'HTML' => $HTML
		);

		if ($this->pObj->MOD_SETTINGS['depth'])	{
			$tree->getTree($this->pObj->id, $this->pObj->MOD_SETTINGS['depth'], '');
		}

			// Traverse page tree:
		$code = '';
		foreach($tree->tree as $data)	{
			$code.= $this->indexed_info(
						$data['row'],
						$data['HTML'].
							$this->showPageDetails(t3lib_div::fixed_lgd($data['row']['title'], 20),$data['row']['uid'])
					);
		}

		if ($code)	{
			$code = '<br/><br/>
					<table border="0" cellspacing="1" cellpadding="2" class="c-list">'.
						$this->printPhashRowHeader().
						$code.
					'</table>';

				// Create section to output:
			$theOutput.=$this->pObj->doc->section('',$code,0,1);
		} else {
			$theOutput.=$this->pObj->doc->section('','<br/><br/>'.$this->pObj->doc->icons(1).'There were no indexed pages found in the tree.<br/><br/>',0,1);
		}

		return 	$theOutput;
	}

	/**
	 * Create information table row for a page regarding indexing information.
	 *
	 * @param	array		Data array for this page
	 * @param	string		HTML content for first column (page tree icon etc.)
	 * @return	string		HTML code. (table row)
	 */
	function indexed_info($data, $firstColContent)	{

			// Query:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'ISEC.*, IP.*, count(*) AS count_val',
					'index_phash IP, index_section ISEC',
					'IP.phash = ISEC.phash AND ISEC.page_id = '.intval($data['uid']),
					'IP.phash,IP.phash_grouping,IP.cHashParams,IP.data_filename,IP.data_page_id,IP.data_page_reg1,IP.data_page_type,IP.data_page_mp,IP.gr_list,IP.item_type,IP.item_title,IP.item_description,IP.item_mtime,IP.tstamp,IP.item_size,IP.contentHash,IP.crdate,IP.parsetime,IP.sys_language_uid,IP.item_crdate,ISEC.phash,ISEC.phash_t3,ISEC.rl0,ISEC.rl1,ISEC.rl2,ISEC.page_id,ISEC.uniqid,IP.externalUrl,IP.recordUid,IP.freeIndexUid,IP.freeIndexSetId',
					'IP.item_type, IP.tstamp',
					($this->maxListPerPage+1)
				);

			// Initialize variables:
		$rowCount = 0;
		$lines = array();		// Collecting HTML rows here.
		$phashAcc = array();	// Collecting phash values (to remove local indexing for)
		$phashAcc[] = 0;

			// Traverse the result set of phash rows selected:
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if ($rowCount == $this->maxListPerPage)	{
				$rowCount++;	// Increase to the extra warning row will appear as well.
				break;
			}

				// Adds a display row:
			$lines[$row['phash_grouping']][] = $this->printPhashRow(
						$row,
						isset($lines[$row['phash_grouping']]),
						$this->getGrListEntriesForPhash($row['phash'], $row['gr_list'])
					);
			$rowCount++;
			$phashAcc[] = $row['phash'];
			$this->allPhashListed[] = $row['phash'];	// For removing all shown phash rows.
		}

			// Compile rows into the table:
		$out = '';
		$cellAttrib = ($data['_CSSCLASS'] ? ' class="'.$data['_CSSCLASS'].'"' : '');
		if (count($lines))	{
			$firstColContent = '<td rowspan="'.$rowCount.'"'.$cellAttrib.'>'.$firstColContent.'</td>';
			foreach($lines as $rowSet)	{
				foreach($rowSet as $rows)	{
					$out.='
						<tr class="bgColor-20">'.$firstColContent.implode('',$rows).'</tr>';

					$firstColContent = '';
				}
			}

			if ($rowCount > $this->maxListPerPage)	{	// Now checking greater than, because we increased $rowCount before...
				$out.='
				<tr class="bgColor-20">
					<td>&nbsp;</td>
					<td colspan="'.($this->returnNumberOfColumns()-1).'">'.$this->pObj->doc->icons(3).'<span class="">There were more than '.$this->maxListPerPage.' rows. <a href="'.htmlspecialchars('index.php?id='.$this->pObj->id.'&listALL=1').'">Click here to list them ALL!</a></span></td>
				</tr>';
			}
		} else {
			$out.='
				<tr class="bgColor-20">
					<td'.$cellAttrib.'>'.$firstColContent.'</td>
					<td colspan="'.($this->returnNumberOfColumns()-1).'"><em>Not indexed</em></td>
				</tr>';
		}

			// Checking for phash-rows which are NOT joined with the section table:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('IP.*', 'index_phash IP', 'IP.data_page_id = '.intval($data['uid']).' AND IP.phash NOT IN ('.implode(',',$phashAcc).')');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$out.='
				<tr class="typo3-red">
					<td colspan="'.$this->returnNumberOfColumns().'"><b>Warning:</b> phash-row "'.$row['phash'].'" didn\'t have a representation in the index_section table!</td>
				</tr>';
			$this->allPhashListed[] = $row['phash'];
		}

		return $out;
	}

	/**
	 * Render a single row of information about a indexing entry.
	 *
	 * @param	array		Row from query (combined phash table with sections etc).
	 * @param	boolean		Set if grouped to previous result; the icon of the element is not shown again.
	 * @param	array		Array of index_grlist records.
	 * @return	array		Array of table rows.
	 * @see indexed_info()
	 */
	function printPhashRow($row,$grouping=0,$extraGrListRows)	{
		$lines = array();

			// Title cell attributes will highlight TYPO3 pages with a slightly darker color (bgColor4) than attached medias. Also IF there are more than one section record for a phash row it will be red as a warning that something is wrong!
		$titleCellAttribs = $row['count_val']!=1?' bgcolor="red"':($row['item_type']==='0' ? ' class="bgColor4"' : '');

		if ($row['item_type'])	{
			$arr = unserialize($row['cHashParams']);
			$page = $arr['key'] ? ' ['.$arr['key'].']' : '';
		} else $page = '';
		$elTitle = $this->linkDetails($row['item_title'] ? htmlspecialchars(t3lib_div::fixed_lgd_cs($this->utf8_to_currentCharset($row['item_title']), 20).$page) : '<em>[No Title]</em>',$row['phash']);
		$cmdLinks = $this->printRemoveIndexed($row['phash'],'Clear phash-row').$this->printReindex($row,'Re-index element');

		switch($this->pObj->MOD_SETTINGS['type'])	{
			case 1:		// Technical details:
					// Display icon:
				if (!$grouping)	{
					$lines[] = '<td>'.$this->makeItemTypeIcon($row['item_type'], $row['data_filename'] ? $row['data_filename'] : $row['item_title']).'</td>';
				} else {
					$lines[] = '<td>&nbsp;</td>';
				}

					// Title displayed:
				$lines[] = '<td'.$titleCellAttribs.'>'.$elTitle.'</td>';

					// Remove-indexing-link:
				$lines[] = '<td>'.$cmdLinks.'</td>';

					// Various data:
				$lines[] = '<td>'.$row['phash'].'</td>';
				$lines[] = '<td>'.$row['contentHash'].'</td>';

				if ($row['item_type']==='0')	{
					$lines[] = '<td>'.($row['data_page_id'] ? $row['data_page_id'] : '&nbsp;').'</td>';
					$lines[] = '<td>'.($row['data_page_type'] ? $row['data_page_type'] : '&nbsp;').'</td>';
					$lines[] = '<td>'.($row['sys_language_uid'] ? $row['sys_language_uid'] : '&nbsp;').'</td>';
					$lines[] = '<td>'.($row['data_page_mp'] ? $row['data_page_mp'] : '&nbsp;').'</td>';
				} else {
					$lines[] = '<td colspan="4">'.htmlspecialchars($row['data_filename']).'</td>';
				}
				$lines[] = '<td>'.$row['gr_list'].$this->printExtraGrListRows($extraGrListRows).'</td>';
				$lines[] = '<td>'.$this->printRootlineInfo($row).'</td>';
				$lines[] = '<td>'.($row['page_id'] ? $row['page_id'] : '&nbsp;').'</td>';
				$lines[] = '<td>'.($row['phash_t3']!=$row['phash'] ? $row['phash_t3'] : '&nbsp;').'</td>';
				$lines[] = '<td>'.($row['freeIndexUid'] ? $row['freeIndexUid'].($row['freeIndexSetId']?'/'.$row['freeIndexSetId']:'') : '&nbsp;').'</td>';
				$lines[] = '<td>'.($row['recordUid'] ? $row['recordUid'] : '&nbsp;').'</td>';



					// cHash parameters:
				$arr = unserialize($row['cHashParams']);
				if (is_array($arr))		{
					$theCHash = $arr['cHash'];
					unset($arr['cHash']);
				}

				if ($row['item_type'])	{	// pdf...
					$lines[] = '<td>'.($arr['key'] ? 'Page '.$arr['key'] : '').'&nbsp;</td>';
				} elseif ($row['item_type']==0) {
					$lines[] = '<td>'.htmlspecialchars(t3lib_div::implodeArrayForUrl('',$arr)).'&nbsp;</td>';
				} else {
					$lines[] = '<td class="bgColor">&nbsp;</td>';
				}

				$lines[] = '<td>'.$theCHash.'</td>';
			break;
			case 2:		// Words and content:
					// Display icon:
				if (!$grouping)	{
					$lines[] = '<td>'.$this->makeItemTypeIcon($row['item_type'], $row['data_filename'] ? $row['data_filename'] : $row['item_title']).'</td>';
				} else {
					$lines[] = '<td>&nbsp;</td>';
				}

					// Title displayed:
				$lines[] = '<td'.$titleCellAttribs.'>'.$elTitle.'</td>';

					// Remove-indexing-link:
				$lines[] = '<td>'.$cmdLinks.'</td>';

					// Query:
				$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
							'*',
							'index_fulltext',
							'phash = '.intval($row['phash'])
						);
				$lines[] = '<td style="white-space: normal;">'.
							htmlspecialchars(t3lib_div::fixed_lgd_cs($this->utf8_to_currentCharset($ftrows[0]['fulltextdata']),3000)).
							'<hr/><em>Size: '.strlen($ftrows[0]['fulltextdata']).'</em>'.
							'</td>';

					// Query:
				$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
							'index_words.baseword, index_rel.*',
							'index_rel, index_words',
							'index_rel.phash = '.intval($row['phash']).
								' AND index_words.wid = index_rel.wid',
							'',
							'',
							'',
							'baseword'
						);

				$wordList = '';
				if (is_array($ftrows))	{
					$indexed_words = array_keys($ftrows);
					sort($indexed_words);
					$wordList = htmlspecialchars($this->utf8_to_currentCharset(implode(' ',$indexed_words)));
					$wordList.='<hr/><em>Count: '.count($indexed_words).'</em>';
				}

				$lines[] = '<td style="white-space: normal;">'.$wordList.'</td>';
			break;
			default:	// Overview
					// Display icon:
				if (!$grouping)	{
					$lines[] = '<td>'.$this->makeItemTypeIcon($row['item_type'], $row['data_filename'] ? $row['data_filename'] : $row['item_title']).'</td>';
				} else {
					$lines[] = '<td>&nbsp;</td>';
				}

					// Title displayed:
				$lines[] = '<td'.$titleCellAttribs.'>'.$elTitle.'</td>';

					// Remove-indexing-link:
				$lines[] = '<td>'.$cmdLinks.'</td>';

				$lines[] = '<td style="white-space: normal;">'.htmlspecialchars($this->utf8_to_currentCharset($row['item_description'])).'...</td>';
				$lines[] = '<td>'.t3lib_div::formatSize($row['item_size']).'</td>';
				$lines[] = '<td>'.t3lib_BEfunc::dateTimeAge($row['tstamp']).'</td>';
			break;
		}

		return $lines;
	}

	/**
	 * Creates the header row for the table
	 *
	 * @return	string		HTML string (table row)
	 */
	function printPhashRowHeader()	{
		$lines = array();

		switch($this->pObj->MOD_SETTINGS['type'])	{
			case 1:
				$lines[] = '<td>&nbsp;</td>';
				$lines[] = '<td>&nbsp;</td>';
				$lines[] = '<td>Title</td>';
				$lines[] = '<td bgcolor="red">'.$this->printRemoveIndexed('ALL','Clear ALL phash-rows below!').'</td>';

				$lines[] = '<td>pHash</td>';
				$lines[] = '<td>contentHash</td>';
				$lines[] = '<td>&amp;id</td>';
				$lines[] = '<td>&amp;type</td>';
				$lines[] = '<td>&amp;L</td>';
				$lines[] = '<td>&amp;MP</td>';
				$lines[] = '<td>grlist</td>';
				$lines[] = '<td>Rootline</td>';
				$lines[] = '<td>page_id</td>';
				$lines[] = '<td>phash_t3</td>';
				$lines[] = '<td>CfgUid</td>';
				$lines[] = '<td>RecUid</td>';
				$lines[] = '<td>GET-parameters</td>';
				$lines[] = '<td>&amp;cHash</td>';
			break;
			case 2:
				$lines[] = '<td>&nbsp;</td>';
				$lines[] = '<td>&nbsp;</td>';
				$lines[] = '<td>Title</td>';
				$lines[] = '<td bgcolor="red">'.$this->printRemoveIndexed('ALL','Clear ALL phash-rows below!').'</td>';
				$lines[] = '<td>Content<br/>
							<img src="clear.gif" width="300" height="1" alt="" /></td>';
				$lines[] = '<td>Words<br/>
							<img src="clear.gif" width="300" height="1" alt="" /></td>';
			break;
			default:
				$lines[] = '<td>&nbsp;</td>';
				$lines[] = '<td>&nbsp;</td>';
				$lines[] = '<td>Title</td>';
				$lines[] = '<td bgcolor="red">'.$this->printRemoveIndexed('ALL','Clear ALL phash-rows below!').'</td>';
				$lines[] = '<td>Description</td>';
				$lines[] = '<td>Size</td>';
				$lines[] = '<td>Indexed:</td>';
			break;
		}

		$out = '<tr class="tableheader bgColor5">'.implode('',$lines).'</tr>';
		return $out;
	}

	/**
	 * Returns the number of columns depending on display type of list
	 *
	 * @return	integer		Number of columns in list:
	 */
	function returnNumberOfColumns()	{
		switch($this->pObj->MOD_SETTINGS['type'])	{
			case 1:
				return 18;
			break;
			case 2:
				return 6;
			break;
			default:
				return 7;
			break;
		}
	}











	/*******************************
	 *
	 * Details display, phash row
	 *
	 *******************************/

	/**
	 * Showing details for a particular phash row
	 *
	 * @param	integer		phash value to display details for.
	 * @return	string		HTML content
	 */
	function showDetailsForPhash($phash)	{

		$content = '';

			// Selects the result row:
		$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'*',
					'index_phash',
					'phash = '.intval($phash)
				);
		$phashRecord = $ftrows[0];

			// If found, display:
		if (is_array($phashRecord))	{
			$content.= '<h4>phash row content:</h4>'.
						$this->utf8_to_currentCharset(t3lib_div::view_array($phashRecord));

				// Getting debug information if any:
			$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'*',
						'index_debug',
						'phash = '.intval($phash)
					);
			if (is_array($ftrows))	{
				$debugInfo = unserialize($ftrows[0]['debuginfo']);
				$lexer = $debugInfo['lexer'];
				unset($debugInfo['lexer']);

				$content.= '<h3>Debug information:</h3>'.
						$this->utf8_to_currentCharset(t3lib_div::view_array($debugInfo));

				$content.= '<h4>Debug information / lexer splitting:</h4>'.
						'<hr/><b>'.
						$this->utf8_to_currentCharset($lexer).
						'</b><hr/>';
			}



			$content.='<h3>Word statistics</h3>';

				// Finding all words for this phash:
			$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'index_words.*, index_rel.*',
						'index_rel, index_words',
						'index_rel.phash = '.intval($phash).
							' AND index_words.wid = index_rel.wid',
						'',
						'index_words.baseword',
						''
					);
			$pageRec = t3lib_BEfunc::getRecord('pages', $phashRecord['data_page_id']);
			$showStopWordCheckBox = $GLOBALS['BE_USER']->isAdmin();
			$content.= $this->listWords($ftrows, 'All words found on page ('.count($ftrows).'):', $showStopWordCheckBox, $pageRec);

				// Group metaphone hash:
			$metaphone = array();
			foreach($ftrows as $row)	{
				$metaphone[$row['metaphone']][] = $row['baseword'];
			}
			$content.= $this->listMetaphoneStat($metaphone, 'Metaphone stats:');

				// Finding top-20 on frequency for this phash:
			$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'index_words.baseword, index_words.metaphone, index_rel.*',
						'index_rel, index_words',
						'index_rel.phash = '.intval($phash).
							' AND index_words.wid = index_rel.wid
							 AND index_words.is_stopword=0',
						'',
						'index_rel.freq DESC',
						'20'
					);
			$content.= $this->listWords($ftrows, 'Top-20 words by frequency:', 2);

				// Finding top-20 on count for this phash:
			$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'index_words.baseword, index_words.metaphone, index_rel.*',
						'index_rel, index_words',
						'index_rel.phash = '.intval($phash).
							' AND index_words.wid = index_rel.wid
							 AND index_words.is_stopword=0',
						'',
						'index_rel.count DESC',
						'20'
					);
			$content.= $this->listWords($ftrows, 'Top-20 words by count:', 2);


			$content.='<h3>Section records for this phash</h3>';

				// Finding sections for this record:
			$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'*',
						'index_section',
						'index_section.phash = '.intval($phash),
						'',
						'',
						''
					);
			$content.= t3lib_div::view_array($ftrows);

				// Add go-back link:
			$content = $this->linkList().$content.$this->linkList();

		} else $content.= 'Error: No phash row found';

		return $content;
	}

	/**
	 * Create table with list of words from $ftrows
	 *
	 * @param	array		Array of records selected from index_rel/index_words
	 * @param	string		Header string to show before table.
	 * @param	boolean		If set, the stopWord checkboxes will be shown in the word list. Only for admins. (because it is a global setting, not per-site).
	 * @param	array		The page record from which to load the keywords, if any.
	 * @return	string		HTML table
	 */
	function listWords($ftrows,$header, $stopWordBoxes=FALSE, $page='')	{

			// Prepare keywords:
		$keywords = is_array($page) ? array_flip(t3lib_div::trimExplode(',',$page['keywords'], 1)) : '';

			// Render list:
		$trows = '';
		$trows.= '
			<tr class="tableheader bgColor5">
				'.($stopWordBoxes ? '<td>'.htmlspecialchars('Stopword:').'</td>' : '').'
				<td>'.htmlspecialchars('Word:').'</td>
				<td>'.htmlspecialchars('Count:').'</td>
				<td>'.htmlspecialchars('First:').'</td>
				<td>'.htmlspecialchars('Frequency:').'</td>
				<td>'.htmlspecialchars('Flags:').'</td>
				'.(is_array($keywords) ? '<td>'.htmlspecialchars('Page keyword:').'</td>' : '').'
			</tr>
		';
		foreach($ftrows as $row)	{
			$hiddenField = $stopWordBoxes!=2 ? '<input type="hidden" name="stopWord['.$row['wid'].']" value="0" />' : '';
			$trows.= '
				<tr class="'.($row['is_stopword'] ? 'bgColor' : 'bgColor4').'">
					'.($stopWordBoxes ? '<td align="center"'.($row['is_stopword'] ? ' style="background-color:red;"' : '').'>'.$hiddenField.'<input type="checkbox" name="stopWord['.$row['wid'].']" value="1"'.($row['is_stopword']?'checked="checked"':'').' /></td>' : '').'
					<td>'.$this->linkWordDetails(htmlspecialchars($this->utf8_to_currentCharset($row['baseword'])), $row['wid']).'</td>
					<td>'.htmlspecialchars($row['count']).'</td>
					<td>'.htmlspecialchars($row['first']).'</td>
					<td>'.htmlspecialchars($row['freq']).'</td>
					<td>'.htmlspecialchars($this->flagsMsg($row['flags'])).'</td>
					'.(is_array($keywords) ? '<td align="center"'.(isset($keywords[$row['baseword']]) ? ' class="bgColor2"' : '').'><input type="hidden" name="pageKeyword['.$row['baseword'].']" value="0" /><input type="checkbox" name="pageKeyword['.$row['baseword'].']" value="1"'.(isset($keywords[$row['baseword']])?'checked="checked"':'').' /></td>' : '').'
				</tr>
			';
		}

		return '<h4>'.htmlspecialchars($header).'</h4>'.
					'
					<table border="0" cellspacing="1" cellpadding="2" class="c-list">
					'.$trows.'
					</table>'.
					($stopWordBoxes ? '<input type="submit" value="Change stop-word settings" name="_stopwords" onclick="document.webinfoForm.action=\''.htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')).'\';" />' : '').
					(is_array($keywords) ? '<input type="submit" value="Set page keywords" name="_pageKeywords" onclick="document.webinfoForm.action=\''.htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')).'\';" /><input type="hidden" name="pageKeyword_pageUid" value="'.$page['uid'].'" />'.
										'<br/>Current keywords are: <em>'.htmlspecialchars(implode(', ',array_keys($keywords))).'</em>' : '');
	}

	/**
	 * Displays table of metaphone groups larger than 1
	 *
	 * @param	array		Result from word selection (index_rel/index_words)
	 * @param	string		Header string
	 * @return	string		HTML table
	 */
	function listMetaphoneStat($ftrows,$header)	{

		$trows = '';
		$trows.= '
			<tr class="tableheader bgColor5">
				<td>'.htmlspecialchars('Metaphone:').'</td>
				<td>'.htmlspecialchars('Hash:').'</td>
				<td>'.htmlspecialchars('Count:').'</td>
				<td>'.htmlspecialchars('Words:').'</td>
			</tr>
		';
		foreach($ftrows as $metaphone => $words)	{
			if (count($words)>1)	{
				$trows.= '
					<tr class="bgColor4">
						<td>'.$this->linkMetaPhoneDetails($this->indexerObj->metaphone($words[0],1),$metaphone).'</td>
						<td>'.htmlspecialchars($metaphone).'</td>
						<td>'.htmlspecialchars(count($words)).'</td>
						<td style="white-space: normal;">'.htmlspecialchars($this->utf8_to_currentCharset(implode(', ',$words))).'</td>
					</tr>
				';
			}
		}

		return '<h4>'.htmlspecialchars($header).'</h4>'.
					'<table border="0" cellspacing="1" cellpadding="2" class="c-list">
					'.$trows.'
					</table>';
	}

	/**
	 * Wraps input string in a link that will display details for the word. Eg. which other pages has the word, metaphone associations etc.
	 *
	 * @param	string		String to wrap, possibly a title or so.
	 * @param	integer		wid value to show details for
	 * @return	string		Wrapped string
	 */
	function linkWordDetails($string,$wid)	{
		return '<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('wid'=>$wid,'phash'=>''))).'">'.$string.'</a>';
	}


	/**
	 * Wraps input string in a link to see more details for metaphone value
	 *
	 * @param	string		String to wrap
	 * @param	integer		Metaphone value
	 * @return	string		Wrapped string
	 */
	function linkMetaPhoneDetails($string,$metaphone)	{
		return '<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('metaphone'=>$metaphone,'wid'=>'','phash'=>''))).'">'.$string.'</a>';
	}

	/**
	 * Creates message for flag value
	 *
	 * @param	integer		Flags integer
	 * @return	string		Message string
	 */
	function flagsMsg($flags)	{
		if ($flags > 0)	{
			return
				($flags & 128 ? '<title>' : '').	// pow(2,7)
				($flags & 64 ? '<meta/keywords>' : '').	// pow(2,6)
				($flags & 32 ? '<meta/description>' : '').	// pow(2,5)
				' ('.$flags.')';
		}
	}










	/*******************************
	 *
	 * Details display, words / metaphone
	 *
	 *******************************/

	/**
	 * Show details for words
	 *
	 * @param	integer		Word ID (wid)
	 * @return	string		HTML content
	 */
	function showDetailsForWord($wid)	{

			// Select references to this word
		$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'index_phash.*, index_section.*, index_rel.*',
					'index_rel, index_section, index_phash',
					'index_rel.wid = '.intval($wid).
						' AND index_rel.phash = index_section.phash'.
						' AND index_section.phash = index_phash.phash',
					'',
					'index_rel.freq DESC',
					''
				);

			// Headers:
		$content.='
			<tr class="tableheader bgColor5">
				<td>phash</td>
				<td>page_id</td>
				<td>data_filename</td>
				<td>count</td>
				<td>first</td>
				<td>freq</td>
				<td>flags</td>
			</tr>';

		if (is_array($ftrows))	{
			foreach($ftrows as $wDat)	{
				$content.='
					<tr class="bgColor4">
						<td>'.$this->linkDetails(htmlspecialchars($wDat['phash']),$wDat['phash']).'</td>
						<td>'.htmlspecialchars($wDat['page_id']).'</td>
						<td>'.htmlspecialchars($wDat['data_filename']).'</td>
						<td>'.htmlspecialchars($wDat['count']).'</td>
						<td>'.htmlspecialchars($wDat['first']).'</td>
						<td>'.htmlspecialchars($wDat['freq']).'</td>
						<td>'.htmlspecialchars($wDat['flags']).'</td>
					</tr>';
			}
		}

			// Compile table:
		$content = '
			<table border="0" cellspacing="1" cellpadding="2" class="c-list">'.
				$content.'
			</table>';

			// Add go-back link:
		$content = $content.$this->linkList();

		return $content;
	}

	/**
	 * Show details for metaphone value
	 *
	 * @param	integer		Metaphone integer hash
	 * @return	string		HTML content
	 */
	function showDetailsForMetaphone($metaphone)	{

			// Finding top-20 on frequency for this phash:
		$ftrows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'index_words.*',
					'index_words',
					'index_words.metaphone = '.intval($metaphone),
					'',
					'index_words.baseword',
					''
				);

		if (count($ftrows))	{
			$content.='<h4>Metaphone: '.$this->indexerObj->metaphone($ftrows[0]['baseword'],1).'</h4>';

			$content.='
				<tr class="tableheader bgColor5">
					<td>Word</td>
					<td>Is stopword?</td>
				</tr>';

			if (is_array($ftrows))	{
				foreach($ftrows as $wDat)	{
					$content.='
						<tr class="bgColor4">
							<td>'.$this->linkWordDetails(htmlspecialchars($wDat['baseword']),$wDat['wid']).'</td>
							<td>'.htmlspecialchars($wDat['is_stopword'] ? 'YES' : 'No').'</td>
						</tr>';
				}
			}

			$content = '
				<table border="0" cellspacing="1" cellpadding="2" class="c-list">'.
					$content.'
				</table>';

			if ($this->indexerObj->metaphone($ftrows[0]['baseword'])!=$metaphone)	{
				$content.='ERROR: Metaphone string and hash did not match for some reason!?';
			}

				// Add go-back link:
			$content = $content.$this->linkList();
		}

		return $content;
	}












	/*******************************
	 *
	 * Helper functions
	 *
	 *******************************/

	/**
	 * Creates icon which clears indexes for a certain list of phash values.
	 *
	 * @param	string		List of phash integers
	 * @param	string		Alt-text for the garbage bin icon.
	 * @return	string		HTML img-tag with link around.
	 */
	function printRemoveIndexed($phash,$alt)	{
		return '<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('deletePhash'=>$phash))).'">'.
				'<img src="'.$GLOBALS['BACK_PATH'].'gfx/garbage.gif" width="11" hspace="1" vspace="2" height="12" border="0" title="'.htmlspecialchars($alt).'" alt="" />'.
				'</a>';
	}

	/**
	 * Button for re-indexing of documents
	 *
	 * @param	array		phash table result row.
	 * @param	string		Title attribute text for icon
	 * @return	string		HTML content; Icon wrapped in link.
	 */
	function printReindex($resultRow,$alt)	{
		if ($resultRow['item_type'] && $resultRow['item_type']!=='0')	{
			return '<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('reindex'=>$resultRow['phash'],'reindex_id'=>$resultRow['page_id']))).'">'.
					'<img src="'.$GLOBALS['BACK_PATH'].'gfx/refresh_n.gif" width="14" hspace="1" vspace="2" height="14" border="0" title="'.htmlspecialchars($alt).'" alt="" />'.
					'</a>';
		}
	}

	/**
	 * Wraps input string in a link that will display details for the phash value set.
	 *
	 * @param	string		String to wrap, possibly a title or so.
	 * @param	integer		phash value to show details for
	 * @return	string		Wrapped string
	 */
	function linkDetails($string,$phash)	{
		return '<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('phash'=>$phash))).'">'.$string.'</a>';
	}

	/**
	 * Creates link back to listing
	 *
	 * @return	string		Link back to list
	 */
	function linkList()	{
		return '<br/><a href="index.php?id='.$this->pObj->id.'">Back to list.</a><br/>';
	}

	/**
	 * Wraps input string in a link that will display details for the phash value set.
	 *
	 * @param	string		String to wrap, possibly a title or so.
	 * @param	integer		phash value to show details for
	 * @return	string		Wrapped string
	 */
	function showPageDetails($string,$id)	{
		return '<a href="'.htmlspecialchars('index.php?id='.$id.'&SET[depth]=0&SET[type]=1').'">'.$string.'</a>';
	}

	/**
	 * Prints the gr_lists attached to a indexed entry.
	 *
	 * @param	array		Array of index_grlist records
	 * @return	string		HTML code.
	 */
	function printExtraGrListRows($extraGrListRows)	{
		if (count($extraGrListRows))	{
			reset($extraGrListRows);
			$lines=array();
			while(list(,$r)=each($extraGrListRows))	{
				$lines[] = $r['gr_list'];
			}
			return "<br/>".$GLOBALS['TBE_TEMPLATE']->dfw(implode('<br/>',$lines));
		}
	}

	/**
	 * Print path for indexing
	 *
	 * @param	array		Result row with content from index_section
	 * @return	string		Rootline information
	 */
	function printRootlineInfo($row)	{
		$uidCollection = array();

		if ($row['rl0'])	{
			$uidCollection[0] = $row['rl0'];
			if ($row['rl1'])	{
				$uidCollection[1] = $row['rl1'];
				if ($row['rl2'])	{
					$uidCollection[2] = $row['rl2'];

						// Additional levels:
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['indexed_search']['addRootLineFields']))	{
						foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['indexed_search']['addRootLineFields'] as $fieldName => $rootLineLevel)	{
							if ($row[$fieldName])	{
								$uidCollection[$rootLineLevel] = $row[$fieldName];
							}
						}
					}
				}
			}
		}

			// Return root line.
		ksort($uidCollection);
		return implode('/',$uidCollection);
	}

	/**
	 * Return icon for file extension
	 *
	 * @param	string		File extension / item type
	 * @param	string		Title attribute value in icon.
	 * @return	string		<img> tag for icon
	 */
	function makeItemTypeIcon($it,$alt='')	{
		if (!isset($this->iconFileNameCache[$it]))	{
			if ($it==='0')	{
				$icon = 'EXT:indexed_search/pi/res/pages.gif';
			} elseif ($this->external_parsers[$it]) {
				$icon = $this->external_parsers[$it]->getIcon($it);
			}

			$fullPath = t3lib_div::getFileAbsFileName($icon);

			if ($fullPath)	{
				$info = @getimagesize($fullPath);
				$iconPath = $GLOBALS['BACK_PATH'].'../'.substr($fullPath,strlen(PATH_site));
				$this->iconFileNameCache[$it] = is_array($info) ? '<img src="'.$iconPath.'" '.$info[3].' title="###TITLE_ATTRIBUTE###" alt="" />' : '';
			}
		}
		return str_replace('###TITLE_ATTRIBUTE###',htmlspecialchars($it.': '.$alt),$this->iconFileNameCache[$it]);
	}

	/**
	 * Converts the input string from utf-8 to the backend charset.
	 *
	 * @param	string		String to convert (utf-8)
	 * @return	string		Converted string (backend charset if different from utf-8)
	 */
	function utf8_to_currentCharset($string)	{
		global $LANG;
		if ($LANG->charSet != 'utf-8')	{
			$string = $LANG->csConvObj->utf8_decode($string, $LANG->charSet, TRUE);
		}
		return $string;
	}












	/********************************
	 *
	 * Reindexing
	 *
	 *******************************/

	/**
	 * Re-indexing files/records attached to a page.
	 *
	 * @param	integer		Phash value
	 * @param	integer		The page uid for the section record (file/url could appear more than one place you know...)
	 * @return	string		HTML content
	 */
	function reindexPhash($phash, $pageId)	{

			// Query:
		list($resultRow) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'ISEC.*, IP.*',
					'index_phash IP, index_section ISEC',
					'IP.phash = ISEC.phash
						AND IP.phash = '.intval($phash).'
						AND ISEC.page_id = '.intval($pageId)
				);

		$content = '';
		if (is_array($resultRow))	{
			if ($resultRow['item_type'] && $resultRow['item_type']!=='0')	{

					// (Re)-Indexing file on page.
				$indexerObj = &t3lib_div::makeInstance('tx_indexedsearch_indexer');
				$indexerObj->backend_initIndexer($pageId, 0, 0, '', $this->getUidRootLineForClosestTemplate($pageId));

					// URL or local file:
				if ($resultRow['externalUrl'])	{
					$indexerObj->indexExternalUrl($resultRow['data_filename']);
				} else {
					$indexerObj->indexRegularDocument($resultRow['data_filename'], TRUE);
				}

				if ($indexerObj->file_phash_arr['phash'] != $resultRow['phash'])	{
					$content.= 'ERROR: phash ('.$indexerObj->file_phash_arr['phash'].') did NOT match '.$resultRow['phash'].' for strange reasons!';
				}

				$content.='<h4>Log for re-indexing of "'.htmlspecialchars($resultRow['data_filename']).'":</h4>';
				$content.=t3lib_div::view_array($indexerObj->internal_log);

				$content.='<h4>Hash-array, page:</h4>';
				$content.=t3lib_div::view_array($indexerObj->hash);

				$content.='<h4>Hash-array, file:</h4>';
				$content.=t3lib_div::view_array($indexerObj->file_phash_arr);
			}
		}

			// Link back to list.
		$content.= $this->linkList();

		return $content;
	}

	/**
	 * Get rootline for closest TypoScript template root.
	 * Algorithm same as used in Web > Template, Object browser
	 *
	 * @param	integer		The page id to traverse rootline back from
	 * @return	array		Array where the root lines uid values are found.
	 */
	function getUidRootLineForClosestTemplate($id)	{
		$tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');	// Defined global here!
		$tmpl->tt_track = 0;	// Do not log time-performance information
		$tmpl->init();

				// Gets the rootLine
		$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($id);
		$tmpl->runThroughTemplates($rootLine,0);	// This generates the constants/config + hierarchy info for the template.

			// Root line uids
		$rootline_uids = array();
		foreach($tmpl->rootLine as $rlkey => $rldat)	{
			$rootline_uids[$rlkey] = $rldat['uid'];
		}

		return $rootline_uids;
	}












	/********************************
	 *
	 * SQL functions
	 *
	 *******************************/

	/**
	 * Removes ALL data regarding a certain list of indexed phash-row
	 *
	 * @param	string		List of phash integers
	 * @param	boolean		If set, page cache is cleared as well.
	 * @return	void
	 */
	function removeIndexedPhashRow($phashList,$clearPageCache=1)	{
			// FIXME: This is only a workaround
		if ($phashList=='ALL')	{
			$this->drawTableOfIndexedPages();
			$phashRows = $this->allPhashListed;
			$this->allPhashListed = array();	// Reset it because it will be filled again later...
		} else {
			$phashRows = t3lib_div::trimExplode(',',$phashList,1);
		}

		foreach($phashRows as $phash)	{
			$phash = intval($phash);
			if ($phash>0)	{

				if ($clearPageCache)	{
						// Clearing page cache:
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('page_id', 'index_section', 'phash='.intval($phash));
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($res))	{
						$idList = array();
						while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
							$idList[] = $row['page_id'];
						}
						$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', 'page_id IN ('.implode(',',$GLOBALS['TYPO3_DB']->cleanIntArray($idList)).')');
					}
				}

					// Removing old registrations for all tables.
				$tableArr = explode(',','index_phash,index_rel,index_section,index_grlist,index_fulltext,index_debug');
				foreach($tableArr as $table)	{
					$GLOBALS['TYPO3_DB']->exec_DELETEquery($table, 'phash='.intval($phash));
				}

					// Did not remove any index_section records for external files where phash_t3 points to this hash!
			}
		}
	}

	/**
	 * Returns an array with gr_list records for a phash
	 *
	 * @param	integer		phash integer to look up on
	 * @param	string		gr_list string to filter OUT of the result (first occurence)
	 * @return	array		Array of records from index_grlist table
	 */
	function getGrListEntriesForPhash($phash,$gr_list)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'index_grlist', 'phash='.intval($phash));
		$lines = array();
		$isRemoved = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if (!$isRemoved && !strcmp($row['gr_list'],$gr_list))	{
				$isRemoved = 1;
			} else {
				$lines[] = $row;
			}
		}
		return $lines;
	}

	/**
	 * Setting / Unsetting stopwords
	 *
	 * @param	array		Array of stop-words WIDs with 0/1 to set / unset
	 * @return	void
	 */
	function processStopWords($stopWords)	{

		if ($GLOBALS['BE_USER']->isAdmin())	{
				// Traverse words
			foreach($stopWords as $wid => $state)	{
				$fieldArray = array(
					'is_stopword' => $state
				);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('index_words', 'wid='.$wid, $fieldArray);
			}
		}
	}

	/**
	 * Setting / Unsetting keywords in page header
	 *
	 * @param	array		Page keywords as keys in array with value 0 or 1 for set or unset.
	 * @param	integer		The page uid of the header where the keywords are to be set.
	 * @return	void
	 */
	function processPageKeywords($pageKeywords, $pageUid)	{

			// Get pages current keywords
		$pageRec = t3lib_BEfunc::getRecord('pages', $pageUid);
		$keywords = array_flip(t3lib_div::trimExplode(',', $pageRec['keywords'], 1));

			// Merge keywords:
		foreach($pageKeywords as $key => $v)	{
			if ($v)	{
				$keywords[$key]=1;
			} else {
				unset($keywords[$key]);
			}
		}

			// Compile new list:
		$data = array();
		$data['pages'][$pageUid]['keywords'] = implode(', ',array_keys($keywords));

		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->stripslashes_values = 0;
		$tce->start($data,array());
		$tce->process_datamap();
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/indexed_search/modfunc1/class.tx_indexedsearch_modfunc1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/indexed_search/modfunc1/class.tx_indexedsearch_modfunc1.php']);
}

?>