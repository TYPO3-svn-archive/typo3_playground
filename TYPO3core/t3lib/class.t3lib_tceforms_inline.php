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
 *   70: class t3lib_TCEforms_inline
 *   87:     function init(&$tceForms)
 *
 *              SECTION: Regular rendering of forms, fields, etc.
 *  121:     function getSingleField_typeInline($table,$field,$row,&$PA)
 *  209:     function getSingleField_typeInline_renderForeignRecord($foreign_table, $rec, $config = array())
 *  259:     function getSingleField_typeInline_renderForeignRecordHeader($foreign_table,$row,$formFieldNames,$config = array())
 *  301:     function getSingleField_typeInline_renderForeignRecordHeaderControl($table,$row,$formFieldNames,$config = array())
 *  471:     function getSingleField_typeInline_addJavaScript()
 *  488:     function getSingleField_typeInline_addJavaScriptSortable($objectId)
 *
 *              SECTION: Handling for AJAX calls
 *  525:     function getSingleField_typeInline_createNewRecord($domObjectId)
 *
 *              SECTION: Get data from database and handle relations
 *  598:     function getSingleField_typeInline_getRelatedRecords($table,$field,$row,&$PA,$config)
 *  650:     function getSingleField_typeInline_getRecord($pid, $table, $uid, $cmd='')
 *
 *              SECTION: Helper functions
 *  762:     function getSingleField_typeInline_getNewRecord($pid, $table)
 *  797:     function getSingleField_typeInline_getStructureTree($domObjectId)
 *  849:     function getSingleField_typeInline_processRequest()
 *
 * TOTAL FUNCTIONS: 13
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



require_once(t3lib_extMgm::extPath('xajax').'class.tx_xajax.php');


class t3lib_TCEforms_inline {
	var $fObj;								// Reference to the calling TCEforms instance
	var $backPath;							// Reference to $fObj->backPath

	var $inlineStructure = array();			// the structure/hierarchy where working in, e.g. cascading inline tables
	var $inlineFirstPid;					// the first call of an inline type appeared on this page (pid of record)
	var $inlineNames = array();				// keys: form, object -> hold the name/id for each of them
	
	var $prependObjectId = '';				// id for DOM objects, set by function dynamically
	var $prependNaming = 'inline';			// how the $this->fObj->prependFormFieldNames should be set ('data' is default)

	var $xajax;								// Instance of the tx_xajax extension
	var $xajaxPrefix = 'inline_';			// prefix for xajax functions in JavaScript


	/**
	 * Intialize an instance of t3lib_TCEforms_inline
	 *
	 * @param	object		$tceForms: Reference to an TCEforms instance
	 * @return	void
	 */
	function init(&$tceForms) {
		$this->fObj = $tceForms;
		$this->backPath =& $tceForms->backPath;

		$this->xajax = t3lib_div::makeInstance('tx_xajax');
		$this->xajax->setRequestURI('alt_doc_ajax.php');
		$this->xajax->decodeUTF8InputOn();
		$this->xajax->outputEntitiesOn();
		// $this->xajax->setCharEncoding('utf-8');
		$this->xajax->setCharEncoding('iso-8859-1');
		$this->xajax->setWrapperPrefix($this->xajaxPrefix);

		$this->xajax->registerFunction(array('createNewRecord', &$this, 'getSingleField_typeInline_createNewRecord'));
		// $this->xajax->registerFunction(array('getHeaderLabel', &$this, 'getHeaderLabel'));
	}


	/*******************************************************
	 *
	 * Regular rendering of forms, fields, etc.
	 *
	 *******************************************************/


	/**
	 * Generation of TCEform elements of the type "inline"
	 * This will render inline-relational-record sets. Relations.
	 *
	 * @param	string		The table name of the record
	 * @param	string		The field name which this element is supposed to edit
	 * @param	array		The record data array where the value(s) for the field can be found
	 * @param	array		An array with additional configuration options.
	 * @return	string		The HTML code for the TCEform field
	 */
	function getSingleField_typeInline($table,$field,$row,&$PA) {
			// Init:
		$config = $PA['fieldConf']['config'];
		$foreign_table = $config['foreign_table'];
		$minitems = t3lib_div::intInRange($config['minitems'],0);
		$maxitems = t3lib_div::intInRange($config['maxitems'],0);
		if (!$maxitems)	$maxitems=100000;

		if ($config['MM']) {
				// FIXME: Just copied for now, change the function to the real demands
			$possibleRecords = $this->getSingleField_typeInline_getPossiblyRecords($table,$field,$row,$PA);
			$selectorBox = $this->getSingleField_typeInline_renderPossibleRecordsSelector($possibleRecords, $config);
		}
		
			// remember the page id (pid of record) where inline editing started first
			// we need that pid for ajax calls, so that they would know where the action takes place on the page structure
		if (!isset($this->inlineFirstPid)) {
			$this->inlineFirstPid = $row['pid'];
			$prependFormFieldNames = $this->fObj->prependFormFieldNames;
			$this->fObj->prependFormFieldNames = $this->prependNaming;
		}
			// add the current inline job to the structure stack
		$this->getSingleField_typeInline_pushStructure($table, $row['uid'], $field);
			// e.g. inline[<table>][<uid>][<field>]
		$nameForm = $this->inlineNames['form'];
			// e.g. inline[<pid>][<table1>][<uid1>][<field1>][<table2>][<uid2>][<field2>]
		$nameObject = $this->inlineNames['object'];
		
			// get the records related to this inline record
		$recordList = $this->getSingleField_typeInline_getRelatedRecords($table,$field,$row,$PA,$config);
			// set the first and last record to the config array
		$config['inline']['first'] = $recordList[0]['uid'];
		$config['inline']['last'] = $recordList[count($recordList)-1]['uid'];

		if ($selectorBox) $item .= $selectorBox;
		
			// add the "Create new record" link if there are less than maxitems
		if (count($recordList) < $maxitems) {
			$onClick = "return inline.createNewRecord('".$nameObject."[$foreign_table]')";
			$item .= '
					<!-- Link for creating a new record: -->
					<div id="typo3-newRecordLink">
						<a href="#" onClick="'.$onClick.'">'.
						'<img'.t3lib_iconWorks::skinImg($this->fObj->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="'.$this->fObj->getLL('l_new',1).'" />'.
						$this->fObj->getLL('l_new',1).
						'</a>
					</div>';
		}

			// wrap the all inline fields of a record with a <div> (like a container)
		// $item .= $this->getSingleField_typeInline_addJavaScriptSortable($nameObject);
		$item .= '<div id="'.$nameObject.'">';
		$relationList = array();
		if (count($recordList)) {
			foreach ($recordList as $rec) {
				$item .= $this->getSingleField_typeInline_renderForeignRecord($row['uid'],$rec,$config);
				$relationList[] = $rec['uid'];
			}
		}
		$item .= '</div>';
		// DEBUG:
		// $item .= '<input size="60" type="text" name="'.$this->inlineNames['ctrlrecords'].'" value="'.implode(',', $relationList).'" />';
		$item .= '<input type="hidden" name="'.$this->inlineNames['ctrlrecords'].'" value="'.implode(',', $relationList).'" />';

			// include JavaScript files once
		if (!$GLOBALS['T3_VAR']['inlineRelational']['imported']) {
			$GLOBALS['SOBE']->doc->JScode .= $this->getSingleField_typeInline_addJavaScript();
			$GLOBALS['T3_VAR']['inlineRelational']['imported'] = true;
			$this->fObj->additionalJS_post[] =
				"\t\t\t\twindow.setTimeout(function() { inline.setPrependFormFieldNames('".$this->fObj->prependFormFieldNames."'); }, 10);";
		}

			// on finishing this section, remove the last item from the structure stack
		$this->getSingleField_typeInline_popStructure();
		
			// if this was the first call to the inline type, restore the values
		if ($prependFormFieldNames) {
			unset($this->inlineFirstPid);
			$this->fObj->prependFormFieldNames = $prependFormFieldNames;
		}
		
		return $item;
	}


	/**
	 * Render the form-fields of a related (foreign) record.
	 *
	 * @param	string		$parentUid: The uid of the parent (embedding) record (uid or NEW...)
	 * @param	array		$rec: The table record of the child/embedded table (normaly post-processed by t3lib_transferData)
	 * @param	array		$config: content of $PA['fieldConf']['config']
	 * @return	string		The HTML code for this "foreign record"
	 */
	function getSingleField_typeInline_renderForeignRecord($parentUid, $rec, $config = array()) {
			// record comes from storage (e.g. database)
		$isNewRecord = t3lib_div::testInt($rec['uid']) ? false : true;

		$foreign_table = $config['foreign_table'];
		$foreign_field = $config['foreign_field'];
		
			// get the current prepentObjectId
		$nameObject = $this->inlineNames['object'];
		$appendFormFieldNames = '['.$foreign_table.']['.$rec['uid'].']';
		$formFieldNames = $nameObject.$appendFormFieldNames;

		$header = $this->getSingleField_typeInline_renderForeignRecordHeader($foreign_table, $rec, $formFieldNames, $config);
		$fields = $this->fObj->getMainFields($foreign_table,$rec);

		$tableAttribs='';
		$tableAttribs.= ' style="margin-right: 5px;' .
			# ($this->borderStyle[0] ? ' '.htmlspecialchars($this->borderStyle[0]) : '') .
			'"';
		$tableAttribs.= $this->fObj->borderStyle[2] ? ' background="'.htmlspecialchars($this->backPath.$this->borderStyle[2]).'"':'';
		# $tableAttribs.= $this->borderStyle[3] ? ' class="'.htmlspecialchars($this->borderStyle[3]).'"':'';
		if ($tableAttribs) $tableAttribs='border="0" cellspacing="0" cellpadding="0" width="100%"'.$tableAttribs;

		$fields = '<table '.$tableAttribs.'>'.$fields.'</table>';
		if ($isNewRecord) {
			$fields .= '<input type="hidden" name="'.$this->inlineNames['form'].$appendFormFieldNames.'[pid]" value="'.$rec['pid'].'"/>';
		} else {
			$fields .= '<input type="hidden" name="'.$this->inlineNames['form'].$appendFormFieldNames.'[__deleted]" value="0" />';
		}

			// set the appearance style of the records of this table
		if (is_array($config['appearance']) && count($config['appearance'])) {
			$appearanceStyle = ' style="'.($config['appearance']['collapseAll'] ? 'display: none; ' : '').'"';
		}

		$out .= '<div id="'.$formFieldNames.'_div" isnewrecord="'.$isNewRecord.'" class="inlineSortable">';
		$out .= '<div id="'.$formFieldNames.'_header" class="inlineDragable">'.$header.'</div>';
		$out .= '<div id="'.$formFieldNames.'_fields"'.$appearanceStyle.'>'.$fields.'</div>';
			// if inline records are related by a "foreign_field"
		if ($foreign_field && $rec['pid']) {
				// if the parent record is new, put the relation information into [__ctrl], this is processed last
			$out .= '<input type="hidden" name="'.$this->prependNaming .
				(t3lib_div::testInt($parentUid) ? '' : '[__ctrl][records]') .
				$appendFormFieldNames.'['.$foreign_field.']" value="'.$parentUid.'" />';
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * Renders the HTML header for a foreign record, such as the title, toggle-function, drag'n'drop, etc.
	 * Later on the command-icons are inserted here.
	 *
	 * @param	string		$foreign_table
	 * @param	array		$row
	 * @param	string		$formFieldNames: Append to prependFormFieldName to get a "namespace" for each form-field
	 * @param	array		$config: content of $PA['fieldConf']['config']
	 * @return	string		The HTML code of the header
	 */
	function getSingleField_typeInline_renderForeignRecordHeader($foreign_table,$row,$formFieldNames,$config = array()) {
		$recTitle = $this->fObj->noTitle(t3lib_BEfunc::getRecordTitle($foreign_table, $row));
		$altText = t3lib_BEfunc::getRecordIconAltText($row, $foreign_table);
		$iconImg = t3lib_iconWorks::getIconImage(
			$foreign_table, $row, $this->backPath,
			'title="'.htmlspecialchars($altText).'" class="absmiddle"'
		);

		$expandSingle = $config['appearance']['expandSingle'] ? 1 : 0;
		$onClick = "return inline.expandCollapseRecord('".htmlspecialchars($formFieldNames)."', $expandSingle)";
		$label .= '<a href="#" onclick="'.$onClick.'" style="display: block">';
		// $label .= '<img '.t3lib_iconWorks::skinImg($this->backPath, 'gfx/ol/plusbullet.gif').' align="absmiddle" /> ';
		$label .= $recTitle;
		$label .= '</a>';

			// from class.db_list_extra.inc
			// $theData[$fCol]=$this->makeControl($table,$row);
			// $theData[$fCol]=$this->makeClip($table,$row);

		$ctrl = $this->getSingleField_typeInline_renderForeignRecordHeaderControl($foreign_table,$row,$formFieldNames,$config);

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
	 * @param	string		$table
	 * @param	array		$row
	 * @param	string		$formFieldNames
	 * @param	[type]		$config: ...
	 * @return	string		The HTML code with the control-icons
	 */
	function getSingleField_typeInline_renderForeignRecordHeaderControl($table,$row,$formFieldNames,$config = array()) {
		global $TCA, $LANG, $SOBE;

			// Initialize:
		# t3lib_div::loadTCA($table);
		$cells=array();
		$isNewItem = substr($row['uid'], 0, 3) == 'NEW';

		$calcPerms = $GLOBALS['BE_USER']->calcPerms(
			t3lib_BEfunc::readPageAccess(
				$row['pid'],
				$GLOBALS['BE_USER']->getPagePermsClause(1)
			)
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
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.showPage',1).'" alt="" />'.
					'</a>';
		}

			// "Move" wizard link for pages/tt_content elements:
		if (($table=="tt_content" && $permsEdit) || ($table=='pages'))	{
			$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\'move_el.php?table='.$table.'&uid='.$row['uid'].'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/move_'.($table=='tt_content'?'record':'page').'.gif','width="11" height="12"').' title="'.$LANG->getLL('move_'.($table=='tt_content'?'record':'page'),1).'" alt="" />'.
					'</a>';
		}

			// FIXME: Handling for this one
			// If the extended control panel is enabled OR if we are seeing a single table:
		if ($SOBE->MOD_SETTINGS['bigControlPanel'] || true)	{

				// "Info": (All records)
			if (!$isNewItem)
				$cells[]='<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.$row['uid'].'\'); return false;').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom2.gif','width="12" height="12"').' title="'.$LANG->getLL('showInfo',1).'" alt="" />'.
					'</a>';

				// If the table is NOT a read-only table, then show these links:
			if (!$TCA[$table]['ctrl']['readOnly'])	{

					// "Revert" link (history/undo)
				if (!$isNewItem)
					$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\'show_rechis.php?element='.rawurlencode($table.':'.$row['uid']).'\',\'#latest\');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/history2.gif','width="13" height="12"').' title="'.$LANG->getLL('history',1).'" alt="" />'.
						'</a>';

					// Versioning:
				if (t3lib_extMgm::isLoaded('version'))	{
					$vers = t3lib_BEfunc::selectVersionsOfRecord($table, $row['uid'], 'uid', $GLOBALS['BE_USER']->workspace);
					if (is_array($vers))	{	// If table can be versionized.
						if (count($vers)>1)	{
							$st = 'background-color: #FFFF00; font-weight: bold;';
							$lab = count($vers)-1;
						} else {
							$st = 'background-color: #9999cc; font-weight: bold;';
							$lab = 'V';
						}

						$cells[]='<a href="'.htmlspecialchars(t3lib_extMgm::extRelPath('version')).'cm1/index.php?table='.rawurlencode($table).'&uid='.rawurlencode($row['uid']).'" style="'.htmlspecialchars($st).'">'.
								$lab.
								'</a>';
					}
				}

					// "Edit Perms" link:
				if ($table=='pages' && $GLOBALS['BE_USER']->check('modules','web_perm'))	{
					$cells[]='<a href="'.htmlspecialchars('mod/web/perm/index.php?id='.$row['uid'].'&return_id='.$row['uid'].'&edit=1').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/perm.gif','width="7" height="12"').' title="'.$LANG->getLL('permissions',1).'" alt="" />'.
							'</a>';
				}

					// "New record after" link (ONLY if the records in the table are sorted by a "sortby"-row or if default values can depend on previous record):
				if ($TCA[$table]['ctrl']['sortby'] || $TCA[$table]['ctrl']['useColumnsForDefaultValues'])	{
					if (
						($table!='pages' && ($calcPerms&16)) || 	// For NON-pages, must have permission to edit content on this parent page
						($table=='pages' && ($calcPerms&8))		// For pages, must have permission to create new pages here.
						)	{
						if ($showNewRecLink)	{
							$onClick = "return inline.createNewRecord('".$formFieldNames."')";
							$params='&edit['.$table.']['.(-$row['uid']).']=new';
							$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
									'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new'.($table=='pages'?'Page':'Record'),1).'" alt="" />'.
									'</a>';
						}
					}
				}

					// "Up/Down" links
				if ($permsEdit && ($TCA[$table]['ctrl']['sortby'] || $config['MM']))	{
					$onClick = "return inline.changeSorting('".$formFieldNames."', '1')";	// Up
					$style = $config['inline']['first'] == $row['uid'] ? 'style="visibility: hidden;"' : '';
					$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'" class="sortingUp" '.$style.'>'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"').' title="'.$LANG->getLL('moveUp',1).'" alt="" />'.
							'</a>';

					$onClick = "return inline.changeSorting('".$formFieldNames."', '-1')";	// Down
					$style = $config['inline']['last'] == $row['uid'] ? 'style="visibility: hidden;"' : '';
					$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'" class="sortingDown" '.$style.'>'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$LANG->getLL('moveDown',1).'" alt="" />'.
							'</a>';
				}

					// "Hide/Unhide" links:
				$hiddenField = $TCA[$table]['ctrl']['enablecolumns']['disabled'];
				if ($permsEdit && $hiddenField && $TCA[$table]['columns'][$hiddenField] && (!$TCA[$table]['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields',$table.':'.$hiddenField)))	{
					$onClick = "return inline.enableDisableRecord('".$formFieldNames."')";
					if ($row[$hiddenField])	{
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=0';
						$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHide'.($table=='pages'?'Page':''),1).'" alt="" id="'.$formFieldNames.'_disabled" />'.
								'</a>';
					} else {
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=1';
						$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hide'.($table=='pages'?'Page':''),1).'" alt="" id="'.$formFieldNames.'_disabled" />'.
								'</a>';
					}
				}

					// "Delete" link:
				if (
					($table=='pages' && ($localCalcPerms&4)) || ($table!='pages' && ($calcPerms&16))
					)	{
					$onClick = "inline.deleteRecord('".$formFieldNames."');";
					$cells[]='<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning').t3lib_BEfunc::referenceCount($table,$row['uid'],' (There are %s reference(s) to this record!)')).')) {	'.$onClick.' } return false;').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete',1).'" alt="" />'.
							'</a>';
				}
			}
		}

			// If the record is edit-locked	by another user, we will show a little warning sign:
		if ($lockInfo=t3lib_BEfunc::isRecordLocked($table,$row['uid']))	{
			$cells[]='<a href="#" onclick="'.htmlspecialchars('alert('.$LANG->JScharCode($lockInfo['msg']).');return false;').'">'.
					'<img'.t3lib_iconWorks::skinImg('','gfx/recordlock_warning3.gif','width="17" height="12"').' title="'.htmlspecialchars($lockInfo['msg']).'" alt="" />'.
					'</a>';
		}


			// Compile items into a DIV-element:
		return '
											<!-- CONTROL PANEL: '.$table.':'.$row['uid'].' -->
											<div class="typo3-DBctrl">'.implode('',$cells).'</div>';
	}

	function getSingleField_typeInline_renderPossibleRecordsSelector($selItems, $config) {
		if(!$disabled) {
				// Create option tags:
			$opt = array();
			$styleAttrValue = '';
			foreach($selItems as $p)	{
				if ($config['iconsInOptionTags'])	{
					$styleAttrValue = $this->fObj->optionTagStyle($p[2]);
				}
				$opt[]= '<option value="'.htmlspecialchars($p[1]).'"'.
								($styleAttrValue ? ' style="'.htmlspecialchars($styleAttrValue).'"' : '').
								'>'.htmlspecialchars($p[0]).'</option>';
			}

				// Put together the selector box:
			$selector_itemListStyle = isset($config['itemListStyle']) ? ' style="'.htmlspecialchars($config['itemListStyle']).'"' : ' style="'.$this->fObj->defaultMultipleSelectorStyle.'"';
			$size = intval($config['size']);
			$size = $config['autoSizeMax'] ? t3lib_div::intInRange(count($itemArray)+1,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
			if ($config['exclusiveKeys'])	{
				$sOnChange = 'setFormValueFromBrowseWin(\''.$PA['itemFormElName'].'\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text,\''.$config['exclusiveKeys'].'\'); ';
			} else {
				$sOnChange = 'setFormValueFromBrowseWin(\''.$PA['itemFormElName'].'\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text); ';
			}
			// $sOnChange .= implode('',$PA['fieldChangeFunc']);
			$itemsToSelect = '
				<select name="'.$PA['itemFormElName'].'_sel"'.
							$this->fObj->insertDefStyle('select').
							($size ? ' size="'.$size.'"' : '').
							' onchange="'.htmlspecialchars($sOnChange).'"'.
							$PA['onFocus'].
							$selector_itemListStyle.'>
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
	 * - xajax.js
	 *
	 * @return	string		The HTML code of the <script type="text/javascript" src="..."> tags
	 */
	function getSingleField_typeInline_addJavaScript() {
		$jsCode = array(
			'<script src="/'.t3lib_extMgm::siteRelPath('scriptaculous').'lib/prototype.js" type="text/javascript"></script>',
			'<script src="/'.t3lib_extMgm::siteRelPath('scriptaculous').'src/scriptaculous.js" type="text/javascript"></script>',
			'<script src="/t3lib/jsfunc.inlinerelational.js" type="text/javascript"></script>',
			$this->xajax->getJavascript('/'.t3lib_extMgm::siteRelPath('xajax'), 'xajax_js/xajax_uncompressed.js'),
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
			window.setTimeout(
				function() {
					Sortable.create(
						"'.$objectId.'",
						{
							tag: "div",
							handle: "inlineHandle",
							only: "inlineSortable",
							constraint: false,
							ghosting: true
						}
					);
				},
				50
			);
		</script>';
		return $jsCode;
	}


	/*******************************************************
	 *
	 * Handling for AJAX calls
	 *
	 *******************************************************/


	/**
	 * Handle calls from xajax to show a new inline-record of the given table.
	 * Normally this method is never called from inside TYPO3. Always from outside by AJAX.
	 *
	 * @param	mixed		$arguments: What to do and where to add, information from the calling browser.
	 * @return	string		An xaJax XML object
	 */
	function getSingleField_typeInline_createNewRecord($domObjectId) {
		global $TCA;

			// set the TCEforms prependFormFieldNames
		$prependFormFieldNames = $this->fObj->prependFormFieldNames;
		$this->fObj->prependFormFieldNames = $this->prependNaming;
		
		$this->getSingleField_typeInline_parseStructureString($domObjectId);
		$current = $this->inlineStructure['unstable'];
		$parent = $this->getSingleField_typeInline_getStructureLevel(-1);

			// load TCA 'config' of the current table
		t3lib_div::loadTCA($parent['table']);
		$config = $TCA[$parent['table']]['columns'][$parent['field']]['config'];

			// dynamically create a new record using t3lib_transferData
		$record = $this->getSingleField_typeInline_getNewRecord($this->inlineFirstPid, $current['table']);
		
			// render the foreign record that should passed back to browser
		$item = $this->getSingleField_typeInline_renderForeignRecord($parent['uid'], $record, $config);

		$objResponse = new tx_xajax_response('iso-8859-1', true);

			// the HTML-object-id's prefix of the dynamically created record
		$objectPrefix = $this->inlineNames['object'].'['.$current['table'].']';

			// append the HTML data at the bottom of the container
		if (!$current['uid']) {
			$objResponse->addScriptCall('inline.domAddNewRecord', 'bottom', $this->inlineNames['object'], $item);
			$objResponse->addScriptCall('inline.memorizeAddRecord', $objectPrefix, $record['uid'], null);

			// ERROR: xajax adds the html data by touching the innerHTML attribute
			// -> entered data of other records in that section gets lost on doing this
			// $objResponse->addAppend($this->inlineNames['object'], 'innerHTML', $item);

			// append the HTML data after an existing record in the container
		} else {
			$objResponse->addScriptCall('inline.domAddNewRecord', 'after', $domObjectId.'_div', $item);
			$objResponse->addScriptCall('inline.memorizeAddRecord', $objectPrefix, $record['uid'], $current['uid']);
			// $objResponse->addScriptCall('inline.insertRecordAfter', $domObjectId.'_div', $item);
		}

			// tell the browser to scroll to the newly created record
		$objResponse->addScriptCall('Element.scrollTo', $this->inlineNames['object'].'['.$current['table'].']['.$record['uid'].']_div');

			// set the TCEforms prependFormFieldNames value back to its initial value
		$this->fObj->prependFormFieldNames = $prependFormFieldNames;

		return $objResponse->getXML();
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

			// Setting this hidden field (as a flag that JavaScript can read out)
		# if (!$disabled) {
		#	$item.= '<input type="hidden" name="'.$PA['itemFormElName'].'_mul" value="'.($config['multiple']?1:0).'" />';
		# }

			// Set max and min items:
		# $maxitems = t3lib_div::intInRange($config['maxitems'],0);
		# if (!$maxitems)	$maxitems=100000;
		# $minitems = t3lib_div::intInRange($config['minitems'],0);

			// Register the required number of elements:
		# $this->fObj->requiredElements[$PA['itemFormElName']] = array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field);

			// Get "removeItems":
		$removeItems = t3lib_div::trimExplode(',',$PA['fieldTSConfig']['removeItems'],1);

			// Perform modification of the selected items array:
		$itemArray = t3lib_div::trimExplode(',',$PA['itemFormElValue'],1);
		foreach($itemArray as $tk => $tv) {
			$tvP = explode('|',$tv,2);
			$evalValue = rawurldecode($tvP[0]);
			$isRemoved = in_array($evalValue,$removeItems) || ($config['form_type']=='select' && $config['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode($table,$field,$evalValue,$config['authMode']));
			if ($isRemoved && !$PA['fieldTSConfig']['disableNoMatchingValueElement'] && !$config['disableNoMatchingValueElement'])	{
				$tvP[1] = rawurlencode(@sprintf($nMV_label, $evalValue));
			} elseif (isset($PA['fieldTSConfig']['altLabels.'][$evalValue])) {
				$tvP[1] = rawurlencode($this->fObj->sL($PA['fieldTSConfig']['altLabels.'][$evalValue]));
			}
				// get the records for this uid using t3lib_transferdata
			$records[] = $this->getSingleField_typeInline_getRecord($row['pid'], $config['foreign_table'], $tvP[0]);
			# $itemArray[$tk] = implode('|',$tvP);
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
	 * @return	array		Array of possible record items
	 */
	function getSingleField_typeInline_getPossiblyRecords($table,$field,$row,&$PA) {
			// Field configuration from TCA:
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
	 * Get a single record row for an TCA table from the database.
	 * t3lib_transferData is used for "upgrading" the values, especially the relations.
	 *
	 * @param	integer		$pid: The pid of the page the record should be stored (only relevant for NEW records)
	 * @param	string		$table: The table to fetch data from (= foreign_table)
	 * @param	string		$uid: The uid of the record to fetch, or empty if a new one should be created
	 * @param	string		$cmd: The command to perform, empty or 'new'
	 * @return	array		A record row from the database post-processed by t3lib_transferData
	 */
	function getSingleField_typeInline_getRecord($pid, $table, $uid, $cmd='') {
		# $prevPageID = is_object($trData) ? $trData->prevPageID : '';
		$trData = t3lib_div::makeInstance('t3lib_transferData');
		$trData->addRawData = TRUE;
		# $trData->defVals = $this->defVals;
		$trData->lockRecords=1;
		$trData->disableRTE = $GLOBALS['SOBE']->MOD_SETTINGS['disableRTE'];
		$trData->prevPageID = $prevPageID;
			// if a new record should be created
		$trData->fetchRecord($table, $uid, $cmd == 'new' ? 'new' : '');
		reset($trData->regTableItems_data);
		$rec = current($trData->regTableItems_data);

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
	 * Handle relations to store data back to database - STATIC
	 *
	 *******************************************************/


	/**
	 * Handle relations and replace NEW... uids by their proper database uids.
	 * Finally the records a pushed to TCEmain and saved, deleted, moved, etc.
	 * This function is normally called from alt_doc.php.
	 *
	 * @param	array	$inline: Reference to the incomming data array of inline records
	 * @param	object	$tec: Reference to a copy of the TCEmain object instance
	 */
	static function getSingleField_typeInline_processData(&$inline, &$tce) {
			// get the fields that hold inline affected data
		$ctrl = $inline['__ctrl'];
		unset($inline['__ctrl']);

		$data = array();
		$cmd = array();

			// find records that should be deleted
		foreach ($inline as $table => $uidData) {
			foreach ($uidData as $uid => $fieldData) {
				if ($fieldData['__deleted'] == 'deleted') {
						// put the item to the cmd array if it's marked to be deleted
					$cmd[$table][$uid]['delete'] = 1;
						// remove the whole record from the data to be saved again by TCEmain
					unset($inline[$table][$uid]);
				} elseif (isset($fieldData['__deleted'])) {
					unset($inline[$table][$uid]['__deleted']);
				}
			}
		}

			// save the inline data without the relations
		$tce->start($inline, array());
		$tce->process_datamap();

			// now process values like '13,NEWabc384823,45' and replace NEW... by proper uids from TCEmain
		foreach ($ctrl['records'] as $table => $uidData) {
			foreach ($uidData as $uid => $fieldData) {
					// FIXME: perhaps this could be removed depending on bug-tracker item 4384
					// if this is an annotation to a recently processed record, substitute to proper uid
				if (preg_match('/NEW/i', $uid) && isset($tce->substNEWwithIDs[$uid]))
					$uid = $tce->substNEWwithIDs[$uid];

					// iterate through fields and substitute NEW... uids by proper uids
				foreach ($fieldData as $field => $value) {
					if (strlen($value) && preg_match('/NEW/i', $value)) {
						$parts = explode(',', $value);
						$partsCnt = count($parts);
						for ($i = 0; $i < $partsCnt; $i++) {
							if (!t3lib_div::testInt($parts[$i])) {
								$parts[$i] = $tce->substNEWwithIDs[$parts[$i]];
							}
						}
						$value = implode(',', $parts);
					}
					$data[$table][$uid][$field] = $value;
				}
			}
		}

			// finally save the relations, and perform deletion of records
		$tce->start($data, $cmd);
		$tce->process_datamap();
		$tce->process_cmdmap();
		
		// FIXME: What should happen if record, that embeds inline child records is deleted or moved to another page
		// - delete: if it's 1:n --> remove the child records (recurisvely!!!)
		// - delete: if it's m:n --> I dont know... yet ;-)
		// - move: possibly adjust the pid of the child records (recurisvely!!!)
	}


	/*******************************************************
	 *
	 * Helper functions
	 *
	 *******************************************************/

		
	function getSingleField_typeInline_pushStructure($table, $uid, $field = '', $params = array()) {
		$this->inlineStructure['stable'][] = array(
			'table'	=> $table,
			'uid' => $uid,
			'field' => $field,
			'params' => $params,
		);
		$this->getSingleField_typeInline_updateStructureNames();
	}
	
	function getSingleField_typeInline_popStructure() {
		if (count($this->inlineStructure['stable'])) {
			$popItem = array_pop($this->inlineStructure['stable']);
			$this->getSingleField_typeInline_updateStructureNames();
		}
		return $popItem;
	}

	function getSingleField_typeInline_updateStructureNames() {
		$current = $this->getSingleField_typeInline_getStructureLevel(-1);
		$lastItemName = $this->getSingleField_typeInline_getStructureItemName($current);
		$this->inlineNames = array(
			'form' => $this->fObj->prependFormFieldNames.$lastItemName,
			'object' => $this->prependNaming.'['.$this->inlineFirstPid.']'.$this->getSingleField_typeInline_getStructurePath(),
			'ctrlrecords' => $this->fObj->prependFormFieldNames.'[__ctrl][records]'.$lastItemName,
		);
	}
	
	function getSingleField_typeInline_getStructureItemName($table, $uid = null, $field = null) {
		if (is_array($table) && !$uid && !$field) {
			$params = $table['params'];
			$field = $table['field'];
			$uid = $table['uid'];
			$table = $table['table'];
		}
		return '['.$table.']['.$uid.']'.(isset($field) ? '['.$field.']' : '');
	}
	
	function getSingleField_typeInline_getStructureLevel($level) {
		if ($level < 0) $level = count($this->inlineStructure['stable'])+$level;
		return $this->inlineStructure['stable'][$level];
	}
	
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
	 * @retunr	void
	 */
	function getSingleField_typeInline_parseStructureString($string) {
		$this->inlineStructure['unstable'] = array();
		$vector = array('table', 'uid', 'field');
		$pattern = '/^'.$this->prependNaming.'\[(.+?)\]\[(.+)\]$/';
		if (preg_match($pattern, $string, $match)) {
			$this->inlineFirstPid = $match[1];
			$parts = explode('][', $match[2]);
			$partsCnt = count($parts);
			for ($i = 0; $i < $partsCnt; $i++) {
				if ($i > 0 && $i % 3 == 0) {
					$this->inlineStructure['stable'][] = $this->inlineStructure['unstable'];
					$this->inlineStructure['unstable'] = array();
				}
				$this->inlineStructure['unstable'][$vector[$i % 3]] = $parts[$i];
			}
			$this->getSingleField_typeInline_updateStructureNames();
			if (!count($this->inlineStructure['unstable'])) unset($this->inlineStructure['unstable']);
		}
	}
	

	/**
	 * Process the xaJax request. This method is normally called directly by alt_doc_ajax.php
	 *
	 * @return	void
	 */
	function getSingleField_typeInline_processRequest() {
		$this->xajax->processRequests();
	}
}
?>