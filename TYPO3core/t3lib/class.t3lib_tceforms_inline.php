<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Oliver Hader <oh@inpublica.de>
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
 * The Inline-Relational-Record-Editing functions as part of the TCEforms.
 *
 * @author	Oliver Hader <oh@inpublica.de>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   82: class t3lib_TCEforms_inline
 *  102:     function init(&$tceForms)
 *  118:     function getSingleField_typeInline($table,$field,$row,&$PA)
 *
 *              SECTION: Regular rendering of forms, fields, etc.
 *  229:     function getSingleField_typeInline_renderForeignRecord($parentUid, $rec, $config = array())
 *  299:     function getSingleField_typeInline_renderForeignRecordHeader($foreign_table,$rec,$config = array())
 *  348:     function getSingleField_typeInline_renderForeignRecordHeaderControl($table,$row,$config = array())
 *  511:     function getSingleField_typeInline_renderCombinationTable(&$rec, $config = array())
 *  568:     function getSingleField_typeInline_renderPossibleRecordsSelector($selItems, $conf, $uniqueIds=array())
 *  622:     function getSingleField_typeInline_addJavaScript()
 *  638:     function getSingleField_typeInline_addJavaScriptSortable($objectId)
 *
 *              SECTION: Handling of AJAX calls
 *  672:     function getSingleField_typeInline_createNewRecord($domObjectId, $foreignUid = 0)
 *  759:     function getSingleField_typeInline_getJSON($jsonArray)
 *
 *              SECTION: Get data from database and handle relations
 *  785:     function getSingleField_typeInline_getRelatedRecords($table,$field,$row,&$PA,$config)
 *  827:     function getSingleField_typeInline_getPossibleRecords($table,$field,$row,$conf,$checkForConfField='foreign_selector')
 *  873:     function getSingleField_typeInline_getUniqueIds($records, $conf=array())
 *  893:     function getSingleField_typeInline_getRecord($pid, $table, $uid, $cmd='')
 *  931:     function getSingleField_typeInline_getNewRecord($pid, $table)
 *
 *              SECTION: Structure stack for handling inline objects/levels
 * 1038:     function getSingleField_typeInline_pushStructure($table, $uid, $field = '', $config = array())
 * 1054:     function getSingleField_typeInline_popStructure()
 * 1071:     function getSingleField_typeInline_updateStructureNames()
 * 1088:     function getSingleField_typeInline_getStructureItemName($levelData)
 * 1103:     function getSingleField_typeInline_getStructureLevel($level)
 * 1116:     function getSingleField_typeInline_getStructurePath($structureDepth = -1)
 * 1141:     function getSingleField_typeInline_parseStructureString($string, $loadConfig = false)
 *
 *              SECTION: Helper functions
 * 1184:     function getSingleField_typeInline_compareStructureConfiguration($compare)
 * 1198:     function getSingleField_typeInline_normalizeUid($string)
 * 1212:     function getSingleField_typeInline_wrapFormsSection($section, $styleAttrs = array(), $tableAttrs = array())
 * 1241:     function getSingleField_typeInline_isInlineChildAndLabelField($table, $field)
 * 1253:     function getSingleField_typeInline_getStructureDepth()
 * 1289:     function arrayCompareComplex($subjectArray, $searchArray, $type = '')
 *
 * TOTAL FUNCTIONS: 29
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class t3lib_TCEforms_inline {
	var $fObj;								// Reference to the calling TCEforms instance
	var $backPath;							// Reference to $fObj->backPath

	var $inlineStructure = array();			// the structure/hierarchy where working in, e.g. cascading inline tables
	var $inlineFirstPid;					// the first call of an inline type appeared on this page (pid of record)
	var $inlineNames = array();				// keys: form, object -> hold the name/id for each of them
	var $inlineData = array();				// inline data array used for JSON output
	var $inlineCount = 0;					// count the number of inline types used

	var $prependNaming = 'data';			// how the $this->fObj->prependFormFieldNames should be set ('data' is default)
	var $prependFormFieldNames;				// reference to $this->fObj->prependFormFieldNames
	var $prependCmdFieldNames;				// reference to $this->fObj->prependCmdFieldNames
	

	/**
	 * Intialize an instance of t3lib_TCEforms_inline
	 *
	 * @param	object		$tceForms: Reference to an TCEforms instance
	 * @return	void
	 */
	function init(&$tceForms) {
		$this->fObj =& $tceForms;
		$this->backPath =& $tceForms->backPath;
		$this->prependFormFieldNames =& $this->fObj->prependFormFieldNames;
		$this->prependCmdFieldNames =& $this->fObj->prependCmdFieldNames;
	}


	/**
	 * Generation of TCEform elements of the type "inline"
	 * This will render inline-relational-record sets. Relations.
	 *
	 * @param	string		$table: The table name of the record
	 * @param	string		$field: The field name which this element is supposed to edit
	 * @param	array		$row: The record data array where the value(s) for the field can be found
	 * @param	array		$PA: An array with additional configuration options.
	 * @return	string		The HTML code for the TCEform field
	 */
	function getSingleField_typeInline($table,$field,$row,&$PA) {
			// check the TCA configuration - if false is returned, something was wrong
		if ($this->checkConfiguration($PA['fieldConf']['config']) === false) return false;
		
			// count the number of processed inline elements
		$this->inlineCount++;

			// Init:
		$config = $PA['fieldConf']['config'];
		$foreign_table = $config['foreign_table'];
		
		// @TODO: Minitems müssen unterstüzt werden und intInRange mit maxitems immer mindestens minItems
		$minitems = t3lib_div::intInRange($config['minitems'],0);
		$maxitems = t3lib_div::intInRange($config['maxitems'],0);
		if (!$maxitems)	$maxitems=100000;

			// remember the page id (pid of record) where inline editing started first
			// we need that pid for ajax calls, so that they would know where the action takes place on the page structure
		if (!isset($this->inlineFirstPid)) {
			$this->inlineFirstPid = $row['pid'];
		}
			// add the current inline job to the structure stack
		$this->getSingleField_typeInline_pushStructure($table, $row['uid'], $field, $config);
			// e.g. inline[<table>][<uid>][<field>]
		$nameForm = $this->inlineNames['form'];
			// e.g. inline[<pid>][<table1>][<uid1>][<field1>][<table2>][<uid2>][<field2>]
		$nameObject = $this->inlineNames['object'];
			// get the records related to this inline record
		$recordList = $this->getSingleField_typeInline_getRelatedRecords($table,$field,$row,$PA,$config);
			// set the first and last record to the config array
		$config['inline']['first'] = $recordList[0]['uid'];
		$config['inline']['last'] = $recordList[count($recordList)-1]['uid'];

			// tell the browser what we have (using JSON later)
		$this->inlineData['config'][$nameObject.'['.$foreign_table.']'] = array(
			'min' => $minitems,
			'max' => $maxitems,
		);
		
			// if relations are required to be unique, get the uids that have already been used on the foreign side of the relation
		if ($config['foreign_unique']) {
			$uniqueIds = $this->getSingleField_typeInline_getUniqueIds($recordList, $config);
			$possibleRecords = $this->getSingleField_typeInline_getPossibleRecords($table,$field,$row,$config,'foreign_unique');
			$uniqueMax = $config['appearance']['useCombination'] ? -1 : count($possibleRecords);
			$this->inlineData['unique'][$nameObject.'['.$foreign_table.']'] = array(
				'max' => $uniqueMax,
				'used' => $uniqueIds,
				'table' => $config['foreign_table'],
				'field' => $config['foreign_unique'],
				'selector' => $config['foreign_selector'] ? true : false,
				'possible' => $this->getSingleField_typeInline_getPossibleRecordsFlat($possibleRecords)
			);
		}

			// if it's required to select from possible child records (reusable children), add a selector box
		if ($config['foreign_selector']) {
				// if not already set by the foreign_unique, set the possibleRecords here and the uniqueIds to an empty array
			if (!$config['foreign_unique']) {
				$possibleRecords = $this->getSingleField_typeInline_getPossibleRecords($table,$field,$row,$config);
				$uniqueIds = array();
			}
			$selectorBox = $this->getSingleField_typeInline_renderPossibleRecordsSelector($possibleRecords,$config,$uniqueIds);
			$item .= $selectorBox;
		}

			// wrap all inline fields of a record with a <div> (like a container)
		$item .= '<div id="'.$nameObject.'">';
		
			// define how to show the "Create new record" link - if there are more than maxitems, hide it
		if (count($recordList) >= $maxitems || ($uniqueMax > 0 && count($recordList) >= $uniqueMax))
			$config['inline']['inlineNewButtonStyle'] = 'display: none;';
			
			// add the "Create new record" link before all child records
		if ($config['appearance']['newRecordLinkPosition'] != 'bottom') {
			$item .= $this->getNewRecordLink($nameObject.'['.$foreign_table.']', $config['inline']['inlineNewButtonStyle']);
		}
			
		$relationList = array();
		if (count($recordList)) {
			foreach ($recordList as $rec) {
				$item .= $this->getSingleField_typeInline_renderForeignRecord($row['uid'],$rec,$config);
				$relationList[] = $rec['uid'];
			}
		}
			
			// add the "Create new record" link after all child records
		if ($config['appearance']['newRecordLinkPosition'] != 'top') {
			$item .= $this->getNewRecordLink($nameObject.'['.$foreign_table.']', $config['inline']['inlineNewButtonStyle']);
		}
		
			// close the wrap for all inline fields (container)
		$item .= '</div>';
		
			// add Drag&Drop functions for sorting
		// $item .= $this->getSingleField_typeInline_addJavaScriptSortable($nameObject);
		$item .= '<input type="hidden" name="'.$nameForm.'" value="'.implode(',', $relationList).'" />';

			// on finishing this section, remove the last item from the structure stack
		$this->getSingleField_typeInline_popStructure();

			// if this was the first call to the inline type, restore the values
		if (!$this->getSingleField_typeInline_getStructureDepth()) {
			unset($this->inlineFirstPid);
		}

		return $item;
	}


	/*******************************************************
	 *
	 * Regular rendering of forms, fields, etc.
	 *
	 *******************************************************/


	/**
	 * Render the form-fields of a related (foreign) record.
	 *
	 * @param	string		$parentUid: The uid of the parent (embedding) record (uid or NEW...)
	 * @param	array		$rec: The table record of the child/embedded table (normaly post-processed by t3lib_transferData)
	 * @param	array		$config: content of $PA['fieldConf']['config']
	 * @return	string		The HTML code for this "foreign record"
	 */
	function getSingleField_typeInline_renderForeignRecord($parentUid, $rec, $config = array()) {
		$foreign_table = $config['foreign_table'];
		$foreign_field = $config['foreign_field'];
		$foreign_selector = $config['foreign_selector'];

			// record comes from storage (e.g. database)
		$isNewRecord = t3lib_div::testInt($rec['uid']) ? false : true;
			// if there is a selector field, normalize it
		if ($foreign_selector) {
			$rec[$foreign_selector] = $this->getSingleField_typeInline_normalizeUid($rec[$foreign_selector]);
		}

		$hasAccess = $this->checkAccess($isNewRecord?'new':'edit', $foreign_table, $rec['uid']);
		
		if(!$hasAccess) return false;

			// get the current prependObjectId
		$nameObject = $this->inlineNames['object'];
		$appendFormFieldNames = '['.$foreign_table.']['.$rec['uid'].']';
		$formFieldNames = $nameObject.$appendFormFieldNames;

		$header = $this->getSingleField_typeInline_renderForeignRecordHeader($foreign_table, $rec, $config);
		$combination = $this->getSingleField_typeInline_renderCombinationTable($rec, $appendFormFieldNames, $config);
		$fields = $this->fObj->getMainFields($foreign_table,$rec);
		$fields = $this->getSingleField_typeInline_wrapFormsSection($fields);

		if ($isNewRecord) {
			$fields .= '<input type="hidden" name="'.$this->prependFormFieldNames.$appendFormFieldNames.'[pid]" value="'.$rec['pid'].'"/>';
		} else {
			$fields .= '<input type="hidden" name="'.$this->prependCmdFieldNames.$appendFormFieldNames.'[delete]" value="1" disabled="disabled" />';
		}
		
			// set the appearance style of the records of this table
		if (is_array($config['appearance']) && count($config['appearance']))
			$appearanceStyle = ' style="'.($config['appearance']['collapseAll'] ? 'display: none; ' : '').'"';

		$out = '<div id="'.$formFieldNames.'_header" class="sortableHandle">'.$header.'</div>';
		$out .= '<div id="'.$formFieldNames.'_fields"'.$appearanceStyle.'>'.$fields.$combination.'</div>';

			// if the fields for symmetric relations were swapped, send information about the keys (foreign_field|symmetric_fields)
			// on saving the record, this information is swapped back for storing via TCEmain
		if ($rec['__symmetric']) {
			$out .= '<input type="hidden" name="'.$this->prependNaming.'[__ctrl][symmetric]' .
				$appendFormFieldNames.'" value="'.htmlspecialchars($rec['__symmetric']).'" />';
		}

		$out = '<div id="'.$formFieldNames.'_div"'.($isNewRecord ? ' class="inlineIsNewRecord"' : '').'>' . $out . '</div>';

		return $out;
	}

	/*
	 * Checks the page access rights (Code for access check mostly taken from alt_doc.php)
	 * as well as the table access rights of the user.
	 */

	function checkAccess($cmd, $table, $theUid) {
		global $BE_USER;

			// Checking if the user has permissions? (Only working as a precaution, because the final permission check is always down in TCE. But it's good to notify the user on beforehand...)
			// First, resetting flags.
		$hasAccess = 0;
		$deniedAccessReason = '';

			// If the command is to create a NEW record...:
		if ($cmd=='new') {
			$calcPRec = t3lib_BEfunc::getRecord('pages',$this->inlineFirstPid);
			if(!is_array($calcPRec)) {
				return false;
			}
			$CALC_PERMS = $BE_USER->calcPerms($calcPRec);	// Permissions for the parent page
			if ($table=='pages')	{	// If pages:
				$hasAccess = $CALC_PERMS&8 ? 1 : 0; // Are we allowed to create new subpages?
			} else {
				$hasAccess = $CALC_PERMS&16 ? 1 : 0; // Are we allowed to edit content on this page?
			}
		} else {	// Edit:
			$calcPRec = t3lib_BEfunc::getRecord($table,$theUid);
			t3lib_BEfunc::fixVersioningPid($table,$calcPRec);
			if (is_array($calcPRec))	{
				if ($table=='pages')	{	// If pages:
					$CALC_PERMS = $BE_USER->calcPerms($calcPRec);
					$hasAccess = $CALC_PERMS&2 ? 1 : 0;
				} else {
					$CALC_PERMS = $BE_USER->calcPerms(t3lib_BEfunc::getRecord('pages',$calcPRec['pid']));	// Fetching pid-record first.
					$hasAccess = $CALC_PERMS&16 ? 1 : 0;
				}

					// Check internals regarding access:
				if ($hasAccess)	{
					$hasAccess = $BE_USER->recordEditAccessInternals($table, $calcPRec);
				}
			}
		}
		
		if(!$BE_USER->check('tables_modify', $table)) {
			$hasAccess = 0;
		}

		if(!$hasAccess) {
			$deniedAccessReason = $BE_USER->errorMsg;
			if($deniedAccessReason) {
				debug($deniedAccessReason);
			}
		}
		
		return $hasAccess;
	
	}

	/**
	 * Renders the HTML header for a foreign record, such as the title, toggle-function, drag'n'drop, etc.
	 * Later on the command-icons are inserted here.
	 *
	 * @param	string		$foreign_table: The foreign_table we create a header for
	 * @param	array		$rec: The current record of that table
	 * @param	array		$config: content of $PA['fieldConf']['config']
	 * @return	string		The HTML code of the header
	 */
	function getSingleField_typeInline_renderForeignRecordHeader($foreign_table,$rec,$config = array()) {
			// if an alternative label for the field we render is set, use it
		$titleCol = $config['foreign_label']
			? $config['foreign_label']
			: $GLOBALS['TCA'][$foreign_table]['ctrl']['label'];

		$recTitle = t3lib_BEfunc::getProcessedValueExtra($foreign_table, $titleCol, $rec[$titleCol]);
		$recTitle = $this->fObj->noTitle($recTitle);

		$altText = t3lib_BEfunc::getRecordIconAltText($rec, $foreign_table);
		$iconImg = t3lib_iconWorks::getIconImage(
			$foreign_table, $rec, $this->backPath,
			'title="'.htmlspecialchars($altText).'" class="absmiddle"'
		);

		$formFieldNames = $this->inlineNames['object'].'['.$foreign_table.']['.$rec['uid'].']';
		$expandSingle = $config['appearance']['expandSingle'] ? 1 : 0;
		$onClick = "return inline.expandCollapseRecord('".htmlspecialchars($formFieldNames)."', $expandSingle)";
		$label .= '<a href="#" onclick="'.$onClick.'" style="display: block">';
		// $label .= '<img '.t3lib_iconWorks::skinImg($this->backPath, 'gfx/ol/plusbullet.gif').' align="absmiddle" /> ';
		$label .= '<span id="'.$formFieldNames.'_label">'.$recTitle.'</span>';
		$label .= '</a>';

			// from class.db_list_extra.inc
			// $theData[$fCol]=$this->makeControl($table,$rec);
			// $theData[$fCol]=$this->makeClip($table,$rec);

		$ctrl = $this->getSingleField_typeInline_renderForeignRecordHeaderControl($foreign_table,$rec,$config);

			// FIXME: Use the correct css-classes to fit with future skins etc.
		$header =
			'<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-right: 5px;"'.
			($this->fObj->borderStyle[2] ? ' background="'.htmlspecialchars($this->backPath.$this->fObj->borderStyle[2]).'"':'').
			($this->fObj->borderStyle[3] ? ' class="'.htmlspecialchars($this->fObj->borderStyle[3]).'"':'').'>' .
			'<tr class="class-main12"><td width="18">'.$iconImg.'</td><td align="left"><b>'.$label.'</b></td><td align="right">'.$ctrl.'</td></tr></table>';

		return $header;
	}


	/**
	 * Render the control-icons for a record header (create new, sorting, delete, disable/enable).
	 * Most of the parts are copy&paste from class.db_list_extra.inc and modified for the JavaScript calls here
	 *
	 * @param	string		$table: The table (foreign_table) we create control-icons for
	 * @param	array		$row: The current record of that table
	 * @param	array		$config: (modified) TCA configuration of the field
	 * @return	string		The HTML code with the control-icons
	 */
	function getSingleField_typeInline_renderForeignRecordHeaderControl($table,$row,$config = array()) {
		global $TCA, $SOBE;

			// Initialize:
		$cells=array();
		$isNewItem = substr($row['uid'], 0, 3) == 'NEW';

		$nameObjectFt = $this->inlineNames['object'].'['.$table.']';
		$nameObjectFtId = $nameObjectFt.'['.$row['uid'].']';

		$calcPerms = $GLOBALS['BE_USER']->calcPerms(
			t3lib_BEfunc::readPageAccess($row['pid'], $GLOBALS['BE_USER']->getPagePermsClause(1))
		);

			// FIXME: Put these calls somewhere else... possibly they arn't needed here
		$web_list_modTSconfig = t3lib_BEfunc::getModTSconfig($row['pid'],'mod.web_list');
		$allowedNewTables = t3lib_div::trimExplode(',',$this->fObj->web_list_modTSconfig['properties']['allowedNewTables'],1);
		$showNewRecLink = !count($allowedNewTables) || in_array($table, $allowedNewTables);

			// If the listed table is 'pages' we have to request the permission settings for each page:
		if ($table=='pages')	{
			$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$row['uid']));
		}

			// This expresses the edit permissions for this particular element:
		$permsEdit = ($table=='pages' && ($localCalcPerms&2)) || ($table!='pages' && ($calcPerms&16));

			// "Show" link (only pages and tt_content elements)
		if ($table=='pages' || $table=='tt_content')	{
			$params='&edit['.$table.']['.$row['uid'].']=edit';
			$cells[]='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($table=='tt_content'?$this->id.'#'.$row['uid']:$row['uid'])).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showPage',1).'" alt="" />'.
					'</a>';
		}

			// "Info": (All records)
		if (!$isNewItem)
			$cells[]='<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.$row['uid'].'\'); return false;').'">'.
				'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom2.gif','width="12" height="12"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:showInfo',1).'" alt="" />'.
				'</a>';

			// If the table is NOT a read-only table, then show these links:
		if (!$TCA[$table]['ctrl']['readOnly'])	{
			
				// "New record after" link (ONLY if the records in the table are sorted by a "sortby"-row or if default values can depend on previous record):
			if ($TCA[$table]['ctrl']['sortby'] || $TCA[$table]['ctrl']['useColumnsForDefaultValues'])	{
				if (
					($table!='pages' && ($calcPerms&16)) || 	// For NON-pages, must have permission to edit content on this parent page
					($table=='pages' && ($calcPerms&8))		// For pages, must have permission to create new pages here.
					)	{
					if ($showNewRecLink)	{
						$onClick = "return inline.createNewRecord('".$nameObjectFt."','".$row['uid']."')";
						$params='&edit['.$table.']['.(-$row['uid']).']=new';
						$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'" class="inlineNewButton"'.$config['inline']['inlineNewButtonStyle'].'>'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:new'.($table=='pages'?'Page':'Record'),1).'" alt="" />'.
								'</a>';
					}
				}
			}

				// "Up/Down" links
			if ($permsEdit && ($TCA[$table]['ctrl']['sortby'] || $config['MM']))	{
				$onClick = "return inline.changeSorting('".$nameObjectFtId."', '1')";	// Up
				$style = $config['inline']['first'] == $row['uid'] ? 'style="visibility: hidden;"' : '';
				$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'" class="sortingUp" '.$style.'>'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:moveUp',1).'" alt="" />'.
						'</a>';

				$onClick = "return inline.changeSorting('".$nameObjectFtId."', '-1')";	// Down
				$style = $config['inline']['last'] == $row['uid'] ? 'style="visibility: hidden;"' : '';
				$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'" class="sortingDown" '.$style.'>'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:moveDown',1).'" alt="" />'.
						'</a>';
			}

				// "Hide/Unhide" links:
			$hiddenField = $TCA[$table]['ctrl']['enablecolumns']['disabled'];
			if ($permsEdit && $hiddenField && $TCA[$table]['columns'][$hiddenField] && (!$TCA[$table]['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields',$table.':'.$hiddenField)))	{
				$onClick = "return inline.enableDisableRecord('".$nameObjectFtId."')";
				if ($row[$hiddenField])	{
					$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=0';
					$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:unHide'.($table=='pages'?'Page':''),1).'" alt="" id="'.$nameObjectFtId.'_disabled" />'.
							'</a>';
				} else {
					$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=1';
					$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:hide'.($table=='pages'?'Page':''),1).'" alt="" id="'.$nameObjectFtId.'_disabled" />'.
							'</a>';
				}
			}

				// "Delete" link:
			if (
				($table=='pages' && ($localCalcPerms&4)) || ($table!='pages' && ($calcPerms&16))
				)	{
				$onClick = "inline.deleteRecord('".$nameObjectFtId."');";
				$cells[]='<a href="#" onclick="'.htmlspecialchars('if (confirm('.$GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('deleteWarning').t3lib_BEfunc::referenceCount($table,$row['uid'],' (There are %s reference(s) to this record!)')).')) {	'.$onClick.' } return false;').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:delete',1).'" alt="" />'.
						'</a>';
			}
		}

			// If the record is edit-locked	by another user, we will show a little warning sign:
		if ($lockInfo=t3lib_BEfunc::isRecordLocked($table,$row['uid']))	{
			$cells[]='<a href="#" onclick="'.htmlspecialchars('alert('.$GLOBALS['LANG']->JScharCode($lockInfo['msg']).');return false;').'">'.
					'<img'.t3lib_iconWorks::skinImg('','gfx/recordlock_warning3.gif','width="17" height="12"').' title="'.htmlspecialchars($lockInfo['msg']).'" alt="" />'.
					'</a>';
		}

			// Compile items into a DIV-element:
		return '
											<!-- CONTROL PANEL: '.$table.':'.$row['uid'].' -->
											<div class="typo3-DBctrl">'.implode('',$cells).'</div>';
	}

	/**
	 * Render a table with TCEforms, that occurs on a intermediate table but should be editable directly,
	 * so two tables are combined (the intermediate table with attributes and the sub-embedded table).
	 * -> This is a direct embedding over two levels!
	 *
	 * @param	array		$rec: The table record of the child/embedded table (normaly post-processed by t3lib_transferData)
	 * @param	string		$appendFormFieldNames: The [<table>][<uid>] of the parent record (the intermediate table)
	 * @param	array		$config: content of $PA['fieldConf']['config']
	 * @return	string		A HTML string with <table> tag around.
	 */
	function getSingleField_typeInline_renderCombinationTable(&$rec, $appendFormFieldNames, $config = array()) {
		$foreign_table = $config['foreign_table'];
		$foreign_selector = $config['foreign_selector'];

		if ($foreign_selector && $config['appearance']['useCombination']) {
			$comboConfig = $GLOBALS['TCA'][$foreign_table]['columns'][$foreign_selector]['config'];
			$comboRecord = array();

				// record does already exist, so load it
			if (t3lib_div::testInt($rec[$foreign_selector])) {
				$comboRecord = $this->getSingleField_typeInline_getRecord(
					$this->inlineFirstPid,
					$comboConfig['foreign_table'],
					$rec[$foreign_selector]
				);
				$isNewRecord = false;

				// it's a new record, so get some default data
			} else {
				$comboRecord = $this->getSingleField_typeInline_getNewRecord(
					$this->inlineFirstPid,
					$comboConfig['foreign_table']
				);
					// tell our parent (intermediate table), that we have a uid (NEW...)
				// $rec[$foreign_selector] = $comboRecord['uid'];
				$isNewRecord = true;
			}

				// get the TCEforms interpretation of the TCA of the child table
			$out = $this->fObj->getMainFields($comboConfig['foreign_table'], $comboRecord);
			$out = $this->getSingleField_typeInline_wrapFormsSection($out, array(), array('class' => 'wrapperAttention'));

				// if this is a new record, add a pid value to store this record and the pointer value for the intermediate table
			if ($isNewRecord) {
				$comboFormFieldName = $this->prependFormFieldNames.'['.$comboConfig['foreign_table'].']['.$comboRecord['uid'].'][pid]';
				$out .= '<input type="hidden" name="'.$comboFormFieldName.'" value="'.$this->inlineFirstPid.'"/>';
			}
			
				// if the foreign_selector field is also responsible for uniqueness, tell the browser the uid of the "other" side of the relation
			if ($isNewRecord || $config['foreign_unique'] == $foreign_selector) {
				$parentFormFieldName = $this->prependFormFieldNames.$appendFormFieldNames.'['.$foreign_selector.']';
				$out .= '<input type="hidden" name="'.$parentFormFieldName.'" value="'.$comboRecord['uid'].'" />';
			}
		}

		return $out;
	}

	/**
	 * Get a selector as used for the select type, to select from all available
	 * records and to create a relation to the embedding record (e.g. like MM).
	 *
	 * @param	array		$selItems: Array of all possible records
	 * @param	array		$conf: TCA configuration of the parent(!) field
	 * @param	array		$uniqueIds: The uids that have already been used and should be unique
	 * @return	string		A HTML <select> box with all possible records
	 */
	function getSingleField_typeInline_renderPossibleRecordsSelector($selItems, $conf, $uniqueIds=array()) {
		$foreign_table = $conf['foreign_table'];
		$foreign_selector = $conf['foreign_selector'];

		$PA = array();
		$PA['fieldConf'] = $GLOBALS['TCA'][$foreign_table]['columns'][$foreign_selector];
		$PA['fieldConf']['config']['form_type'] = $PA['fieldConf']['config']['form_type'] ? $PA['fieldConf']['config']['form_type'] : $PA['fieldConf']['config']['type'];	// Using "form_type" locally in this script
		$PA['fieldTSConfig'] = $this->fObj->setTSconfig($foreign_table,array(),$foreign_selector);
		$config = $PA['fieldConf']['config'];

		if(!$disabled) {
				// Create option tags:
			$opt = array();
			$styleAttrValue = '';
			foreach($selItems as $p)	{
				if ($config['iconsInOptionTags'])	{
					$styleAttrValue = $this->fObj->optionTagStyle($p[2]);
				}
				if (!in_array($p[1], $uniqueIds)) {
					$opt[]= '<option value="'.htmlspecialchars($p[1]).'"'.
									' style="'.(in_array($p[1], $uniqueIds) ? '' : '').
									($styleAttrValue ? ' style="'.htmlspecialchars($styleAttrValue) : '').'">'.
									htmlspecialchars($p[0]).'</option>';
				}
			}

				// Put together the selector box:
			$selector_itemListStyle = isset($config['itemListStyle']) ? ' style="'.htmlspecialchars($config['itemListStyle']).'"' : ' style="'.$this->fObj->defaultMultipleSelectorStyle.'"';
			$size = intval($config['size']);
			$size = $config['autoSizeMax'] ? t3lib_div::intInRange(count($itemArray)+1,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
			$sOnChange = "return inline.importNewRecord('".$this->inlineNames['object']."[".$conf['foreign_table']."]', this.options[this.selectedIndex])";
			$itemsToSelect = '
				<select id="'.$this->inlineNames['object'].'['.$conf['foreign_table'].']_selector"'.
							$this->fObj->insertDefStyle('select').
							($size ? ' size="'.$size.'"' : '').
							' onchange="'.htmlspecialchars($sOnChange).'"'.
							$PA['onFocus'].
							$selector_itemListStyle.
							($conf['foreign_unique'] ? ' isunique="isunique"' : '').'>
					'.implode('
					',$opt).'
				</select>';
		}

		return $itemsToSelect;
	}


	/**
	 * Get the <script type="text/javascript" src="..."> tags of:
	 * - prototype.js
	 * - script.acolo.us
	 *
	 * @return	string		The HTML code of the <script type="text/javascript" src="..."> tags
	 */
	function getSingleField_typeInline_addJavaScript() {
		$jsCode = array(
			'<script src="prototype.js" type="text/javascript"></script>',
			'<script src="scriptaculous/scriptaculous.js" type="text/javascript"></script>',
			'<script src="../t3lib/jsfunc.inlinerelational.js" type="text/javascript"></script>',
		);

		return implode("\n", $jsCode);
	}

	/**
	 * Add Sortable functionality using script.acolo.us "Sortable".
	 *
	 * @param	string		$objectId: The container id of the object - elements inside will be sortable
	 * @return	string		The HTML code creating the Sortable element, wrapped by <script>
	 */
	function getSingleField_typeInline_addJavaScriptSortable($objectId) {
		$jsCode = '
		<script type="text/javascript">
			Sortable.create(
				"'.$objectId.'",
				{
					onUpdate: function() { alert("done"); },
					tag: "div",
					handle: "sortableHandle",
					overlap: "vertical",
					constraint: "vertical",
					delay: 300
				}
			);
		</script>';
		return $jsCode;
	}


	/*******************************************************
	 *
	 * Handling of AJAX calls
	 *
	 *******************************************************/


	/**
	 * Handle AJAX calls to show a new inline-record of the given table.
	 * Normally this method is never called from inside TYPO3. Always from outside by AJAX.
	 *
	 * @param	mixed		$arguments: What to do and where to add, information from the calling browser.
	 * @param	string		$foreignUid: If set, the new record should be inserted after that one
	 * @return	string		A JSON string
	 */
	function getSingleField_typeInline_createNewRecord($domObjectId, $foreignUid = 0) {
		global $TCA;

			// parse the DOM identifier (string), add the levels to the structure stack (array) and load the TCA config
		$this->getSingleField_typeInline_parseStructureString($domObjectId, true);
			// the current table - for this table we should add/import records
		$current = $this->inlineStructure['unstable'];
			// the parent table - this table embeds the current table
		$parent = $this->getSingleField_typeInline_getStructureLevel(-1);
			// get TCA 'config' of the parent table
		$config = $parent['config'];

			// dynamically create a new record using t3lib_transferData
		if (!$foreignUid || !t3lib_div::testInt($foreignUid) || $config['foreign_selector']) {
			$record = $this->getSingleField_typeInline_getNewRecord($this->inlineFirstPid, $current['table']);

			// dynamically import an existing record (this could be a call from a select box)
		} else {
			$record = $this->getSingleField_typeInline_getRecord($this->inlineFirstPid, $current['table'], $foreignUid);
		}

			// now there is a foreign_selector, so there is a new record on the intermediate table, but
			// this intermediate table holds a field, which is responsible for the foreign_selector, so
			// we have to set this field to the uid we get - or if none, to a new uid
		if ($config['foreign_selector'] && $foreignUid) {
			$record[$config['foreign_selector']] = $foreignUid;
		}

			// the HTML-object-id's prefix of the dynamically created record
		$objectPrefix = $this->inlineNames['object'].'['.$current['table'].']';
		$objectId = $objectPrefix.'['.$record['uid'].']';

			// render the foreign record that should passed back to browser
		$item = $this->getSingleField_typeInline_renderForeignRecord($parent['uid'], $record, $config);
		if($item === false) {
			$jsonArray = array(
				'data'	=> 'Access denied',
				'scriptCall' => array(
					"alert('Access denied');",
				)
			);
			return $this->getSingleField_typeInline_getJSON($jsonArray);
		} 

		if (!$current['uid']) {
			$jsonArray = array(
				'data'	=> $item,
				'scriptCall' => array(
					"inline.domAddNewRecord('bottom','".$this->inlineNames['object']."','$objectPrefix',json.data);",
					"inline.memorizeAddRecord('$objectPrefix','".$record['uid']."',null,'$foreignUid');"
				)
			);

			// append the HTML data after an existing record in the container
		} else {
			$jsonArray = array(
				'data'	=> $item,
				'scriptCall' => array(
					"inline.domAddNewRecord('after','".$domObjectId.'_div'."','$objectPrefix',json.data);",
					"inline.memorizeAddRecord('$objectPrefix','".$record['uid']."','".$current['uid']."','$foreignUid');"
				)
			);
		}

			// if a new level of child records (child of children) was created, send the JSON array
		if (count($this->inlineData))
			$jsonArray['scriptCall'][] = 'inline.addToDataArray('.$this->getSingleField_typeInline_getJSON($this->inlineData).');';
			// tell the browser to scroll to the newly created record
		$jsonArray['scriptCall'][] = "Element.scrollTo('".$objectId."_div');";
			// fade out and fade in the new record in the browser view to catch the user's eye
		$jsonArray['scriptCall'][] = "inline.fadeOutFadeIn('".$objectId."_div');";

			// return the JSON string
		return $this->getSingleField_typeInline_getJSON($jsonArray);
	}


	/**
	 * Creates recursively a JSON literal from a mulidimensional associative array.
	 * Uses Services_JSON (http://mike.teczno.com/JSON/doc/)
	 *
	 * @param	array		$jsonArray: The array (or part of) to be transformed to JSON
	 * @return	string		If $level>0: part of JSON literal; if $level==0: whole JSON literal wrapped with <script> tags
	 */
	function getSingleField_typeInline_getJSON($jsonArray) {
		if (!$GLOBALS['JSON']) {
			require_once('json.php');
			$GLOBALS['JSON'] = t3lib_div::makeInstance('Services_JSON');
		}
		return $GLOBALS['JSON']->encode($jsonArray);
	}
	
	/**
	 * Creates a link/button to create new records
	 *
	 * @param	string		$objectPrefix: The "path" to the child record to create (e.g. '[parten_table][parent_uid][parent_field][child_table]')
	 * @param	string		$style: If a style should be added to the link (e.g. 'display: none;')
	 * @return	string		The HTML code for the new record link
	 */
	function getNewRecordLink($objectPrefix, $style = '') {
		if ($style) $style = ' style="'.$style.'"';
		$onClick = "return inline.createNewRecord('$objectPrefix')";
		$out = '
				<div class="typo3-newRecordLink">
					<a href="#" onClick="'.$onClick.'" class="inlineNewButton"'.$style.'>'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="'.$this->fObj->getLL('l_new',1).'" />'.
					$this->fObj->getLL('l_new',1).
					'</a>
				</div>';
		return $out;
	}


	/*******************************************************
	 *
	 * Get data from database and handle relations
	 *
	 *******************************************************/


	/**
	 * Get the related records of the embedding item, this could be 1:n, m:n.
	 *
	 * @param	string		$table: The table name of the record
	 * @param	string		$field: The field name which this element is supposed to edit
	 * @param	array		$row: The record data array where the value(s) for the field can be found
	 * @param	array		$PA: An array with additional configuration options.
	 * @param	array		$config: (Redundant) content of $PA['fieldConf']['config'] (for convenience)
	 * @return	array		The records related to the parent item
	 */
	function getSingleField_typeInline_getRelatedRecords($table,$field,$row,&$PA,$config) {
		$records = array();

			// Creating the label for the "No Matching Value" entry.
		$nMV_label = isset($PA['fieldTSConfig']['noMatchingValue_label']) ? $this->fObj->sL($PA['fieldTSConfig']['noMatchingValue_label']) : '[ '.$this->fObj->getLL('l_noMatchingValue').' ]';

			// Register the required number of elements:
		# $this->fObj->requiredElements[$PA['itemFormElName']] = array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field);

			// Perform modification of the selected items array:
		$itemArray = t3lib_div::trimExplode(',',$PA['itemFormElValue'],1);
		foreach($itemArray as $tk => $tv) {
			$tvP = explode('|',$tv,2);
				// get the records for this uid using t3lib_transferdata
			$records[] = $this->getSingleField_typeInline_getRecord($row['pid'], $config['foreign_table'], $tvP[0]);
		}

		return $records;
	}


	/**
	 * Get possible records.
	 * Copied from TCEform and modified.
	 *
	 * @param	string		The table name of the record
	 * @param	string		The field name which this element is supposed to edit
	 * @param	array		The record data array where the value(s) for the field can be found
	 * @param	array		An array with additional configuration options.
	 * @param	string		$checkForConfField: For which field in the foreign_table the possible records should be fetched
	 * @return	array		Array of possible record items
	 */
	function getSingleField_typeInline_getPossibleRecords($table,$field,$row,$conf,$checkForConfField='foreign_selector') {
			// Field configuration from TCA:
		$foreign_table = $conf['foreign_table'];
		$foreign_check = $conf[$checkForConfField];

		$PA = array();
		$PA['fieldConf'] = $GLOBALS['TCA'][$foreign_table]['columns'][$foreign_check];
		$PA['fieldConf']['config']['form_type'] = $PA['fieldConf']['config']['form_type'] ? $PA['fieldConf']['config']['form_type'] : $PA['fieldConf']['config']['type'];	// Using "form_type" locally in this script
		$PA['fieldTSConfig'] = $this->fObj->setTSconfig($foreign_table,array(),$foreign_check);
		$config = $PA['fieldConf']['config'];

			// Getting the selector box items from the system
		$selItems = $this->fObj->addSelectOptionsToItemArray($this->fObj->initItemArray($PA['fieldConf']),$PA['fieldConf'],$this->fObj->setTSconfig($table,$row),$field);
		if ($config['itemsProcFunc']) $selItems = $this->fObj->procItems($selItems,$PA['fieldTSConfig']['itemsProcFunc.'],$config,$table,$row,$field);

			// Possibly remove some items:
		$removeItems = t3lib_div::trimExplode(',',$PA['fieldTSConfig']['removeItems'],1);
		foreach($selItems as $tk => $p)	{

				// Checking languages and authMode:
			$languageDeny = $TCA[$table]['ctrl']['languageField'] && !strcmp($TCA[$table]['ctrl']['languageField'], $field) && !$GLOBALS['BE_USER']->checkLanguageAccess($p[1]);
			$authModeDeny = $config['form_type']=='select' && $config['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode($table,$field,$p[1],$config['authMode']);
			if (in_array($p[1],$removeItems) || $languageDeny || $authModeDeny)	{
				unset($selItems[$tk]);
			} elseif (isset($PA['fieldTSConfig']['altLabels.'][$p[1]])) {
				$selItems[$tk][0]=$this->fObj->sL($PA['fieldTSConfig']['altLabels.'][$p[1]]);
			}

				// Removing doktypes with no access:
			if ($table.'.'.$field == 'pages.doktype')	{
				if (!($GLOBALS['BE_USER']->isAdmin() || t3lib_div::inList($GLOBALS['BE_USER']->groupData['pagetypes_select'],$p[1])))	{
					unset($selItems[$tk]);
				}
			}
		}

		return $selItems;
	}

	/**
	 * Gets the uids of a select/selector that should be unique an have already been used.
	 *
	 * @param	array		$records: All inline records on this level
	 * @param	array		$conf: The TCA field configuration of the inline field to be rendered
	 * @return	array		The uids, that have been used already and should be used unique
	 */
	function getSingleField_typeInline_getUniqueIds($records, $conf=array()) {
		$uniqueIds = array();

		if ($conf['foreign_unique'] && count($records))
			foreach ($records as $rec) $uniqueIds[$rec['uid']] = $rec[$conf['foreign_unique']];

		return $uniqueIds;
	}


	/**
	 * Get a single record row for a TCA table from the database.
	 * t3lib_transferData is used for "upgrading" the values, especially the relations.
	 *
	 * @param	integer		$pid: The pid of the page the record should be stored (only relevant for NEW records)
	 * @param	string		$table: The table to fetch data from (= foreign_table)
	 * @param	string		$uid: The uid of the record to fetch, or empty if a new one should be created
	 * @param	string		$cmd: The command to perform, empty or 'new'
	 * @return	array		A record row from the database post-processed by t3lib_transferData
	 */
	function getSingleField_typeInline_getRecord($pid, $table, $uid, $cmd='') {
		$trData = t3lib_div::makeInstance('t3lib_transferData');
		$trData->addRawData = TRUE;
		# $trData->defVals = $this->defVals;
		$trData->lockRecords=1;
		$trData->disableRTE = $GLOBALS['SOBE']->MOD_SETTINGS['disableRTE'];
			// if a new record should be created
		$trData->fetchRecord($table, $uid, ($cmd === 'new' ? 'new' : ''));
		reset($trData->regTableItems_data);
		$rec = current($trData->regTableItems_data);

			// Check, if we have to swap fields for the case that the parent uid is responsible
			// for the pointer value on the *other* side - this is the case if an other parent
			// built the relation and were looking at it now.
			// This is only relevant on relation records, that were already saved.
		$level = $this->getSingleField_typeInline_getStructureLevel(-1);
		if (t3lib_div::testInt($rec['uid']) && $level['config']['symmetric_field']) {
				// if our current relation was defined from the "other side", swap the value pairs
			if ($level['uid'] == $rec[$level['config']['symmetric_field']]) {
				$temp = $rec[$level['config']['foreign_field']];
				$rec[$level['config']['foreign_field']] = $rec[$level['config']['symmetric_field']];
				$rec[$level['config']['symmetric_field']] = $temp;
				$rec['__symmetric'] = $level['config']['foreign_field'].'|'.$level['config']['symmetric_field'];
			}
		}

		$rec['uid'] = $cmd == 'new' ? uniqid('NEW') : $uid;
		if ($cmd=='new') $rec['pid'] = $pid;

		return $rec;
	}


	/**
	 * Wrapper. Calls getSingleField_typeInline_getRecord in case of a new record should be created.
	 *
	 * @param	integer		$pid: The pid of the page the record should be stored (only relevant for NEW records)
	 * @param	string		$table: The table to fetch data from (= foreign_table)
	 * @return	array		A record row from the database post-processed by t3lib_transferData
	 */
	function getSingleField_typeInline_getNewRecord($pid, $table) {
		return $this->getSingleField_typeInline_getRecord($pid, $table, '', 'new');
	}


	/*******************************************************
	 *
	 * Structure stack for handling inline objects/levels
	 *
	 *******************************************************/


	/**
	 * Add a new level on top of the structure stack. Other functions can access the
	 * stack and determine, if there's possibly a endless loop.
	 *
	 * @param	string		$table: The table name of the record
	 * @param	string		$uid: The uid of the record that embeds the inline data
	 * @param	string		$field: The field name which this element is supposed to edit
	 * @param	array		$config: The TCA-configuration of the inline field
	 * @return	void
	 */
	function getSingleField_typeInline_pushStructure($table, $uid, $field = '', $config = array()) {
		$this->inlineStructure['stable'][] = array(
			'table'	=> $table,
			'uid' => $uid,
			'field' => $field,
			'config' => $config,
		);
		$this->getSingleField_typeInline_updateStructureNames();
	}


	/**
	 * Remove the item on top of the structure stack and return it.
	 *
	 * @return	array		The top item of the structure stack - array(<table>,<uid>,<field>,<config>)
	 */
	function getSingleField_typeInline_popStructure() {
		if (count($this->inlineStructure['stable'])) {
			$popItem = array_pop($this->inlineStructure['stable']);
			$this->getSingleField_typeInline_updateStructureNames();
		}
		return $popItem;
	}


	/**
	 * For common use of DOM object-ids and form field names of a several inline-level,
	 * these names/identifiers are preprocessed and set to $this->inlineNames.
	 * This function is automatically called if a level is pushed to or removed from the
	 * inline structure stack.
	 *
	 * @return	void
	 */
	function getSingleField_typeInline_updateStructureNames() {
		$current = $this->getSingleField_typeInline_getStructureLevel(-1);
		$lastItemName = $this->getSingleField_typeInline_getStructureItemName($current);
		$this->inlineNames = array(
			'form' => $this->prependFormFieldNames.$lastItemName,
			'object' => $this->prependNaming.'['.$this->inlineFirstPid.']'.$this->getSingleField_typeInline_getStructurePath(),
		);
	}


	/**
	 * Create a name/id for usage in HTML output of a level of the structure stack.
	 *
	 * @param	array		$levelData: Array of a level of the structure stack (containing the keys table, uid and field)
	 * @return	string		The name/id of that level, to be used for HTML output
	 */
	function getSingleField_typeInline_getStructureItemName($levelData) {
		return	'['.$levelData['table'].']' .
				'['.$levelData['uid'].']' .
				(isset($levelData['field']) ? '['.$levelData['field'].']' : '');
	}


	/**
	 * Get a level from the stack and return the data.
	 * If the $level value is negative, this function works top-down,
	 * if the $level value is positive, this function works bottom-up.
	 *
	 * @param	integer		$level: Which level to return
	 * @return	array		The item of the stack at the requested level
	 */
	function getSingleField_typeInline_getStructureLevel($level) {
		$inlineStructureCount = count($this->inlineStructure['stable']);
		if ($level < 0) $level = $inlineStructureCount+$level;
		if ($level >= 0 && $level < $inlineStructureCount)
			return $this->inlineStructure['stable'][$level];
		else
			return false;
	}


	/**
	 * Get the identifiers of a given depth of level, from the top of the stack to the bottom.
	 * An identifier consists looks like [<table>][<uid>][<field>].
	 *
	 * @param	integer		$structureDepth: How much levels to output, beginning from the top of the stack
	 * @return	string		The path of identifiers
	 */
	function getSingleField_typeInline_getStructurePath($structureDepth = -1) {
		$structureCount = count($this->inlineStructure['stable']);
		if ($structureDepth < 0 || $structureDepth > $structureCount) $structureDepth = $structureCount;

		for ($i = 1; $i <= $structureDepth; $i++) {
			$current = $this->getSingleField_typeInline_getStructureLevel(-$i);
			$string = $this->getSingleField_typeInline_getStructureItemName($current).$string;
		}

		return $string;
	}


	/**
	 * Convert the DOM object-id of an inline container to an array.
	 * The object-id could look like 'data[inline][tx_mmftest_company][1][employees]'.
	 * The result is written to $this->inlineStructure.
	 * There are two keys:
	 *  - 'stable': Containing full qualified identifiers (table, uid and field)
	 *  - 'unstable': Containting partly filled data (e.g. only table and possibly field)
	 *
	 * @param	string		$domObjectId: The DOM object-id
	 * @param	boolean		$loadConfig: Load the TCA configuration for that level
	 * @return	void
	 */
	function getSingleField_typeInline_parseStructureString($string, $loadConfig = false) {
		global $TCA;

		$unstable = array();
		$vector = array('table', 'uid', 'field');
		$pattern = '/^'.$this->prependNaming.'\[(.+?)\]\[(.+)\]$/';
		if (preg_match($pattern, $string, $match)) {
			$this->inlineFirstPid = $match[1];
			$parts = explode('][', $match[2]);
			$partsCnt = count($parts);
			for ($i = 0; $i < $partsCnt; $i++) {
				if ($i > 0 && $i % 3 == 0) {
						// load the TCA configuration of the table field and store it in the stack
					if ($loadConfig) {
						t3lib_div::loadTCA($unstable['table']);
						$unstable['config'] = $TCA[$unstable['table']]['columns'][$unstable['field']]['config'];
					}
					$this->inlineStructure['stable'][] = $unstable;
					$unstable = array();
				}
				$unstable[$vector[$i % 3]] = $parts[$i];
			}
			$this->getSingleField_typeInline_updateStructureNames();
			if (count($unstable)) $this->inlineStructure['unstable'] = $unstable;
		}
	}


	/*******************************************************
	 *
	 * Helper functions
	 *
	 *******************************************************/
	
	
	/**
	 * Does some checks on the TCA configuration of the inline field to render.
	 *
	 * @param	array		$config: Reference to the TCA field configuration
	 * @return	boolean		If critical configuration errors were found, false is returned
	 */
	function checkConfiguration(&$config) {
		$foreign_table = $config['foreign_table'];
		
			// an inline field must have a foreign_table, if not, stop all further inline actions for this field
		if (!$foreign_table || !is_array($GLOBALS['TCA'][$foreign_table]))
			return false;
			
		if (!is_array($config['appearance']))
			$config['appearance'] = array();
		if (!in_array($config['appearance']['newRecordLinkPosition'], array('top', 'bottom', 'both')))
			$config['appearance']['newRecordLinkPosition'] = 'top';
		
		return true;
	}


	/**
	 * Check the keys and values in the $compare array against the ['config'] part of the top level of the stack.
	 * A boolean value is return depending on how the comparison was successful.
	 *
	 * @param	array		$compare: keys and values to compare to the ['config'] part of the top level of the stack
	 * @return	boolean		Whether the comparison was successful
	 * @see 	arrayCompareComplex
	 */
	function getSingleField_typeInline_compareStructureConfiguration($compare) {
		$level = $this->getSingleField_typeInline_getStructureLevel(-1);
		$result = $this->arrayCompareComplex($level, $compare);

		return $result;
	}


	/**
	 * Normalize a relation "uid" published by transferData, like "1|Company%201"
	 *
	 * @param	string		$string: A transferData reference string, containing the uid
	 * @return	string		The normalized uid
	 */
	function getSingleField_typeInline_normalizeUid($string) {
		$parts = explode('|', $string);
		return $parts[0];
	}


	/**
	 * Wrap the HTML code of a section with a table tag.
	 *
	 * @param	string		$section: The HTML code to be wrapped
	 * @param	array		$styleAttrs: Attributes for the style argument in the table tag
	 * @param	array		$tableAttrs: Attributes for the table tag (like width, border, etc.)
	 * @return	string		The wrapped HTML code
	 */
	function getSingleField_typeInline_wrapFormsSection($section, $styleAttrs = array(), $tableAttrs = array()) {
		if (!$styleAttrs['margin-right']) $styleAttrs['margin-right'] = '5px';

		foreach ($styleAttrs as $key => $value) $style .= ($style?' ':'').$key.': '.htmlspecialchars($value).'; ';
		if ($style) $style = ' style="'.$style.'"';

		if (!$tableAttrs['background'] && $this->fObj->borderStyle[2]) $tableAttrs['background'] = $this->backPath.$this->borderStyle[2];
		if (!$tableAttrs['cellspacing']) $tableAttrs['cellspacing'] = '0';
		if (!$tableAttrs['cellpadding']) $tableAttrs['cellpadding'] = '0';
		if (!$tableAttrs['border']) $tableAttrs['border'] = '0';
		if (!$tableAttrs['width']) $tableAttrs['width'] = '100%';
		if (!$tableAttrs['class'] && $this->borderStyle[3]) $tableAttrs['class'] = $this->borderStyle[3];

		foreach ($tableAttrs as $key => $value) $table .= ($table?' ':'').$key.'="'.htmlspecialchars($value).'"';

		$out = '<table '.$table.$style.'>'.$section.'</table>';
		return $out;
	}


	/**
	 * Checks if the $table is the child of a inline type AND the $field is the label field of this table.
	 * This function is used to dynamically update the label while editing. This has no effect on labels,
	 * that were processed by a TCEmain-hook on saving.
	 *
	 * @param	string		$table: The table to check
	 * @param	string		$field: The field on this table to check
	 * @return	boolean		is inline child and field is responsible for the label
	 */
	function getSingleField_typeInline_isInlineChildAndLabelField($table, $field) {
		$level = $this->getSingleField_typeInline_getStructureLevel(-1);
		if ($level['config']['foreign_label'])
			$label = $level['config']['foreign_label'];
		else
			$label = $GLOBALS['TCA'][$table]['ctrl']['label'];
		return $level['config']['foreign_table'] === $table && $label == $field ? true : false;
	}

	
	/**
	 * Get the depth of the stable structure stack.
	 * (count($this->inlineStructure['stable'])
	 *
	 * @return	integer		The depth of the structure stack
	 */
	function getSingleField_typeInline_getStructureDepth() {
		return count($this->inlineStructure['stable']);
	}

	
	/**
	 * Handles complex comparison requests on an array.
	 * A request could look like the following:
	 *
	 * $searchArray = array(
	 * 		'%AND'	=> array(
	 * 			'key1'	=> 'value1',
	 * 			'key2'	=> 'value2',
	 * 			'%OR'	=> array(
	 * 				'subarray' => array(
	 * 					'subkey' => 'subvalue'
	 * 				),
	 * 				'key3'	=> 'value3',
	 * 				'key4'	=> 'value4'
	 * 			)
	 * 		)
	 * );
	 *
	 * It is possible to use the array keys '%AND.1', '%AND.2', etc. to prevent
	 * overwriting the sub-array. It could be neccessary, if you use complex comparisons.
	 *
	 * The example above means, key1 *AND* key2 (and their values) have to match with
	 * the $subjectArray and additional one *OR* key3 or key4 have to meet the same
	 * condition.
	 * It is also possible to compare parts of a sub-array (e.g. "subarray"), so this
	 * function recurses down one level in that sub-array.
	 *
	 * @param	array		$subjectArray: The array to search in
	 * @param	array		$searchArray: The array with keys and values to search for
	 * @param	string		$type: Use '%AND' or '%OR' for comparision
	 * @return	boolean		The result of the comparison
	 */
	function arrayCompareComplex($subjectArray, $searchArray, $type = '') {
		$localMatches = 0;
		$localEntries = 0;

		if (is_array($searchArray) && count($searchArray)) {
				// if no type was passed, try to determine
			if (!$type) {
				reset($searchArray);
				$type = key($searchArray);
				$searchArray = current($searchArray);
			}

				// we use '%AND' and '%OR' in uppercase
			$type = strtoupper($type);

				// split regular elements from sub elements
			foreach ($searchArray as $key => $value) {
				$localEntries++;

					// process a sub-group of OR-conditions
				if (substr(strtoupper($key),0,3) == '%OR')
					$localMatches += $this->arrayCompareComplex($subjectArray, $value, '%OR') ? 1 : 0;
					// process a sub-group of AND-conditions
				elseif (substr(strtoupper($key),0,4) == '%AND')
					$localMatches += $this->arrayCompareComplex($subjectArray, $value, '%AND') ? 1 : 0;
					// a part of a array should be compared, so step down in the array hierarchy
				elseif (is_array($value))
					$localMatches += $this->arrayCompareComplex($subjectArray[$key], $value, $type) ? 1 : 0;
					// directly compare a value
				else
					$localMatches += isset($subjectArray[$key]) && isset($value) && $subjectArray[$key] === $value ? 1 : 0;

					// if one or more matches are required ('OR'), return true after the first successful match
				if ($type == '%OR' && $localMatches > 0) return true;
					// if all matches are required ('AND') and we have no result after the first run, return false
				if ($type == '%AND' && $localMatches == 0) return false;
			}
		}

			// return the result for '%AND' (if nothing was checked, true is returned)
		return $localEntries == $localMatches ? true : false;
	}

	
	/**
	 * Makes a flat array from the $possibleRecords array.
	 * The key of the flat array is the value of the record,
	 * the value of the flat array is the label of the record.
	 *
	 * @param	array		$possibleRecords: The possibleRecords array (for select fields)
	 * @return	array		A flat array with key=uid, value=label
	 */
	function getSingleField_typeInline_getPossibleRecordsFlat($possibleRecords) {
		$flat = array();
		if (is_array($possibleRecords))
			foreach ($possibleRecords as $record) $flat[$record[1]] = $record[0];
		return $flat;
	}


	
	/**
	 * Check, if a field should be skipped, that was defined to be handled as foreign_field or foreign_sortby of
	 * the parent record of the "inline"-type - if so, we have to skip this field - the rendering is done via "inline" as hidden field
	 * 
	 * @param	string		$table: The table name
	 * @param	string		$field: The field name
	 * @return	boolean		Determines whether the field should be skipped.
	 */
	function getSingleField_typeInline_skipField($table, $field, $config) {
		$skipThisField = false;
		if ($this->getSingleField_typeInline_getStructureDepth()) {
			$searchArray = array(
				'%OR' => array(
					'%AND.0' => array(
						'config' => array(
							'foreign_table' => $table,
							'%OR' => array(
								'foreign_field' => $field,
								'foreign_sortby' => $field,
								'%AND' => array(
									'appearance' => array('useCombination' => 1),
									'foreign_selector' => $field,
								),
								'MM' => $config['MM']
							)
						)
					),
					'%AND.1' => array(
						'config' => array(
							'foreign_table' => $config['foreign_table'],
							'foreign_selector' => $config['foreign_field']
						),
					)
				)
			);

				// if the test on the configuration was successful, skip this field
			$skipThisField = $this->getSingleField_typeInline_compareStructureConfiguration($searchArray, true);
		}
		return $skipThisField;
	}
 }
?>