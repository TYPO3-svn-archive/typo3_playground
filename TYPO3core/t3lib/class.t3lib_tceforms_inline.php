<?php


require_once(t3lib_extMgm::extPath('xajax').'class.tx_xajax.php');
require_once(t3lib_extMgm::extPath('scriptaculous').'class.tx_scriptaculous.php');


class t3lib_tceforms_inline {
	var $fObj;		// Reference to the calling TCEforms instance
	var $backPath;	// Reference to $fObj->backPath

	var $xajax;		// Instance of the tx_xajax extension
	

	function init(&$tceForms) {
		$this->fObj = $tceForms;
		$this->backPath =& $tceForms->backPath;

		$this->xajax = t3lib_div::makeInstance('tx_xajax');
		$this->xajax->setRequestURI('alt_doc_ajax.php');
		$this->xajax->decodeUTF8InputOn();
		$this->xajax->outputEntitiesOn();
		// $this->xajax->setCharEncoding('utf-8');
		$this->xajax->setCharEncoding('iso-8859-1');
		$this->xajax->setWrapperPrefix('inline_');

		$this->xajax->registerFunction(array('createNewRecord', &$this, 'getSingleField_typeInline_createNewRecord'));
		$this->xajax->registerFunction(array('getHeaderLabel', &$this, 'getHeaderLabel'));
	}
	
	/**
	 * Handle calls from xajax to show a new inline-record of the given table.
	 * Normally this method is never called from inside TYPO3. Always from outside by AJAX.
	 *
	 * @param	mixed		$arguments: What to do and where to add, information from the calling browser.
	 * @return	string		An xaJax XML object
	 */
	function getSingleField_typeInline_createNewRecord($arguments) {
		$structureTree = $this->getSingleField_typeInline_getStructureTree($arguments);
		$lastBranch = $structureTree['lastBranch'] == null ? $structureTree : $structureTree['lastBranch'];
		
		$item = 'hallo welt';
		
		$objResponse = new tx_xajax_response('iso-8859-1', true);
		
			// add new id to the internal hidden form field
		$objResponse->addScriptCall('tx_dynbeedit_addNewInstanceHtml', $args, $newInstanceHtml);
			// append the HTML data to the container
		$objResponse->addScriptCall('tx_dynbeedit_addNewInstanceHtml', $args, $item);
		
		return $objResponse->getXML();
	}
	
	
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

		$itemArray = array();
		$recordList = array();
		
		$minitems = t3lib_div::intInRange($config['minitems'],0);
		$maxitems = t3lib_div::intInRange($config['maxitems'],0);
		if (!$maxitems)	$maxitems=100000;

			// get the records related to this inline record
		$recordList = $this->getSingleField_typeInline_getRelatedRecords($table,$field,$row,$PA,$config);
		$recordList[] = $this->getSingleField_typeInline_getRecord($row['pid'], $foreign_table, '', 'new');
		
			// FIXME: maybe something nicer than overwriting and setting back later
			// (extend getMainFields with attribute 'prependFormFieldNames'?)
		$prependFormFieldNames = $this->fObj->prependFormFieldNames;
		$this->fObj->prependFormFieldNames .= '[inline]'.substr($PA['itemFormElName'], strlen($this->fObj->prependFormFieldNames));
			
			// add the "Create new record" link if there are less than maxitems
		if (count($recordList) < $maxitems) {
			$onClick = "return inline.createNewRecord('".$this->fObj->prependFormFieldNames."')";
			$item .= '
					<!--
						Link for creating a new record:
					-->
					<div id="typo3-newRecordLink">
						<a href="#" onClick="'.$onClick.'">'.
						'<img'.t3lib_iconWorks::skinImg($this->fObj->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="'.$this->fObj->getLL('l_new',1).'" />'.
						$this->fObj->getLL('l_new',1).
						'</a>
					</div>';
		}
		
			// wrap the all inline fields of a record with a <div> (like a container)
		$item .= '<div id="'.$this->fObj->prependFormFieldNames.'">';
		if (count($recordList)) {
			foreach ($recordList as $rec) {
				$item .= $this->getSingleField_typeInline_renderForeignRecord($row,$foreign_table,$rec,$PA);
			}
		}
		$item .= '</div>';
		
		/*
			* show "add new record" link
			* render foreign records
			  * render foreign record header
			  * render main TCEforms of this record
		*/

		$this->fObj->prependFormFieldNames = $prependFormFieldNames;
		
			// include JavaScript files
		if (!$GLOBALS['T3_VAR']['inlineRelational']['imported']) {
			$GLOBALS['SOBE']->doc->JScode .= $this->getSingleField_typeInline_addJavaScript();
			$GLOBALS['T3_VAR']['inlineRelational']['imported'] = true;
		}
		
		return $item;
	}

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
	
	function getSingleField_typeInline_renderForeignRecord($parentRow,$foreign_table,$rec,&$PA) {
			// record comes from storage (e.g. database)
		$isNewRecord = t3lib_div::testInt($foreign_uid) ? false : true;
		
		$appendFormFieldNames = '['.$foreign_table.']['.$rec['uid'].']';
		
		$fields = $this->fObj->getMainFields($foreign_table,$rec);
		$header = $this->getSingleField_typeInline_renderForeignRecordHeader($foreign_table, $rec, $appendFormFieldNames);

		$tableAttribs='';
		$tableAttribs.= ' style="margin-right: 5px;' .
			# ($this->borderStyle[0] ? ' '.htmlspecialchars($this->borderStyle[0]) : '') .
			'"';
		$tableAttribs.= $this->fObj->borderStyle[2] ? ' background="'.htmlspecialchars($this->backPath.$this->borderStyle[2]).'"':'';
		# $tableAttribs.= $this->borderStyle[3] ? ' class="'.htmlspecialchars($this->borderStyle[3]).'"':'';
		if ($tableAttribs) $tableAttribs='border="0" cellspacing="0" cellpadding="0" width="100%"'.$tableAttribs;
		
		$fields = '<table '.$tableAttribs.'>'.$fields.'</table>';
		if ($isNewRecord) $fields .= '<input type="hidden" name="'.$this->fObj->prependFormFieldNames.$appendFormFieldNames.'[pid]" value="'.$rec['pid'].'"/>';

		$out .= '<div id="'.$this->fObj->prependFormFieldNames.$appendFormFieldNames.'_div">';
		$out .= '<div id="'.$this->fObj->prependFormFieldNames.$appendFormFieldNames.'_header">'.$header.'</div>';
		$out .= '<div id="'.$this->fObj->prependFormFieldNames.$appendFormFieldNames.'_fields">'.$fields.'</div>';
		$out .= '</div>';
				
		return $out;
	}
	
	function getSingleField_typeInline_renderForeignRecordHeader($foreign_table,$row,$appendFormFieldNames) {
		$recTitle = $this->fObj->noTitle(t3lib_BEfunc::getRecordTitle($foreign_table, $row));
		$altText = t3lib_BEfunc::getRecordIconAltText($row, $foreign_table);
		$iconImg = t3lib_iconWorks::getIconImage(
			$foreign_table, $row, $this->backPath,
			'title="'.htmlspecialchars($altText).'" class="absmiddle"'
		);
		
		$onClick = "return inline.expandCollapseRecord('".htmlspecialchars($this->fObj->prependFormFieldNames.$appendFormFieldNames)."')";
		$label .= '<a href="#" onclick="'.$onClick.'" style="display: block">';
		// $label .= '<img '.t3lib_iconWorks::skinImg($this->backPath, 'gfx/ol/plusbullet.gif').' align="absmiddle" /> ';
		$label .= $recTitle;
		$label .= '</a>';

			// from class.db_list_extra.inc
			// $theData[$fCol]=$this->makeControl($table,$row);
			// $theData[$fCol]=$this->makeClip($table,$row);
		
		$ctrl = $this->getSingleField_typeInline_renderForeignRecordHeaderControl($foreign_table,$row,$appendFormFieldNames);
		
			// FIXME: Use the correct css-classes to fit with future skins etc.
		$header =
			'<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-right: 5px;"'.
			($this->fObj->borderStyle[2] ? ' background="'.htmlspecialchars($this->backPath.$this->fObj->borderStyle[2]).'"':'').
			($this->fObj->borderStyle[3] ? ' class="'.htmlspecialchars($this->fObj->borderStyle[3]).'"':'').'>' .
			'<tr class="class-main12"><td width="18">'.$iconImg.'</td><td align="left"><b>'.$label.'</b></td><td align="right">'.$ctrl.'</td></tr></table>';
		
		return $header;
	}
	
	// copied and modified form from class.db_list_extra.inc
	function getSingleField_typeInline_renderForeignRecordHeaderControl($table,$row,$appendFormFieldNames) {
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
							$onClick = "return inline.createNewRecord('".$this->fObj->prependFormFieldNames.$appendFormFieldNames."')";
							$params='&edit['.$table.']['.(-$row['uid']).']=new';
							$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
									'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new'.($table=='pages'?'Page':'Record'),1).'" alt="" />'.
									'</a>';
						}
					}
				}

					// "Up/Down" links
				if ($permsEdit && $TCA[$table]['ctrl']['sortby']  && !$this->sortField && !$this->searchLevels)	{
					if (isset($this->currentTable['prev'][$row['uid']]))	{	// Up
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['prev'][$row['uid']];
						$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"').' title="'.$LANG->getLL('moveUp',1).'" alt="" />'.
								'</a>';
					} else {
						$cells[]='<img src="clear.gif" '.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"',2).' alt="" />';
					}
					if ($this->currentTable['next'][$row['uid']])	{	// Down
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['next'][$row['uid']];
						$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$LANG->getLL('moveDown',1).'" alt="" />'.
								'</a>';
					} else {
						$cells[]='<img src="clear.gif" '.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"',2).' alt="" />';
					}
				}

					// "Hide/Unhide" links:
				$hiddenField = $TCA[$table]['ctrl']['enablecolumns']['disabled'];
				if ($permsEdit && $hiddenField && $TCA[$table]['columns'][$hiddenField] && (!$TCA[$table]['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields',$table.':'.$hiddenField)))	{
					$onClick = "return inline.enableDisableRecord('".$this->fObj->prependFormFieldNames.$appendFormFieldNames."')";
					if ($row[$hiddenField])	{
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=0';
						$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHide'.($table=='pages'?'Page':''),1).'" alt="" id="'.$this->fObj->prependFormFieldNames.$appendFormFieldNames.'_disabled" />'.
								'</a>';
					} else {
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=1';
						$cells[]='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hide'.($table=='pages'?'Page':''),1).'" alt="" id="'.$this->fObj->prependFormFieldNames.$appendFormFieldNames.'_disabled" />'.
								'</a>';
					}
				}

					// "Delete" link:
				if (
					($table=='pages' && ($localCalcPerms&4)) || ($table!='pages' && ($calcPerms&16))
					)	{
					$params='&cmd['.$table.']['.$row['uid'].'][delete]=1';
					$cells[]='<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning').t3lib_BEfunc::referenceCount($table,$row['uid'],' (There are %s reference(s) to this record!)')).')) {jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');} return false;').'">'.
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
	
	function getSingleField_typeInline_addJavaScript() {
		$jsCode = array(
			'<script src="/'.t3lib_extMgm::siteRelPath('scriptaculous').'lib/prototype.js" type="text/javascript"></script>',
			'<script src="/'.t3lib_extMgm::siteRelPath('scriptaculous').'lib/prototype.js" type="text/javascript"></script>',
			'<script src="/t3lib/jsfunc.inlinerelational.js" type="text/javascript"></script>',
			$this->xajax->getJavascript('/'.t3lib_extMgm::siteRelPath('xajax'), 'xajax_js/xajax_uncompressed.js'),
		);

		
		return implode("\n", $jsCode);
	}

	/**
	 * Convert the DOM object-id of an inline container to an array.
	 * The object-id could look like 'data[inline][tx_mmftest_company][1][employees]'.
	 * The returned array could look like this:
	 * 
	 * >> The 'lastBranch' key is a reference to the last child child (deep) down in the structure.
	 * 
	 * Array(
	 * 	table => tx_firstlevel,
	 *  uid => 123,
	 *  field => somefield,
	 *  child => Array(
	 * 		table => tx_secondlevel,
	 * 		uid => 234,
	 * 		field => anotherfield,
	 * 		child => Array(...),
	 * 	),
	 * 	lastBranch => Array(
	 * 		table => tx_secondlevel,
	 * 		uid => 234,
	 * 		field => anotherfield,
	 * 		child => Array(...),
	 * 	)
	 * )
	 *
	 * @param	string	$domObjectId: The DOM object-id
	 * @return	array	The structure tree with the keys table, uid, field, child and lastBranch
	 */
	function getSingleField_typeInline_getStructureTree($domObjectId) {
		// data[inline][tx_mmftest_company][1][employees]
		
		$strucutreTree = array();
		$strucutreTree['lastBranch'] = null;
		$currentBranch =& $strucutreTree;

		$hasPrefix = 0;
		$vector = array('table', 'uid', 'field');

		$pattern = '/^'.$this->fObj->prependFormFieldNames.'\[(.+)\]$/';
		if (preg_match($pattern, $domObjectId, $match)) {
			$hops = explode('][', $match[1]);
			$hopsCount = count($hops);
			if ($hops[0] == 'inline') $hasPrefix = 1;

			foreach ($hops as $index => $hop) {
				if ($index > 0 && ($hasPrefix && $hop == 'inline' || !$hasPrefix && $index % 3 == 0 && $index+1 < $hopsCount)) {
					$currentBranch['child'] = array();
					$currentBranch =& $currentBranch['child'];
					$strucutreTree['lastBranch'] =& $currentBranch;
				}

					// itereate next if it's an 'inline' indicator
				if ($hasPrefix && $hop == 'inline')	continue;
					// add a field (table, uid or field) to the current branch
				$currentBranch[$vector[($index%(3+$hasPrefix))-$hasPrefix]] = $hop;
			}
		}
		
		return $strucutreTree;
	}
	
	function getSingleField_typeInline_processRequest() {
		$this->xajax->processRequests();
	}
}
?>