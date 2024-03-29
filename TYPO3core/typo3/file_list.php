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
 * Web>File: File listing
 *
 * $Id$
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   77: class SC_file_list
 *  103:     function init()
 *  130:     function menuConfig()
 *  151:     function main()
 *  325:     function printContent()
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


unset($MCONF);
require ('mod/file/list/conf.php');
require ('init.php');
require ('template.php');
$LANG->includeLLFile('EXT:lang/locallang_mod_file_list.xml');
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once (PATH_t3lib.'class.t3lib_extfilefunc.php');
require_once (PATH_t3lib.'class.t3lib_recordlist.php');
require_once (PATH_t3lib.'class.t3lib_clipboard.php');
require_once ('class.file_list.inc');
$BE_USER->modAccess($MCONF,1);







/**
 * Script Class for creating the list of files in the File > Filelist module
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_file_list {
	var $MCONF=array();			// Module configuration
	var $MOD_MENU=array();
	var $MOD_SETTINGS=array();


		// Internal:
	var $content;	// Accumulated HTML output
	var $basicFF;	// File operation object (t3lib_basicFileFunctions)
	var $doc;		// Template object

		// Internal, static: GPvars:
	var $id;		// "id" -> the path to list.
	var $pointer;	// Pointer to listing
	var $table;		// "Table"
	var $imagemode;	// Thumbnail mode.
	var $cmd;
	var $overwriteExistingFiles;


	/**
	 * Initialize variables, file object
	 * Incoming GET vars include id, pointer, table, imagemode
	 *
	 * @return	void
	 */
	function init()	{
		global $TYPO3_CONF_VARS,$FILEMOUNTS;

			// Setting GPvars:
		$this->id = t3lib_div::_GP('id');
		$this->pointer = t3lib_div::_GP('pointer');
		$this->table = t3lib_div::_GP('table');
		$this->imagemode = t3lib_div::_GP('imagemode');
		$this->cmd = t3lib_div::_GP('cmd');
		$this->overwriteExistingFiles = t3lib_div::_GP('overwriteExistingFiles');

			// Setting module name:
		$this->MCONF = $GLOBALS['MCONF'];

			// File operation object:
		$this->basicFF = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->basicFF->init($FILEMOUNTS,$TYPO3_CONF_VARS['BE']['fileExtensions']);

			// Configure the "menu" - which is used internally to save the values of sorting, displayThumbs etc.
		$this->menuConfig();
	}

	/**
	 * Setting the menu/session variables
	 *
	 * @return	void
	 */
	function menuConfig()	{
			// MENU-ITEMS:
			// If array, then it's a selector box menu
			// If empty string it's just a variable, that'll be saved.
			// Values NOT in this array will not be saved in the settings-array for the module.
		$this->MOD_MENU = array(
			'sort' => '',
			'reverse' => '',
			'displayThumbs' => '',
			'clipBoard' => ''
		);

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
	}

	/**
	 * Main function, creating the listing
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$FILEMOUNTS;

			// Initialize the template object
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->docType = 'xhtml_trans';

			// Validating the input "id" (the path, directory!) and checking it against the mounts of the user.
		$this->id = $this->basicFF->is_directory($this->id);
		$access = $this->id && $this->basicFF->checkPathAgainstMounts($this->id.'/');

			// There there was access to this file path, continue, make the list
		if ($access)	{

				// Create filelisting object
			$filelist = t3lib_div::makeInstance('fileList');
			$filelist->backPath = $BACK_PATH;
			$filelist->thumbs = $this->MOD_SETTINGS['displayThumbs']?1:$BE_USER->uc['thumbnailsByDefault'];

				// Create clipboard object and initialize that
			$filelist->clipObj = t3lib_div::makeInstance('t3lib_clipboard');
			$filelist->clipObj->fileMode=1;
			$filelist->clipObj->initializeClipboard();

			$CB = t3lib_div::_GET('CB');
			if ($this->cmd=='setCB') $CB['el'] = $filelist->clipObj->cleanUpCBC(array_merge(t3lib_div::_POST('CBH'),t3lib_div::_POST('CBC')),'_FILE');
			if (!$this->MOD_SETTINGS['clipBoard'])	$CB['setP']='normal';
			$filelist->clipObj->setCmd($CB);
			$filelist->clipObj->cleanCurrent();
			$filelist->clipObj->endClipboard();	// Saves

				// If the "cmd" was to delete files from the list (clipboard thing), do that:
			if ($this->cmd=='delete')	{
				$items = $filelist->clipObj->cleanUpCBC(t3lib_div::_POST('CBC'),'_FILE',1);
				if (count($items))	{
						// Make command array:
					$FILE=array();
					reset($items);
					while(list(,$v)=each($items))	{
						$FILE['delete'][]=array('data'=>$v);
					}

						// Init file processing object for deleting and pass the cmd array.
					$fileProcessor = t3lib_div::makeInstance('t3lib_extFileFunctions');
					$fileProcessor->init($FILEMOUNTS, $TYPO3_CONF_VARS['BE']['fileExtensions']);
					$fileProcessor->init_actionPerms($BE_USER->user['fileoper_perms']);
					$fileProcessor->dontCheckForUnique = $this->overwriteExistingFiles ? 1 : 0;
					$fileProcessor->start($FILE);
					$fileProcessor->processData();

					$fileProcessor->printLogErrorMessages();
				}
			}

			if (!isset($this->MOD_SETTINGS['sort']))	{
					// Set default sorting
				$this->MOD_SETTINGS['sort'] = 'file';
				$this->MOD_SETTINGS['reverse'] = 0;
			}

				// Start up filelisting object, include settings.
			$this->pointer = t3lib_div::intInRange($this->pointer,0,100000);
			$filelist->start($this->id,$this->pointer,$this->MOD_SETTINGS['sort'],$this->MOD_SETTINGS['reverse'],$this->MOD_SETTINGS['clipBoard']);

				// Write the header
			$filelist->writeTop($this->id);

				// Generate the list
			$filelist->generateList();

				// Write the footer
			$filelist->writeBottom();

				// Set top JavaScript:
			$this->doc->JScode=$this->doc->wrapScriptTags('

			if (top.fsMod) top.fsMod.recentIds["file"] = unescape("'.rawurlencode($this->id).'");
			function jumpToUrl(URL)	{	//
				window.location.href = URL;
			}

			'.$filelist->CBfunctions()	// ... and add clipboard JavaScript functions
			);

				// This will return content necessary for the context sensitive clickmenus to work: bodytag events, JavaScript functions and DIV-layers.
			$CMparts=$this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode.=$CMparts[0];
			$this->doc->postCode.= $CMparts[2];


				// Create output
			$this->content='';
			$this->content.=$this->doc->startPage($LANG->getLL('files'));
			$this->content.= '<form action="'.htmlspecialchars($filelist->listURL()).'" method="post" name="dblistForm">';
			$this->content.= $filelist->HTMLcode;
			$this->content.= '<input type="hidden" name="cmd" /></form>';

				// FileList Module CSH:
			$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'filelist_module', $GLOBALS['BACK_PATH'],'<br/>|');

			$this->content.='
				<!--
					"Upload" and "New" buttons
				-->
				<div id="typo3-filelist-buttons">
					<table border="0" cellpadding="4" cellspacing="0">
						<tr>
							<td>
								<form name="upload" action="'.$BACK_PATH.'file_upload.php">
									<input type="hidden" name="target" value="'.htmlspecialchars($this->id).'" />
									<input type="hidden" name="returnUrl" value="'.htmlspecialchars($filelist->listURL()).'" />
									<input type="submit" value="'.$GLOBALS['LANG']->makeEntities($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:cm.upload',1)).'" />
								</form>
							</td>
							<td>
								<form name="new" action="'.$BACK_PATH.'file_newfolder.php">
									<input type="hidden" name="target" value="'.htmlspecialchars($this->id).'" />
									<input type="hidden" name="returnUrl" value="'.htmlspecialchars($filelist->listURL()).'" />
									<input type="submit" value="'.$GLOBALS['LANG']->makeEntities($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:cm.new',1)).'" />
								</form>
							</td>
						</tr>
					</table>
				</div>
			';

			if ($filelist->HTMLcode)	{	// Making listing options:

				$this->content.='

					<!--
						Listing options for clipboard and thumbnails
					-->
					<div id="typo3-listOptions">
				';

					// Add "display thumbnails" checkbox:
				$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[displayThumbs]',$this->MOD_SETTINGS['displayThumbs'],'file_list.php','').' '.$LANG->getLL('displayThumbs',1).'<br />';

					// Add clipboard button
				$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[clipBoard]',$this->MOD_SETTINGS['clipBoard'],'file_list.php','').' '.$LANG->getLL('clipBoard',1);

				$this->content.='
					</div>
				';
				$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'filelist_options', $GLOBALS['BACK_PATH']);


					// Set clipboard:
				if ($this->MOD_SETTINGS['clipBoard'])	{
					$this->content.=$filelist->clipObj->printClipboard();
					$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'filelist_clipboard', $GLOBALS['BACK_PATH']);
				}
			}

				// Add shortcut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.='<br /><br />'.$this->doc->makeShortcutIcon('pointer,id,target,table',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']);
			}
		} else {
				// Create output - no access (no warning though)
			$this->content='';
			$this->content.=$this->doc->startPage($LANG->getLL('files'));
		}

	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/file_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/file_list.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_file_list');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();


if ($TYPO3_CONF_VARS['BE']['compressionLevel'])	{
	new gzip_encode($TYPO3_CONF_VARS['BE']['compressionLevel']);
}
?>