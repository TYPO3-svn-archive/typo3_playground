<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Versioning module
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  102: class tx_version_cm1 extends t3lib_SCbase
 *
 *              SECTION: Standard module initialization
 *  138:     function menuConfig()
 *  175:     function main()
 *  236:     function jumpToUrl(URL)
 *  296:     function printContent()
 *
 *              SECTION: Versioning management
 *  322:     function versioningMgm()
 *  485:     function pageSubContent($pid,$c=0)
 *  539:     function lookForOwnVersions($table,$uid)
 *  556:     function adminLinks($table,$row)
 *
 *              SECTION: Workspace management
 *  628:     function workspaceMgm()
 *  688:     function displayWorkspaceOverview()
 *  758:     function displayWorkspaceOverview_list($pArray)
 *  923:     function displayWorkspaceOverview_setInPageArray(&$pArray,$table,$row)
 *  936:     function displayWorkspaceOverview_allStageCmd()
 *
 *              SECTION: Helper functions (REDUNDANT FROM user/ws/index.php - someone could refactor this...)
 *  986:     function formatVerId($verId)
 *  996:     function formatWorkspace($wsid)
 * 1023:     function formatCount($count)
 * 1050:     function versionsInOtherWS($table,$uid)
 * 1080:     function showStageChangeLog($table,$id,$stageCommands)
 * 1129:     function subElements($uid,$treeLevel,$origId=0)
 * 1232:     function subElements_getNonPageRecords($tN, $uid, &$recList)
 * 1262:     function subElements_renderItem(&$tCell,$tN,$uid,$rec,$origId,$iconMode,$HTMLdata)
 * 1331:     function markupNewOriginals()
 * 1353:     function createDiffView($table, $diff_1_record, $diff_2_record)
 * 1470:     function displayWorkspaceOverview_stageCmd($table,&$rec_off)
 * 1557:     function displayWorkspaceOverview_commandLinks($table,&$rec_on,&$rec_off,$vType)
 * 1627:     function displayWorkspaceOverview_commandLinksSub($table,$rec,$origId)
 *
 *              SECTION: Processing
 * 1683:     function publishAction()
 *
 * TOTAL FUNCTIONS: 27
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:version/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
	// DEFAULT initialization of a module [END]

require_once(PATH_t3lib.'class.t3lib_diff.php');
require_once(PATH_typo3.'mod/user/ws/class.wslib.php');
require_once(PATH_t3lib.'class.t3lib_pagetree.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');



/**
 * Versioning module, including workspace management
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class tx_version_cm1 extends t3lib_SCbase {

		// Default variables for backend modules
	var $MCONF = array();				// Module configuration
	var $MOD_MENU = array();			// Module menu items
	var $MOD_SETTINGS = array();		// Module session settings
	var $doc;							// Document Template Object
	var $content;						// Accumulated content


		// Internal:
	var $showWorkspaceCol = 0;
	var $formatWorkspace_cache = array();
	var $formatCount_cache = array();
	var $targets = array();		// Accumulation of online targets.
	var $pageModule = '';			// Name of page module
	var $publishAccess = FALSE;
	var $be_user_Array = array();
	var $stageIndex = array();
	var $recIndex = array();






	/*********************************
	 *
	 * Standard module initialization
	 *
	 *********************************/

	/**
	 * Initialize menu configuration
	 *
	 * @return	void
	 */
	function menuConfig()	{

			// Menu items:
		$this->MOD_MENU = array(
			'filter' => array(
				1 => 'Drafts',
				2 => 'Archive',
				0 => 'All',
			),
			'display' => array(
				0 => '[Live workspace]',
				-98 => 'Draft Workspaces',
				-99 => 'All',
				-1 => '[Default Draft]'
			),
			'diff' => ''
		);

			// Add workspaces:
		if ($GLOBALS['BE_USER']->workspace===0)	{	// Spend time on this only in online workspace because it might take time:
			$workspaces = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title,adminusers,members,reviewers','sys_workspace','pid=0'.t3lib_BEfunc::deleteClause('sys_workspace'),'','title');
			foreach($workspaces as $rec)	{
				if ($GLOBALS['BE_USER']->checkWorkspace($rec))	{
					$this->MOD_MENU['display'][$rec['uid']] = '['.$rec['uid'].'] '.$rec['title'];
				}
			}
		}

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name'], 'ses');
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// Setting module configuration:
		$this->MCONF = $GLOBALS['MCONF'];
		
		$this->REQUEST_URI = str_replace('&sendToReview=1','',t3lib_div::getIndpEnv('REQUEST_URI'));

			// Draw the header.
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="post">';

	        // Add styles
		$this->doc->inDocStylesArray[$GLOBALS['MCONF']['name']] = '
.version-diff-1 { background-color: green; }
.version-diff-2 { background-color: red; }
';

			// Setting up the context sensitive menu:
		$CMparts = $this->doc->getContextMenuCode();
		$this->doc->JScode.= $CMparts[0];
		$this->doc->bodyTagAdditions = $CMparts[1];
		$this->doc->postCode.= $CMparts[2];

			// Getting input data:
		$this->id = intval(t3lib_div::_GP('id'));		// Page id. If set, indicates activation from Web>Versioning module
		if (!$this->id)	{
			$this->uid = intval(t3lib_div::_GP('uid'));		// Record uid. Goes with table name to indicate specific record
			$this->table = t3lib_div::_GP('table');			// Record table. Goes with uid to indicate specific record
		} else {
			$this->uid = $this->id;
			$this->table = 'pages';
		}
		$this->details = t3lib_div::_GP('details');		// Page id. If set, indicates activation from Web>Versioning module
		$this->diffOnly = t3lib_div::_GP('diffOnly');		// Flag. If set, shows only the offline version and with diff-view

			// Force this setting:
		$this->MOD_SETTINGS['expandSubElements'] = TRUE;
		$this->MOD_SETTINGS['diff'] = $this->details || $this->MOD_SETTINGS['diff']?1:0;

			// Reading the record:
		$record = t3lib_BEfunc::getRecord($this->table,$this->uid);
		if ($record['pid']==-1)	{
			$record = t3lib_BEfunc::getRecord($this->table,$record['t3ver_oid']);
		}
		$pidValue = $this->table==='pages' ? $this->uid : $record['pid'];

			// Checking access etc.
		if (is_array($record) && $TCA[$this->table]['ctrl']['versioningWS'])	{
			$this->uid = $record['uid']; 	// Might have changed if new live record was found!

				// Access check!
				// The page will show only if there is a valid page and if this page may be viewed by the user
			$this->pageinfo = t3lib_BEfunc::readPageAccess($pidValue,$this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;

			if (($pidValue && $access) || ($BE_USER->user['admin'] && !$pidValue))	{

					// JavaScript
				$this->doc->JScode.= '
					<script language="javascript" type="text/javascript">
						script_ended = 0;
						function jumpToUrl(URL)	{
							window.location.href = URL;
						}

						function hlSubelements(origId, verId, over, diffLayer)	{	//
							if (over)	{
								document.getElementById(\'orig_\'+origId).attributes.getNamedItem("class").nodeValue = \'typo3-ver-hl\';
								document.getElementById(\'ver_\'+verId).attributes.getNamedItem("class").nodeValue = \'typo3-ver-hl\';
								if (diffLayer)	{
									document.getElementById(\'diff_\'+verId).style.visibility = \'visible\';
								}
							} else {
								document.getElementById(\'orig_\'+origId).attributes.getNamedItem("class").nodeValue = \'typo3-ver\';
								document.getElementById(\'ver_\'+verId).attributes.getNamedItem("class").nodeValue = \'typo3-ver\';
								if (diffLayer)	{
									document.getElementById(\'diff_\'+verId).style.visibility = \'hidden\';
								}
							}
						}
					</script>
				';

					// If another page module was specified, replace the default Page module with the new one
				$newPageModule = trim($BE_USER->getTSConfigVal('options.overridePageModule'));
				$this->pageModule = t3lib_BEfunc::isModuleSetInTBE_MODULES($newPageModule) ? $newPageModule : 'web_layout';

					// Setting publish access permission for workspace:
				$this->publishAccess = $BE_USER->workspacePublishAccess($BE_USER->workspace);


				$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br/>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

				$this->content.=$this->doc->startPage($LANG->getLL('title'));
				$this->content.=$this->doc->header($LANG->getLL('title'));
				$this->content.=$this->doc->spacer(5);
				$this->content.=$this->doc->section('',$headerSection);
				$this->content.=$this->doc->divider(5);

					// Render content:
				if ($this->id)	{
					$this->workspaceMgm();
				} else {
					$this->versioningMgm();
				}

					// ShortCut
				if ($BE_USER->mayMakeShortcut())	{
					$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
				}
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or id value, create empty document:
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->section($LANG->getLL('clickAPage_header'),$LANG->getLL('clickAPage_content'),0,1);
		}
	}

	/**
	 * Outputs accumulated module content to browser.
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}










	/******************************
	 *
	 * Versioning management
	 *
	 ******************************/

	/**
	 * Management of versions for record
	 *
	 * @return	void
	 */
	function versioningMgm()	{
		global $TCA;

			// Diffing:
		$diff_1 = t3lib_div::_POST('diff_1');
		$diff_2 = t3lib_div::_POST('diff_2');
		if (t3lib_div::_POST('do_diff'))	{
			$content='';
			$content.='<h3>DIFFING:</h3>';
			if ($diff_1 && $diff_2)	{
				$diff_1_record = t3lib_BEfunc::getRecord($this->table, $diff_1);
				$diff_2_record = t3lib_BEfunc::getRecord($this->table, $diff_2);

				if (is_array($diff_1_record) && is_array($diff_2_record))	{
					t3lib_div::loadTCA($this->table);
					$t3lib_diff_Obj = t3lib_div::makeInstance('t3lib_diff');

					$tRows=array();
								$tRows[] = '
									<tr class="bgColor5 tableheader">
										<td>Fieldname:</td>
										<td width="98%">Colored diff-view:</td>
									</tr>
								';
					foreach($diff_1_record as $fN => $fV)	{
						if ($TCA[$this->table]['columns'][$fN] && $TCA[$this->table]['columns'][$fN]['config']['type']!='passthrough' && !t3lib_div::inList('t3ver_label',$fN))	{
							if (strcmp($diff_1_record[$fN],$diff_2_record[$fN]))	{

								$diffres = $t3lib_diff_Obj->makeDiffDisplay(
									t3lib_BEfunc::getProcessedValue($this->table,$fN,$diff_2_record[$fN],0,1),
									t3lib_BEfunc::getProcessedValue($this->table,$fN,$diff_1_record[$fN],0,1)
								);

								$tRows[] = '
									<tr class="bgColor4">
										<td>'.$fN.'</td>
										<td width="98%">'.$diffres.'</td>
									</tr>
								';
							}
						}
					}

					if (count($tRows)>1)	{
						$content.='<table border="0" cellpadding="1" cellspacing="1" width="100%">'.implode('',$tRows).'</table><br/><br/>';
					} else {
						$content.='Records matches completely on all editable fields!';
					}
				} else $content.='ERROR: Records could strangely not be found!';
			} else {
				$content.='ERROR: You didn\'t select two sources for diffing!';
			}
		}

			// Element:
		$record = t3lib_BEfunc::getRecord($this->table,$this->uid);
		$recordIcon = t3lib_iconWorks::getIconImage($this->table,$record,$this->doc->backPath,'class="absmiddle"');
		$recTitle = t3lib_BEfunc::getRecordTitle($this->table,$record,TRUE);

			// Display versions:
		$content.='
			'.$recordIcon.$recTitle.'
			<form name="theform" action="'.str_replace('&sendToReview=1','',$this->REQUEST_URI).'" method="post">
			<table border="0" cellspacing="1" cellpadding="1">';
			$content.='
				<tr class="bgColor5 tableheader">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>Title</td>
					<td>UID</td>
					<td title="t3ver_oid - Reference to live version UID">oid</td>
					<td title="t3ver_id - Version number, incremental integer">id</td>
					<td title="t3ver_wsid - Workspace ID. There can be only one version of an element per ID (except ID zero).">wsid</td>
					<td title="t3ver_state - Special states of a version: 1=Placeholder for &quot;New&quot;. 2=Marked deleted.">state</td>
					<td title="t3ver_stage - Publishing stage: Editing (0), review (1), publish (10), rejected (-1).">stage</td>
					<td title="t3ver_count - Life cycle counter. Incremented each time element is unpublished.">count</td>
					<td>pid</td>
					<td>t3ver_label</td>
					<td colspan="2"><input type="submit" name="do_diff" value="Diff" /></td>
				</tr>';

		$versions = t3lib_BEfunc::selectVersionsOfRecord($this->table, $this->uid, '*', $GLOBALS['BE_USER']->workspace);
		foreach($versions as $row)	{
			$adminLinks = $this->adminLinks($this->table,$row);

			$content.='
				<tr class="'.($row['uid']!=$this->uid ? 'bgColor4' : 'bgColor2 tableheader').'">
					<td>'.($row['uid']!=$this->uid ? '<a href="'.$this->doc->issueCommand('&cmd['.$this->table.']['.$this->uid.'][version][swapWith]='.$row['uid'].'&cmd['.$this->table.']['.$this->uid.'][version][action]=swap').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/insert1.gif','width="14" height="14"').' alt="" title="SWAP with current" />'.
						'</a>' /* (
							$this->table == 'pages' ?
							'<a href="'.$this->doc->issueCommand('&cmd['.$this->table.']['.$this->uid.'][version][action]=swap&cmd['.$this->table.']['.$this->uid.'][version][swapWith]='.$row['uid'].'&cmd['.$this->table.']['.$this->uid.'][version][swapContent]=1').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/insert2.gif','width="14" height="14"').' alt="" title="Publish page AND content!" />'.
						'</a>'.
							'<a href="'.$this->doc->issueCommand('&cmd['.$this->table.']['.$this->uid.'][version][action]=swap&cmd['.$this->table.']['.$this->uid.'][version][swapWith]='.$row['uid'].'&cmd['.$this->table.']['.$this->uid.'][version][swapContent]=ALL').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/insert4.gif','width="14" height="14"').' alt="" title="Publish page AND content! - AND ALL SUBPAGES!" />'.
						'</a>' : '') */ : '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/blinkarrow_left.gif','width="5" height="9"').' alt="" title="CURRENT ONLINE VERSION!"/>').'</td>
					<td nowrap="nowrap">'.$adminLinks.'</td>
					<td nowrap="nowrap">'.t3lib_BEfunc::getRecordTitle($this->table,$row,TRUE).'</td>
					<td>'.$row['uid'].'</td>
					<td>'.$row['t3ver_oid'].'</td>
					<td>'.$row['t3ver_id'].'</td>
					<td>'.$row['t3ver_wsid'].'</td>
					<td>'.$row['t3ver_state'].'</td>
					<td>'.$row['t3ver_stage'].'</td>
					<td>'.$row['t3ver_count'].'</td>
					<td>'.$row['pid'].'</td>
					<td nowrap="nowrap"><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit['.$this->table.']['.$row['uid'].']=edit&columnsOnly=t3ver_label',$this->doc->backPath)).'"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' alt="" title="Edit"/></a>'.htmlspecialchars($row['t3ver_label']).'</td>
					<td class="version-diff-1"><input type="radio" name="diff_1" value="'.$row['uid'].'"'.($diff_1==$row['uid'] ? ' checked="checked"':'').'/></td>
					<td class="version-diff-2"><input type="radio" name="diff_2" value="'.$row['uid'].'"'.($diff_2==$row['uid'] ? ' checked="checked"':'').'/></td>
				</tr>';

				// Show sub-content if the table is pages AND it is not the online branch (because that will mostly render the WHOLE tree below - not smart;)
			if ($this->table == 'pages' && $row['uid']!=$this->uid)	{
				$sub = $this->pageSubContent($row['uid']);

				if ($sub)	{
					$content.='
						<tr>
							<td></td>
							<td></td>
							<td colspan="10">'.$sub.'</td>
							<td colspan="2"></td>
						</tr>';
				}
			}
		}
		$content.='</table></form>';

		$this->content.=$this->doc->section('',$content,0,1);


			// Create new:
		$content='

			<form action="'.$this->doc->backPath.'tce_db.php" method="post">
			Label: <input type="text" name="cmd['.$this->table.']['.$this->uid.'][version][label]" /><br/>
			'.($this->table == 'pages' ? '<select name="cmd['.$this->table.']['.$this->uid.'][version][treeLevels]">
				'.($GLOBALS['BE_USER']->workspaceVersioningTypeAccess(0) ? '<option value="0">Page: Page + content</option>' : '').'
				'.($GLOBALS['BE_USER']->workspaceVersioningTypeAccess(1) ? '<option value="100">Branch: All subpages</option>' : '').'
				'.($GLOBALS['BE_USER']->workspaceVersioningTypeAccess(-1) ? '<option value="-1">Element: Just record</option>' : '').'
			</select>' : '').'
			<br/><input type="hidden" name="cmd['.$this->table.']['.$this->uid.'][version][action]" value="new" />
			<input type="hidden" name="prErr" value="1" />
			<input type="hidden" name="redirect" value="'.htmlspecialchars($this->REQUEST_URI).'" />
			<input type="submit" name="_" value="Create new version" />

			</form>

		';

		$this->content.=$this->doc->spacer(15);
		$this->content.=$this->doc->section('Create new version',$content,0,1);

	}

	/**
	 * Recursively look for children for page version with $pid
	 *
	 * @param	integer		UID of page record for which to look up sub-elements following that version
	 * @param	integer		Counter, do not set (limits to 100 levels)
	 * @return	string		Table with content if any
	 */
	function pageSubContent($pid,$c=0)	{
		global $TCA;

		$tableNames = t3lib_div::removeArrayEntryByValue(array_keys($TCA),'pages');
		$tableNames[] = 'pages';

		foreach($tableNames as $tN)	{
				// Basically list ALL tables - not only those being copied might be found!
			#if ($TCA[$tN]['ctrl']['versioning_followPages'] || $tN=='pages')	{
				$mres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $tN, 'pid='.intval($pid).t3lib_BEfunc::deleteClause($tN), '', ($TCA[$tN]['ctrl']['sortby'] ? $TCA[$tN]['ctrl']['sortby'] : ''));

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($mres))	{
					$content.='
						<tr>
							<td colspan="4" class="'.($TCA[$tN]['ctrl']['versioning_followPages'] ? 'bgColor6' : ($tN=='pages' ? 'bgColor5' : 'bgColor-10')).'"'.(!$TCA[$tN]['ctrl']['versioning_followPages'] && $tN!='pages' ? ' style="color: #666666; font-style:italic;"':'').'>'.$tN.'</td>
						</tr>';
					while ($subrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($mres))	{
						$ownVer = $this->lookForOwnVersions($tN,$subrow['uid']);
						$content.='
							<tr>
								<td>'.$this->adminLinks($tN,$subrow).'</td>
								<td>'.$subrow['uid'].'</td>
								'.($ownVer>1 ? '<td style="font-weight: bold; background-color: yellow;"><a href="index.php?table='.rawurlencode($tN).'&uid='.$subrow['uid'].'">'.($ownVer-1).'</a></td>' : '<td></td>').'
								<td width="98%">'.t3lib_BEfunc::getRecordTitle($tN,$subrow,TRUE).'</td>
							</tr>';

						if ($tN == 'pages' && $c<100)	{
							$sub = $this->pageSubContent($subrow['uid'],$c+1);

							if ($sub)	{
								$content.='
									<tr>
										<td></td>
										<td></td>
										<td></td>
										<td width="98%">'.$sub.'</td>
									</tr>';
							}
						}
					}
				}
			#}
		}

		return $content ? '<table border="1" cellpadding="1" cellspacing="0" width="100%">'.$content.'</table>' : '';
	}

	/**
	 * Look for number of versions of a record
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @return	integer		Number of versions for record, false if none.
	 */
	function lookForOwnVersions($table,$uid)	{
		global $TCA;

		$versions = t3lib_BEfunc::selectVersionsOfRecord($table, $uid, 'uid');
		if (is_array($versions))	{
			return count($versions);
		}
		return FALSE;
	}

	/**
	 * Administrative links for a table / record
	 *
	 * @param	string		Table name
	 * @param	array		Record for which administrative links are generated.
	 * @return	string		HTML link tags.
	 */
	function adminLinks($table,$row)	{
		global $BE_USER;

			// Edit link:
		$adminLink = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit['.$table.']['.$row['uid'].']=edit',$this->doc->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' alt="" title="Edit"/>'.
						'</a>';

			// Delete link:
		$adminLink.= '<a href="'.htmlspecialchars($this->doc->issueCommand('&cmd['.$table.']['.$row['uid'].'][delete]=1')).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/garbage.gif','width="11" height="12"').' alt="" title="Delete"/>'.
						'</a>';



		if ($table == 'pages')	{

				// If another page module was specified, replace the default Page module with the new one
			$newPageModule = trim($BE_USER->getTSConfigVal('options.overridePageModule'));
			$pageModule = t3lib_BEfunc::isModuleSetInTBE_MODULES($newPageModule) ? $newPageModule : 'web_layout';

				// Perform some acccess checks:
			$a_wl = $BE_USER->check('modules','web_list');
			$a_wp = t3lib_extMgm::isLoaded('cms') && $BE_USER->check('modules',$pageModule);

			$adminLink.='<a href="#" onclick="top.loadEditId('.$row['uid'].');top.goToModule(\''.$pageModule.'\'); return false;">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,t3lib_extMgm::extRelPath('cms').'layout/layout.gif','width="14" height="12"').' title="" alt="" />'.
						'</a>';
			$adminLink.='<a href="#" onclick="top.loadEditId('.$row['uid'].');top.goToModule(\'web_list\'); return false;">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'mod/web/list/list.gif','width="14" height="12"').' title="" alt="" />'.
						'</a>';

				// "View page" icon is added:
			$adminLink.='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($row['uid'],$this->doc->backPath,t3lib_BEfunc::BEgetRootLine($row['uid']))).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="12" height="12"').' title="" alt="" />'.
				'</a>';
		} else {
			if ($row['pid']==-1)	{
				$getVars = '&ADMCMD_vPrev['.rawurlencode($table.':'.$row['t3ver_oid']).']='.$row['uid'];

					// "View page" icon is added:
				$adminLink.='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($row['_REAL_PID'],$this->doc->backPath,t3lib_BEfunc::BEgetRootLine($row['_REAL_PID']),'','',$getVars)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="12" height="12"').' title="" alt="" />'.
					'</a>';
			}
		}

		return $adminLink;
	}











	/******************************
	 *
	 * Workspace management
	 *
	 ******************************/

	/**
	 * Management of workspace for page ID
	 * Called when $this->id is set.
	 *
	 * @return	void
	 */
	function workspaceMgm()	{

		$menu = '';
		if ($GLOBALS['BE_USER']->workspace===0)	{
			$menu.= t3lib_BEfunc::getFuncMenu($this->id,'SET[filter]',$this->MOD_SETTINGS['filter'],$this->MOD_MENU['filter']);
			$menu.= t3lib_BEfunc::getFuncMenu($this->id,'SET[display]',$this->MOD_SETTINGS['display'],$this->MOD_MENU['display']);
		}
		if (!$this->details && $GLOBALS['BE_USER']->workspace && !$this->diffOnly)	{
			$menu.= t3lib_BEfunc::getFuncCheck($this->id,'SET[diff]',$this->MOD_SETTINGS['diff']).' Show difference view';
		}

		if ($menu)	{
			$this->content.=$this->doc->section('',$menu,0,1);
		}

			// Perform workspace publishing action if buttons are pressed:
		$errors = $this->publishAction();

			// Generate workspace overview:
		$WSoverview = $this->displayWorkspaceOverview();

			// Buttons for publish / swap:
		$actionLinks = '<br/>';
		if ($GLOBALS['BE_USER']->workspace!==0)	{
			if ($this->publishAccess)	{
				$actionLinks.= '<input type="submit" name="_publish" value="Publish page" onclick="return confirm(\'Are you sure you want to publish all content '.($GLOBALS['BE_USER']->workspaceRec['publish_access']&1 ? 'in &quot;Publish&quot; stage ':'').'from this page?\');"/>';
				if ($GLOBALS['BE_USER']->workspaceSwapAccess())	{
					$actionLinks.= '<input type="submit" name="_swap" value="Swap page" onclick="return confirm(\'Are you sure you want to publish (swap) all content '.($GLOBALS['BE_USER']->workspaceRec['publish_access']&1 ? 'in &quot;Publish&quot; stage ':'').'from this page?\');" />';
				}
			} else {
				$actionLinks.= $this->doc->icons(1).'You are not permitted to publish from this workspace';
			}
		}
		$actionLinks.= '<input type="submit" name="_" value="Refresh" />';
		$actionLinks.= '<input type="submit" name="_previewLink" value="Preview Link" />';
		$actionLinks.= $this->displayWorkspaceOverview_allStageCmd();

		if ($actionLinks || count($errors))	{
			$this->content.= $this->doc->section('',$actionLinks.(count($errors) ? '<h3>Errors:</h3><br/>'.implode('<br/>',$errors).'<hr/>' : ''),0,1);
		}

		if (t3lib_div::_POST('_previewLink'))	{
			$params = 'id='.$this->id.'&ADMCMD_view=1&ADMCMD_editIcons=1&ADMCMD_previewWS='.$GLOBALS['BE_USER']->workspace;
			$previewUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'index.php?ADMCMD_prev='.t3lib_BEfunc::compilePreviewKeyword($params, $GLOBALS['BE_USER']->user['uid']);

			$this->content.= $this->doc->section('Preview Url:','You can preview this page from the workspace using this link for the next 48 hours (does not require backend login):<br/><br/><a target="_blank" href="'.htmlspecialchars($previewUrl).'">'.$previewUrl.'</a>',0,1);
		}

			// Output overview content:
		$this->content.= $this->doc->spacer(15);
		$this->content.= $this->doc->section($this->details ? 'Details for version' : 'Workspace management', $WSoverview,0,1);

	}

	/**
	 * Rendering the overview of versions in the current workspace
	 *
	 * @return	string		HTML (table)
	 * @see typo3/mod/user/ws/index.php for sister function!
	 */
	function displayWorkspaceOverview()	{

			// Initialize variables:
		$this->showWorkspaceCol = $GLOBALS['BE_USER']->workspace===0 && $this->MOD_SETTINGS['display']<=-98;

			// Get usernames and groupnames
		$be_group_Array = t3lib_BEfunc::getListGroupNames('title,uid');
		$groupArray = array_keys($be_group_Array);
		$this->be_user_Array = t3lib_BEfunc::getUserNames();
		if (!$GLOBALS['BE_USER']->isAdmin())		$this->be_user_Array = t3lib_BEfunc::blindUserNames($this->be_user_Array,$groupArray,1);

			// Initialize Workspace ID and filter-value:
		if ($GLOBALS['BE_USER']->workspace===0)	{
			$wsid = $this->details ? -99 : $this->MOD_SETTINGS['display'];		// Set wsid to the value from the menu (displaying content of other workspaces)
			$filter = $this->details ? 0 : $this->MOD_SETTINGS['filter'];
		} else {
			$wsid = $GLOBALS['BE_USER']->workspace;
			$filter = 0;
		}

			// Initialize workspace object and request all pending versions:
		$wslibObj = t3lib_div::makeInstance('wslib');

			// Selecting ALL versions belonging to the workspace:
		$versions = $wslibObj->selectVersionsInWorkspace($wsid, $filter, -99, $this->uid);	// $this->uid is the page id of LIVE record.

			// Traverse versions and build page-display array:
		$pArray = array();
		foreach($versions as $table => $records)	{
			foreach($records as $rec)	{
				$pageIdField = $table==='pages' ? 't3ver_oid' : 'realpid';
				$this->displayWorkspaceOverview_setInPageArray(
					$pArray,
					$table,
					$rec
				);
			}
		}

			// Make header of overview:
		$tableRows = array();
		if (count($pArray))	{
			$tableRows[] = '
				<tr class="bgColor5 tableheader">
					'.($this->diffOnly?'':'<td nowrap="nowrap" colspan="2">Live Version:</td>').'
					<td nowrap="nowrap" colspan="2">Draft Versions:</td>
					<td nowrap="nowrap"'.($this->diffOnly?' colspan="2"':' colspan="4"').'>Controls:</td>
				</tr>';

				// Add lines from overview:
			$tableRows = array_merge($tableRows, $this->displayWorkspaceOverview_list($pArray));

			$table = '<table border="0" cellpadding="0" cellspacing="1" class="lrPadding workspace-overview">'.implode('',$tableRows).'</table>';
		} else $table = '';

		$linkBack = t3lib_div::_GP('returnUrl') ? '<a href="'.htmlspecialchars(t3lib_div::_GP('returnUrl')).'" class="typo3-goBack"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/goback.gif','width="14" height="14"').' alt="" />Click here to go back</a><br/><br/>' : '';
		$resetDiffOnly = $this->diffOnly ? '<a href="index.php?id='.intval($this->id).'" class="typo3-goBack">Show all information</a><br/><br/>' : '';

		$versionSelector = $GLOBALS['BE_USER']->workspace ? $this->doc->getVersionSelector($this->id) : '';

		return $versionSelector.$linkBack.$resetDiffOnly.$table.$this->markupNewOriginals();
	}

	/**
	 * Rendering the content for the publish / review overview:
	 * (Made for internal recursive calling)
	 *
	 * @param	array		Storage of the elements to display (see displayWorkspaceOverview() / displayWorkspaceOverview_setInPageArray())
	 * @return	array		Table rows, see displayWorkspaceOverview()
	 */
	function displayWorkspaceOverview_list($pArray)	{
		global $TCA;

			// If there ARE elements on this level, print them:
		$warnAboutVersions_nonPages = FALSE;
		$warnAboutVersions_page = FALSE;
		if (is_array($pArray))	{
			foreach($pArray as $table => $oidArray)	{
				foreach($oidArray as $oid => $recs)	{

						// Get CURRENT online record and icon based on "t3ver_oid":
					$rec_on = t3lib_BEfunc::getRecord($table,$oid);
					$icon = t3lib_iconWorks::getIconImage($table, $rec_on, $this->doc->backPath,' align="top" title="'.t3lib_BEfunc::getRecordIconAltText($rec_on,$table).'"');
					if ($GLOBALS['BE_USER']->workspace===0) {	// Only edit online records if in ONLINE workspace:
						$icon = $this->doc->wrapClickMenuOnIcon($icon, $table, $rec_on['uid'], 1, '', '+edit,view,info,delete');
					}

						// Online version display:
						// Create the main cells which will span over the number of versions there is.
					$verLinkUrl = $TCA[$table]['ctrl']['versioningWS'];
					$origElement = $icon.
						($verLinkUrl ? '<a href="'.htmlspecialchars('index.php?table='.$table.'&uid='.$rec_on['uid']).'">' : '').
						t3lib_BEfunc::getRecordTitle($table,$rec_on,TRUE).
						($verLinkUrl ? '</a>' : '');
					$mainCell_rowSpan = count($recs)>1 ? ' rowspan="'.count($recs).'"' : '';
					$mainCell = '
								<td align="center"'.$mainCell_rowSpan.'>'.$this->formatVerId($rec_on['t3ver_id']).'</td>
								<td nowrap="nowrap"'.$mainCell_rowSpan.'>'.
									$origElement.
									'###SUB_ELEMENTS###'.	// For substitution with sub-elements, if any.
								'</td>';

						// Offline versions display:
						// Traverse the versions of the element
					foreach($recs as $rec)	{

							// Get the offline version record and icon:
						$rec_off = t3lib_BEfunc::getRecord($table,$rec['uid']);
						$icon = t3lib_iconWorks::getIconImage($table, $rec_off, $this->doc->backPath, ' align="top" title="'.t3lib_BEfunc::getRecordIconAltText($rec_off,$table).'"');
						$icon = $this->doc->wrapClickMenuOnIcon($icon, $table, $rec_off['uid'], 1, '', '+edit,view,info,delete');

							// Prepare diff-code:
						if ($this->MOD_SETTINGS['diff'] || $this->diffOnly)	{
							$diffCode = '';
							list($diffHTML,$diffPct) = $this->createDiffView($table, $rec_off, $rec_on);
							if ($rec_on['t3ver_state']==1)	{	// New record:
								$diffCode.= $this->doc->icons(1).'New element<br/>';	// TODO Localize?
								$diffCode.= $diffHTML;
							} elseif ($rec_off['t3ver_state']==2)	{
								$diffCode.= $this->doc->icons(2).'Deleted element<br/>';
							} else {
								$diffCode.= ($diffPct<0 ? 'N/A' : ($diffPct ? $diffPct.'% change:' : ''));
								$diffCode.= $diffHTML;
							}
						} else $diffCode = '';

							// Prepare swap-mode values:
						if ($table==='pages' && $rec_off['t3ver_swapmode']!=-1)	{
							if ($rec_off['t3ver_swapmode']>0)	{
								$vType = 'branch';
							} else {
								$vType = 'page';
							}
						} else {
							$vType = 'element';
						}

						switch($vType) {
							case 'element':
								$swapLabel = ' [Element]';
								$swapClass = 'ver-element';
								$warnAboutVersions_nonPages = $warnAboutVersions_page;	// Setting this if sub elements are found with a page+content (must be rendered prior to this of course!)
							break;
							case 'page':
								$swapLabel = ' [Page]';
								$swapClass = 'ver-page';
								$warnAboutVersions_page = !$this->showWorkspaceCol;		// This value is true only if multiple workspaces are shown and we need the opposite here.
							break;
							case 'branch':
								$swapLabel = ' [Branch]';
								$swapClass = 'ver-branch';
							break;
						}

							// Modify main cell based on first version shown:
						$subElements = array();
						if ($table==='pages' && $rec_off['t3ver_swapmode']!=-1 && $mainCell)	{	// For "Page" and "Branch" swap modes where $mainCell is still carrying content (only first version)
							$subElements['on'] = $this->subElements($rec_on['uid'], $rec_off['t3ver_swapmode']);
							$subElements['off'] = $this->subElements($rec_off['uid'],$rec_off['t3ver_swapmode'],$rec_on['uid']);
						}
						$mainCell = str_replace('###SUB_ELEMENTS###', $subElements['on'], $mainCell);

							// Create version element:
						$versionsInOtherWS = $this->versionsInOtherWS($table, $rec_on['uid']);
						$versionsInOtherWSWarning = $versionsInOtherWS && $GLOBALS['BE_USER']->workspace!==0 ? '<br/>'.$this->doc->icons(2).'Other version(s) in workspace '.$versionsInOtherWS : '';
						$multipleWarning = (!$mainCell && $GLOBALS['BE_USER']->workspace!==0? '<br/>'.$this->doc->icons(3).'<b>Multiple versions in same workspace!</b>' : '');
						$verWarning = $warnAboutVersions || ($warnAboutVersions_nonPages && $GLOBALS['TCA'][$table]['ctrl']['versioning_followPages'])? '<br/>'.$this->doc->icons(3).'<b>Version inside version!</b>' : '';
						$verElement = $icon.
							(!$this->details ? '<a href="'.htmlspecialchars($this->doc->backPath.t3lib_extMgm::extRelPath('version').'cm1/index.php?id='.($table==='pages'?$rec_on['uid']:$rec_on['pid']).'&details='.rawurlencode($table.':'.$rec_off['uid']).'&returnUrl='.rawurlencode($this->REQUEST_URI)).'">' : '').
							t3lib_BEfunc::getRecordTitle($table,$rec_off,TRUE).
							(!$this->details ? '</a>' : '').
							$versionsInOtherWSWarning.
							$multipleWarning.
							$verWarning;

						$ctrlTable = '
								<td nowrap="nowrap">'.$this->showStageChangeLog($table,$rec_off['uid'],$this->displayWorkspaceOverview_stageCmd($table,$rec_off)).'</td>
								<td nowrap="nowrap" class="'.$swapClass.'">'.
									$this->displayWorkspaceOverview_commandLinks($table,$rec_on,$rec_off,$vType).
									htmlspecialchars($swapLabel).
									'&nbsp;&nbsp;</td>
								'.(!$this->diffOnly?'<td nowrap="nowrap"><b>Lifecycle:</b> '.htmlspecialchars($this->formatCount($rec_off['t3ver_count'])).'</td>'.		// Lifecycle
									($this->showWorkspaceCol ? '
								<td nowrap="nowrap">&nbsp;&nbsp;<b>Workspace:</b> '.htmlspecialchars($this->formatWorkspace($rec_off['t3ver_wsid'])).'</td>' : ''):'');

						if ($diffCode)	{
							$verElement = $verElement.'
							<br/><b>Difference to live element:</b>
							<table border="0" cellpadding="0" cellspacing="0" class="ver-verElement">
								<tr>
									<td class="c-diffCell">'.$diffCode.'</td>
								</tr>
							</table>';
						}


							// Create version cell:
						$verCell = '
								<td align="center">'.$this->formatVerId($rec_off['t3ver_id']).'</td>
								<td nowrap="nowrap">'.
									$verElement.
									$subElements['off'].
									'</td>
								';

							// Compile table row:
						$tableRows[] = '
							<tr class="bgColor4">
								'.
								($this->diffOnly?'':$mainCell).
								$verCell.
								$ctrlTable.
								'
							</tr>';

							// Reset the main cell:
						$mainCell = '';

					}
				}
			}
		}

		return $tableRows;
	}

	/**
	 * Building up of the $pArray
	 * (Internal)
	 *
	 * @param	array		Array that is built up with the page tree structure
	 * @param	string		Table name
	 * @param	array		Table row
	 * @return	void		$pArray is passed by reference and modified internally
	 */
	function displayWorkspaceOverview_setInPageArray(&$pArray,$table,$row)	{
		if (!$this->details || $this->details==$table.':'.$row['uid'])	{
			$pArray[$table][$row['t3ver_oid']][] = $row;
		}
	}

	/**
	 * Links to stage change of a version
	 *
	 * @param	string		Table name
	 * @param	array		Offline record (version)
	 * @return	string		HTML content, mainly link tags and images.
	 */
	function displayWorkspaceOverview_allStageCmd()	{

		$table = t3lib_div::_GP('table');
		if ($table && $table!='pages')	{
			$uid = t3lib_div::_GP('uid');
			if ($rec_off = t3lib_BEfunc::getRecordWSOL($table,$uid)) {
				$uid = $rec_off['_ORIG_uid'];
			}
		} else $table = '';

		if ($table)	{
			if ($uid && $this->recIndex[$table][$uid])	{
				$sId = $this->recIndex[$table][$uid];
				switch($sId)	{
					case 1:
						$label = 'Comment for Reviewer:';
					break;
					case 10:
						$label = 'Comment for Publisher:';
					break;
				}
			} else $sId = 0;
		} else {
			if (count($this->stageIndex[1]))	{	// Review:
				$sId = 1;
				$color = '#666666';
				$label = 'Sending %s item(s) to review. Comment for Reviewer:';
				$titleAttrib = 'Send all to Review';
			} elseif(count($this->stageIndex[10]))  {	// Publish:
				$sId = 10;
				$color = '#6666cc';
				$label = 'Approving %s item(s) to publishing. Comment for Publisher:';
				$titleAttrib = 'Approve all for Publishing';
			} else {
				$sId = 0;
			}
		}

		if ($sId>0)	{
			$issueCmd = '';
			$itemCount = 0;

			if ($table && $uid && $this->recIndex[$table][$uid])	{
				$issueCmd.='&cmd['.$table.']['.$uid.'][version][action]=setStage';
				$issueCmd.='&cmd['.$table.']['.$uid.'][version][stageId]='.$this->recIndex[$table][$uid];
			} else {
				foreach($this->stageIndex[$sId] as $table => $uidArray)	{
					$issueCmd.='&cmd['.$table.']['.implode(',',$uidArray).'][version][action]=setStage';
					$issueCmd.='&cmd['.$table.']['.implode(',',$uidArray).'][version][stageId]='.$sId;
					$itemCount+=count($uidArray);
				}
			}

			$onClick = 'var commentTxt=window.prompt("'.sprintf($label,$itemCount).'","");
							if (commentTxt!=null) {window.location.href="'.$this->doc->issueCommand($issueCmd,$this->REQUEST_URI).'&generalComment="+escape(commentTxt);}';

			if (t3lib_div::_GP('sendToReview'))	{
				$onClick.= ' else {window.location.href = "'.$this->REQUEST_URI.'"}';
				$actionLinks.=
					$this->doc->wrapScriptTags($onClick);
			} else {
				$onClick.= ' return false;';
				$actionLinks.=
					'<input type="submit" name="_" value="'.htmlspecialchars($titleAttrib).'" onclick="'.htmlspecialchars($onClick).'" />';
			}	
		} elseif (t3lib_div::_GP('sendToReview'))	{
			$onClick = 'window.location.href = "'.$this->REQUEST_URI.'";';
			$actionLinks.=
				$this->doc->wrapScriptTags($onClick);
		} else $actionLinks = '';

		return $actionLinks;
	}






	/**************************************
	 *
	 * Helper functions (REDUNDANT FROM user/ws/index.php - someone could refactor this...)
	 *
	 *************************************/

	/**
	 * Formatting the version number for HTML output
	 *
	 * @param	integer		Version number
	 * @return	string		Version number for output
	 */
	function formatVerId($verId)	{
		return '1.'.$verId;
	}

	/**
	 * Formatting workspace ID into a visual label
	 *
	 * @param	integer		Workspace ID
	 * @return	string		Workspace title
	 */
	function formatWorkspace($wsid)	{

			// Render, if not cached:
		if (!isset($this->formatWorkspace_cache[$wsid]))	{
			switch($wsid)	{
				case -1:
					$this->formatWorkspace_cache[$wsid] = '[Offline]';
				break;
				case 0:
					$this->formatWorkspace_cache[$wsid] = '';	// Does not output anything for ONLINE because it might confuse people to think that the elemnet IS online which is not the case - only that it exists as an offline version in the online workspace...
				break;
				default:
					list($titleRec) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('title','sys_workspace','uid='.intval($wsid).t3lib_BEfunc::deleteClause('sys_workspace'));
					$this->formatWorkspace_cache[$wsid] = '['.$wsid.'] '.$titleRec['title'];
				break;
			}
		}

		return $this->formatWorkspace_cache[$wsid];
	}

	/**
	 * Format publishing count for version (lifecycle state)
	 *
	 * @param	integer		t3ver_count value (number of times it has been online)
	 * @return	string		String translation of count.
	 */
	function formatCount($count)	{

			// Render, if not cached:
		if (!isset($this->formatCount_cache[$count]))	{
			switch($count)	{
				case 0:
					$this->formatCount_cache[$count] = 'Draft';
				break;
				case 1:
					$this->formatCount_cache[$count] = 'Archive';
				break;
				default:
					$this->formatCount_cache[$count] = 'Published '.$count.' times';
				break;
			}
		}

		return $this->formatCount_cache[$count];
	}

	/**
	 * Looking for versions of a record in other workspaces than the current
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @return	string		List of other workspace IDs
	 */
	function versionsInOtherWS($table,$uid)	{

			// Check for duplicates:
			// Select all versions of record NOT in this workspace:
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			't3ver_wsid',
			$table,
			'pid=-1
				AND t3ver_oid='.intval($uid).'
				AND t3ver_wsid!='.intval($GLOBALS['BE_USER']->workspace).'
				AND (t3ver_wsid=-1 OR t3ver_wsid>0)'.
				t3lib_BEfunc::deleteClause($table),
			'',
			't3ver_wsid',
			'',
			't3ver_wsid'
		);
		if (count($rows))	{
			return implode(',',array_keys($rows));
		}
	}

	/**
	 * Looks up stage changes for version and displays a formatted view on mouseover.
	 *
	 * @param	string		Table name
	 * @param	integer		Record ID
	 * @param	string		HTML string to wrap the mouseover around (should be stage change links)
	 * @return	string		HTML code.
	 */
	function showStageChangeLog($table,$id,$stageCommands)	{
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'log_data,tstamp,userid',
			'sys_log',
			'action=6 and details_nr=30
				AND tablename='.$GLOBALS['TYPO3_DB']->fullQuoteStr($table,'sys_log').'
				AND recuid='.intval($id)
		);

		$entry = array();
		foreach($rows as $dat)	{
			$data = unserialize($dat['log_data']);
			$username = $this->be_user_Array[$dat['userid']] ? $this->be_user_Array[$dat['userid']]['username'] : '['.$dat['userid'].']';

			switch($data['stage'])	{
				case 1:
					$text = 'sent element to "Review"';
				break;
				case 10:
					$text = 'approved for "Publish"';
				break;
				case -1:
					$text = 'rejected element!';
				break;
				case 0:
					$text = 'reset to "Editing"';
				break;
				default:
					$text = '[undefined]';
				break;
			}
			$text = t3lib_BEfunc::dateTime($dat['tstamp']).': "'.$username.'" '.$text;
			$text.= ($data['comment']?'<br/>User Comment: <em>'.$data['comment'].'</em>':'');

			$entry[] = $text;
		}

		return count($entry) ? '<span onmouseover="document.getElementById(\'log_'.$table.$id.'\').style.visibility = \'visible\';" onmouseout="document.getElementById(\'log_'.$table.$id.'\').style.visibility = \'hidden\';">'.$stageCommands.' ('.count($entry).')</span>'.
				'<div class="logLayer" style="visibility: hidden; position: absolute;" id="log_'.$table.$id.'">'.implode('<hr/>',array_reverse($entry)).'</div>' : $stageCommands;
	}

	/**
	 * Creates display of sub elements of a page when the swap mode is either "Page" or "Branch" (0 / ALL)
	 *
	 * @param	integer		Page uid (for either online or offline version, but it MUST have swapmode/treeLevel set to >0 (not -1 indicating element versioning)
	 * @param	integer		The treeLevel value, >0 indicates "branch" while 0 means page+content. (-1 would have meant element versioning, but that should never happen for a call to this function!)
	 * @param	integer		For offline versions; This is t3ver_oid, the original ID of the online page.
	 * @return	string		HTML content.
	 */
	function subElements($uid,$treeLevel,$origId=0)	{
		global $TCA;

		if (!$this->details && ($GLOBALS['BE_USER']->workspace===0 || !$this->MOD_SETTINGS['expandSubElements']))	{	// In online workspace we have a reduced view because otherwise it will bloat the listing:
			return '<br/>
					<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/ol/joinbottom.gif','width="18" height="16"').' align="top" alt="" title="" />'.
					($origId ?
						'<a href="'.htmlspecialchars($this->doc->backPath.t3lib_extMgm::extRelPath('version').'cm1/index.php?id='.$uid.'&details='.rawurlencode('pages:'.$uid).'&returnUrl='.rawurlencode($this->REQUEST_URI)).'">'.
						'<span class="typo3-dimmed"><em>[Sub elements, click for details]</em><span></a>' :
						'<span class="typo3-dimmed"><em>[Sub elements]</em><span>');
		} else {	// For an offline workspace, show sub elements:

			$tCell = array();

				// Find records that follow pages when swapping versions:
			$recList = array();
			foreach($TCA as $tN => $tCfg)	{
				if ($tN!='pages' && ($treeLevel>0 || $TCA[$tN]['ctrl']['versioning_followPages']))	{
					$this->subElements_getNonPageRecords($tN, $uid, $recList);
				}
			}

				// Render records collected above:
			$elCount = count($recList)-1;
			foreach($recList as $c => $comb)	{
				list($tN,$rec) = $comb;

				$this->subElements_renderItem(
					$tCell,
					$tN,
					$uid,
					$rec,
					$origId,
					$c==$elCount && $treeLevel==0 ? 1 : 0,		// If true, will show bottom-join icon.
					''
				);
			}

				// For branch, dive into the subtree:
			if ($treeLevel>0) {

					// Drawing tree:
				$tree = t3lib_div::makeInstance('t3lib_pageTree');
				$tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));
				$tree->makeHTML = 2;		// 2=Also rendering depth-data into the result array
				$tree->getTree($uid, 99, '');

					// Traverse page tree:
				foreach($tree->tree as $data)	{

						// Render page in table cell:
					$this->subElements_renderItem(
						$tCell,
						'pages',
						$uid,
						t3lib_BEfunc::getRecord('pages',$data['row']['uid']),	// Needs all fields, at least more than what is given in $data['row']...
						$origId,
						2,		// 2=the join icon and icon for the record is not rendered for pages (where all is in $data['HTML']
						$data['HTML']
					);

						// Find all records from page and collect in $recList:
					$recList = array();
					foreach($TCA as $tN => $tCfg)	{
						if ($tN!=='pages')	{
							$this->subElements_getNonPageRecords($tN, $data['row']['uid'], $recList);
						}
					}

						// Render records collected above:
					$elCount = count($recList)-1;
					foreach($recList as $c => $comb)	{
						list($tN,$rec) = $comb;

						$this->subElements_renderItem(
							$tCell,
							$tN,
							$uid,
							$rec,
							$origId,
							$c==$elCount?1:0,	// If true, will show bottom-join icon.
							$data['HTML_depthData']
						);
					}
				}
			}

			return '
					<!-- Sub-element tree for versions -->
					<table border="0" cellpadding="0" cellspacing="1" class="ver-subtree">
						'.implode('',$tCell).'
					</table>';
		}
	}

	/**
	 * Select records from a table and add them to recList
	 *
	 * @param	string		Table name (from TCA)
	 * @param	integer		PID to select records from
	 * @param	array		Array where records are accumulated, passed by reference
	 * @return	void
	 */
	function subElements_getNonPageRecords($tN, $uid, &$recList)	{
		global $TCA;

		$records = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			$tN,
			'pid='.intval($uid).
				($TCA[$tN]['ctrl']['versioningWS'] ? ' AND t3ver_state=0' : '').
				t3lib_BEfunc::deleteClause($tN),
			'',
			$TCA[$tN]['ctrl']['sortby'] ? $TCA[$tN]['ctrl']['sortby'] : $GLOBALS['TYPO3_DB']->stripOrderBy($TCA[$tN]['ctrl']['default_sortby'])
		);

		foreach($records as $rec)	{
			$recList[] = array($tN,$rec);
		}
	}

	/**
	 * Render a single item in a subelement list into a table row:
	 *
	 * @param	array		Table rows, passed by reference
	 * @param	string		Table name
	 * @param	integer		Page uid for which the subelements are selected/shown
	 * @param	array		Row of element in list
	 * @param	integer		The uid of the online version of $uid. If zero it means we are drawing a row for the online version itself while a value means we are drawing display for an offline version.
	 * @param	integer		Mode of icon display: 0=not the last, 1= is the last in list (make joinbottom icon then), 2=do not shown icons are all (for pages from the page tree already rendered)
	 * @param	string		Prefix HTML data (icons for tree rendering)
	 * @return	void		(Content accumulated in $tCell!)
	 */
	function subElements_renderItem(&$tCell,$tN,$uid,$rec,$origId,$iconMode,$HTMLdata)	{
		global $TCA;

			// Initialize:
		$origUidFields = $TCA[$tN]['ctrl']['origUid'];
		$diffCode = '';

		if ($origUidFields)	{	// If there is a field for this table with original uids we will use that to connect records:
			if (!$origId)	{	// In case we are displaying the online originals:
				$this->targets['orig_'.$uid.'_'.$tN.'_'.$rec['uid']] = $rec;	// Build up target array (important that
				$tdParams =  ' id="orig_'.$uid.'_'.$tN.'_'.$rec['uid'].'" class="typo3-ver"';		// Setting ID of the table row
			} else {	// Version branch:
				if ($this->targets['orig_'.$origId.'_'.$tN.'_'.$rec[$origUidFields]])	{	// If there IS a corresponding original record...:

						// Prepare Table row parameters:
					$tdParams =  ' onmouseover="hlSubelements(\''.$origId.'_'.$tN.'_'.$rec[$origUidFields].'\', \''.$uid.'_'.$tN.'_'.$rec[$origUidFields].'\', 1, '.($this->MOD_SETTINGS['diff']==2?1:0).');"'.
								' onmouseout="hlSubelements(\''.$origId.'_'.$tN.'_'.$rec[$origUidFields].'\', \''.$uid.'_'.$tN.'_'.$rec[$origUidFields].'\', 0, '.($this->MOD_SETTINGS['diff']==2?1:0).');"'.
								' id="ver_'.$uid.'_'.$tN.'_'.$rec[$origUidFields].'" class="typo3-ver"';

						// Create diff view:
					if ($this->MOD_SETTINGS['diff'])	{
						list($diffHTML,$diffPct) = $this->createDiffView($tN, $rec, $this->targets['orig_'.$origId.'_'.$tN.'_'.$rec[$origUidFields]]);

						if ($this->MOD_SETTINGS['diff']==2)	{
							$diffCode =
								($diffPct ? '<span class="nobr">'.$diffPct.'% change</span>' : '-').
								'<div style="visibility: hidden; position: absolute;" id="diff_'.$uid.'_'.$tN.'_'.$rec[$origUidFields].'" class="diffLayer">'.
								$diffHTML.
								'</div>';
						} else {
							$diffCode =
								($diffPct<0 ? 'N/A' : ($diffPct ? $diffPct.'% change:' : '')).
								$diffHTML;
						}
					}

						// Unsetting the target fields allows us to mark all originals without a version in the subtree (see ->markupNewOriginals())
					unset($this->targets['orig_'.$origId.'_'.$tN.'_'.$rec[$origUidFields]]);
				} else {	// No original record, so must be new:
					$tdParams =  ' class="typo3-ver-new"';
				}
			}
		} else {	// If no original uid column is supported for this table we are forced NOT to display any diff or highlighting.
			$tdParams = ' class="typo3-ver-noComp"';
		}

			// Compile the cell:
		$tCell[] = '
						<tr'.$tdParams.'>
							<td class="iconTitle">'.
								$HTMLdata.
								($iconMode < 2 ?
									'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/ol/join'.($iconMode ? 'bottom' : '').'.gif','width="18" height="16"').' alt="" />'.
									t3lib_iconWorks::getIconImage($tN, $rec, $this->doc->backPath,'') : '').
								t3lib_BEfunc::getRecordTitle($tN, $rec, TRUE).
							'</td>
							<td class="cmdCell">'.
								$this->displayWorkspaceOverview_commandLinksSub($tN,$rec,$origId).
							'</td>'.($origId ? '<td class="diffCell">'.
								$diffCode.
							'</td>':'').'
						</tr>';
	}

	/**
	 * JavaScript code to mark up new records that are online (in sub element lists)
	 *
	 * @return	string		HTML javascript section
	 */
	function markupNewOriginals()	{

		if (count($this->targets))	{
			$scriptCode = '';
			foreach($this->targets as $key => $rec)	{
				$scriptCode.='
					document.getElementById(\''.$key.'\').attributes.getNamedItem("class").nodeValue = \'typo3-ver-new\';
				';
			}

			return $this->doc->wrapScriptTags($scriptCode);
		}
	}

	/**
	 * Create visual difference view of two records. Using t3lib_diff library
	 *
	 * @param	string		Table name
	 * @param	array		New version record (green)
	 * @param	array		Old version record (red)
	 * @return	array		Array with two keys (0/1) with HTML content / percentage integer (if -1, then it means N/A) indicating amount of change
	 */
	function createDiffView($table, $diff_1_record, $diff_2_record)	{
		global $TCA;

			// Initialize:
		$pctChange = 'N/A';

			// Check that records are arrays:
		if (is_array($diff_1_record) && is_array($diff_2_record))	{

				// Load full table description and initialize diff-object:
			t3lib_div::loadTCA($table);
			$t3lib_diff_Obj = t3lib_div::makeInstance('t3lib_diff');

				// Add header row:
			$tRows = array();
			$tRows[] = '
				<tr class="bgColor5 tableheader">
					<td>Fieldname:</td>
					<td width="98%" nowrap="nowrap">Colored diff-view:</td>
				</tr>
			';

				// Initialize variables to pick up string lengths in:
			$allStrLen = 0;
			$diffStrLen = 0;

				// Traversing the first record and process all fields which are editable:
			foreach($diff_1_record as $fN => $fV)	{
				if ($TCA[$table]['columns'][$fN] && $TCA[$table]['columns'][$fN]['config']['type']!='passthrough' && !t3lib_div::inList('t3ver_label',$fN))	{

						// Check if it is files:
					$isFiles = FALSE;
					if (strcmp(trim($diff_1_record[$fN]),trim($diff_2_record[$fN])) &&
							$TCA[$table]['columns'][$fN]['config']['type']=='group' &&
							$TCA[$table]['columns'][$fN]['config']['internal_type']=='file')	{

							// Initialize:
						$uploadFolder = $TCA[$table]['columns'][$fN]['config']['uploadfolder'];
						$files1 = array_flip(t3lib_div::trimExplode(',', $diff_1_record[$fN],1));
						$files2 = array_flip(t3lib_div::trimExplode(',', $diff_2_record[$fN],1));

							// Traverse filenames and read their md5 sum:
						foreach($files1 as $filename => $tmp)	{
							$files1[$filename] = @is_file(PATH_site.$uploadFolder.'/'.$filename) ? md5(t3lib_div::getUrl(PATH_site.$uploadFolder.'/'.$filename)) : $filename;
						}
						foreach($files2 as $filename => $tmp)	{
							$files2[$filename] = @is_file(PATH_site.$uploadFolder.'/'.$filename) ? md5(t3lib_div::getUrl(PATH_site.$uploadFolder.'/'.$filename)) : $filename;
						}

							// Implode MD5 sums and set flag:
						$diff_1_record[$fN] = implode(' ',$files1);
						$diff_2_record[$fN] = implode(' ',$files2);
						$isFiles = TRUE;
					}

						// If there is a change of value:
					if (strcmp(trim($diff_1_record[$fN]),trim($diff_2_record[$fN])))	{


							// Get the best visual presentation of the value and present that:
						$val1 = t3lib_BEfunc::getProcessedValue($table,$fN,$diff_2_record[$fN],0,1);
						$val2 = t3lib_BEfunc::getProcessedValue($table,$fN,$diff_1_record[$fN],0,1);

							// Make diff result and record string lenghts:
						$diffres = $t3lib_diff_Obj->makeDiffDisplay($val1,$val2,$isFiles?'div':'span');
						$diffStrLen+= $t3lib_diff_Obj->differenceLgd;
						$allStrLen+= strlen($val1.$val2);

							// If the compared values were files, substituted MD5 hashes:
						if ($isFiles)	{
							$allFiles = array_merge($files1,$files2);
							foreach($allFiles as $filename => $token)	{
								if (strlen($token)==32 && strstr($diffres,$token))	{
									$filename =
										t3lib_BEfunc::thumbCode(array($fN=>$filename),$table,$fN,$this->doc->backPath).
										$filename;
									$diffres = str_replace($token,$filename,$diffres);
								}
							}
						}

							// Add table row with result:
						$tRows[] = '
							<tr class="bgColor4">
								<td>'.htmlspecialchars($GLOBALS['LANG']->sL(t3lib_BEfunc::getItemLabel($table,$fN))).'</td>
								<td width="98%">'.$diffres.'</td>
							</tr>
						';
					} else {
							// Add string lengths even if value matched - in this was the change percentage is not high if only a single field is changed:
						$allStrLen+=strlen($diff_1_record[$fN].$diff_2_record[$fN]);
					}
				}
			}

				// Calculate final change percentage:
			$pctChange = $allStrLen ? ceil($diffStrLen*100/$allStrLen) : -1;

				// Create visual representation of result:
			if (count($tRows)>1)	{
				$content.= '<table border="0" cellpadding="1" cellspacing="1" class="diffTable">'.implode('',$tRows).'</table>';
			} else {
				$content.= '<span class="nobr">'.$this->doc->icons(1).'Complete match on editable fields.</span>';
			}
		} else $content.= $this->doc->icons(3).'ERROR: Records could strangely not be found!';

			// Return value:
		return array($content,$pctChange);
	}

	/**
	 * Links to stage change of a version
	 *
	 * @param	string		Table name
	 * @param	array		Offline record (version)
	 * @return	string		HTML content, mainly link tags and images.
	 */
	function displayWorkspaceOverview_stageCmd($table,&$rec_off)	{
#debug($rec_off['t3ver_stage']);
		switch((int)$rec_off['t3ver_stage'])	{
			case 0:
				$sId = 1;
				$sLabel = 'Editing';
				$color = '#666666';
 				$label = 'Comment for Reviewer:';
				$titleAttrib = 'Send to Review';
			break;
			case 1:
				$sId = 10;
				$sLabel = 'Review';
				$color = '#6666cc';
				$label = 'Comment for Publisher:';
				$titleAttrib = 'Approve for Publishing';
			break;
			case 10:
				$sLabel = 'Publish';
				$color = '#66cc66';
			break;
			case -1:
				$sLabel = $this->doc->icons(2).'Rejected';
				$sId = 0;
				$color = '#ff0000';
				$label = 'Comment:';
				$titleAttrib = 'Reset stage';
			break;
			default:
				$sLabel = 'Undefined';
				$sId = 0;
				$color = '';
			break;
		}
#debug($sId);

		$raiseOk = !$GLOBALS['BE_USER']->workspaceCannotEditOfflineVersion($table,$rec_off);

		if ($raiseOk && $rec_off['t3ver_stage']!=-1)	{
			$onClick = 'var commentTxt=window.prompt("Please explain why you reject:","");
							if (commentTxt!=null) {window.location.href="'.$this->doc->issueCommand(
							'&cmd['.$table.']['.$rec_off['uid'].'][version][action]=setStage'.
							'&cmd['.$table.']['.$rec_off['uid'].'][version][stageId]=-1'
							).'&cmd['.$table.']['.$rec_off['uid'].'][version][comment]="+escape(commentTxt);}'.
							' return false;';
				// Reject:
			$actionLinks.=
				'<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/down.gif','width="14" height="14"').' alt="" align="top" title="Reject" />'.
				'</a>';
		} else {
				// Reject:
			$actionLinks.=
				'<img src="'.$this->doc->backPath.'gfx/clear.gif" width="14" height="14" alt="" align="top" title="" />';
		}

		$actionLinks.= '<span style="background-color: '.$color.'; color: white;">'.$sLabel.'</span>';

			// Raise
		if ($raiseOk)	{
			$onClick = 'var commentTxt=window.prompt("'.$label.'","");
							if (commentTxt!=null) {window.location.href="'.$this->doc->issueCommand(
							'&cmd['.$table.']['.$rec_off['uid'].'][version][action]=setStage'.
							'&cmd['.$table.']['.$rec_off['uid'].'][version][stageId]='.$sId
							).'&cmd['.$table.']['.$rec_off['uid'].'][version][comment]="+escape(commentTxt);}'.
							' return false;';
			if ($rec_off['t3ver_stage']!=10)	{
				$actionLinks.=
					'<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/up.gif','width="14" height="14"').' alt="" align="top" title="'.htmlspecialchars($titleAttrib).'" />'.
					'</a>';

				$this->stageIndex[$sId][$table][] = $rec_off['uid'];
				$this->recIndex[$table][$rec_off['uid']] = $sId;
			}
		}
		return $actionLinks;
	}

	/**
	 * Links to publishing etc of a version
	 *
	 * @param	string		Table name
	 * @param	array		Online record
	 * @param	array		Offline record (version)
	 * @param	string		Swap type, "branch", "page" or "element"
	 * @return	string		HTML content, mainly link tags and images.
	 */
	function displayWorkspaceOverview_commandLinks($table,&$rec_on,&$rec_off,$vType)	{
		if ($this->publishAccess && (!($GLOBALS['BE_USER']->workspaceRec['publish_access']&1) || (int)$rec_off['t3ver_stage']===10))	{
			$actionLinks =
				'<a href="'.htmlspecialchars($this->doc->issueCommand(
						'&cmd['.$table.']['.$rec_on['uid'].'][version][action]=swap'.
						'&cmd['.$table.']['.$rec_on['uid'].'][version][swapWith]='.$rec_off['uid']
						)).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/insert1.gif','width="14" height="14"').' alt="" align="top" title="Publish" />'.
				'</a>';
			if ($GLOBALS['BE_USER']->workspaceSwapAccess() && (int)$rec_on['t3ver_state']!==1 && (int)$rec_off['t3ver_state']!==2)	{
				$actionLinks.=
					'<a href="'.htmlspecialchars($this->doc->issueCommand(
							'&cmd['.$table.']['.$rec_on['uid'].'][version][action]=swap'.
							'&cmd['.$table.']['.$rec_on['uid'].'][version][swapWith]='.$rec_off['uid'].
							'&cmd['.$table.']['.$rec_on['uid'].'][version][swapIntoWS]=1'
							)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/swap.png','width="14" height="14"').' alt="" align="top" title="Swap" />'.
					'</a>';
			}
		}

		if (!$GLOBALS['BE_USER']->workspaceCannotEditOfflineVersion($table,$rec_off))	{
				// Release
			$actionLinks.=
				'<a href="'.htmlspecialchars($this->doc->issueCommand('&cmd['.$table.']['.$rec_off['uid'].'][version][action]=clearWSID')).'" onclick="return confirm(\'Remove from workspace?\');">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/group_clear.gif','width="14" height="14"').' alt="" align="top" title="Remove from workspace" />'.
				'</a>';

				// Edit
			if ($table==='pages' && $vType!=='element')	{
				$tempUid = ($vType==='branch' || $GLOBALS['BE_USER']->workspace===0 ? $rec_off['uid'] : $rec_on['uid']);
				$actionLinks.=
					'<a href="#" onclick="top.loadEditId('.$tempUid.');top.goToModule(\''.$this->pageModule.'\'); return false;">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,t3lib_extMgm::extRelPath('cms').'layout/layout.gif','width="14" height="12"').' title="Edit page" alt="" />'.
					'</a>';
			} else {
				$params = '&edit['.$table.']['.$rec_off['uid'].']=edit';
				$actionLinks.=
					'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="12" height="12"').' title="Edit element" alt="" />'.
					'</a>';
			}
		}

			// History/Log
		$actionLinks.=
			'<a href="'.htmlspecialchars($this->doc->backPath.'show_rechis.php?element='.rawurlencode($table.':'.$rec_off['uid']).'&returnUrl='.rawurlencode($this->REQUEST_URI)).'">'.
			'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/history2.gif','width="13" height="12"').' title="Show Log" alt="" />'.
			'</a>';

			// View
		if ($table==='pages')	{
			$tempUid = ($vType==='branch' || $GLOBALS['BE_USER']->workspace===0 ? $rec_off['uid'] : $rec_on['uid']);
			$actionLinks.=
				'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($tempUid,$this->doc->backPath,t3lib_BEfunc::BEgetRootLine($tempUid))).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="12" height="12"').' title="" alt="" />'.
				'</a>';
		}

		return $actionLinks;
	}

	/**
	 * Links to publishing etc of a version
	 *
	 * @param	string		Table name
	 * @param	array		Record
	 * @param	integer		The uid of the online version of $uid. If zero it means we are drawing a row for the online version itself while a value means we are drawing display for an offline version.
	 * @return	string		HTML content, mainly link tags and images.
	 */
	function displayWorkspaceOverview_commandLinksSub($table,$rec,$origId)	{
		$uid = $rec['uid'];
		if ($origId || $GLOBALS['BE_USER']->workspace===0)	{
			if (!$GLOBALS['BE_USER']->workspaceCannotEditRecord($table,$rec))	{
					// Edit
				if ($table==='pages')	{
					$actionLinks.=
						'<a href="#" onclick="top.loadEditId('.$uid.');top.goToModule(\''.$this->pageModule.'\'); return false;">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,t3lib_extMgm::extRelPath('cms').'layout/layout.gif','width="14" height="12"').' title="Edit page" alt="" />'.
						'</a>';
				} else {
					$params = '&edit['.$table.']['.$uid.']=edit';
					$actionLinks.=
						'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="12" height="12"').' title="Edit element" alt="" />'.
						'</a>';
				}
			}

				// History/Log
			$actionLinks.=
				'<a href="'.htmlspecialchars($this->doc->backPath.'show_rechis.php?element='.rawurlencode($table.':'.$uid).'&returnUrl='.rawurlencode($this->REQUEST_URI)).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/history2.gif','width="13" height="12"').' title="Show Log" alt="" />'.
				'</a>';
		}

			// View
		if ($table==='pages')	{
			$actionLinks.=
				'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($uid,$this->doc->backPath,t3lib_BEfunc::BEgetRootLine($uid))).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="12" height="12"').' title="" alt="" />'.
				'</a>';
		}

		return $actionLinks;
	}









	/**********************************
	 *
	 * Processing
	 *
	 **********************************/

	/**
	 * Will publish workspace if buttons are pressed
	 *
	 * @return	void
	 */
	function publishAction()	{

			// If "Publish" or "Swap" buttons are pressed:
		if (t3lib_div::_POST('_publish') || t3lib_div::_POST('_swap'))	{

			if ($this->table==='pages')	{	// Making sure ->uid is a page ID!
					// Initialize workspace object and request all pending versions:
				$wslibObj = t3lib_div::makeInstance('wslib');
				$cmd = $wslibObj->getCmdArrayForPublishWS($GLOBALS['BE_USER']->workspace, t3lib_div::_POST('_swap'),$this->uid);

					// Execute the commands:
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values = 0;
				$tce->start(array(), $cmd);
				$tce->process_cmdmap();

				t3lib_BEfunc::getSetUpdateSignal('updatePageTree');

				return $tce->errorLog;
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/version/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/version/cm1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_version_cm1');
$SOBE->init();


$SOBE->main();
$SOBE->printContent();

?>
