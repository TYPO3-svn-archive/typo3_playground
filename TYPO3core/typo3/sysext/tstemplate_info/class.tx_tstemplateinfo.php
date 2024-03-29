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
 * @author	Kasper Sk�rh�j <kasperYYYY@typo3.com>
 */

require_once(PATH_t3lib."class.t3lib_extobjbase.php");

class tx_tstemplateinfo extends t3lib_extobjbase {
	function modMenu()	{
		global $LANG;

		return Array (
			#"ts_template_editor_TArows" => ""
		);
	}
	function tableRow($label,$data,$field)	{
		$ret='<tr><td class="bgColor4" width="1%">';
		$ret.='<a href="index.php?id='.$this->pObj->id.'&e['.$field.']=1"><img '.t3lib_iconWorks::skinImg($GLOBALS["BACK_PATH"],'gfx/edit2.gif').' width=11 height=12 hspace=3 border=0 title="Edit field"></a>';
		$ret.='</td><td class="bgColor4" width="1%"><b>'.$label.'&nbsp;&nbsp;</b></td><td class="bgColor4" width="99%">'.$data.'&nbsp;</td></tr>';
		return $ret;
	}
	function procesResources($resources,$func=0)	{
		$arr = t3lib_div::trimExplode(",",$resources.",,",1);
		$out="";
		if ($func)	{$bgcol=' class="bgColor4"';} else {$bgcol="";}
		while(list($k,$v)=each($arr))	{
			$path = PATH_site.$GLOBALS["TCA"]["sys_template"]["columns"]["resources"]["config"]["uploadfolder"]."/".$v;
			$functions="";
			if ($func)	{
				$functions='<td bgcolor=red nowrap>Delete: <input type="Checkbox" name="data[remove_resource]['.$k.']" value="'.htmlspecialchars($v).'"></td>';
				$functions.='<td'.$bgcol.' nowrap>To top: <input type="Checkbox" name="data[totop_resource]['.$k.']" value="'.htmlspecialchars($v).'"></td>';
				$functions.='<td'.$bgcol.' nowrap>';
				$fI=t3lib_div::split_fileref($v);
				if (t3lib_div::inList($this->pObj->textExtensions,$fI["fileext"]))	{
					$functions.='<a href="index.php?id='.$this->pObj->id.'&e[file]='.rawurlencode($v).'"><img '.t3lib_iconWorks::skinImg($GLOBALS["BACK_PATH"],'gfx/edit2.gif').' width=11 height=12 hspace=3 border=0 title="Edit file"></a>';
				}
				$functions.='</td>';
			}
			$thumb=t3lib_BEfunc::thumbCode(array("resources"=>$v),"sys_template","resources",$GLOBALS["BACK_PATH"],"");
			$out.='<tr><td'.$bgcol.' nowrap>'.$v.'&nbsp;&nbsp;</td><td'.$bgcol.' nowrap>&nbsp;'.t3lib_div::formatSize(@filesize($path)).'&nbsp;</td>'.$functions.'<td'.$bgcol.'>'.trim($thumb).'</td></tr>';
		}
		if ($out)	{
			if ($func)	{
				$out = '<table border=0 cellpadding=1 cellspacing=1 width="100%">'.$out.'</table>';
				$out='<table border=0 cellpadding=0 cellspacing=0>
					<tr><td class="bgColor2">'.$out.'<img src=clear.gif width=465 height=1></td></tr>
				</table>';
			} else {
				$out = '<table border=0 cellpadding=0 cellspacing=0>'.$out.'</table>';
			}
		}
		return $out;
	}
	function resourceListForCopy($id,$template_uid)	{
		global $tmpl;
		$sys_page = t3lib_div::makeInstance("t3lib_pageSelect");
		$rootLine = $sys_page->getRootLine($id);
		$tmpl->runThroughTemplates($rootLine,$template_uid);	// This generates the constants/config + hierarchy info for the template.
		$theResources = t3lib_div::trimExplode(",",$tmpl->resources,1);
		reset($theResources);
		while(list($k,$v)=each($theResources))	{
			$fI=pathinfo($v);
			if (t3lib_div::inList($this->pObj->textExtensions,strtolower($fI["extension"])))	{
				$path = PATH_site.$GLOBALS["TCA"]["sys_template"]["columns"]["resources"]["config"]["uploadfolder"]."/".$v;
				$thumb=t3lib_BEfunc::thumbCode(array("resources"=>$v),"sys_template","resources",$GLOBALS["BACK_PATH"],"");
				$out.='<tr><td'.$bgcol.' nowrap>'.$v.'&nbsp;&nbsp;</td><td'.$bgcol.' nowrap>&nbsp;'.t3lib_div::formatSize(@filesize($path)).'&nbsp;</td><td'.$bgcol.'>'.trim($thumb).'</td><td><input type="Checkbox" name="data[makecopy_resource]['.$k.']" value="'.htmlspecialchars($v).'"></td></tr>';
			}
		}
		$out = $out ? '<table border=0 cellpadding=0 cellspacing=0>'.$out.'</table>' : "";
		return $out;
	}
	function initialize_editor($pageId,$template_uid=0)	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $tmpl,$tplRow,$theConstants;

		$tmpl = t3lib_div::makeInstance("t3lib_tsparser_ext");	// Defined global here!
		$tmpl->tt_track = 0;	// Do not log time-performance information
		$tmpl->init();

		$tplRow = $tmpl->ext_getFirstTemplate($pageId,$template_uid);	// Get the row of the first VISIBLE template of the page. whereclause like the frontend.
		if (is_array($tplRow))	{
			return 1;
		}
	}
	function main()	{
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		global $tmpl,$tplRow,$theConstants;

		$edit = $this->pObj->edit;
		$e = $this->pObj->e;

		t3lib_div::loadTCA("sys_template");

		// **************************
		// Create extension template
		// **************************
		$this->pObj->createTemplate($this->pObj->id);


		// **************************
		// Checking for more than one template an if, set a menu...
		// **************************
		$manyTemplatesMenu = $this->pObj->templateMenu();
		$template_uid = 0;
		if ($manyTemplatesMenu)	{
			$template_uid = $this->pObj->MOD_SETTINGS["templatesOnPage"];
		}





		// **************************
		// Initialize
		// **************************
		$existTemplate = $this->initialize_editor($this->pObj->id,$template_uid);		// initialize
		if ($existTemplate)	{
			$saveId = $tplRow['_ORIG_uid'] ? $tplRow['_ORIG_uid'] : $tplRow['uid'];

				// Update template ?
			$POST = t3lib_div::_POST();
			if ($POST["submit"])	{
				require_once (PATH_t3lib."class.t3lib_tcemain.php");
					// Set the data to be saved
				$recData=array();
				$alternativeFileName=array();
				$resList = $tplRow["resources"];

				$tmp_upload_name = '';
				$tmp_newresource_name = '';	// Set this to blank

				if (is_array($POST["data"]))	{
					while(list($field,$val)=each($POST["data"]))	{
						switch($field)	{
							case "constants":
							case "config":
							case "title":
							case "sitetitle":
							case "description":
								$recData["sys_template"][$saveId][$field] = $val;
							break;
							case "resources":
								$tmp_upload_name = t3lib_div::upload_to_tempfile($_FILES["resources"]["tmp_name"]);	// If there is an uploaded file, move it for the sake of safe_mode.
								if ($tmp_upload_name)	{
									if ($tmp_upload_name!="none" && $_FILES["resources"]["name"])	{
										$alternativeFileName[$tmp_upload_name] = trim($_FILES["resources"]["name"]);
										$resList = $tmp_upload_name.",".$resList;
									}
								}
							break;
							case "new_resource":
								$newName = trim(t3lib_div::_GP("new_resource"));
								if ($newName)	{
									$newName.=".".t3lib_div::_GP("new_resource_ext");
									$tmp_newresource_name = t3lib_div::tempnam("new_resource_");
									$alternativeFileName[$tmp_newresource_name] = $newName;
									$resList = $tmp_newresource_name.",".$resList;
								}
							break;
							case "makecopy_resource":
								if (is_array($val))	{
									reset($val);
									$resList=",".$resList.",";
									while(list($k,$file)=each($val))	{
										$tmp_name = PATH_site.$TCA["sys_template"]["columns"]["resources"]["config"]["uploadfolder"]."/".$file;
		//								debug($tmp_name);
										$resList = $tmp_name.",".$resList;
									}
								}
							break;
							case "remove_resource":
								if (is_array($val))	{
									reset($val);
									$resList=",".$resList.",";
									while(list($k,$file)=each($val))	{
										$resList = str_replace(",".$file.",", ",", $resList);
									}
								}
							break;
							case "totop_resource":
								if (is_array($val))	{
									reset($val);
									$resList=",".$resList.",";
									while(list($k,$file)=each($val))	{
										$resList = str_replace(",".$file.",", ",", $resList);
										$resList = ",".$file.$resList;
									}
								}
		//						debug($resList);
							break;
						}
					}
				}
				$resList=implode(t3lib_div::trimExplode(",",$resList,1),",");
				if (strcmp($resList,$tplRow["resources"]))	{
					$recData["sys_template"][$saveId]["resources"] = $resList;
		//			debug("update resource - ".$resList);
				}
				if (count($recData))	{
						// Create new  tce-object
					$tce = t3lib_div::makeInstance("t3lib_TCEmain");
					$tce->stripslashes_values=0;
					$tce->alternativeFileName = $alternativeFileName;
						// Initialize
		//				debug($recData);
					$tce->start($recData,Array());
						// Saved the stuff
					$tce->process_datamap();
						// Clear the cache (note: currently only admin-users can clear the cache in tce_main.php)
					$tce->clear_cacheCmd("all");

						// re-read the template ...
					$this->initialize_editor($this->pObj->id,$template_uid);
				}

					// Unlink any uploaded/new temp files there was:
				t3lib_div::unlink_tempfile($tmp_upload_name);
				t3lib_div::unlink_tempfile($tmp_newresource_name);

					// If files has been edited:
				if (is_array($edit))		{
					if ($edit["filename"] && $tplRow["resources"] && t3lib_div::inList($tplRow["resources"], $edit["filename"]))	{		// Check if there are resources, and that the file is in the resourcelist.
						$path = PATH_site.$TCA["sys_template"]["columns"]["resources"]["config"]["uploadfolder"]."/".$edit["filename"];
						$fI=t3lib_div::split_fileref($edit["filename"]);
						if (@is_file($path) && t3lib_div::getFileAbsFileName($path) && t3lib_div::inList($this->pObj->textExtensions,$fI["fileext"]))	{		// checks that have already been done.. Just to make sure
							if (filesize($path)<(30*1024))	{	// checks that have already been done.. Just to make sure
								t3lib_div::writeFile($path,$edit['file']);

								$theOutput.=$this->pObj->doc->spacer(10);
								$theOutput.=$this->pObj->doc->section("<font color=red>FILE CHANGED</font>","Resource '".$edit["filename"]."' has been updated.",0,0,0,1);

									// Clear cache - the file has probably affected the template setup
								$tce = t3lib_div::makeInstance("t3lib_TCEmain");
								$tce->stripslashes_values=0;
								$tce->start(Array(),Array());
								$tce->clear_cacheCmd("all");
							}
						}
					}
				}
			}

			$theOutput.=$this->pObj->doc->spacer(5);
			$theOutput.=$this->pObj->doc->section("Template information:",'<img src="'.$BACK_PATH.t3lib_iconWorks::getIcon("sys_template",$tplRow).'" width=18 height=16 align=top><b>'.htmlspecialchars($tplRow["title"]).'</b>'.htmlspecialchars(trim($tplRow["sitetitle"])?' - ('.$tplRow["sitetitle"].')':''),0,1);
			if ($manyTemplatesMenu)	{
				$theOutput.=$this->pObj->doc->section("",$manyTemplatesMenu);
				$theOutput.=$this->pObj->doc->divider(5);
			}

		/*	$e[constants]=1;
			$e[config]=1;
			$e[title]=1;
			$e[sitetitle]=1;
			*/
			#$numberOfRows= t3lib_div::intInRange($this->pObj->MOD_SETTINGS["ts_template_editor_TArows"],0,150);
			#if (!$numberOfRows)
			$numberOfRows = 35;

			if (t3lib_div::_POST('abort'))		{unset($e);}	// If abort pressed, nothing should be edited.

			if (is_array($e))	{
				$theOutput.=$this->pObj->doc->section("",'<BR><input type="Submit" name="submit" value="Update"> <input type="Submit" name="abort" value="Cancel">');
			}
			if ($e["title"])	{
				$outCode='<input type="Text" name="data[title]" value="'.htmlspecialchars($tplRow[title]).'"'.$this->pObj->doc->formWidth().'>';
				$outCode.='<input type="Hidden" name="e[title]" value="1">';
				$theOutput.=$this->pObj->doc->spacer(15);
				$theOutput.=$this->pObj->doc->section("Title:",$outCode);
			}
			if ($e["sitetitle"])	{
				$outCode='<input type="Text" name="data[sitetitle]" value="'.htmlspecialchars($tplRow[sitetitle]).'"'.$this->pObj->doc->formWidth().'>';
				$outCode.='<input type="Hidden" name="e[sitetitle]" value="1">';
				$theOutput.=$this->pObj->doc->spacer(15);
				$theOutput.=$this->pObj->doc->section("Sitetitle:",$outCode);
			}
			if ($e["description"])	{
				$outCode='<textarea name="data[description]" rows="5" class="fixed-font enable-tab"'.$this->pObj->doc->formWidthText(48,"","").'>'.t3lib_div::formatForTextarea($tplRow[description]).'</textarea>';
				$outCode.='<input type="Hidden" name="e[description]" value="1">';
				$theOutput.=$this->pObj->doc->spacer(15);
				$theOutput.=$this->pObj->doc->section("Description:",$outCode);
			}
			if ($e["resources"])	{
					// Upload
				$outCode='<input type="File" name="resources"'.$this->pObj->doc->formWidth().' size="50">';
				$outCode.='<input type="Hidden" name="data[resources]" value="1">';
				$outCode.='<input type="Hidden" name="e[resources]" value="1">';
				$outCode.='<BR>Allowed extensions: <b>'.$TCA["sys_template"]["columns"]["resources"]["config"]["allowed"].'</b>';
				$outCode.='<BR>Max file size: <b>'.t3lib_div::formatSize($TCA["sys_template"]["columns"]["resources"]["config"]["max_size"]*1024).'</b>';
				$theOutput.=$this->pObj->doc->spacer(15);
				$theOutput.=$this->pObj->doc->section("Upload resource:",$outCode);

					// New
				$opt=explode(",",$this->pObj->textExtensions);
				$optTags="";
				while(list(,$extVal)=each($opt))	{
					$optTags.='<option value="'.$extVal.'">.'.$extVal.'</option>';
				}
				$outCode='<input type="text" name="new_resource"'.$this->pObj->doc->formWidth(20).'>
					<select name="new_resource_ext">'.$optTags.'</select>';
				$outCode.='<input type="Hidden" name="data[new_resource]" value="1">';
				$theOutput.=$this->pObj->doc->spacer(15);
				$theOutput.=$this->pObj->doc->section("New text resource (enter name):",$outCode);

					// Make copy
				$rL = $this->resourceListForCopy($this->pObj->id,$template_uid);
				if ($rL)	{
					$theOutput.=$this->pObj->doc->spacer(20);
					$theOutput.=$this->pObj->doc->section("Make a copy of resource:",$rL);
				}

					// Update resource list
				$rL = $this->procesResources($tplRow["resources"],1);
				if ($rL)	{
					$theOutput.=$this->pObj->doc->spacer(20);
					$theOutput.=$this->pObj->doc->section("Update resource list:",$rL);
				}
			}
			if ($e["constants"])	{
				$outCode='<textarea name="data[constants]" rows="'.$numberOfRows.'" wrap="off" class="fixed-font enable-tab"'.$this->pObj->doc->formWidthText(48,"width:98%;height:70%","off").' class="fixed-font">'.t3lib_div::formatForTextarea($tplRow["constants"]).'</textarea>';
				$outCode.='<input type="Hidden" name="e[constants]" value="1">';
				$theOutput.=$this->pObj->doc->spacer(15);
				$theOutput.=$this->pObj->doc->section("Constants:","");
				$theOutput.=$this->pObj->doc->sectionEnd().$outCode;
			}
			if ($e["file"])	{
				$path = PATH_site.$TCA["sys_template"]["columns"]["resources"]["config"]["uploadfolder"]."/".$e[file];

				$fI=t3lib_div::split_fileref($e[file]);
				if (@is_file($path) && t3lib_div::inList($this->pObj->textExtensions,$fI["fileext"]))	{
					if (filesize($path)<($TCA['sys_template']['columns']['resources']['config']['max_size']*1024))	{
						$fileContent = t3lib_div::getUrl($path);
						$outCode='File: <b>'.$e[file].'</b><BR>';
						$outCode.='<textarea name="edit[file]" rows="'.$numberOfRows.'" wrap="off" class="fixed-font enable-tab"'.$this->pObj->doc->formWidthText(48,"width:98%;height:70%","off").' class="fixed-font">'.t3lib_div::formatForTextarea($fileContent).'</textarea>';
						$outCode.='<input type="Hidden" name="edit[filename]" value="'.$e[file].'">';
						$outCode.='<input type="Hidden" name="e[file]" value="'.htmlspecialchars($e[file]).'">';
						$theOutput.=$this->pObj->doc->spacer(15);
						$theOutput.=$this->pObj->doc->section("Edit Resource:","");
						$theOutput.=$this->pObj->doc->sectionEnd().$outCode;
					} else {
						$theOutput.=$this->pObj->doc->spacer(15);
						$theOutput.=$this->pObj->doc->section('<font color=red>Filesize exceeded '.$TCA['sys_template']['columns']['resources']['config']['max_size'].' Kbytes</font>','Files larger than '.$TCA['sys_template']['columns']['resources']['config']['max_size'].' KByes are not allowed to be edited.',0,0,0,1);
					}
				}
			}
			if ($e["config"])	{
				$outCode='<textarea name="data[config]" rows="'.$numberOfRows.'" wrap="off" class="fixed-font enable-tab"'.$this->pObj->doc->formWidthText(48,"width:98%;height:70%","off").' class="fixed-font">'.t3lib_div::formatForTextarea($tplRow["config"]).'</textarea>';

				if (t3lib_extMgm::isLoaded("tsconfig_help"))	{
					$url=$BACK_PATH."wizard_tsconfig.php?mode=tsref";
					$params=array();
					$params["formName"]="editForm";
					$params["itemName"]="data[config]";
					$outCode.='<a href="#" onClick="vHWin=window.open(\''.$url.t3lib_div::implodeArrayForUrl("",array("P"=>$params)).'\',\'popUp'.$md5ID.'\',\'height=500,width=780,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;"><img '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/wizard_tsconfig.gif').' width="22" height="27" border="0" title="TSref reference"></a>';
				}

				$outCode.='<input type="Hidden" name="e[config]" value="1">';
				$theOutput.=$this->pObj->doc->spacer(15);
				$theOutput.=$this->pObj->doc->section("Setup:","");
				$theOutput.=$this->pObj->doc->sectionEnd().$outCode;
			}
			if (is_array($e))	{
				$theOutput.=$this->pObj->doc->section("",'<BR><input type="Submit" name="submit" value="Update"> <input type="Submit" name="abort" value="Cancel">');
			}

					// Processing:
		//	debug($tplRow);
			$outCode="";
			$outCode.=$this->tableRow("Title:",htmlspecialchars($tplRow["title"]),"title");
			$outCode.=$this->tableRow("Sitetitle:",htmlspecialchars($tplRow["sitetitle"]),"sitetitle");
			$outCode.=$this->tableRow("Description:",nl2br(htmlspecialchars($tplRow["description"])),"description");
			$outCode.=$this->tableRow("Resources:",$this->procesResources($tplRow["resources"]),"resources");
			$outCode.=$this->tableRow("Constants:","(edit to view, ".(trim($tplRow[constants]) ? count(explode(chr(10), $tplRow[constants])):0)." lines)","constants");
			$outCode.=$this->tableRow("Setup:","(edit to view, ".(trim($tplRow[config]) ? count(explode(chr(10), $tplRow[config])):0)." lines)","config");
			$outCode='<table border=0 cellpadding=1 cellspacing=1 width="100%">'.$outCode.'</table>';

			$outCode='<table border=0 cellpadding=0 cellspacing=0>
				<tr><td class="bgColor2">'.$outCode.'<img src=clear.gif width=465 height=1></td></tr>
			</table>';

				// Edit all icon:
			$outCode.='<BR><a href="#" onClick="'.t3lib_BEfunc::editOnClick(rawurlencode('&createExtension=0').'&edit[sys_template]['.$tplRow['uid'].']=edit',$BACK_PATH,'').'"><strong>Click here to edit whole template record</strong></a>';

			$theOutput.=$this->pObj->doc->spacer(25);
			$theOutput.=$this->pObj->doc->section("",$outCode);

			$theOutput.=$this->pObj->doc->spacer(10);
			$theOutput.=$this->pObj->doc->section("Cache",'Click here to <a href="index.php?id='.$this->pObj->id.'&clear_all_cache=1"><strong>clear all cache</strong></a>.<BR><br>
			Status: '.$this->pObj->getCountCacheTables(1),0,1);

		//	$theOutput.=$this->pObj->doc->divider(5);
#			$menu = htmlspecialchars("Rows in <TEXTAREA> fields: ").t3lib_BEfunc::getFuncInput($this->pObj->id,"SET[ts_template_editor_TArows]",$this->pObj->MOD_SETTINGS["ts_template_editor_TArows"]);
#			$theOutput.=$this->pObj->doc->section("CONFIG:",$menu,0,1);
		} else {
			$theOutput.=$this->pObj->noTemplate(1);
		}
		return $theOutput;
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tstemplate_info/class.tx_tstemplateinfo.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tstemplate_info/class.tx_tstemplateinfo.php"]);
}

?>
