<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2006 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Contains the reknown class "t3lib_div" with general purpose functions
 *
 * $Id$
 * Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
 * XHTML compliant
 * Usage counts are based on search 22/2 2003 through whole source including tslib/
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  232: class t3lib_div
 *
 *              SECTION: GET/POST Variables
 *  262:     function _GP($var)
 *  280:     function _GET($var=NULL)
 *  297:     function _POST($var=NULL)
 *  313:     function _GETset($inputGet,$key='')
 *  336:     function GPvar($var,$strip=0)
 *  353:     function GParrayMerged($var)
 *
 *              SECTION: IMAGE FUNCTIONS
 *  397:     function gif_compress($theFile, $type)
 *  425:     function png_to_gif_by_imagemagick($theFile)
 *  450:     function read_png_gif($theFile,$output_png=0)
 *
 *              SECTION: STRING FUNCTIONS
 *  499:     function fixed_lgd($string,$origChars,$preStr='...')
 *  524:     function fixed_lgd_pre($string,$chars)
 *  538:     function fixed_lgd_cs($string,$chars)
 *  555:     function breakTextForEmail($str,$implChar="\n",$charWidth=76)
 *  574:     function breakLinesForEmail($str,$implChar="\n",$charWidth=76)
 *  610:     function cmpIP($baseIP, $list)
 *  626:     function cmpIPv4($baseIP, $list)
 *  668:     function cmpIPv6($baseIP, $list)
 *  711:     function IPv6Hex2Bin ($hex)
 *  726:     function normalizeIPv6($address)
 *  782:     function validIPv6($ip)
 *  805:     function cmpFQDN($baseIP, $list)
 *  835:     function inList($list,$item)
 *  847:     function rmFromList($element,$list)
 *  863:     function expandList($list)
 *  894:     function intInRange($theInt,$min,$max=2000000000,$zeroValue=0)
 *  910:     function intval_positive($theInt)
 *  923:     function int_from_ver($verNumberStr)
 *  934:     function compat_version($verNumberStr)
 *  952:     function md5int($str)
 *  965:     function shortMD5($input, $len=10)
 *  978:     function uniqueList($in_list, $secondParameter=NULL)
 *  992:     function split_fileref($fileref)
 * 1030:     function dirname($path)
 * 1046:     function modifyHTMLColor($color,$R,$G,$B)
 * 1066:     function modifyHTMLColorAll($color,$all)
 * 1077:     function rm_endcomma($string)
 * 1090:     function danish_strtoupper($string)
 * 1105:     function convUmlauts($str)
 * 1118:     function testInt($var)
 * 1130:     function isFirstPartOfStr($str,$partStr)
 * 1146:     function formatSize($sizeInBytes,$labels='')
 * 1181:     function convertMicrotime($microtime)
 * 1195:     function splitCalc($string,$operators)
 * 1217:     function calcPriority($string)
 * 1258:     function calcParenthesis($string)
 * 1284:     function htmlspecialchars_decode($value)
 * 1299:     function deHSCentities($str)
 * 1312:     function slashJS($string,$extended=0,$char="'")
 * 1325:     function rawUrlEncodeJS($str)
 * 1337:     function rawUrlEncodeFP($str)
 * 1348:     function validEmail($email)
 * 1363:     function formatForTextarea($content)
 *
 *              SECTION: ARRAY FUNCTIONS
 * 1394:     function inArray($in_array,$item)
 * 1411:     function intExplode($delim, $string)
 * 1430:     function revExplode($delim, $string, $count=0)
 * 1450:     function trimExplode($delim, $string, $onlyNonEmptyValues=0)
 * 1472:     function uniqueArray($valueArray)
 * 1484:     function removeArrayEntryByValue($array,$cmpValue)
 * 1513:     function implodeArrayForUrl($name,$theArray,$str='',$skipBlank=0,$rawurlencodeParamName=0)
 * 1538:     function explodeUrl2Array($string,$multidim=FALSE)
 * 1564:     function compileSelectedGetVarsFromArray($varList,$getArray,$GPvarAlt=1)
 * 1587:     function addSlashesOnArray(&$theArray)
 * 1611:     function stripSlashesOnArray(&$theArray)
 * 1633:     function slashArray($arr,$cmd)
 * 1650:     function array_merge_recursive_overrule($arr0,$arr1,$notAddKeys=0,$includeEmtpyValues=true)
 * 1683:     function array_merge($arr1,$arr2)
 * 1696:     function csvValues($row,$delim=',',$quote='"')
 *
 *              SECTION: HTML/XML PROCESSING
 * 1738:     function get_tag_attributes($tag)
 * 1775:     function split_tag_attributes($tag)
 * 1809:     function implodeAttributes($arr,$xhtmlSafe=FALSE,$dontOmitBlankAttribs=FALSE)
 * 1836:     function implodeParams($arr,$xhtmlSafe=FALSE,$dontOmitBlankAttribs=FALSE)
 * 1851:     function wrapJS($string, $linebreak=TRUE)
 * 1882:     function xml2tree($string,$depth=999)
 * 1969:     function array2xml($array,$NSprefix='',$level=0,$docTag='phparray',$spaceInd=0, $options=array(),$stackData=array())
 * 2088:     function xml2array($string,$NSprefix='',$reportDocTag=FALSE)
 * 2198:     function xmlRecompileFromStructValArray($vals)
 * 2242:     function xmlGetHeaderAttribs($xmlData)
 *
 *              SECTION: FILES FUNCTIONS
 * 2275:     function getURL($url, $includeHeader=0)
 * 2342:     function writeFile($file,$content)
 * 2367:     function fixPermissions($file)
 * 2384:     function writeFileToTypo3tempDir($filepath,$content)
 * 2427:     function mkdir($theNewFolder)
 * 2446:     function mkdir_deep($destination,$deepDir)
 * 2468:     function get_dirs($path)
 * 2493:     function getFilesInDir($path,$extensionList='',$prependPath=0,$order='')
 * 2547:     function getAllFilesAndFoldersInPath($fileArr,$path,$extList='',$regDirs=0,$recursivityLevels=99)
 * 2570:     function removePrefixPathFromList($fileArr,$prefixToRemove)
 * 2586:     function fixWindowsFilePath($theFile)
 * 2598:     function resolveBackPath($pathStr)
 * 2626:     function locationHeaderUrl($path)
 *
 *              SECTION: DEBUG helper FUNCTIONS
 * 2666:     function debug_ordvalue($string,$characters=100)
 * 2683:     function view_array($array_in)
 * 2711:     function print_array($array_in)
 * 2726:     function debug($var="",$brOrHeader=0)
 * 2757:     function debug_trail()
 * 2779:     function debugRows($rows,$header='')
 *
 *              SECTION: SYSTEM INFORMATION
 * 2857:     function getThisUrl()
 * 2873:     function linkThisScript($getParams=array())
 * 2897:     function linkThisUrl($url,$getParams=array())
 * 2920:     function getIndpEnv($getEnvName)
 * 3113:     function milliseconds()
 * 3125:     function clientInfo($useragent='')
 *
 *              SECTION: TYPO3 SPECIFIC FUNCTIONS
 * 3212:     function getFileAbsFileName($filename,$onlyRelative=1,$relToTYPO3_mainDir=0)
 * 3248:     function validPathStr($theFile)
 * 3259:     function isAbsPath($path)
 * 3270:     function isAllowedAbsPath($path)
 * 3287:     function verifyFilenameAgainstDenyPattern($filename)
 * 3305:     function upload_copy_move($source,$destination)
 * 3331:     function upload_to_tempfile($uploadedFileName)
 * 3349:     function unlink_tempfile($uploadedTempFileName)
 * 3365:     function tempnam($filePrefix)
 * 3379:     function stdAuthCode($uid_or_record,$fields='',$codeLength=8)
 * 3410:     function cHashParams($addQueryParams)
 * 3433:     function hideIfNotTranslated($l18n_cfg_fieldValue)
 * 3448:     function readLLfile($fileRef,$langKey)
 * 3472:     function readLLXMLfile($fileRef,$langKey)
 * 3589:     function llXmlAutoFileName($fileRef,$language)
 * 3633:     function loadTCA($table)
 * 3653:     function resolveSheetDefInDS($dataStructArray,$sheet='sDEF')
 * 3686:     function resolveAllSheetsInDS($dataStructArray)
 * 3715:     function callUserFunction($funcName,&$params,&$ref,$checkPrefix='user_',$silent=0)
 * 3813:     function &getUserObj($classRef,$checkPrefix='user_',$silent=0)
 * 3871:     function &makeInstance($className)
 * 3883:     function makeInstanceClassName($className)
 * 3897:     function &makeInstanceService($serviceType, $serviceSubType='', $excludeServiceKeys=array())
 * 3961:     function plainMailEncoded($email,$subject,$message,$headers='',$enc='',$charset='',$dontEncodeHeader=false)
 * 4031:     function quoted_printable($string,$maxlen=76)
 * 4078:     function encodeHeader($line,$enc='',$charset='ISO-8859-1')
 * 4121:     function substUrlsInPlainText($message,$urlmode='76',$index_script_url='')
 * 4155:     function makeRedirectUrl($inUrl,$l=0,$index_script_url='')
 * 4182:     function freetypeDpiComp($font_size)
 * 4194:     function initSysLog()
 * 4251:     function sysLog($msg, $extKey, $severity=0)
 * 4334:     function devLog($msg, $extKey, $severity=0, $dataVar=FALSE)
 * 4355:     function arrayToLogString($arr, $valueList=array(), $valueLength=20)
 * 4378:     function imageMagickCommand($command, $parameters, $path='')
 * 4425:     function unQuoteFilenames($parameters,$unQuote=FALSE)
 * 4459:     function quoteJSvalue($value, $inScriptTags = false)
 *
 * TOTAL FUNCTIONS: 138
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */












/**
 * The legendary "t3lib_div" class - Miscellaneous functions for general purpose.
 * Most of the functions does not relate specifically to TYPO3
 * However a section of functions requires certain TYPO3 features available
 * See comments in the source.
 * You are encouraged to use this library in your own scripts!
 *
 * USE:
 * The class is intended to be used without creating an instance of it.
 * So: Don't instantiate - call functions with "t3lib_div::" prefixed the function name.
 * So use t3lib_div::[method-name] to refer to the functions, eg. 't3lib_div::milliseconds()'
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_div {





	/*************************
	 *
	 * GET/POST Variables
	 *
	 * Background:
	 * Input GET/POST variables in PHP may have their quotes escaped with "\" or not depending on configuration.
	 * TYPO3 has always converted quotes to BE escaped if the configuration told that they would not be so.
	 * But the clean solution is that quotes are never escaped and that is what the functions below offers.
	 * Eventually TYPO3 should provide this in the global space as well.
	 * In the transitional phase (or forever..?) we need to encourage EVERY to read and write GET/POST vars through the API functions below.
	 *
	 *************************/

	/**
	 * Returns the 'GLOBAL' value of incoming data from POST or GET, with priority to POST (that is equalent to 'GP' order)
	 * Strips slashes from all output, both strings and arrays.
	 * This function substitutes t3lib_div::GPvar()
	 * To enhancement security in your scripts, please consider using t3lib_div::_GET or t3lib_div::_POST if you already know by which method your data is arriving to the scripts!
	 * Usage: 537
	 *
	 * @param	string		GET/POST var to return
	 * @return	mixed		POST var named $var and if not set, the GET var of the same name.
	 * @see GPvar()
	 */
	function _GP($var)	{
		if(empty($var)) return;
		$value = isset($_POST[$var]) ? $_POST[$var] : $_GET[$var];
		if (isset($value))	{
			if (is_array($value))	{ t3lib_div::stripSlashesOnArray($value); } else { $value = stripslashes($value); }
		}
		return $value;
	}

	/**
	 * Returns the global GET array (or value from) normalized to contain un-escaped values.
	 * ALWAYS use this API function to acquire the GET variables!
	 * Usage: 27
	 *
	 * @param	string		Optional pointer to value in GET array (basically name of GET var)
	 * @return	mixed		If $var is set it returns the value of $_GET[$var]. If $var is NULL (default), returns $_GET itself. In any case *slashes are stipped from the output!*
	 * @see _POST(), _GP(), _GETset()
	 */
	function _GET($var=NULL)	{
		$value = ($var === NULL) ? $_GET : (empty($var) ? NULL : $_GET[$var]);
		if (isset($value))	{	// Removes slashes since TYPO3 has added them regardless of magic_quotes setting.
			if (is_array($value))	{ t3lib_div::stripSlashesOnArray($value); } else { $value = stripslashes($value); }
		}
		return $value;
	}

	/**
	 * Returns the global POST array (or value from) normalized to contain un-escaped values.
	 * ALWAYS use this API function to acquire the POST variables!
	 * Usage: 41
	 *
	 * @param	string		Optional pointer to value in POST array (basically name of POST var)
	 * @return	mixed		If $var is set it returns the value of $_POST[$var]. If $var is NULL (default), returns $_POST itself. In any case *slashes are stipped from the output!*
	 * @see _GET(), _GP()
	 */
	function _POST($var=NULL)	{
		$value = ($var === NULL) ? $_POST : (empty($var) ? NULL : $_POST[$var]);
		if (isset($value))	{	// Removes slashes since TYPO3 has added them regardless of magic_quotes setting.
			if (is_array($value))	{ t3lib_div::stripSlashesOnArray($value); } else { $value = stripslashes($value); }
		}
		return $value;
	}

	/**
	 * Writes input value to $_GET
	 * Usage: 2
	 *
	 * @param	array		Array to write to $_GET. Values should NOT be escaped at input time (but will be escaped before writing according to TYPO3 standards).
	 * @param	string		Alternative key; If set, this will not set the WHOLE GET array, but only the key in it specified by this value!
	 * @return	void
	 */
	function _GETset($inputGet,$key='')	{
			// ADDS slashes since TYPO3 standard currently is that slashes MUST be applied (regardless of magic_quotes setting).
		if (strcmp($key,''))	{
			if (is_array($inputGet))	{ t3lib_div::addSlashesOnArray($inputGet); } else { $inputGet = addslashes($inputGet); }
			$GLOBALS['HTTP_GET_VARS'][$key] = $_GET[$key] = $inputGet;
		} elseif (is_array($inputGet)){
			t3lib_div::addSlashesOnArray($inputGet);
			$GLOBALS['HTTP_GET_VARS'] = $_GET = $inputGet;
		}
	}

	/**
	 * GET/POST variable
	 * Returns the 'GLOBAL' value of incoming data from POST or GET, with priority to POST (that is equalent to 'GP' order)
	 * Strips slashes of string-outputs, but not arrays UNLESS $strip is set. If $strip is set all output will have escaped characters unescaped.
	 * Usage: 2
	 *
	 * @param	string		GET/POST var to return
	 * @param	boolean		If set, values are stripped of return values that are *arrays!* - string/integer values returned are always strip-slashed()
	 * @return	mixed		POST var named $var and if not set, the GET var of the same name.
	 * @deprecated		Use t3lib_div::_GP instead (ALWAYS delivers a value with un-escaped values!)
	 * @see _GP()
	 */
	function GPvar($var,$strip=0)	{
		if(empty($var)) return;
		$value = isset($_POST[$var]) ? $_POST[$var] : $_GET[$var];
		if (isset($value) && is_string($value))	{ $value = stripslashes($value); }	// Originally check '&& get_magic_quotes_gpc() ' but the values of $_GET are always slashed regardless of get_magic_quotes_gpc() because HTTP_POST/GET_VARS are run through addSlashesOnArray in the very beginning of index_ts.php eg.
		if ($strip && isset($value) && is_array($value)) { t3lib_div::stripSlashesOnArray($value); }
		return $value;
	}

	/**
	 * Returns the GET/POST global arrays merged with POST taking precedence.
	 * Usage: 1
	 *
	 * @param	string		Key (variable name) from GET or POST vars
	 * @return	array		Returns the GET vars merged recursively onto the POST vars.
	 * @ignore
	 * @deprecated
	 */
	function GParrayMerged($var)	{
		$postA = is_array($_POST[$var]) ? $_POST[$var] : array();
		$getA = is_array($_GET[$var]) ? $_GET[$var] : array();
		$mergedA = t3lib_div::array_merge_recursive_overrule($getA,$postA);
		t3lib_div::stripSlashesOnArray($mergedA);
		return $mergedA;
	}










	/*************************
	 *
	 * IMAGE FUNCTIONS
	 *
	 *************************/


	/**
	 * Compressing a GIF file if not already LZW compressed
	 * This function is a workaround for the fact that ImageMagick and/or GD does not compress GIF-files to their minimun size (that is RLE or no compression used)
	 *
	 * 		The function takes a file-reference, $theFile, and saves it again through GD or ImageMagick in order to compress the file
	 * 		GIF:
	 * 		If $type is not set, the compression is done with ImageMagick (provided that $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw'] is pointing to the path of a lzw-enabled version of 'convert') else with GD (should be RLE-enabled!)
	 * 		If $type is set to either 'IM' or 'GD' the compression is done with ImageMagick and GD respectively
	 * 		PNG:
	 * 		No changes.
	 *
	 * 		$theFile is expected to be a valid GIF-file!
	 * 		The function returns a code for the operation.
	 * Usage: 9
	 *
	 * @param	string		Filepath
	 * @param	string		See description of function
	 * @return	string		Returns "GD" if GD was used, otherwise "IM" if ImageMagick was used. If nothing done at all, it returns empty string.
	 * @internal
	 */
	function gif_compress($theFile, $type)	{
		$gfxConf = $GLOBALS['TYPO3_CONF_VARS']['GFX'];
		$returnCode='';
		if ($gfxConf['gif_compress'] && strtolower(substr($theFile,-4,4))=='.gif')	{	// GIF...
			if (($type=='IM' || !$type) && $gfxConf['im'] && $gfxConf['im_path_lzw'])	{	// IM
				$cmd = t3lib_div::imageMagickCommand('convert', '"'.$theFile.'" "'.$theFile.'"', $gfxConf['im_path_lzw']);
				exec($cmd);

				$returnCode='IM';
			} elseif (($type=='GD' || !$type) && $gfxConf['gdlib'] && !$gfxConf['gdlib_png'])	{	// GD
				$tempImage = imageCreateFromGif($theFile);
				imageGif($tempImage, $theFile);
				imageDestroy($tempImage);
				$returnCode='GD';
			}
		}
		return $returnCode;
	}

	/**
	 * Converts a png file to gif
	 * This converts a png file to gif IF the FLAG $GLOBALS['TYPO3_CONF_VARS']['FE']['png_to_gif'] is set true.
	 * Usage: 5
	 *
	 * @param	string		$theFile	the filename with path
	 * @return	string		new filename
	 * @internal
	 */
	function png_to_gif_by_imagemagick($theFile)	{
		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['png_to_gif']
			&& $GLOBALS['TYPO3_CONF_VARS']['GFX']['im']
			&& $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']
			&& strtolower(substr($theFile,-4,4))=='.png'
			&& @is_file($theFile))	{	// IM
				$newFile = substr($theFile,0,-4).'.gif';
				$cmd = t3lib_div::imageMagickCommand('convert', '"'.$theFile.'" "'.$newFile.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
				exec($cmd);
				$theFile = $newFile;
					// unlink old file?? May be bad idea bacause TYPO3 would then recreate the file every time as TYPO3 thinks the file is not generated because it's missing!! So do not unlink $theFile here!!
		}
		return $theFile;
	}

	/**
	 * Returns filename of the png/gif version of the input file (which can be png or gif).
	 * If input file type does not match the wanted output type a conversion is made and temp-filename returned.
	 * Usage: 2
	 *
	 * @param	string		Filepath of image file
	 * @param	boolean		If set, then input file is converted to PNG, otherwise to GIF
	 * @return	string		If the new image file exists, it's filepath is returned
	 * @internal
	 */
	function read_png_gif($theFile,$output_png=0)	{
		if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] && @is_file($theFile))	{
			$ext = strtolower(substr($theFile,-4,4));
			if (
					((string)$ext=='.png' && $output_png)	||
					((string)$ext=='.gif' && !$output_png)
				)	{
				return $theFile;
			} else {
				$newFile = PATH_site.'typo3temp/readPG_'.md5($theFile.'|'.filemtime($theFile)).($output_png?'.png':'.gif');
				exec($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'].'convert "'.$theFile.'" "'.$newFile.'"');
				if (@is_file($newFile))	return $newFile;
			}
		}
	}















	/*************************
	 *
	 * STRING FUNCTIONS
	 *
	 *************************/

	/**
	 * Truncate string
	 * Returns a new string of max. $chars length.
	 * If the string is longer, it will be truncated and appended with '...'.
	 * Usage: 39
	 *
	 * @param	string		string to truncate
	 * @param	integer		must be an integer with an absolute value of at least 4. if negative the string is cropped from the right end.
	 * @param	string		String to append to the output if it is truncated, default is '...'
	 * @return	string		new string
	 * @deprecated		Works ONLY for single-byte charsets! USE t3lib_div::fixed_lgd_cs() instead
	 * @see fixed_lgd_pre()
	 */
	function fixed_lgd($string,$origChars,$preStr='...')	{
		$chars = abs($origChars);
		if ($chars >= 4)	{
			if(strlen($string)>$chars)  {
				return $origChars < 0 ?
					$preStr.trim(substr($string, -($chars-3))) :
					trim(substr($string, 0, $chars-3)).$preStr;
			}
		}
		return $string;
	}

	/**
	 * Truncate string
	 * Returns a new string of max. $chars length.
	 * If the string is longer, it will be truncated and prepended with '...'.
	 * This works like fixed_lgd, but is truncated in the start of the string instead of the end
	 * Usage: 6
	 *
	 * @param	string		string to truncate
	 * @param	integer		must be an integer of at least 4
	 * @return	string		new string
	 * @deprecated		Use either fixed_lgd() or fixed_lgd_cs() (with negative input value for $chars)
	 * @see fixed_lgd()
	 */
	function fixed_lgd_pre($string,$chars)	{
		return strrev(t3lib_div::fixed_lgd(strrev($string),$chars));
	}

	/**
	 * Truncates a string with appended/prepended "..." and takes backend character set into consideration
	 * Use only from backend!
	 * Usage: 75
	 *
	 * @param	string		string to truncate
	 * @param	integer		must be an integer with an absolute value of at least 4. if negative the string is cropped from the right end.
	 * @return	string		New string
	 * @see fixed_lgd()
	 */
	function fixed_lgd_cs($string,$chars)	{
		if (is_object($GLOBALS['LANG']))	{
			return $GLOBALS['LANG']->csConvObj->crop($GLOBALS['LANG']->charSet,$string,$chars,'...');
		} else {
			return t3lib_div::fixed_lgd($string, $chars);
		}
	}

	/**
	 * Breaks up the text for emails
	 * Usage: 1
	 *
	 * @param	string		The string to break up
	 * @param	string		The string to implode the broken lines with (default/typically \n)
	 * @param	integer		The line length
	 * @deprecated		Use PHP function wordwrap()
	 * @return	string
	 */
	function breakTextForEmail($str,$implChar="\n",$charWidth=76)	{
		$lines = explode(chr(10),$str);
		$outArr=array();
		while(list(,$lStr)=each($lines))	{
			$outArr[] = t3lib_div::breakLinesForEmail($lStr,$implChar,$charWidth);
		}
		return implode(chr(10),$outArr);
	}

	/**
	 * Breaks up a single line of text for emails
	 * Usage: 5
	 *
	 * @param	string		The string to break up
	 * @param	string		The string to implode the broken lines with (default/typically \n)
	 * @param	integer		The line length
	 * @return	string
	 * @see breakTextForEmail()
	 */
	function breakLinesForEmail($str,$implChar="\n",$charWidth=76)	{
		$lines=array();
		$l=$charWidth;
		$p=0;
		while(strlen($str)>$p)	{
			$substr=substr($str,$p,$l);
			if (strlen($substr)==$l)	{
				$count = count(explode(' ',trim(strrev($substr))));
				if ($count>1)	{	// OK...
					$parts = explode(' ',strrev($substr),2);
					$theLine = strrev($parts[1]);
				} else {
					$afterParts = explode(' ',substr($str,$l+$p),2);
					$theLine = $substr.$afterParts[0];
				}
				if (!strlen($theLine))	{break; }	// Error, because this would keep us in an endless loop.
			} else {
				$theLine=$substr;
			}

			$lines[]=trim($theLine);
			$p+=strlen($theLine);
			if (!trim(substr($str,$p,$l)))	break;	// added...
		}
		return implode($implChar,$lines);
	}

	/**
	 * Match IP number with list of numbers with wildcard
	 * Dispatcher method for switching into specialised IPv4 and IPv6 methods.
	 * Usage: 10
	 *
	 * @param	string		$baseIP is the current remote IP address for instance, typ. REMOTE_ADDR
	 * @param	string		$list is a comma-list of IP-addresses to match with. *-wildcard allowed instead of number, plus leaving out parts in the IP number is accepted as wildcard (eg. 192.168.*.* equals 192.168). If list is "*" no check is done and the function returns TRUE immediately.
	 * @return	boolean		True if an IP-mask from $list matches $baseIP
	 */
	function cmpIP($baseIP, $list)	{
		if ($list==='*')	return TRUE;
		if (strstr($baseIP, ':') && t3lib_div::validIPv6($baseIP))	{
			return t3lib_div::cmpIPv6($baseIP, $list);
		} else {
			return t3lib_div::cmpIPv4($baseIP, $list);
		}
	}

	/**
	 * Match IPv4 number with list of numbers with wildcard
	 *
	 * @param	string		$baseIP is the current remote IP address for instance, typ. REMOTE_ADDR
	 * @param	string		$list is a comma-list of IP-addresses to match with. *-wildcard allowed instead of number, plus leaving out parts in the IP number is accepted as wildcard (eg. 192.168.*.* equals 192.168)
	 * @return	boolean		True if an IP-mask from $list matches $baseIP
	 */
	function cmpIPv4($baseIP, $list)	{
		$IPpartsReq = explode('.',$baseIP);
		if (count($IPpartsReq)==4)	{
			$values = t3lib_div::trimExplode(',',$list,1);

			foreach($values as $test)	{
				list($test,$mask) = explode('/',$test);

				if(intval($mask)) {
						// "192.168.3.0/24"
					$lnet = ip2long($test);
					$lip = ip2long($baseIP);
					$binnet = str_pad( decbin($lnet),32,'0','STR_PAD_LEFT');
					$firstpart = substr($binnet,0,$mask);
					$binip = str_pad( decbin($lip),32,'0','STR_PAD_LEFT');
					$firstip = substr($binip,0,$mask);
					$yes = (strcmp($firstpart,$firstip)==0);
				} else {
						// "192.168.*.*"
					$IPparts = explode('.',$test);
					$yes = 1;
					reset($IPparts);
					while(list($index,$val)=each($IPparts))	{
						$val = trim($val);
						if (strcmp($val,'*') && strcmp($IPpartsReq[$index],$val))	{
							$yes=0;
						}
					}
				}
				if ($yes) return true;
			}
		}
		return false;
	}

	/**
	 * Match IPv6 address with a list of IPv6 prefixes
	 *
	 * @param	string		$baseIP is the current remote IP address for instance
	 * @param	string		$list is a comma-list of IPv6 prefixes, could also contain IPv4 addresses
	 * @return	boolean		True if an baseIP matches any prefix
	 */
	function cmpIPv6($baseIP, $list)	{
		$success = false;	// Policy default: Deny connection
		$baseIP = t3lib_div::normalizeIPv6($baseIP);

		$values = t3lib_div::trimExplode(',',$list,1);
		foreach ($values as $test)	{
			list($test,$mask) = explode('/',$test);
			if (t3lib_div::validIPv6($test))	{
				$test = t3lib_div::normalizeIPv6($test);
				if (intval($mask))	{
					switch ($mask) {	// test on /48 /64
						case '48':
							$testBin = substr(t3lib_div::IPv6Hex2Bin($test), 0, 48);
							$baseIPBin = substr(t3lib_div::IPv6Hex2Bin($baseIP), 0, 48);
							$success = strcmp($testBin, $baseIPBin)==0 ? true : false;
						break;
						case '64':
							$testBin = substr(t3lib_div::IPv6Hex2Bin($test), 0, 64);
							$baseIPBin = substr(t3lib_div::IPv6Hex2Bin($baseIP), 0, 64);
							$success = strcmp($testBin, $baseIPBin)==0 ? true : false;
						break;
						default:
							$success = false;
					}
				} else {
					if (t3lib_div::validIPv6($test))	{	// test on full ip address 128 bits
						$testBin = t3lib_div::IPv6Hex2Bin($test);
						$baseIPBin = t3lib_div::IPv6Hex2Bin($baseIP);
						$success = strcmp($testBin, $baseIPBin)==0 ? true : false;
					}
				}
			}
			if ($success) return true;
		}
		return false;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$hex: ...
	 * @return	[type]		...
	 */
	function IPv6Hex2Bin ($hex)	{
		$bin = '';
		$hex = str_replace(':', '', $hex);	// Replace colon to nothing
		for ($i=0; $i<strlen($hex); $i=$i+2)	{
			$bin.= chr(hexdec(substr($hex, $i, 2)));
		}
		return $bin;
	}

	/**
	 * Normalize an IPv6 address to full length
	 *
	 * @param	string		Given IPv6 address
	 * @return	string		Normalized address
	 */
	function normalizeIPv6($address)	{
		$normalizedAddress = '';
		$stageOneAddress = '';

		$chunks = explode('::', $address);	// Count 2 if if address has hidden zero blocks
		if (count($chunks)==2)	{
			$chunksLeft = explode(':', $chunks[0]);
			$chunksRight = explode(':', $chunks[1]);
			$left = count($chunksLeft);
			$right = count($chunksRight);

				// Special case: leading zero-only blocks count to 1, should be 0
			if ($left==1 && strlen($chunksLeft[0])==0)	$left=0;

			$hiddenBlocks = 8 - ($left + $right);
			$hiddenPart = '';
			while ($h<$hiddenBlocks)	{
				$hiddenPart .= '0000:';
				$h++;
			}

			if ($left == 0) {
				$stageOneAddress = $hiddenPart . $chunks[1];
			} else {
				$stageOneAddress = $chunks[0] . ':' . $hiddenPart . $chunks[1];
			}
		} else $stageOneAddress = $address;

			// normalize the blocks:
		$blocks = explode(':', $stageOneAddress);
		$divCounter = 0;
		foreach ($blocks as $block)	{
			$tmpBlock = '';
			$i = 0;
			$hiddenZeros = 4 - strlen($block);
			while ($i < $hiddenZeros)	{
				$tmpBlock .= '0';
				$i++;
			}
			$normalizedAddress .= $tmpBlock . $block;
			if ($divCounter < 7)	{
				$normalizedAddress .= ':';
				$divCounter++;
			}
		}
		return $normalizedAddress;
	}

	/**
	 * Validate a given IP address to the IPv6 address format.
	 *
	 * Example for possible format:  43FB::BB3F:A0A0:0 | ::1
	 *
	 * @param	string		IP address to be tested
	 * @return	boolean		True if $ip is of IPv6 format.
	 */
	function validIPv6($ip)	{
		$uppercaseIP = strtoupper($ip);

		$regex = '/^(';
		$regex.= '(([\dA-F]{1,4}:){7}[\dA-F]{1,4})|';
		$regex.= '(([\dA-F]{1,4}){1}::([\dA-F]{1,4}:){1,5}[\dA-F]{1,4})|';
		$regex.= '(([\dA-F]{1,4}:){2}:([\dA-F]{1,4}:){1,4}[\dA-F]{1,4})|';
		$regex.= '(([\dA-F]{1,4}:){3}:([\dA-F]{1,4}:){1,3}[\dA-F]{1,4})|';
		$regex.= '(([\dA-F]{1,4}:){4}:([\dA-F]{1,4}:){1,2}[\dA-F]{1,4})|';
		$regex.= '(([\dA-F]{1,4}:){5}:([\dA-F]{1,4}:){0,1}[\dA-F]{1,4})|';
		$regex.= '(::([\dA-F]{1,4}:){0,6}[\dA-F]{1,4})';
		$regex.= ')$/';

		return preg_match($regex, $uppercaseIP) ? true : false;
	}

	/**
	 * Match fully qualified domain name with list of strings with wildcard
	 *
	 * @param	string		The current remote IP address for instance, typ. REMOTE_ADDR
	 * @param	string		A comma-list of domain names to match with. *-wildcard allowed but cannot be part of a string, so it must match the full host name (eg. myhost.*.com => correct, myhost.*domain.com => wrong)
	 * @return	boolean		True if a domain name mask from $list matches $baseIP
	 */
	function cmpFQDN($baseIP, $list)        {
		if (count(explode('.',$baseIP))==4)     {
			$resolvedHostName = explode('.', gethostbyaddr($baseIP));
			$values = t3lib_div::trimExplode(',',$list,1);

			foreach($values as $test)	{
				$hostNameParts = explode('.',$test);
				$yes = 1;

				foreach($hostNameParts as $index => $val)	{
					$val = trim($val);
					if (strcmp($val,'*') && strcmp($resolvedHostName[$index],$val)) {
						$yes=0;
					}
				}
				if ($yes) return true;
			}
		}
		return false;
	}

	/**
	 * Check for item in list
	 * Check if an item exists in a comma-separated list of items.
	 * Usage: 163
	 *
	 * @param	string		comma-separated list of items (string)
	 * @param	string		item to check for
	 * @return	boolean		true if $item is in $list
	 */
	function inList($list,$item)	{
		return strstr(','.$list.',', ','.$item.',') ? true : false;
	}

	/**
	 * Removes an item from a comma-separated list of items.
	 * Usage: 1
	 *
	 * @param	string		element to remove
	 * @param	string		comma-separated list of items (string)
	 * @return	string		new comma-separated list of items
	 */
	function rmFromList($element,$list)	{
		$items = explode(',',$list);
		while(list($k,$v)=each($items))	{
			if ($v==$element)	{unset($items[$k]);}
		}
		return implode(',',$items);
	}

	/**
	 * Expand a comma-separated list of integers with ranges (eg 1,3-5,7 becomes 1,3,4,5,7).
	 * Ranges are limited to 1000 values per range.
	 *
	 * @param	string		comma-separated list of integers with ranges (string)
	 * @return	string		new comma-separated list of items
	 * @author	Martin Kutschker <martin.kutschker@activesolution.at>
	 */
	function expandList($list)      {
		$items = explode(',',$list);
		$list = array();
		while(list(,$item)=each($items))	{
			$range = explode('-',$item);
			if (isset($range[1]))	{
				$runAwayBrake = 1000;
				for ($n=$range[0]; $n<=$range[1]; $n++)	{
					$list[] = $n;

					$runAwayBrake--;
					if ($runAwayBrake<=0)	break;
				}
			} else {
				$list[] = $item;
			}
		}

		return implode(',',$list);
	}

	/**
	 * Forces the integer $theInt into the boundaries of $min and $max. If the $theInt is 'false' then the $zeroValue is applied.
	 * Usage: 224
	 *
	 * @param	integer		Input value
	 * @param	integer		Lower limit
	 * @param	integer		Higher limit
	 * @param	integer		Default value if input is false.
	 * @return	integer		The input value forced into the boundaries of $min and $max
	 */
	function intInRange($theInt,$min,$max=2000000000,$zeroValue=0)	{
		// Returns $theInt as an integer in the integerspace from $min to $max
		$theInt = intval($theInt);
		if ($zeroValue && !$theInt)	{$theInt=$zeroValue;}	// If the input value is zero after being converted to integer, zeroValue may set another default value for it.
		if ($theInt<$min){$theInt=$min;}
		if ($theInt>$max){$theInt=$max;}
		return $theInt;
	}

	/**
	 * Returns the $integer if greater than zero, otherwise returns zero.
	 * Usage: 1
	 *
	 * @param	integer		Integer string to process
	 * @return	integer
	 */
	function intval_positive($theInt)	{
		$theInt = intval($theInt);
		if ($theInt<0){$theInt=0;}
		return $theInt;
	}

	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 * Usage: 2
	 *
	 * @param	string		Version number on format x.x.x
	 * @return	integer		Integer version of version number (where each part can count to 999)
	 */
	function int_from_ver($verNumberStr)	{
		$verParts = explode('.',$verNumberStr);
		return intval((int)$verParts[0].str_pad((int)$verParts[1],3,'0',STR_PAD_LEFT).str_pad((int)$verParts[2],3,'0',STR_PAD_LEFT));
	}

	/**
	 * Returns true if the current TYPO3 version (or compatibility version) is compatible to the input version
	 * Notice that this function compares branches, not versions (4.0.1 would be > 4.0.0 although they use the same compat_version)
	 *
	 * @param	string		Minimum branch number required (format x.y / e.g. "4.0" NOT "4.0.0"!)
	 * @return	boolean		Returns true if this setup is compatible with the provided version number
	 * @todo	Still needs a function to convert versions to branches
	 */
	function compat_version($verNumberStr)	{
		global $TYPO3_CONF_VARS;
		$currVersionStr = $TYPO3_CONF_VARS['SYS']['compat_version'] ? $TYPO3_CONF_VARS['SYS']['compat_version'] : TYPO3_branch;

		if (t3lib_div::int_from_ver($currVersionStr) < t3lib_div::int_from_ver($verNumberStr))	{
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Makes a positive integer hash out of the first 7 chars from the md5 hash of the input
	 * Usage: 5
	 *
	 * @param	string		String to md5-hash
	 * @return	integer		Returns 28bit integer-hash
	 */
	function md5int($str)	{
		return hexdec(substr(md5($str),0,7));
	}

	/**
	 * Returns the first 10 positions of the MD5-hash		(changed from 6 to 10 recently)
	 *
	 * Usage: 37
	 *
	 * @param	string		Input string to be md5-hashed
	 * @param	integer		The string-length of the output
	 * @return	string		Substring of the resulting md5-hash, being $len chars long (from beginning)
	 */
	function shortMD5($input, $len=10)	{
		return substr(md5($input),0,$len);
	}

	/**
	 * Takes comma-separated lists and arrays and removes all duplicates
	 * If a value in the list is trim(empty), the value is ignored.
	 * Usage: 16
	 *
	 * @param	string		Accept multiple parameters wich can be comma-separated lists of values and arrays.
	 * @param	mixed		$secondParameter: Dummy field, which if set will show a warning!
	 * @return	string		Returns the list without any duplicates of values, space around values are trimmed
	 */
	function uniqueList($in_list, $secondParameter=NULL)	{
		if (is_array($in_list))	die('t3lib_div::uniqueList() does NOT support array arguments anymore! Only string comma lists!');
		if (isset($secondParameter))	die('t3lib_div::uniqueList() does NOT support more than a single argument value anymore. You have specified more than one.');

		return implode(',',array_unique(t3lib_div::trimExplode(',',$in_list,1)));
	}

	/**
	 * Splits a reference to a file in 5 parts
	 * Usage: 43
	 *
	 * @param	string		Filename/filepath to be analysed
	 * @return	array		Contains keys [path], [file], [filebody], [fileext], [realFileext]
	 */
	function split_fileref($fileref)	{
		$reg = array();
		if (	ereg('(.*/)(.*)$',$fileref,$reg)	)	{
			$info['path'] = $reg[1];
			$info['file'] = $reg[2];
		} else {
			$info['path'] = '';
			$info['file'] = $fileref;
		}
		$reg='';
		if (	ereg('(.*)\.([^\.]*$)',$info['file'],$reg)	)	{
			$info['filebody'] = $reg[1];
			$info['fileext'] = strtolower($reg[2]);
			$info['realFileext'] = $reg[2];
		} else {
			$info['filebody'] = $info['file'];
			$info['fileext'] = '';
		}
		reset($info);
		return $info;
	}

	/**
	 * Returns the directory part of a path without trailing slash
	 * If there is no dir-part, then an empty string is returned.
	 * Behaviour:
	 *
	 * '/dir1/dir2/script.php' => '/dir1/dir2'
	 * '/dir1/' => '/dir1'
	 * 'dir1/script.php' => 'dir1'
	 * 'd/script.php' => 'd'
	 * '/script.php' => ''
	 * '' => ''
	 * Usage: 5
	 *
	 * @param	string		Directory name / path
	 * @return	string		Processed input value. See function description.
	 */
	function dirname($path)	{
		$p=t3lib_div::revExplode('/',$path,2);
		return count($p)==2?$p[0]:'';
	}

	/**
	 * Modifies a HTML Hex color by adding/subtracting $R,$G and $B integers
	 * Usage: 11
	 *
	 * @param	string		A hexadecimal color code, #xxxxxx
	 * @param	integer		Offset value 0-255
	 * @param	integer		Offset value 0-255
	 * @param	integer		Offset value 0-255
	 * @return	string		A hexadecimal color code, #xxxxxx, modified according to input vars
	 * @see modifyHTMLColorAll()
	 */
	function modifyHTMLColor($color,$R,$G,$B)	{
		// This takes a hex-color (# included!) and adds $R, $G and $B to the HTML-color (format: #xxxxxx) and returns the new color
		$nR = t3lib_div::intInRange(hexdec(substr($color,1,2))+$R,0,255);
		$nG = t3lib_div::intInRange(hexdec(substr($color,3,2))+$G,0,255);
		$nB = t3lib_div::intInRange(hexdec(substr($color,5,2))+$B,0,255);
		return '#'.
			substr('0'.dechex($nR),-2).
			substr('0'.dechex($nG),-2).
			substr('0'.dechex($nB),-2);
	}

	/**
	 * Modifies a HTML Hex color by adding/subtracting $all integer from all R/G/B channels
	 * Usage: 6
	 *
	 * @param	string		A hexadecimal color code, #xxxxxx
	 * @param	integer		Offset value 0-255 for all three channels.
	 * @return	string		A hexadecimal color code, #xxxxxx, modified according to input vars
	 * @see modifyHTMLColor()
	 */
	function modifyHTMLColorAll($color,$all)	{
		return t3lib_div::modifyHTMLColor($color,$all,$all,$all);
	}

	/**
	 * Removes comma (if present) in the end of string
	 * Usage: 2
	 *
	 * @param	string		String from which the comma in the end (if any) will be removed.
	 * @return	string
	 */
	function rm_endcomma($string)	{
		return ereg_replace(',$','',$string);
	}

	/**
	 * strtoupper which converts danish (and other characters) characters as well
	 * Usage: 0
	 *
	 * @param	string		String to process
	 * @return	string
	 * @deprecated		Use t3lib_cs::conv_case() instead or for HTML output, wrap your content in <span class="uppercase">...</span>)
	 * @ignore
	 */
	function danish_strtoupper($string)	{
		$value = strtoupper($string);
		return strtr($value, '���������������', '���������������');
	}

	/**
	 * Change umlaut characters to plain ASCII with normally two character target
	 * Only known characters will be converted, so don't expect a result for any character.
	 *
	 * � => ae, � => Oe
	 *
	 * @param	string		String to convert.
	 * @deprecated		Works only for western europe single-byte charsets! Use t3lib_cs::specCharsToASCII() instead!
	 * @return	string
	 */
	function convUmlauts($str)	{
		$pat  = array (	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/',	'/�/'	);
		$repl = array (	'ae',	'Ae',	'oe',	'Oe',	'ue',	'Ue',	'ss',	'aa',	'AA',	'oe',	'OE',	'ae',	'AE'	);
		return preg_replace($pat,$repl,$str);
	}

	/**
	 * Tests if the input is an integer.
	 * Usage: 77
	 *
	 * @param	mixed		Any input variable to test.
	 * @return	boolean		Returns true if string is an integer
	 */
	function testInt($var)	{
		return !strcmp($var,intval($var));
	}

	/**
	 * Returns true if the first part of $str matches the string $partStr
	 * Usage: 59
	 *
	 * @param	string		Full string to check
	 * @param	string		Reference string which must be found as the "first part" of the full string
	 * @return	boolean		True if $partStr was found to be equal to the first part of $str
	 */
	function isFirstPartOfStr($str,$partStr)	{
		// Returns true, if the first part of a $str equals $partStr and $partStr is not ''
		$psLen = strlen($partStr);
		if ($psLen)	{
			return substr($str,0,$psLen)==(string)$partStr;
		} else return false;
	}

	/**
	 * Formats the input integer $sizeInBytes as bytes/kilobytes/megabytes (-/K/M)
	 * Usage: 53
	 *
	 * @param	integer		Number of bytes to format.
	 * @param	string		Labels for bytes, kilo, mega and giga separated by vertical bar (|) and possibly encapsulated in "". Eg: " | K| M| G" (which is the default value)
	 * @return	string		Formatted representation of the byte number, for output.
	 */
	function formatSize($sizeInBytes,$labels='')	{

			// Set labels:
		if (strlen($labels) == 0) {
		    $labels = ' | K| M| G';
		} else {
		    $labels = str_replace('"','',$labels);
		}
		$labelArr = explode('|',$labels);

			// Find size:
		if ($sizeInBytes>900)	{
			if ($sizeInBytes>900000000)	{	// GB
				$val = $sizeInBytes/(1024*1024*1024);
				return number_format($val, (($val<20)?1:0), '.', '').$labelArr[3];
			}
			elseif ($sizeInBytes>900000)	{	// MB
				$val = $sizeInBytes/(1024*1024);
				return number_format($val, (($val<20)?1:0), '.', '').$labelArr[2];
			} else {	// KB
				$val = $sizeInBytes/(1024);
				return number_format($val, (($val<20)?1:0), '.', '').$labelArr[1];
			}
		} else {	// Bytes
			return $sizeInBytes.$labelArr[0];
		}
	}

	/**
	 * Returns microtime input to milliseconds
	 * Usage: 2
	 *
	 * @param	string		Microtime
	 * @return	integer		Microtime input string converted to an integer (milliseconds)
	 */
	function convertMicrotime($microtime)	{
		$parts = explode(' ',$microtime);
		return round(($parts[0]+$parts[1])*1000);
	}

	/**
	 * This splits a string by the chars in $operators (typical /+-*) and returns an array with them in
	 * Usage: 2
	 *
	 * @param	string		Input string, eg "123 + 456 / 789 - 4"
	 * @param	string		Operators to split by, typically "/+-*"
	 * @return	array		Array with operators and operands separated.
	 * @see tslib_cObj::calc(), tslib_gifBuilder::calcOffset()
	 */
	function splitCalc($string,$operators)	{
		$res = Array();
		$sign='+';
		while($string)	{
			$valueLen=strcspn($string,$operators);
			$value=substr($string,0,$valueLen);
			$res[] = Array($sign,trim($value));
			$sign=substr($string,$valueLen,1);
			$string=substr($string,$valueLen+1);
		}
		reset($res);
		return $res;
	}

	/**
	 * Calculates the input by +,-,*,/,%,^ with priority to + and -
	 * Usage: 1
	 *
	 * @param	string		Input string, eg "123 + 456 / 789 - 4"
	 * @return	integer		Calculated value. Or error string.
	 * @see calcParenthesis()
	 */
	function calcPriority($string)	{
		$string=ereg_replace('[[:space:]]*','',$string);	// removing all whitespace
		$string='+'.$string;	// Ensuring an operator for the first entrance
		$qm='\*\/\+-^%';
		$regex = '(['.$qm.'])(['.$qm.']?[0-9\.]*)';
			// split the expression here:
		$reg = array();
		preg_match_all('/'.$regex.'/',$string,$reg);

		reset($reg[2]);
		$number=0;
		$Msign='+';
		$err='';
		$buffer=doubleval(current($reg[2]));
		next($reg[2]);	// Advance pointer
		while(list($k,$v)=each($reg[2]))	{
			$v=doubleval($v);
			$sign = $reg[1][$k];
			if ($sign=='+' || $sign=='-')	{
				$number = $Msign=='-' ? $number-=$buffer : $number+=$buffer;
				$Msign = $sign;
				$buffer=$v;
			} else {
				if ($sign=='/')	{if ($v) $buffer/=$v; else $err='dividing by zero';}
				if ($sign=='%')	{if ($v) $buffer%=$v; else $err='dividing by zero';}
				if ($sign=='*')	{$buffer*=$v;}
				if ($sign=='^')	{$buffer=pow($buffer,$v);}
			}
		}
		$number = $Msign=='-' ? $number-=$buffer : $number+=$buffer;
		return $err ? 'ERROR: '.$err : $number;
	}

	/**
	 * Calculates the input with parenthesis levels
	 * Usage: 2
	 *
	 * @param	string		Input string, eg "(123 + 456) / 789 - 4"
	 * @return	integer		Calculated value. Or error string.
	 * @see calcPriority(), tslib_cObj::stdWrap()
	 */
	function calcParenthesis($string)	{
		$securC=100;
		do {
			$valueLenO=strcspn($string,'(');
			$valueLenC=strcspn($string,')');
			if ($valueLenC==strlen($string) || $valueLenC < $valueLenO)	{
				$value = t3lib_div::calcPriority(substr($string,0,$valueLenC));
				$string = $value.substr($string,$valueLenC+1);
				return $string;
			} else {
				$string = substr($string,0,$valueLenO).t3lib_div::calcParenthesis(substr($string,$valueLenO+1));
			}
				// Security:
			$securC--;
			if ($securC<=0)	break;
		} while($valueLenO<strlen($string));
		return $string;
	}

	/**
	 * Inverse version of htmlspecialchars()
	 * Usage: 4
	 *
	 * @param	string		Value where &gt;, &lt;, &quot; and &amp; should be converted to regular chars.
	 * @return	string		Converted result.
	 */
	function htmlspecialchars_decode($value)	{
		$value = str_replace('&gt;','>',$value);
		$value = str_replace('&lt;','<',$value);
		$value = str_replace('&quot;','"',$value);
		$value = str_replace('&amp;','&',$value);
		return $value;
	}

	/**
	 * Re-converts HTML entities if they have been converted by htmlspecialchars()
	 * Usage: 10
	 *
	 * @param	string		String which contains eg. "&amp;amp;" which should stay "&amp;". Or "&amp;#1234;" to "&#1234;". Or "&amp;#x1b;" to "&#x1b;"
	 * @return	string		Converted result.
	 */
	function deHSCentities($str)	{
		return ereg_replace('&amp;([#[:alnum:]]*;)','&\1',$str);
	}

	/**
	 * This function is used to escape any ' -characters when transferring text to JavaScript!
	 * Usage: 3
	 *
	 * @param	string		String to escape
	 * @param	boolean		If set, also backslashes are escaped.
	 * @param	string		The character to escape, default is ' (single-quote)
	 * @return	string		Processed input string
	 */
	function slashJS($string,$extended=0,$char="'")	{
		if ($extended)	{$string = str_replace ("\\", "\\\\", $string);}
		return str_replace ($char, "\\".$char, $string);
	}

	/**
	 * Version of rawurlencode() where all spaces (%20) are re-converted to space-characters.
	 * Usefull when passing text to JavaScript where you simply url-encode it to get around problems with syntax-errors, linebreaks etc.
	 * Usage: 4
	 *
	 * @param	string		String to raw-url-encode with spaces preserved
	 * @return	string		Rawurlencoded result of input string, but with all %20 (space chars) converted to real spaces.
	 */
	function rawUrlEncodeJS($str)	{
		return str_replace('%20',' ',rawurlencode($str));
	}

	/**
	 * rawurlencode which preserves "/" chars
	 * Usefull when filepaths should keep the "/" chars, but have all other special chars encoded.
	 * Usage: 5
	 *
	 * @param	string		Input string
	 * @return	string		Output string
	 */
	function rawUrlEncodeFP($str)	{
		return str_replace('%2F','/',rawurlencode($str));
	}

	/**
	 * Checking syntax of input email address
	 * Usage: 5
	 *
	 * @param	string		Input string to evaluate
	 * @return	boolean		Returns true if the $email address (input string) is valid; Has a "@", domain name with at least one period and only allowed a-z characters.
	 */
	function validEmail($email)	{
		$email = trim ($email);
		if (strstr($email,' '))	 return FALSE;
		return ereg('^[A-Za-z0-9\._-]+[@][A-Za-z0-9\._-]+[\.].[A-Za-z0-9]+$',$email) ? TRUE : FALSE;
	}

	/**
	 * Formats a string for output between <textarea>-tags
	 * All content outputted in a textarea form should be passed through this function
	 * Not only is the content htmlspecialchar'ed on output but there is also a single newline added in the top. The newline is necessary because browsers will ignore the first newline after <textarea> if that is the first character. Therefore better set it!
	 * Usage: 23
	 *
	 * @param	string		Input string to be formatted.
	 * @return	string		Formatted for <textarea>-tags
	 */
	function formatForTextarea($content)	{
		return chr(10).htmlspecialchars($content);
	}












	/*************************
	 *
	 * ARRAY FUNCTIONS
	 *
	 *************************/

	/**
	 * Check if an item exists in an array
	 * Please note that the order of parameters is reverse compared to the php4-function in_array()!!!
	 * Usage: 3
	 *
	 * @param	array		one-dimensional array of items
	 * @param	string		item to check for
	 * @return	boolean		true if $item is in the one-dimensional array $in_array
	 * @internal
	 */
	function inArray($in_array,$item)	{
		if (is_array($in_array))	{
			while (list(,$val)=each($in_array))	{
				if (!is_array($val) && !strcmp($val,$item)) return true;
			}
		}
	}

	/**
	 * Explodes a $string delimited by $delim and passes each item in the array through intval().
	 * Corresponds to explode(), but with conversion to integers for all values.
	 * Usage: 76
	 *
	 * @param	string		Delimiter string to explode with
	 * @param	string		The string to explode
	 * @return	array		Exploded values, all converted to integers
	 */
	function intExplode($delim, $string)	{
		$temp = explode($delim,$string);
		while(list($key,$val)=each($temp))	{
			$temp[$key]=intval($val);
		}
		reset($temp);
		return $temp;
	}

	/**
	 * Reverse explode which explodes the string counting from behind.
	 * Thus t3lib_div::revExplode(':','my:words:here',2) will return array('my:words','here')
	 * Usage: 8
	 *
	 * @param	string		Delimiter string to explode with
	 * @param	string		The string to explode
	 * @param	integer		Number of array entries
	 * @return	array		Exploded values
	 */
	function revExplode($delim, $string, $count=0)	{
		$temp = explode($delim,strrev($string),$count);
		while(list($key,$val)=each($temp))	{
			$temp[$key]=strrev($val);
		}
		$temp=array_reverse($temp);
		reset($temp);
		return $temp;
	}

	/**
	 * Explodes a string and trims all values for whitespace in the ends.
	 * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
	 * Usage: 256
	 *
	 * @param	string		Delimiter string to explode with
	 * @param	string		The string to explode
	 * @param	boolean		If set, all empty values (='') will NOT be set in output
	 * @return	array		Exploded values
	 */
	function trimExplode($delim, $string, $onlyNonEmptyValues=0)	{
		$temp = explode($delim,$string);
		$newtemp=array();
		while(list($key,$val)=each($temp))	{
			if (!$onlyNonEmptyValues || strcmp('',trim($val)))	{
				$newtemp[]=trim($val);
			}
		}
		reset($newtemp);
		return $newtemp;
	}

	/**
	 * Remove duplicate values from an array
	 * Usage: 0
	 *
	 * @param	array		Array of values to make unique
	 * @return	array
	 * @ignore
	 * @deprecated		Use the PHP function array_unique instead
	 */
	function uniqueArray($valueArray)	{
		return array_unique($valueArray);
	}

	/**
	 * Removes the value $cmpValue from the $array if found there. Returns the modified array
	 * Usage: 3
	 *
	 * @param	array		Array containing the values
	 * @param	string		Value to search for and if found remove array entry where found.
	 * @return	array		Output array with entries removed if search string is found
	 */
	function removeArrayEntryByValue($array,$cmpValue)	{
		if (is_array($array))	{
			reset($array);
			while(list($k,$v)=each($array))	{
				if (is_array($v))	{
					$array[$k] = t3lib_div::removeArrayEntryByValue($v,$cmpValue);
				} else {
					if (!strcmp($v,$cmpValue))	{
						unset($array[$k]);
					}
				}
			}
		}
		reset($array);
		return $array;
	}

	/**
	 * Implodes a multidim-array into GET-parameters (eg. &param[key][key2]=value2&param[key][key3]=value3)
	 * Usage: 24
	 *
	 * @param	string		Name prefix for entries. Set to blank if you wish none.
	 * @param	array		The (multidim) array to implode
	 * @param	string		(keep blank)
	 * @param	boolean		If set, parameters which were blank strings would be removed.
	 * @param	boolean		If set, the param name itself (for example "param[key][key2]") would be rawurlencoded as well.
	 * @return	string		Imploded result, fx. &param[key][key2]=value2&param[key][key3]=value3
	 * @see explodeUrl2Array()
	 */
	function implodeArrayForUrl($name,$theArray,$str='',$skipBlank=0,$rawurlencodeParamName=0)	{
		if (is_array($theArray))	{
			foreach($theArray as $Akey => $AVal)	{
				$thisKeyName = $name ? $name.'['.$Akey.']' : $Akey;
				if (is_array($AVal))	{
					$str = t3lib_div::implodeArrayForUrl($thisKeyName,$AVal,$str,$skipBlank,$rawurlencodeParamName);
				} else {
					if (!$skipBlank || strcmp($AVal,''))	{
						$str.='&'.($rawurlencodeParamName ? rawurlencode($thisKeyName) : $thisKeyName).
							'='.rawurlencode($AVal);
					}
				}
			}
		}
		return $str;
	}

	/**
	 * Explodes a string with GETvars (eg. "&id=1&type=2&ext[mykey]=3") into an array
	 *
	 * @param	string		GETvars string
	 * @param	boolean		If set, the string will be parsed into a multidimensional array if square brackets are used in variable names (using PHP function parse_str())
	 * @return	array		Array of values. All values AND keys are rawurldecoded() as they properly should be. But this means that any implosion of the array again must rawurlencode it!
	 * @see implodeArrayForUrl()
	 */
	function explodeUrl2Array($string,$multidim=FALSE)	{
		$output = array();
		if ($multidim)	{
			parse_str($string,$output);
		} else {
			$p = explode('&',$string);
			foreach($p as $v)	{
				if (strlen($v))	{
					list($pK,$pV) = explode('=',$v,2);
					$output[rawurldecode($pK)] = rawurldecode($pV);
				}
			}
		}
		return $output;
	}

	/**
	 * Returns an array with selected keys from incoming data.
	 * (Better read source code if you want to find out...)
	 * Usage: 3
	 *
	 * @param	string		List of variable/key names
	 * @param	array		Array from where to get values based on the keys in $varList
	 * @param	boolean		If set, then t3lib_div::_GP() is used to fetch the value if not found (isset) in the $getArray
	 * @return	array		Output array with selected variables.
	 */
	function compileSelectedGetVarsFromArray($varList,$getArray,$GPvarAlt=1)	{
		$keys = t3lib_div::trimExplode(',',$varList,1);
		$outArr=array();
		foreach($keys as $v)	{
			if (isset($getArray[$v]))	{
				$outArr[$v]=$getArray[$v];
			} elseif ($GPvarAlt) {
				$outArr[$v]=t3lib_div::_GP($v);
			}
		}
		return $outArr;
	}

	/**
	 * AddSlash array
	 * This function traverses a multidimentional array and adds slashes to the values.
	 * NOTE that the input array is and argument by reference.!!
	 * Twin-function to stripSlashesOnArray
	 * Usage: 8
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function addSlashesOnArray(&$theArray)	{
		if (is_array($theArray))	{
			reset($theArray);
			while(list($Akey,$AVal)=each($theArray))	{
				if (is_array($AVal))	{
					t3lib_div::addSlashesOnArray($theArray[$Akey]);
				} else {
					$theArray[$Akey] = addslashes($AVal);
				}
			}
			reset($theArray);
		}
	}

	/**
	 * StripSlash array
	 * This function traverses a multidimentional array and strips slashes to the values.
	 * NOTE that the input array is and argument by reference.!!
	 * Twin-function to addSlashesOnArray
	 * Usage: 10
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function stripSlashesOnArray(&$theArray)	{
		if (is_array($theArray))	{
			reset($theArray);
			while(list($Akey,$AVal)=each($theArray))	{
				if (is_array($AVal))	{
					t3lib_div::stripSlashesOnArray($theArray[$Akey]);
				} else {
					$theArray[$Akey] = stripslashes($AVal);
				}
			}
			reset($theArray);
		}
	}

	/**
	 * Either slashes ($cmd=add) or strips ($cmd=strip) array $arr depending on $cmd
	 * Usage: 0
	 *
	 * @param	array		Multidimensional input array
	 * @param	string		"add" or "strip", depending on usage you wish.
	 * @return	array
	 */
	function slashArray($arr,$cmd)	{
		if ($cmd=='strip')	t3lib_div::stripSlashesOnArray($arr);
		if ($cmd=='add')	t3lib_div::addSlashesOnArray($arr);
		return $arr;
	}

	/**
	 * Merges two arrays recursively and "binary safe" (integer keys are overridden as well), overruling similar values in the first array ($arr0) with the values of the second array ($arr1)
	 * In case of identical keys, ie. keeping the values of the second.
	 * Usage: 0
	 *
	 * @param	array		First array
	 * @param	array		Second array, overruling the first array
	 * @param	boolean		If set, keys that are NOT found in $arr0 (first array) will not be set. Thus only existing value can/will be overruled from second array.
	 * @param	boolean		If set, values from $arr1 will overrule if they are empty. Default: true
	 * @return	array		Resulting array where $arr1 values has overruled $arr0 values
	 */
	function array_merge_recursive_overrule($arr0,$arr1,$notAddKeys=0,$includeEmtpyValues=true) {
		reset($arr1);
		while(list($key,$val) = each($arr1)) {
			if(is_array($arr0[$key])) {
				if (is_array($arr1[$key]))	{
					$arr0[$key] = t3lib_div::array_merge_recursive_overrule($arr0[$key],$arr1[$key],$notAddKeys);
				}
			} else {
				if ($notAddKeys) {
					if (isset($arr0[$key])) {
						if ($includeEmtpyValues OR $val) {
							$arr0[$key] = $val;
						}
					}
				} else {
					if ($includeEmtpyValues OR $val) {
						$arr0[$key] = $val;
					}
				}
			}
		}
		reset($arr0);
		return $arr0;
	}

	/**
	 * An array_merge function where the keys are NOT renumbered as they happen to be with the real php-array_merge function. It is "binary safe" in the sense that integer keys are overridden as well.
	 * Usage: 16
	 *
	 * @param	array		First array
	 * @param	array		Second array
	 * @return	array		Merged result.
	 */
	function array_merge($arr1,$arr2)	{
		return $arr2+$arr1;
	}

	/**
	 * Takes a row and returns a CSV string of the values with $delim (default is ,) and $quote (default is ") as separator chars.
	 * Usage: 5
	 *
	 * @param	array		Input array of values
	 * @param	string		Delimited, default is comman
	 * @param	string		Quote-character to wrap around the values.
	 * @return	string		A single line of CSV
	 */
	function csvValues($row,$delim=',',$quote='"')	{
		reset($row);
		$out=array();
		while(list(,$value)=each($row))	{
			list($valPart) = explode(chr(10),$value);
			$valPart = trim($valPart);
			$out[]=str_replace($quote,$quote.$quote,$valPart);
		}
		$str = $quote.implode($quote.$delim.$quote,$out).$quote;
		return $str;
	}

	/**
	 * Removes dots "." from end of a key identifier of TypoScript styled array.
	 * array('key.' => array('property.' => 'value')) --> array('key' => array('property' => 'value'))
	 *
	 * @param	array	$ts: TypoScript configuration array
	 * @return	array	TypoScript configuration array without dots and the end of all keys 
	 */
	function removeDotsFromTS($ts) {
		$out = array();
		if (is_array($ts)) {
			foreach ($ts as $key => $value) {
				if (is_array($value)) {
					$key = rtrim($key, '.');
					$out[$key] = t3lib_div::removeDotsFromTS($value);
				} else {
					$out[$key] = $value;
				}
			}
		}
		return $out;
	}
















	/*************************
	 *
	 * HTML/XML PROCESSING
	 *
	 *************************/

	/**
	 * Returns an array with all attributes of the input HTML tag as key/value pairs. Attributes are only lowercase a-z
	 * $tag is either a whole tag (eg '<TAG OPTION ATTRIB=VALUE>') or the parameterlist (ex ' OPTION ATTRIB=VALUE>')
	 * If a attribute is empty (I call it 'an option'), then the value for the key is empty. You can check if it existed with isset()
	 * Usage: 8
	 *
	 * @param	string		HTML-tag string (or attributes only)
	 * @return	array		Array with the attribute values.
	 */
	function get_tag_attributes($tag)	{
		$components = t3lib_div::split_tag_attributes($tag);
		$name = '';	 // attribute name is stored here
		$valuemode = '';
		if (is_array($components))	{
			while (list($key,$val) = each ($components))	{
				if ($val != '=')	{	// Only if $name is set (if there is an attribute, that waits for a value), that valuemode is enabled. This ensures that the attribute is assigned it's value
					if ($valuemode)	{
						if ($name)	{
							$attributes[$name] = $val;
							$name = '';
						}
					} else {
						if ($key = strtolower(ereg_replace('[^a-zA-Z0-9]','',$val)))	{
							$attributes[$key] = '';
							$name = $key;
						}
					}
					$valuemode = '';
				} else {
					$valuemode = 'on';
				}
			}
			if (is_array($attributes))	reset($attributes);
			return $attributes;
		}
	}

	/**
	 * Returns an array with the 'components' from an attribute list from an HTML tag. The result is normally analyzed by get_tag_attributes
	 * Removes tag-name if found
	 * Usage: 2
	 *
	 * @param	string		HTML-tag string (or attributes only)
	 * @return	array		Array with the attribute values.
	 * @internal
	 */
	function split_tag_attributes($tag)	{
		$tag_tmp = trim(eregi_replace ('^<[^[:space:]]*','',trim($tag)));
			// Removes any > in the end of the string
		$tag_tmp = trim(eregi_replace ('>$','',$tag_tmp));

		while (strcmp($tag_tmp,''))	{	// Compared with empty string instead , 030102
			$firstChar=substr($tag_tmp,0,1);
			if (!strcmp($firstChar,'"') || !strcmp($firstChar,"'"))	{
				$reg=explode($firstChar,$tag_tmp,3);
				$value[]=$reg[1];
				$tag_tmp=trim($reg[2]);
			} elseif (!strcmp($firstChar,'=')) {
				$value[] = '=';
				$tag_tmp = trim(substr($tag_tmp,1));		// Removes = chars.
			} else {
					// There are '' around the value. We look for the next ' ' or '>'
				$reg = split('[[:space:]=]',$tag_tmp,2);
				$value[] = trim($reg[0]);
				$tag_tmp = trim(substr($tag_tmp,strlen($reg[0]),1).$reg[1]);
			}
		}
		if (is_array($value))	reset($value);
		return $value;
	}

	/**
	 * Implodes attributes in the array $arr for an attribute list in eg. and HTML tag (with quotes)
	 * Usage: 14
	 *
	 * @param	array		Array with attribute key/value pairs, eg. "bgcolor"=>"red", "border"=>0
	 * @param	boolean		If set the resulting attribute list will have a) all attributes in lowercase (and duplicates weeded out, first entry taking precedence) and b) all values htmlspecialchar()'ed. It is recommended to use this switch!
	 * @param	boolean		If true, don't check if values are blank. Default is to omit attributes with blank values.
	 * @return	string		Imploded attributes, eg. 'bgcolor="red" border="0"'
	 */
	function implodeAttributes($arr,$xhtmlSafe=FALSE,$dontOmitBlankAttribs=FALSE)	{
		if (is_array($arr))	{
			if ($xhtmlSafe)	{
				$newArr=array();
				foreach($arr as $p => $v)	{
					if (!isset($newArr[strtolower($p)])) $newArr[strtolower($p)] = htmlspecialchars($v);
				}
				$arr = $newArr;
			}
			$list = array();
			foreach($arr as $p => $v)	{
				if (strcmp($v,'') || $dontOmitBlankAttribs)	{$list[]=$p.'="'.$v.'"';}
			}
			return implode(' ',$list);
		}
	}

	/**
	 * Implodes attributes in the array $arr for an attribute list in eg. and HTML tag (with quotes)
	 *
	 * @param	array		See implodeAttributes()
	 * @param	boolean		See implodeAttributes()
	 * @param	boolean		See implodeAttributes()
	 * @return	string		See implodeAttributes()
	 * @deprecated	Name was changed into implodeAttributes
	 * @see implodeAttributes()
	 */
	function implodeParams($arr,$xhtmlSafe=FALSE,$dontOmitBlankAttribs=FALSE)	{
		return t3lib_div::implodeAttributes($arr,$xhtmlSafe,$dontOmitBlankAttribs);
	}

	/**
	 * Wraps JavaScript code XHTML ready with <script>-tags
	 * Automatic re-identing of the JS code is done by using the first line as ident reference.
	 * This is nice for identing JS code with PHP code on the same level.
	 *
	 * @param	string		JavaScript code
	 * @param	boolean		Wrap script element in linebreaks? Default is TRUE.
	 * @return	string		The wrapped JS code, ready to put into a XHTML page
	 * @author	Ingmar Schlecht <ingmars@web.de>
	 * @author	Ren� Fritz <r.fritz@colorcube.de>
	 */
	function wrapJS($string, $linebreak=TRUE) {
		if(trim($string)) {
				// <script wrapped in nl?
			$cr = $linebreak? "\n" : '';

				// remove nl from the beginning
			$string = preg_replace ('/^\n+/', '', $string);
				// re-ident to one tab using the first line as reference
			$match = array();
			if(preg_match('/^(\t+)/',$string,$match)) {
				$string = str_replace($match[1],"\t", $string);
			}
			$string = $cr.'<script type="text/javascript">
/*<![CDATA[*/
'.$string.'
/*]]>*/
</script>'.$cr;
		}
		return trim($string);
	}


	/**
	 * Parses XML input into a PHP array with associative keys
	 * Usage: 0
	 *
	 * @param	string		XML data input
	 * @param	integer		Number of element levels to resolve the XML into an array. Any further structure will be set as XML.
	 * @return	mixed		The array with the parsed structure unless the XML parser returns with an error in which case the error message string is returned.
	 * @author bisqwit at iki dot fi dot not dot for dot ads dot invalid / http://dk.php.net/xml_parse_into_struct + kasperYYYY@typo3.com
	 */
	function xml2tree($string,$depth=999) {
		$parser = xml_parser_create();
		$vals = array();
		$index = array();

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $string, $vals, $index);

		if (xml_get_error_code($parser))	return 'Line '.xml_get_current_line_number($parser).': '.xml_error_string(xml_get_error_code($parser));
		xml_parser_free($parser);

		$stack = array( array() );
		$stacktop = 0;
		$startPoint=0;

// FIXME don't use unset() - what does that mean? Use NULL or similar.
		unset($tagi);
		foreach($vals as $key => $val) {
			$type = $val['type'];

				// open tag:
			if ($type=='open' || $type=='complete') {
				$stack[$stacktop++] = $tagi;

				if ($depth==$stacktop)	{
					$startPoint=$key;
				}

				$tagi = array('tag' => $val['tag']);

				if(isset($val['attributes']))  $tagi['attrs'] = $val['attributes'];
				if(isset($val['value']))	$tagi['values'][] = $val['value'];
			}
				// finish tag:
			if ($type=='complete' || $type=='close')	{
				$oldtagi = $tagi;
				$tagi = $stack[--$stacktop];
				$oldtag = $oldtagi['tag'];
				unset($oldtagi['tag']);

				if ($depth==($stacktop+1))	{
					if ($key-$startPoint > 0)	{
						$partArray = array_slice(
							$vals,
							$startPoint+1,
							$key-$startPoint-1
						);
						#$oldtagi=array('XMLvalue'=>t3lib_div::xmlRecompileFromStructValArray($partArray));
						$oldtagi['XMLvalue']=t3lib_div::xmlRecompileFromStructValArray($partArray);
					} else {
						$oldtagi['XMLvalue']=$oldtagi['values'][0];
					}
				}

				$tagi['ch'][$oldtag][] = $oldtagi;
				unset($oldtagi);
			}
				// cdata
			if($type=='cdata') {
				$tagi['values'][] = $val['value'];
			}
		}
		return $tagi['ch'];
	}

	/**
	 * Turns PHP array into XML. See array2xml()
	 *
	 * @param	array		The input PHP array with any kind of data; text, binary, integers. Not objects though.
	 * @param	string		Alternative document tag. Default is "phparray".
	 * @param	array		Options for the compilation. See array2xml() for description.
	 * @param	string		Forced charset to prologue
	 * @return	string		An XML string made from the input content in the array.
	 * @see xml2array(),array2xml()
	 */
	function array2xml_cs($array,$docTag='phparray',$options=array(),$charset='')	{

			// Figure out charset if not given explicitly:
		if (!$charset)	{
			if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'])	{	// First priority: forceCharset! If set, this will be authoritative!
				$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
			} elseif (is_object($GLOBALS['LANG']))	{
				$charset = $GLOBALS['LANG']->charSet;	// If "LANG" is around, that will hold the current charset
			} else {
				$charset = 'iso-8859-1';	// THIS is just a hopeful guess!
			}
		}

			// Return XML:
		return '<?xml version="1.0" encoding="'.htmlspecialchars($charset).'" standalone="yes" ?>'.chr(10).
				t3lib_div::array2xml($array,'',0,$docTag,0, $options);
	}

	/**
	 * Deprecated to call directly (unless you are aware of using XML prologues)! Use "array2xml_cs" instead (which adds an XML-prologue)
	 *
	 * Converts a PHP array into an XML string.
	 * The XML output is optimized for readability since associative keys are used as tagnames.
	 * This also means that only alphanumeric characters are allowed in the tag names AND only keys NOT starting with numbers (so watch your usage of keys!). However there are options you can set to avoid this problem.
	 * Numeric keys are stored with the default tagname "numIndex" but can be overridden to other formats)
	 * The function handles input values from the PHP array in a binary-safe way; All characters below 32 (except 9,10,13) will trigger the content to be converted to a base64-string
	 * The PHP variable type of the data IS preserved as long as the types are strings, arrays, integers and booleans. Strings are the default type unless the "type" attribute is set.
	 * The output XML has been tested with the PHP XML-parser and parses OK under all tested circumstances with 4.x versions. However, with PHP5 there seems to be the need to add an XML prologue a la <?xml version="1.0" encoding="[charset]" standalone="yes" ?> - otherwise UTF-8 is assumed! Unfortunately, many times the output from this function is used without adding that prologue meaning that non-ASCII characters will break the parsing!! This suchs of course! Effectively it means that the prologue should always be prepended setting the right characterset, alternatively the system should always run as utf-8!
	 * However using MSIE to read the XML output didn't always go well: One reason could be that the character encoding is not observed in the PHP data. The other reason may be if the tag-names are invalid in the eyes of MSIE. Also using the namespace feature will make MSIE break parsing. There might be more reasons...
	 * Usage: 5
	 *
	 * @param	array		The input PHP array with any kind of data; text, binary, integers. Not objects though.
	 * @param	string		tag-prefix, eg. a namespace prefix like "T3:"
	 * @param	integer		Current recursion level. Don't change, stay at zero!
	 * @param	string		Alternative document tag. Default is "phparray".
	 * @param	integer		If greater than zero, then the number of spaces corresponding to this number is used for indenting, if less than zero - no indentation, if zero - a single chr(9) (TAB) is used
	 * @param	array		Options for the compilation. Key "useNindex" => 0/1 (boolean: whether to use "n0, n1, n2" for num. indexes); Key "useIndexTagForNum" => "[tag for numerical indexes]"; Key "useIndexTagForAssoc" => "[tag for associative indexes"; Key "parentTagMap" => array('parentTag' => 'thisLevelTag')
	 * @param	string		Stack data. Don't touch.
	 * @return	string		An XML string made from the input content in the array.
	 * @see xml2array()
	 */
	function array2xml($array,$NSprefix='',$level=0,$docTag='phparray',$spaceInd=0, $options=array(),$stackData=array())	{
			// The list of byte values which will trigger binary-safe storage. If any value has one of these char values in it, it will be encoded in base64
		$binaryChars = chr(0).chr(1).chr(2).chr(3).chr(4).chr(5).chr(6).chr(7).chr(8).
						chr(11).chr(12).chr(14).chr(15).chr(16).chr(17).chr(18).chr(19).
						chr(20).chr(21).chr(22).chr(23).chr(24).chr(25).chr(26).chr(27).chr(28).chr(29).
						chr(30).chr(31);
			// Set indenting mode:
		$indentChar = $spaceInd ? ' ' : chr(9);
		$indentN = $spaceInd>0 ? $spaceInd : 1;
		$nl = ($spaceInd >= 0 ? chr(10) : '');

			// Init output variable:
		$output='';

			// Traverse the input array
		if (is_array($array))	{
			foreach($array as $k=>$v)	{
				$attr = '';
				$tagName = $k;

					// Construct the tag name.
				if(isset($options['grandParentTagMap'][$stackData['grandParentTagName'].'/'.$stackData['parentTagName']])) {		// Use tag based on grand-parent + parent tag name
					$attr.=' index="'.htmlspecialchars($tagName).'"';
					$tagName = (string)$options['grandParentTagMap'][$stackData['grandParentTagName'].'/'.$stackData['parentTagName']];
				}elseif(isset($options['parentTagMap'][$stackData['parentTagName'].':_IS_NUM']) && t3lib_div::testInt($tagName)) {		// Use tag based on parent tag name + if current tag is numeric
					$attr.=' index="'.htmlspecialchars($tagName).'"';
					$tagName = (string)$options['parentTagMap'][$stackData['parentTagName'].':_IS_NUM'];
				}elseif(isset($options['parentTagMap'][$stackData['parentTagName'].':'.$tagName])) {		// Use tag based on parent tag name + current tag
					$attr.=' index="'.htmlspecialchars($tagName).'"';
					$tagName = (string)$options['parentTagMap'][$stackData['parentTagName'].':'.$tagName];
				} elseif(isset($options['parentTagMap'][$stackData['parentTagName']])) {		// Use tag based on parent tag name:
					$attr.=' index="'.htmlspecialchars($tagName).'"';
					$tagName = (string)$options['parentTagMap'][$stackData['parentTagName']];
				} elseif (!strcmp(intval($tagName),$tagName))	{	// If integer...;
					if ($options['useNindex']) {	// If numeric key, prefix "n"
						$tagName = 'n'.$tagName;
					} else {	// Use special tag for num. keys:
						$attr.=' index="'.$tagName.'"';
						$tagName = $options['useIndexTagForNum'] ? $options['useIndexTagForNum'] : 'numIndex';
					}
				} elseif($options['useIndexTagForAssoc']) {		// Use tag for all associative keys:
					$attr.=' index="'.htmlspecialchars($tagName).'"';
					$tagName = $options['useIndexTagForAssoc'];
				}

					// The tag name is cleaned up so only alphanumeric chars (plus - and _) are in there and not longer than 100 chars either.
				$tagName = substr(ereg_replace('[^[:alnum:]_-]','',$tagName),0,100);

					// If the value is an array then we will call this function recursively:
				if (is_array($v))	{

						// Sub elements:
					if ($options['alt_options'][$stackData['path'].'/'.$tagName])	{
						$subOptions = $options['alt_options'][$stackData['path'].'/'.$tagName];
						$clearStackPath = $subOptions['clearStackPath'];
					} else {
						$subOptions = $options;
						$clearStackPath = FALSE;
					}

					$content = $nl .
								t3lib_div::array2xml(
									$v,
									$NSprefix,
									$level+1,
									'',
									$spaceInd,
									$subOptions,
									array(
										'parentTagName' => $tagName,
										'grandParentTagName' => $stackData['parentTagName'],
										'path' => $clearStackPath ? '' : $stackData['path'].'/'.$tagName,
									)
								).
								($spaceInd >= 0 ? str_pad('',($level+1)*$indentN,$indentChar) : '');
					if ((int)$options['disableTypeAttrib']!=2)	{	// Do not set "type = array". Makes prettier XML but means that empty arrays are not restored with xml2array
						$attr.=' type="array"';
					}
				} else {	// Just a value:

						// Look for binary chars:
					if (strcspn($v,$binaryChars) != strlen($v))	{	// Go for base64 encoding if the initial segment NOT matching any binary char has the same length as the whole string!
							// If the value contained binary chars then we base64-encode it an set an attribute to notify this situation:
						$content = $nl.chunk_split(base64_encode($v));
						$attr.=' base64="1"';
					} else {
							// Otherwise, just htmlspecialchar the stuff:
						$content = htmlspecialchars($v);
						$dType = gettype($v);
						if ($dType!='string' && !$options['disableTypeAttrib'])	{ $attr.=' type="'.$dType.'"'; }
					}
				}

					// Add the element to the output string:
				$output.=($spaceInd >= 0 ? str_pad('',($level+1)*$indentN,$indentChar) : '').'<'.$NSprefix.$tagName.$attr.'>'.$content.'</'.$NSprefix.$tagName.'>'.$nl;
			}
		}

			// If we are at the outer-most level, then we finally wrap it all in the document tags and return that as the value:
		if (!$level)	{
			$output =
				'<'.$docTag.'>'.$nl.
				$output.
				'</'.$docTag.'>';
		}

		return $output;
	}

	/**
	 * Converts an XML string to a PHP array.
	 * This is the reverse function of array2xml()
	 * Usage: 17
	 *
	 * @param	string		XML content to convert into an array
	 * @param	string		The tag-prefix resolve, eg. a namespace like "T3:"
	 * @param	boolean		If set, the document tag will be set in the key "_DOCUMENT_TAG" of the output array
	 * @return	mixed		If the parsing had errors, a string with the error message is returned. Otherwise an array with the content.
	 * @see array2xml()
	 */
	function xml2array($string,$NSprefix='',$reportDocTag=FALSE) {
		global $TYPO3_CONF_VARS;

			// Create parser:
		$parser = xml_parser_create();
		$vals = array();
		$index = array();

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);

			//  PHP4 doesn't like Unicode byte order marks (BOM), so we have to check for them
			// The BOM check comes first, so that the PHP5 preg_match() below doesn't have to check for it
		if(substr($string,0,3)=="\xEF\xBB\xBF")	{
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'utf-8');
		}
			// PHP 4.x: output charset is the same as the input charset, charsets are handled transparently if not specified in xml_parser_create()
			// PHP 5.0.0 & 5.0.1: default output charset is ISO-8859-1, only ASCII, ISO-8859-1 and UTF-8 are supported!!!
			// PHP 5.0.2+: default output charset is UTF-8	, only ASCII, ISO-8859-1 and UTF-8 are supported!!!
		elseif ((double)phpversion()>=5)	{
			$match = array();
			preg_match('/^[[:space:]]*<\?xml[^>]*encoding[[:space:]]*=[[:space:]]*"([^"]*)"/',substr($string,0,200),$match);
			$theCharset = $match[1] ? $match[1] : ($TYPO3_CONF_VARS['BE']['forceCharset'] ? $TYPO3_CONF_VARS['BE']['forceCharset'] : 'iso-8859-1');
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $theCharset);  // us-ascii / utf-8 / iso-8859-1
		}

			// Parse content:
		xml_parse_into_struct($parser, $string, $vals, $index);

			// If error, return error message:
		if (xml_get_error_code($parser))	{
			return 'Line '.xml_get_current_line_number($parser).': '.xml_error_string(xml_get_error_code($parser));
		}
		xml_parser_free($parser);

			// Init vars:
		$stack = array(array());
		$stacktop = 0;
		$current=array();
		$tagName = '';
		$documentTag = '';

			// Traverse the parsed XML structure:
		foreach($vals as $key => $val) {

				// First, process the tag-name (which is used in both cases, whether "complete" or "close")
			$tagName = $val['tag'];
			if (!$documentTag)	$documentTag = $tagName;

				// Test for name space:
			$tagName = ($NSprefix && substr($tagName,0,strlen($NSprefix))==$NSprefix) ? substr($tagName,strlen($NSprefix)) : $tagName;

				// Test for numeric tag, encoded on the form "nXXX":
			$testNtag = substr($tagName,1);	// Closing tag.
			$tagName = (substr($tagName,0,1)=='n' && !strcmp(intval($testNtag),$testNtag)) ? intval($testNtag) : $tagName;

				// Test for alternative index value:
			if (strlen($val['attributes']['index']))	{ $tagName = $val['attributes']['index']; }

				// Setting tag-values, manage stack:
			switch($val['type'])	{
				case 'open':		// If open tag it means there is an array stored in sub-elements. Therefore increase the stackpointer and reset the accumulation array:
					$current[$tagName] = array();	// Setting blank place holder
					$stack[$stacktop++] = $current;
					$current = array();
				break;
				case 'close':	// If the tag is "close" then it is an array which is closing and we decrease the stack pointer.
					$oldCurrent = $current;
					$current = $stack[--$stacktop];
					end($current);	// Going to the end of array to get placeholder key, key($current), and fill in array next:
					$current[key($current)] = $oldCurrent;
					unset($oldCurrent);
				break;
				case 'complete':	// If "complete", then it's a value. If the attribute "base64" is set, then decode the value, otherwise just set it.
					if ($val['attributes']['base64'])	{
						$current[$tagName] = base64_decode($val['value']);
					} else {
						$current[$tagName] = (string)$val['value']; // Had to cast it as a string - otherwise it would be evaluate false if tested with isset()!!

							// Cast type:
						switch((string)$val['attributes']['type'])	{
							case 'integer':
								$current[$tagName] = (integer)$current[$tagName];
							break;
							case 'double':
								$current[$tagName] = (double)$current[$tagName];
							break;
							case 'boolean':
								$current[$tagName] = (bool)$current[$tagName];
							break;
							case 'array':
								$current[$tagName] = array();	// MUST be an empty array since it is processed as a value; Empty arrays would end up here because they would have no tags inside...
							break;
						}
					}
				break;
			}
		}

		if ($reportDocTag)	{
			$current[$tagName]['_DOCUMENT_TAG'] = $documentTag;
		}

			// Finally return the content of the document tag.
		return $current[$tagName];
	}

	/**
	 * This implodes an array of XML parts (made with xml_parse_into_struct()) into XML again.
	 * Usage: 2
	 *
	 * @param	array		A array of XML parts, see xml2tree
	 * @return	string		Re-compiled XML data.
	 */
	function xmlRecompileFromStructValArray($vals)	{
		$XMLcontent='';

		foreach($vals as $val) {
			$type = $val['type'];

				// open tag:
			if ($type=='open' || $type=='complete') {
				$XMLcontent.='<'.$val['tag'];
				if(isset($val['attributes']))  {
					foreach($val['attributes'] as $k => $v)	{
						$XMLcontent.=' '.$k.'="'.htmlspecialchars($v).'"';
					}
				}
				if ($type=='complete')	{
					if(isset($val['value']))	{
						$XMLcontent.='>'.htmlspecialchars($val['value']).'</'.$val['tag'].'>';
					} else $XMLcontent.='/>';
				} else $XMLcontent.='>';

				if ($type=='open' && isset($val['value']))	{
					$XMLcontent.=htmlspecialchars($val['value']);
				}
			}
				// finish tag:
			if ($type=='close')	{
				$XMLcontent.='</'.$val['tag'].'>';
			}
				// cdata
			if($type=='cdata') {
				$XMLcontent.=htmlspecialchars($val['value']);
			}
		}

		return $XMLcontent;
	}

	/**
	 * Extract the encoding scheme as found in the first line of an XML document (typically)
	 * Usage: 1
	 *
	 * @param	string		XML data
	 * @return	string		Encoding scheme (lowercase), if found.
	 */
	function xmlGetHeaderAttribs($xmlData)	{
		$xmlHeader = substr(trim($xmlData),0,200);
		$reg=array();
		if (eregi('^<\?xml([^>]*)\?\>',$xmlHeader,$reg))	{
			return t3lib_div::get_tag_attributes($reg[1]);
		}
	}











	/*************************
	 *
	 * FILES FUNCTIONS
	 *
	 *************************/

	/**
	 * Reads the file or url $url and returns the content
	 * If you are having trouble with proxys when reading URLs you can configure your way out of that with settings like $TYPO3_CONF_VARS['SYS']['curlUse'] etc.
	 * Usage: 83
	 *
	 * @param	string		Filepath/URL to read
	 * @param	integer		Whether the HTTP header should be fetched or not. 0=disable, 1=fetch header+content, 2=fetch header only
	 * @param	array		HTTP headers to be used in the request
	 * @return	string		The content from the resource given as input.
	 */
	function getURL($url, $includeHeader = 0, $requestHeaders = false)	{
		$content = false;

			// (Proxy support implemented by Arco <arco@appeltaart.mine.nu>)
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] == '1' && preg_match('/^https?:\/\//', $url))	{
				// External URL without error checking.
			$ch = curl_init();
			if (!$ch)	{
				return false;
			}

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, $includeHeader ? 1 : 0);
			curl_setopt($ch, CURLOPT_NOBODY, $includeHeader == 2 ? 1 : 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			if (is_array($requestHeaders))	{
				curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
			}

			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer'])	{
				curl_setopt($ch, CURLOPT_PROXY, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']);

					// Not sure if this is needed
				if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel'])	{
					curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyTunnel']);
				}
				if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass'])	{
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass']);
				}
			}
			$content = curl_exec($ch);
			curl_close($ch);

		} elseif ($includeHeader)	{
			$parsedURL = parse_url($url);
			if (!t3lib_div::inList('ftp,ftps,http,https,gopher,telnet', $parsedURL['scheme']))	{
				return false;
			}

			$fp = @fsockopen($parsedURL['host'], ($parsedURL['port'] > 0 ? $parsedURL['port'] : 80), $errno, $errstr, 2.0);
			if (!$fp)	{
				return false;
			}

			$msg = 'GET ' . $parsedURL['path'] .
					($parsedURL['query'] ? '?' . $parsedURL['query'] : '') .
					' HTTP/1.0' . "\r\n" . 'Host: ' .
					$parsedURL['host'] . "\r\n\r\n";
			fputs($fp, $msg);
			while (!feof($fp))	{
				$line = fgets($fp, 2048);
				$content.= $line;
				if ($includeHeader == 2 && !strlen(trim($line)))	{
					break;	// Stop at the first empty line (= end of header)
				}
			}
			fclose($fp);

		} elseif (is_array($requestHeaders) && function_exists('stream_context_create'))	{

			$ctx = stream_context_create(array(
						'http' => array(
							'header' => implode("\r\n", $requestHeaders)
						)
					)
				);

			if (function_exists('file_get_contents') && version_compare(phpversion(), '5.0', '>=')) {
				$content = @file_get_contents($url, false, $ctx);
			}
			elseif (false !== ($fd = @fopen($url, 'rb', false, $ctx)))	{
				$content = '';
				while (!feof($fd))	{
					$content.= fread($fd, 4096);
				}
				fclose($fd);
			}
		}
		elseif (function_exists('file_get_contents'))	{
			$content = @file_get_contents($url);
		}
		elseif (false !== ($fd = @fopen($url, 'rb')))	{
			$content = '';
			while (!feof($fd))	{
				$content.= fread($fd, 4096);
			}
			fclose($fd);
		}

		return $content;
	}

	/**
	 * Writes $content to the file $file
	 * Usage: 30
	 *
	 * @param	string		Filepath to write to
	 * @param	string		Content to write
	 * @return	boolean		True if the file was successfully opened and written to.
	 */
	function writeFile($file,$content)	{
		if (!@is_file($file))	$changePermissions = true;

		if ($fd = fopen($file,'wb'))	{
			$res = fwrite($fd,$content);
			fclose($fd);

			if ($res===false)	return false;

			if ($changePermissions)	{	// Change the permissions only if the file has just been created
				t3lib_div::fixPermissions($file);
			}

			return true;
		}

		return false;
	}

	/**
	 * Setting file system mode & group ownership of file
	 *
	 * @param	string		Filepath of newly created file
	 * @return	void
	 */
	function fixPermissions($file)	{
		if (@is_file($file) && TYPO3_OS!='WIN')	{
			@chmod($file, octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask']));		// "@" is there because file is not necessarily OWNED by the user
			if($GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup'])	{	// skip this if createGroup is empty
				@chgrp($file, $GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup']);		// "@" is there because file is not necessarily OWNED by the user
			}
		}
	}

	/**
	 * Writes $content to a filename in the typo3temp/ folder (and possibly one or two subfolders...)
	 * Accepts an additional subdirectory in the file path!
	 *
	 * @param	string		Absolute filepath to write to inside "typo3temp/". First part of this string must match PATH_site."typo3temp/"
	 * @param	string		Content string to write
	 * @return	string		Returns false on success, otherwise an error string telling about the problem.
	 */
	function writeFileToTypo3tempDir($filepath,$content)	{

			// Parse filepath into directory and basename:
		$fI = pathinfo($filepath);
		$fI['dirname'].= '/';

			// Check parts:
		if (t3lib_div::validPathStr($filepath) && $fI['basename'] && strlen($fI['basename'])<60)	{
			if (defined('PATH_site'))	{
				$dirName = PATH_site.'typo3temp/';	// Setting main temporary directory name (standard)
				if (@is_dir($dirName))	{
					if (t3lib_div::isFirstPartOfStr($fI['dirname'],$dirName))	{

							// Checking if the "subdir" is found:
						$subdir = substr($fI['dirname'],strlen($dirName));
						if ($subdir)	{
							if (ereg('^[[:alnum:]_]+\/$',$subdir) || ereg('^[[:alnum:]_]+\/[[:alnum:]_]+\/$',$subdir))	{
								$dirName.= $subdir;
								if (!@is_dir($dirName))	{
									t3lib_div::mkdir_deep(PATH_site.'typo3temp/', $subdir);
								}
							} else return 'Subdir, "'.$subdir.'", was NOT on the form "[[:alnum:]_]/" or  "[[:alnum:]_]/[[:alnum:]_]/"';
						}
							// Checking dir-name again (sub-dir might have been created):
						if (@is_dir($dirName))	{
							if ($filepath == $dirName.$fI['basename'])	{
								t3lib_div::writeFile($filepath, $content);
								if (!@is_file($filepath))	return 'File not written to disk! Write permission error in filesystem?';
							} else return 'Calculated filelocation didn\'t match input $filepath!';
						} else return '"'.$dirName.'" is not a directory!';
					} else return '"'.$fI['dirname'].'" was not within directory PATH_site + "typo3temp/"';
				} else return 'PATH_site + "typo3temp/" was not a directory!';
			} else return 'PATH_site constant was NOT defined!';
		} else return 'Input filepath "'.$filepath.'" was generally invalid!';
	}

	/**
	 * Wrapper function for mkdir, setting folder permissions according to $GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'] and group ownership according to $GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup']
	 * Usage: 6
	 *
	 * @param	string		Absolute path to folder, see PHP mkdir() function. Removes trailing slash internally.
	 * @return	boolean		TRUE if @mkdir went well!
	 */
	function mkdir($theNewFolder)	{
		$theNewFolder = preg_replace('|/$|','',$theNewFolder);
		if (mkdir($theNewFolder, octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask']))){
			chmod($theNewFolder, octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask'])); //added this line, because the mode at 'mkdir' has a strange behaviour sometimes

			if($GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup'])	{	// skip this if createGroup is empty
				chgrp($theNewFolder, $GLOBALS['TYPO3_CONF_VARS']['BE']['createGroup']);
			}
			return TRUE;
		}
	}

	/**
	 * Creates a directory - including parent directories if necessary - in the file system
	 *
	 * @param	string		Base folder. This must exist! Must have trailing slash! Example "/root/typo3site/"
	 * @param	string		Deep directory to create, eg. "xx/yy/" which creates "/root/typo3site/xx/yy/" if $destination is "/root/typo3site/"
	 * @return	string		If error, returns error string.
	 */
	function mkdir_deep($destination,$deepDir)	{
		$allParts = t3lib_div::trimExplode('/',$deepDir,1);
		$root = '';
		foreach($allParts as $part)	{
			$root.= $part.'/';
			if (!is_dir($destination.$root))	{
				t3lib_div::mkdir($destination.$root);
				if (!@is_dir($destination.$root))	{
					return 'Error: The directory "'.$destination.$root.'" could not be created...';
				}
			}
		}
	}

	/**
	 * Returns an array with the names of folders in a specific path
	 * Will return 'error' (string) if there were an error with reading directory content.
	 * Usage: 11
	 *
	 * @param	string		Path to list directories from
	 * @return	array		Returns an array with the directory entries as values. If no path, the return value is nothing.
	 */
	function get_dirs($path)	{
		if ($path)	{
			$d = @dir($path);
			if (is_object($d))	{
				while($entry=$d->read()) {
					if (@is_dir($path.'/'.$entry) && $entry!= '..' && $entry!= '.')	{
					    $filearray[]=$entry;
					}
				}
				$d->close();
			} else return 'error';
			return $filearray;
		}
	}

	/**
	 * Returns an array with the names of files in a specific path
	 * Usage: 18
	 *
	 * @param	string		$path: Is the path to the file
	 * @param	string		$extensionList is the comma list of extensions to read only (blank = all)
	 * @param	boolean		If set, then the path is prepended the filenames. Otherwise only the filenames are returned in the array
	 * @param	string		$order is sorting: 1= sort alphabetically, 'mtime' = sort by modification time.
	 * @param	string		A comma seperated list of filenames to exclude, no wildcards
	 * @return	array		Array of the files found
	 */
	function getFilesInDir($path,$extensionList='',$prependPath=0,$order='',$excludePattern='')	{

			// Initialize variabels:
		$filearray = array();
		$sortarray = array();
		$path = ereg_replace('\/$','',$path);

			// Find files+directories:
		if (@is_dir($path))	{
			$extensionList = strtolower($extensionList);
			$d = dir($path);
			if (is_object($d))	{
				while($entry=$d->read()) {
					if (@is_file($path.'/'.$entry))	{
						$fI = pathinfo($entry);
						$key = md5($path.'/'.$entry);	// Don't change this ever - extensions may depend on the fact that the hash is an md5 of the path! (import/export extension)
						if ((!strlen($extensionList) || t3lib_div::inList($extensionList,strtolower($fI['extension']))) && (!strlen($excludePattern) || !preg_match('/^'.$excludePattern.'$/',$entry)))	{
						    $filearray[$key]=($prependPath?$path.'/':'').$entry;
								if ($order=='mtime') {$sortarray[$key]=filemtime($path.'/'.$entry);}
								elseif ($order)	{$sortarray[$key]=$entry;}
						}
					}
				}
				$d->close();
			} else return 'error opening path: "'.$path.'"';
		}

			// Sort them:
		if ($order) {
			asort($sortarray);
			reset($sortarray);
			$newArr=array();
			while(list($k,$v)=each($sortarray))	{
				$newArr[$k]=$filearray[$k];
			}
			$filearray=$newArr;
		}

			// Return result
		reset($filearray);
		return $filearray;
	}

	/**
	 * Recursively gather all files and folders of a path.
	 * Usage: 5
	 *
	 * @param	array		$fileArr: Empty input array (will have files added to it)
	 * @param	string		$path: The path to read recursively from (absolute) (include trailing slash!)
	 * @param	string		$extList: Comma list of file extensions: Only files with extensions in this list (if applicable) will be selected.
	 * @param	boolean		$regDirs: If set, directories are also included in output.
	 * @param	integer		$recursivityLevels: The number of levels to dig down...
	 * @param string		$excludePattern: regex pattern of files/directories to exclude
	 * @return	array		An array with the found files/directories.
	 */
	function getAllFilesAndFoldersInPath($fileArr,$path,$extList='',$regDirs=0,$recursivityLevels=99,$excludePattern='')	{
		if ($regDirs)	$fileArr[] = $path;
		$fileArr = array_merge($fileArr, t3lib_div::getFilesInDir($path,$extList,1,1,$excludePattern));

		$dirs = t3lib_div::get_dirs($path);
		if (is_array($dirs) && $recursivityLevels>0)	{
			foreach ($dirs as $subdirs)	{
				if ((string)$subdirs!='' && (!strlen($excludePattern) || !preg_match('/^'.$excludePattern.'$/',$subdirs)))	{
					$fileArr = t3lib_div::getAllFilesAndFoldersInPath($fileArr,$path.$subdirs.'/',$extList,$regDirs,$recursivityLevels-1,$excludePattern);
				}
			}
		}
		return $fileArr;
	}

	/**
	 * Removes the absolute part of all files/folders in fileArr
	 * Usage: 2
	 *
	 * @param	array		$fileArr: The file array to remove the prefix from
	 * @param	string		$prefixToRemove: The prefix path to remove (if found as first part of string!)
	 * @return	array		The input $fileArr processed.
	 */
	function removePrefixPathFromList($fileArr,$prefixToRemove)	{
		foreach($fileArr as $k => $absFileRef)	{
			if(t3lib_div::isFirstPartOfStr($absFileRef,$prefixToRemove))	{
				$fileArr[$k] = substr($absFileRef,strlen($prefixToRemove));
			} else return 'ERROR: One or more of the files was NOT prefixed with the prefix-path!';
		}
		return $fileArr;
	}

	/**
	 * Fixes a path for windows-backslashes and reduces double-slashes to single slashes
	 * Usage: 2
	 *
	 * @param	string		File path to process
	 * @return	string
	 */
	function fixWindowsFilePath($theFile)	{
		return str_replace('//','/', str_replace('\\','/', $theFile));
	}

	/**
	 * Resolves "../" sections in the input path string.
	 * For example "fileadmin/directory/../other_directory/" will be resolved to "fileadmin/other_directory/"
	 * Usage: 2
	 *
	 * @param	string		File path in which "/../" is resolved
	 * @return	string
	 */
	function resolveBackPath($pathStr)	{
		$parts = explode('/',$pathStr);
		$output=array();
		$c = 0;
		foreach($parts as $pV)	{
			if ($pV=='..')	{
				if ($c)	{
					array_pop($output);
					$c--;
				} else $output[]=$pV;
			} else {
				$c++;
				$output[]=$pV;
			}
		}
		return implode('/',$output);
	}

	/**
	 * Prefixes a URL used with 'header-location' with 'http://...' depending on whether it has it already.
	 * - If already having a scheme, nothing is prepended
	 * - If having REQUEST_URI slash '/', then prefixing 'http://[host]' (relative to host)
	 * - Otherwise prefixed with TYPO3_REQUEST_DIR (relative to current dir / TYPO3_REQUEST_DIR)
	 * Usage: 30
	 *
	 * @param	string		URL / path to prepend full URL addressing to.
	 * @return	string
	 */
	function locationHeaderUrl($path)	{
		$uI = parse_url($path);
		if (substr($path,0,1)=='/')	{ // relative to HOST
			$path = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').$path;
		} elseif (!$uI['scheme'])	{ // No scheme either
			$path = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR').$path;
		}
		return $path;
	}
















	/*************************
	 *
	 * DEBUG helper FUNCTIONS
	 *
	 *************************/

	/**
	 * Returns a string with a list of ascii-values for the first $characters characters in $string
	 * Usage: 0
	 *
	 * @param	string		String to show ASCII value for
	 * @param	integer		Number of characters to show
	 * @return	string		The string with ASCII values in separated by a space char.
	 * @internal
	 */
	function debug_ordvalue($string,$characters=100)	{
		if(strlen($string) < $characters)	$characters = strlen($string);
		for ($i=0; $i<$characters; $i++)	{
			$valuestring.=' '.ord(substr($string,$i,1));
		}
		return trim($valuestring);
	}

	/**
	 * Returns HTML-code, which is a visual representation of a multidimensional array
	 * use t3lib_div::print_array() in order to print an array
	 * Returns false if $array_in is not an array
	 * Usage: 31
	 *
	 * @param	array		Array to view
	 * @return	string		HTML output
	 */
	function view_array($array_in)	{
		if (is_array($array_in))	{
			$result='<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">';
			if (!count($array_in))	{$result.= '<tr><td><font face="Verdana,Arial" size="1"><b>'.htmlspecialchars("EMPTY!").'</b></font></td></tr>';}
			while (list($key,$val)=each($array_in))	{
				$result.= '<tr><td valign="top"><font face="Verdana,Arial" size="1">'.htmlspecialchars((string)$key).'</font></td><td>';
				if (is_array($array_in[$key]))	{
					$result.=t3lib_div::view_array($array_in[$key]);
				} else
					$result.= '<font face="Verdana,Arial" size="1" color="red">'.nl2br(htmlspecialchars((string)$val)).'<br /></font>';
				$result.= '</td></tr>';
			}
			$result.= '</table>';
		} else	{
			$result  = '<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">
				<tr>
					<td><font face="Verdana,Arial" size="1" color="red">'.nl2br(htmlspecialchars((string)$array_in)).'<br /></font></td>
				</tr>
			</table>';	// Output it as a string.
		}
		return $result;
	}

	/**
	 * Prints an array
	 * Usage: 6
	 *
	 * @param	array		Array to print visually (in a table).
	 * @return	void
	 * @internal
	 * @see view_array()
	 */
	function print_array($array_in)	{
		echo t3lib_div::view_array($array_in);
	}

	/**
	 * Makes debug output
	 * Prints $var in bold between two vertical lines
	 * If not $var the word 'debug' is printed
	 * If $var is an array, the array is printed by t3lib_div::print_array()
	 * Usage: 8
	 *
	 * @param	mixed		Variable to print
	 * @param	mixed		If the parameter is a string it will be used as header. Otherwise number of break tags to apply after (positive integer) or before (negative integer) the output.
	 * @return	void
	 */
	function debug($var="",$brOrHeader=0)	{
		if ($brOrHeader && !t3lib_div::testInt($brOrHeader))	{
			echo '<table class="typo3-debug" border="0" cellpadding="0" cellspacing="0" bgcolor="white" style="border:0px; margin-top:3px; margin-bottom:3px;"><tr><td style="background-color:#bbbbbb; font-family: verdana,arial; font-weight: bold; font-size: 10px;">'.htmlspecialchars((string)$brOrHeader).'</td></tr><tr><td>';
		} elseif ($brOrHeader<0)	{
			for($a=0;$a<abs(intval($brOrHeader));$a++){echo '<br />';}
		}

		if (is_array($var))	{
			t3lib_div::print_array($var);
		} elseif (is_object($var))	{
			echo '<b>|Object:<pre>';
			print_r($var);
			echo '</pre>|</b>';
		} elseif ((string)$var!='')	{
			echo '<b>|'.htmlspecialchars((string)$var).'|</b>';
		} else {
			echo '<b>| debug |</b>';
		}

		if ($brOrHeader && !t3lib_div::testInt($brOrHeader))	{
			echo '</td></tr></table>';
		} elseif ($brOrHeader>0)	{
			for($a=0;$a<intval($brOrHeader);$a++){echo '<br />';}
		}
	}

	/**
	 * Displays the "path" of the function call stack in a string, using debug_backtrace
	 *
	 * @return	string
	 */
	function debug_trail()	{
		if (function_exists('debug_backtrace'))	{
			$trail = debug_backtrace();
			$trail = array_reverse($trail);
			array_pop($trail);

			$path = array();
			foreach($trail as $dat)	{
				$path[] = $dat['class'].$dat['type'].$dat['function'];
			}

			return implode(' // ',$path);
		} else return 'N/A';
	}

	/**
	 * Displays an array as rows in a table. Useful to debug output like an array of database records.
	 *
	 * @param	array		Array of arrays with similar keys
	 * @param	string		Table header
	 * @return	void		Outputs to browser.
	 */
	function debugRows($rows,$header='')	{
		if (is_array($rows))	{
			reset($rows);
			$firstEl = current($rows);
			if (is_array($firstEl))	{
				$headerColumns = array_keys($firstEl);
				$tRows = array();

					// Header:
				$tRows[] = '<tr><td colspan="'.count($headerColumns).'" style="background-color:#bbbbbb; font-family: verdana,arial; font-weight: bold; font-size: 10px;"><strong>'.htmlspecialchars($header).'</strong></td></tr>';
				$tCells = array();
				foreach($headerColumns as $key)	{
					$tCells[] = '
							<td><font face="Verdana,Arial" size="1"><strong>'.htmlspecialchars($key).'</strong></font></td>';
				}
				$tRows[] = '
						<tr>'.implode('',$tCells).'
						</tr>';

					// Rows:
				foreach($rows as $singleRow)	{
					$tCells = array();
					foreach($headerColumns as $key)	{
						$tCells[] = '
							<td><font face="Verdana,Arial" size="1">'.htmlspecialchars($singleRow[$key]).'</font></td>';
					}
					$tRows[] = '
						<tr>'.implode('',$tCells).'
						</tr>';
				}

				$table = '
					<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">'.implode('',$tRows).'
					</table>';
				echo $table;
			} else debug('Empty array of rows',$header);
		} else debug('No array of rows',$header);
	}




























	/*************************
	 *
	 * SYSTEM INFORMATION
	 *
	 *************************/

	/**
	 * Returns the HOST+DIR-PATH of the current script (The URL, but without 'http://' and without script-filename)
	 * Usage: 1
	 *
	 * @return	string
	 */
	function getThisUrl()	{
		$p=parse_url(t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT'));		// Url of this script
		$dir=t3lib_div::dirname($p['path']).'/';	// Strip file
		$url = str_replace('//','/',$p['host'].($p['port']?':'.$p['port']:'').$dir);
		return $url;
	}

	/**
	 * Returns the link-url to the current script.
	 * In $getParams you can set associative keys corresponding to the GET-vars you wish to add to the URL. If you set them empty, they will remove existing GET-vars from the current URL.
	 * REMEMBER to always use htmlspecialchars() for content in href-properties to get ampersands converted to entities (XHTML requirement and XSS precaution)
	 * Usage: 52
	 *
	 * @param	array		Array of GET parameters to include
	 * @return	string
	 */
	function linkThisScript($getParams=array())	{
		$parts = t3lib_div::getIndpEnv('SCRIPT_NAME');
		$params = t3lib_div::_GET();

		foreach($getParams as $k => $v)	{
			if (strcmp($v,''))	{
				$params[$k]=$v;
			} else unset($params[$k]);
		}

		$pString = t3lib_div::implodeArrayForUrl('',$params);

		return $pString ? $parts.'?'.ereg_replace('^&','',$pString) : $parts;
	}

	/**
	 * Takes a full URL, $url, possibly with a querystring and overlays the $getParams arrays values onto the quirystring, packs it all together and returns the URL again.
	 * So basically it adds the parameters in $getParams to an existing URL, $url
	 * Usage: 2
	 *
	 * @param	string		URL string
	 * @param	array		Array of key/value pairs for get parameters to add/overrule with. Can be multidimensional.
	 * @return	string		Output URL with added getParams.
	 */
	function linkThisUrl($url,$getParams=array())	{
		$parts = parse_url($url);
		$getP = array();
		if ($parts['query'])	{
			parse_str($parts['query'],$getP);
		}
		$getP = t3lib_div::array_merge_recursive_overrule($getP,$getParams);
		$uP = explode('?',$url);

		$params = t3lib_div::implodeArrayForUrl('',$getP);
		$outurl = $uP[0].($params ? '?'.substr($params, 1) : '');

		return $outurl;
	}

	/**
	 * Abstraction method which returns System Environment Variables regardless of server OS, CGI/MODULE version etc. Basically this is SERVER variables for most of them.
	 * This should be used instead of getEnv() and $_SERVER/ENV_VARS to get reliable values for all situations.
	 * Usage: 221
	 *
	 * @param	string		Name of the "environment variable"/"server variable" you wish to use. Valid values are SCRIPT_NAME, SCRIPT_FILENAME, REQUEST_URI, PATH_INFO, REMOTE_ADDR, REMOTE_HOST, HTTP_REFERER, HTTP_HOST, HTTP_USER_AGENT, HTTP_ACCEPT_LANGUAGE, QUERY_STRING, TYPO3_DOCUMENT_ROOT, TYPO3_HOST_ONLY, TYPO3_HOST_ONLY, TYPO3_REQUEST_HOST, TYPO3_REQUEST_URL, TYPO3_REQUEST_SCRIPT, TYPO3_REQUEST_DIR, TYPO3_SITE_URL, _ARRAY
	 * @return	string		Value based on the input key, independent of server/os environment.
	 */
	function getIndpEnv($getEnvName)	{
		/*
			Conventions:
			output from parse_url():
			URL:	http://username:password@192.168.1.4:8080/typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/?arg1,arg2,arg3&p1=parameter1&p2[key]=value#link1
			    [scheme] => 'http'
			    [user] => 'username'
			    [pass] => 'password'
			    [host] => '192.168.1.4'
				[port] => '8080'
			    [path] => '/typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/'
			    [query] => 'arg1,arg2,arg3&p1=parameter1&p2[key]=value'
			    [fragment] => 'link1'

				Further definition: [path_script] = '/typo3/32/temp/phpcheck/index.php'
									[path_dir] = '/typo3/32/temp/phpcheck/'
									[path_info] = '/arg1/arg2/arg3/'
									[path] = [path_script/path_dir][path_info]


			Keys supported:

			URI______:
				REQUEST_URI		=	[path]?[query]		= /typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/?arg1,arg2,arg3&p1=parameter1&p2[key]=value
				HTTP_HOST		=	[host][:[port]]		= 192.168.1.4:8080
				SCRIPT_NAME		=	[path_script]++		= /typo3/32/temp/phpcheck/index.php		// NOTICE THAT SCRIPT_NAME will return the php-script name ALSO. [path_script] may not do that (eg. '/somedir/' may result in SCRIPT_NAME '/somedir/index.php')!
				PATH_INFO		=	[path_info]			= /arg1/arg2/arg3/
				QUERY_STRING	=	[query]				= arg1,arg2,arg3&p1=parameter1&p2[key]=value
				HTTP_REFERER	=	[scheme]://[host][:[port]][path]	= http://192.168.1.4:8080/typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/?arg1,arg2,arg3&p1=parameter1&p2[key]=value
										(Notice: NO username/password + NO fragment)

			CLIENT____:
				REMOTE_ADDR		=	(client IP)
				REMOTE_HOST		=	(client host)
				HTTP_USER_AGENT	=	(client user agent)
				HTTP_ACCEPT_LANGUAGE	= (client accept language)

			SERVER____:
				SCRIPT_FILENAME	=	Absolute filename of script		(Differs between windows/unix). On windows 'C:\\blabla\\blabl\\' will be converted to 'C:/blabla/blabl/'

			Special extras:
				TYPO3_HOST_ONLY =		[host] = 192.168.1.4
				TYPO3_PORT =			[port] = 8080 (blank if 80, taken from host value)
				TYPO3_REQUEST_HOST = 	[scheme]://[host][:[port]]
				TYPO3_REQUEST_URL =		[scheme]://[host][:[port]][path]?[query] (scheme will by default be "http" until we can detect something different)
				TYPO3_REQUEST_SCRIPT =  [scheme]://[host][:[port]][path_script]
				TYPO3_REQUEST_DIR =		[scheme]://[host][:[port]][path_dir]
				TYPO3_SITE_URL = 		[scheme]://[host][:[port]][path_dir] of the TYPO3 website frontend
				TYPO3_SITE_SCRIPT = 	[script / Speaking URL] of the TYPO3 website
				TYPO3_DOCUMENT_ROOT =	Absolute path of root of documents: TYPO3_DOCUMENT_ROOT.SCRIPT_NAME = SCRIPT_FILENAME (typically)
				TYPO3_SSL = 			Returns TRUE if this session uses SSL (HTTPS)

			Notice: [fragment] is apparently NEVER available to the script!


			Testing suggestions:
			- Output all the values.
			- In the script, make a link to the script it self, maybe add some parameters and click the link a few times so HTTP_REFERER is seen
			- ALSO TRY the script from the ROOT of a site (like 'http://www.mytest.com/' and not 'http://www.mytest.com/test/' !!)

		*/

#		if ($getEnvName=='HTTP_REFERER')	return '';

		$retVal = '';

		switch ((string)$getEnvName)	{
			case 'SCRIPT_NAME':
				$retVal = (php_sapi_name()=='cgi'||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) ? ($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) : ($_SERVER['ORIG_SCRIPT_NAME']?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME']);
			break;
			case 'SCRIPT_FILENAME':
				$retVal = str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME'])));
			break;
			case 'REQUEST_URI':
					// Typical application of REQUEST_URI is return urls, forms submitting to itself etc. Example: returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))
				if (!$_SERVER['REQUEST_URI'])	{	// This is for ISS/CGI which does not have the REQUEST_URI available.
					$retVal = '/'.ereg_replace('^/','',t3lib_div::getIndpEnv('SCRIPT_NAME')).
						($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:'');
				} else $retVal = $_SERVER['REQUEST_URI'];
			break;
			case 'PATH_INFO':
					// $_SERVER['PATH_INFO']!=$_SERVER['SCRIPT_NAME'] is necessary because some servers (Windows/CGI) are seen to set PATH_INFO equal to script_name
					// Further, there must be at least one '/' in the path - else the PATH_INFO value does not make sense.
					// IF 'PATH_INFO' never works for our purpose in TYPO3 with CGI-servers, then 'php_sapi_name()=='cgi'' might be a better check. Right now strcmp($_SERVER['PATH_INFO'],t3lib_div::getIndpEnv('SCRIPT_NAME')) will always return false for CGI-versions, but that is only as long as SCRIPT_NAME is set equal to PATH_INFO because of php_sapi_name()=='cgi' (see above)
//				if (strcmp($_SERVER['PATH_INFO'],t3lib_div::getIndpEnv('SCRIPT_NAME')) && count(explode('/',$_SERVER['PATH_INFO']))>1)	{
				if (php_sapi_name()!='cgi'&&php_sapi_name()!='cgi-fcgi')	{
					$retVal = $_SERVER['PATH_INFO'];
				}
			break;
				// These are let through without modification
			case 'REMOTE_ADDR':
			case 'REMOTE_HOST':
			case 'HTTP_REFERER':
			case 'HTTP_HOST':
			case 'HTTP_USER_AGENT':
			case 'HTTP_ACCEPT_LANGUAGE':
			case 'QUERY_STRING':
				$retVal = $_SERVER[$getEnvName];
			break;
			case 'TYPO3_DOCUMENT_ROOT':
				// Some CGI-versions (LA13CGI) and mod-rewrite rules on MODULE versions will deliver a 'wrong' DOCUMENT_ROOT (according to our description). Further various aliases/mod_rewrite rules can disturb this as well.
				// Therefore the DOCUMENT_ROOT is now always calculated as the SCRIPT_FILENAME minus the end part shared with SCRIPT_NAME.
				$SFN = t3lib_div::getIndpEnv('SCRIPT_FILENAME');
				$SN_A = explode('/',strrev(t3lib_div::getIndpEnv('SCRIPT_NAME')));
				$SFN_A = explode('/',strrev($SFN));
				$acc = array();
				while(list($kk,$vv)=each($SN_A))	{
					if (!strcmp($SFN_A[$kk],$vv))	{
						$acc[] = $vv;
					} else break;
				}
				$commonEnd=strrev(implode('/',$acc));
				if (strcmp($commonEnd,''))	{ $DR = substr($SFN,0,-(strlen($commonEnd)+1)); }
				$retVal = $DR;
			break;
			case 'TYPO3_HOST_ONLY':
				$p = explode(':',$_SERVER['HTTP_HOST']);
				$retVal = $p[0];
			break;
			case 'TYPO3_PORT':
				$p = explode(':',$_SERVER['HTTP_HOST']);
				$retVal = $p[1];
			break;
			case 'TYPO3_REQUEST_HOST':
				$retVal = (t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://').
					$_SERVER['HTTP_HOST'];
			break;
			case 'TYPO3_REQUEST_URL':
				$retVal = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').t3lib_div::getIndpEnv('REQUEST_URI');
			break;
			case 'TYPO3_REQUEST_SCRIPT':
				$retVal = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').t3lib_div::getIndpEnv('SCRIPT_NAME');
			break;
			case 'TYPO3_REQUEST_DIR':
				$retVal = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').t3lib_div::dirname(t3lib_div::getIndpEnv('SCRIPT_NAME')).'/';
			break;
			case 'TYPO3_SITE_URL':
				if (defined('PATH_thisScript') && defined('PATH_site'))	{
					$lPath = substr(dirname(PATH_thisScript),strlen(PATH_site)).'/';
					$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
					$siteUrl = substr($url,0,-strlen($lPath));
					if (substr($siteUrl,-1)!='/')	$siteUrl.='/';
					$retVal = $siteUrl;
				}
			break;
			case 'TYPO3_SITE_SCRIPT':
				$retVal = substr(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),strlen(t3lib_div::getIndpEnv('TYPO3_SITE_URL')));
			break;
			case 'TYPO3_SSL':
				$retVal = $_SERVER['SSL_SESSION_ID'] || !strcmp($_SERVER['HTTPS'],'on') || !strcmp($_SERVER['HTTPS'],'1') ? TRUE : FALSE;	// see http://bugs.typo3.org/view.php?id=3909
			break;
			case '_ARRAY':
				$out = array();
					// Here, list ALL possible keys to this function for debug display.
				$envTestVars = t3lib_div::trimExplode(',','
					HTTP_HOST,
					TYPO3_HOST_ONLY,
					TYPO3_PORT,
					PATH_INFO,
					QUERY_STRING,
					REQUEST_URI,
					HTTP_REFERER,
					TYPO3_REQUEST_HOST,
					TYPO3_REQUEST_URL,
					TYPO3_REQUEST_SCRIPT,
					TYPO3_REQUEST_DIR,
					TYPO3_SITE_URL,
					TYPO3_SITE_SCRIPT,
					TYPO3_SSL,
					SCRIPT_NAME,
					TYPO3_DOCUMENT_ROOT,
					SCRIPT_FILENAME,
					REMOTE_ADDR,
					REMOTE_HOST,
					HTTP_USER_AGENT,
					HTTP_ACCEPT_LANGUAGE',1);
				reset($envTestVars);
				while(list(,$v)=each($envTestVars))	{
					$out[$v]=t3lib_div::getIndpEnv($v);
				}
				reset($out);
				$retVal = $out;
			break;
		}
		return $retVal;
	}

	/**
	 * milliseconds
	 * microtime recalculated to t3lib_div::milliseconds(1/1000 sec)
	 * Usage: 20
	 *
	 * @return	integer
	 */
	function milliseconds()	{
		$p=explode(' ',microtime());
		return round(($p[0]+$p[1])*1000);
	}

	/**
	 * Client Browser Information
	 * Usage: 4
	 *
	 * @param	string		Alternative User Agent string (if empty, t3lib_div::getIndpEnv('HTTP_USER_AGENT') is used)
	 * @return	array		Parsed information about the HTTP_USER_AGENT in categories BROWSER, VERSION, SYSTEM and FORMSTYLE
	 */
	function clientInfo($useragent='')	{
		if (!$useragent) $useragent=t3lib_div::getIndpEnv('HTTP_USER_AGENT');

		$bInfo=array();
			// Which browser?
		if (strstr($useragent,'Konqueror'))	{
			$bInfo['BROWSER']= 'konqu';
		} elseif (strstr($useragent,'Opera'))	{
			$bInfo['BROWSER']= 'opera';
		} elseif (preg_match('/MSIE [4567]/', $useragent))	{
			$bInfo['BROWSER']= 'msie';
		} elseif (strstr($useragent,'Mozilla/4') || strstr($useragent,'Mozilla/5'))	{
			$bInfo['BROWSER']='net';
		}
		if ($bInfo['BROWSER'])	{
				// Browser version
			switch($bInfo['BROWSER'])	{
				case 'net':
					$bInfo['VERSION']= doubleval(substr($useragent,8));
					if (strstr($useragent,'Netscape6/')) {$bInfo['VERSION']=doubleval(substr(strstr($useragent,'Netscape6/'),10));}
					if (strstr($useragent,'Netscape/7')) {$bInfo['VERSION']=doubleval(substr(strstr($useragent,'Netscape/7'),9));}
				break;
				case 'msie':
					$tmp = strstr($useragent,'MSIE');
					$bInfo['VERSION'] = doubleval(ereg_replace('^[^0-9]*','',substr($tmp,4)));
				break;
				case 'opera':
					$tmp = strstr($useragent,'Opera');
					$bInfo['VERSION'] = doubleval(ereg_replace('^[^0-9]*','',substr($tmp,5)));
				break;
				case 'konqu':
					$tmp = strstr($useragent,'Konqueror/');
					$bInfo['VERSION'] = doubleval(substr($tmp,10));
				break;
			}
				// Client system
			if (strstr($useragent,'Win'))	{
				$bInfo['SYSTEM'] = 'win';
			} elseif (strstr($useragent,'Mac'))	{
				$bInfo['SYSTEM'] = 'mac';
			} elseif (strstr($useragent,'Linux') || strstr($useragent,'X11') || strstr($useragent,'SGI') || strstr($useragent,' SunOS ') || strstr($useragent,' HP-UX '))	{
				$bInfo['SYSTEM'] = 'unix';
			}
		}
			// Is true if the browser supports css to format forms, especially the width
		$bInfo['FORMSTYLE']=($bInfo['BROWSER']=='msie' || ($bInfo['BROWSER']=='net'&&$bInfo['VERSION']>=5) || $bInfo['BROWSER']=='opera' || $bInfo['BROWSER']=='konqu');

		return $bInfo;
	}

	/**
	 * Get the fully-qualified domain name of the host.
	 * Usage: 2
	 *
	 * @param	boolean		Use request host (when not in CLI mode).
	 * @return	string		The fully-qualified host name.
	 */
	function getHostname($requestHost=TRUE)	{
		$host = '';
		if ($requestHost && (!defined('TYPO3_cliMode') || !TYPO3_cliMode))	{
			$host = $_SERVER['HTTP_HOST'];
		}
		if (!$host)	{
				// will fail for PHP 4.1 and 4.2
			$host = @php_uname('n');
				// 'n' is ignored in broken installations
			if (strpos($host, ' '))	$host = '';
		}
			// we have not found a FQDN yet
		if ($host && strpos('.',$host) === FALSE)	{
			$ip = gethostbyname($host);
				// we got an IP address
			if ($ip != $host)	{
				$fqdn = gethostbyaddr($ip);
				if ($ip != $fqdn)	$host = $fqdn;
			}
		}
		if (!$host)	$host = 'localhost.localdomain';

		return $host;
	}






















	/*************************
	 *
	 * TYPO3 SPECIFIC FUNCTIONS
	 *
	 *************************/

	/**
	 * Returns the absolute filename of a relative reference, resolves the "EXT:" prefix (way of referring to files inside extensions) and checks that the file is inside the PATH_site of the TYPO3 installation and implies a check with t3lib_div::validPathStr(). Returns false if checks failed. Does not check if the file exists.
	 * Usage: 24
	 *
	 * @param	string		The input filename/filepath to evaluate
	 * @param	boolean		If $onlyRelative is set (which it is by default), then only return values relative to the current PATH_site is accepted.
	 * @param	boolean		If $relToTYPO3_mainDir is set, then relative paths are relative to PATH_typo3 constant - otherwise (default) they are relative to PATH_site
	 * @return	string		Returns the absolute filename of $filename IF valid, otherwise blank string.
	 */
	function getFileAbsFileName($filename,$onlyRelative=1,$relToTYPO3_mainDir=0)	{
		if (!strcmp($filename,''))		return '';

		if ($relToTYPO3_mainDir)	{
			if (!defined('PATH_typo3'))	return '';
			$relPathPrefix = PATH_typo3;
		} else {
			$relPathPrefix = PATH_site;
		}
		if (substr($filename,0,4)=='EXT:')	{	// extension
			list($extKey,$local) = explode('/',substr($filename,4),2);
			$filename='';
			if (strcmp($extKey,'') && t3lib_extMgm::isLoaded($extKey) && strcmp($local,''))	{
				$filename = t3lib_extMgm::extPath($extKey).$local;
			}
		} elseif (!t3lib_div::isAbsPath($filename))	{	// relative. Prepended with $relPathPrefix
			$filename=$relPathPrefix.$filename;
		} elseif ($onlyRelative && !t3lib_div::isFirstPartOfStr($filename,$relPathPrefix)) {	// absolute, but set to blank if not allowed
			$filename='';
		}
		if (strcmp($filename,'') && t3lib_div::validPathStr($filename))	{	// checks backpath.
			return $filename;
		}
	}

	/**
	 * Checks for malicious file paths.
	 * Returns true if no '//', '..' or '\' is in the $theFile
	 * This should make sure that the path is not pointing 'backwards' and further doesn't contain double/back slashes.
	 * So it's compatible with the UNIX style path strings valid for TYPO3 internally.
	 * Usage: 14
	 *
	 * @param	string		Filepath to evaluate
	 * @return	boolean		True, if no '//', '\', '/../' is in the $theFile and $theFile doesn't begin with '../'
	 * @todo	Possible improvement: Should it rawurldecode the string first to check if any of these characters is encoded ?
	 */
	function validPathStr($theFile)	{
		if (!strstr($theFile,'//') && !strstr($theFile,'\\') && !preg_match('#(?:^\.\.|/\.\./)#',$theFile))	return true;
	}

	/**
	 * Checks if the $path is absolute or relative (detecting either '/' or 'x:/' as first part of string) and returns true if so.
	 * Usage: 8
	 *
	 * @param	string		Filepath to evaluate
	 * @return	boolean
	 */
	function isAbsPath($path)	{
		return TYPO3_OS=='WIN' ? substr($path,1,2)==':/' :  substr($path,0,1)=='/';
	}

	/**
	 * Returns true if the path is absolute, without backpath '..' and within the PATH_site OR within the lockRootPath
	 * Usage: 5
	 *
	 * @param	string		Filepath to evaluate
	 * @return	boolean
	 */
	function isAllowedAbsPath($path)	{
		if (t3lib_div::isAbsPath($path) &&
			t3lib_div::validPathStr($path) &&
				(	t3lib_div::isFirstPartOfStr($path,PATH_site)
					||
					($GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'] && t3lib_div::isFirstPartOfStr($path,$GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath']))
				)
			)	return true;
	}

	/**
	 * Verifies the input filename againts the 'fileDenyPattern'. Returns true if OK.
	 * Usage: 2
	 *
	 * @param	string		Filepath to evaluate
	 * @return	boolean
	 */
	function verifyFilenameAgainstDenyPattern($filename)	{
		if (strcmp($filename,'') && strcmp($GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'],''))	{
			$result = eregi($GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'],$filename);
			if ($result)	return false;	// so if a matching filename is found, return false;
		}
		return true;
	}

	/**
	 * Moves $source file to $destination if uploaded, otherwise try to make a copy
	 * Usage: 4
	 *
	 * @param	string		Source file, absolute path
	 * @param	string		Destination file, absolute path
	 * @return	boolean		Returns true if the file was moved.
	 * @coauthor	Dennis Petersen <fessor@software.dk>
	 * @see upload_to_tempfile()
	 */
	function upload_copy_move($source,$destination)	{
		if (is_uploaded_file($source))	{
			$uploaded = TRUE;
			// Return the value of move_uploaded_file, and if false the temporary $source is still around so the user can use unlink to delete it:
			$uploadedResult = move_uploaded_file($source, $destination);
		} else {
			$uploaded = FALSE;
			@copy($source,$destination);
		}

		t3lib_div::fixPermissions($destination);	// Change the permissions of the file

			// If here the file is copied and the temporary $source is still around, so when returning false the user can try unlink to delete the $source
		return $uploaded ? $uploadedResult : FALSE;
	}

	/**
	 * Will move an uploaded file (normally in "/tmp/xxxxx") to a temporary filename in PATH_site."typo3temp/" from where TYPO3 can use it under safe_mode.
	 * Use this function to move uploaded files to where you can work on them.
	 * REMEMBER to use t3lib_div::unlink_tempfile() afterwards - otherwise temp-files will build up! They are NOT automatically deleted in PATH_site."typo3temp/"!
	 * Usage: 6
	 *
	 * @param	string		The temporary uploaded filename, eg. $_FILES['[upload field name here]']['tmp_name']
	 * @return	string		If a new file was successfully created, return its filename, otherwise blank string.
	 * @see unlink_tempfile(), upload_copy_move()
	 */
	function upload_to_tempfile($uploadedFileName)	{
		if (is_uploaded_file($uploadedFileName))	{
			$tempFile = t3lib_div::tempnam('upload_temp_');
			move_uploaded_file($uploadedFileName, $tempFile);
			return @is_file($tempFile) ? $tempFile : '';
		}
	}

	/**
	 * Deletes (unlink) a temporary filename in 'PATH_site."typo3temp/"' given as input.
	 * The function will check that the file exists, is in PATH_site."typo3temp/" and does not contain back-spaces ("../") so it should be pretty safe.
	 * Use this after upload_to_tempfile() or tempnam() from this class!
	 * Usage: 9
	 *
	 * @param	string		Filepath for a file in PATH_site."typo3temp/". Must be absolute.
	 * @return	boolean		Returns true if the file was unlink()'ed
	 * @see upload_to_tempfile(), tempnam()
	 */
	function unlink_tempfile($uploadedTempFileName)	{
		if ($uploadedTempFileName && t3lib_div::validPathStr($uploadedTempFileName) && t3lib_div::isFirstPartOfStr($uploadedTempFileName,PATH_site.'typo3temp/') && @is_file($uploadedTempFileName))	{
			if (unlink($uploadedTempFileName))	return TRUE;
		}
	}

	/**
	 * Create temporary filename (Create file with unique file name)
	 * This function should be used for getting temporary filenames - will make your applications safe for open_basedir = on
	 * REMEMBER to delete the temporary files after use! This is done by t3lib_div::unlink_tempfile()
	 * Usage: 7
	 *
	 * @param	string		Prefix to temp file (which will have no extension btw)
	 * @return	string		result from PHP function tempnam() with PATH_site.'typo3temp/' set for temp path.
	 * @see unlink_tempfile(), upload_to_tempfile()
	 */
	function tempnam($filePrefix)	{
		return tempnam(PATH_site.'typo3temp/',$filePrefix);
	}

	/**
	 * Standard authentication code (used in Direct Mail, checkJumpUrl and setfixed links computations)
	 * Usage: 2
	 *
	 * @param	mixed		Uid (integer) or record (array)
	 * @param	string		List of fields from the record if that is given.
	 * @param	integer		Length of returned authentication code.
	 * @return	string		MD5 hash of 8 chars.
	 * @internal
	 */
	function stdAuthCode($uid_or_record,$fields='',$codeLength=8)	{

		if (is_array($uid_or_record))	{
			$recCopy_temp=array();
			if ($fields)	{
				$fieldArr = t3lib_div::trimExplode(',',$fields,1);
				reset($fieldArr);
				while(list($k,$v)=each($fieldArr))	{
					$recCopy_temp[$k]=$uid_or_record[$v];
				}
			} else {
				$recCopy_temp=$uid_or_record;
			}
			$preKey = implode('|',$recCopy_temp);
		} else {
			$preKey = $uid_or_record;
		}

		$authCode = $preKey.'||'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
		$authCode = substr(md5($authCode),0,$codeLength);
		return $authCode;
	}

	/**
	 * Splits the input query-parameters into an array with certain parameters filtered out.
	 * Used to create the cHash value
	 *
	 * @param	string		Query-parameters: "&xxx=yyy&zzz=uuu"
	 * @return	array		Array with key/value pairs of query-parameters WITHOUT a certain list of variable names (like id, type, no_cache etc.) and WITH a variable, encryptionKey, specific for this server/installation
	 * @see tslib_fe::makeCacheHash(), tslib_cObj::typoLink()
	 */
	function cHashParams($addQueryParams) {
		$params = explode('&',substr($addQueryParams,1));	// Splitting parameters up

			// Make array:
		$pA = array();
		foreach($params as $theP)	{
			$pKV = explode('=', $theP);	// Splitting single param by '=' sign
			if (!t3lib_div::inList('id,type,no_cache,cHash,MP,ftu',$pKV[0]))	{
				$pA[rawurldecode($pKV[0])] = (string)rawurldecode($pKV[1]);
			}
		}
		$pA['encryptionKey'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
		ksort($pA);

		return $pA;
	}

	/**
	 * Responds on input localization setting value whether the page it comes from should be hidden if no translation exists or not.
	 *
	 * @param	integer		Value from "l18n_cfg" field of a page record
	 * @return	boolean		True if the page should be hidden
	 */
	function hideIfNotTranslated($l18n_cfg_fieldValue)	{
		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'])	{
			return $l18n_cfg_fieldValue&2 ? FALSE : TRUE;
		} else {
			return $l18n_cfg_fieldValue&2 ? TRUE : FALSE;
		}
	}

	/**
	 * Includes a locallang file and returns the $LOCAL_LANG array found inside.
	 *
	 * @param	string		Input is a file-reference (see t3lib_div::getFileAbsFileName) which, if exists, is included. That file is expected to be a 'local_lang' file containing a $LOCAL_LANG array.
	 * @param	string		Language key
	 * @return	array		Value of $LOCAL_LANG found in the included file. If that array is found it's returned. Otherwise an empty array
	 */
	function readLLfile($fileRef,$langKey)	{

		$file = t3lib_div::getFileAbsFileName($fileRef);
		if ($file)	{
			$baseFile = ereg_replace('\.(php|xml)$', '', $file);

			if (@is_file($baseFile.'.xml'))	{
				$LOCAL_LANG = t3lib_div::readLLXMLfile($baseFile.'.xml', $langKey);
			} elseif (@is_file($baseFile.'.php'))	{
				include($baseFile.'.php');
			} else die('Filereference, "'.$file.'", not found!');
		}

		return is_array($LOCAL_LANG)?$LOCAL_LANG:array();
	}

	/**
	 * Includes a locallang-xml file and returns the $LOCAL_LANG array
	 * Works only when the frontend or backend has been initialized with a charset conversion object. See first code lines.
	 *
	 * @param	string		Absolute reference to locallang-XML file
	 * @param	string		TYPO3 language key, eg. "dk" or "de" or "default"
	 * @return	array		LOCAL_LANG array in return.
	 */
	function readLLXMLfile($fileRef,$langKey)	{

		if (is_object($GLOBALS['LANG']))	{
			$csConvObj = &$GLOBALS['LANG']->csConvObj;
		} elseif (is_object($GLOBALS['TSFE']))	{
			$csConvObj = &$GLOBALS['TSFE']->csConvObj;
		} else $csConvObj = NULL;

		if (@is_file($fileRef) && $langKey && is_object($csConvObj))	{

				// Set charset:
			$origCharset = $csConvObj->parse_charset($csConvObj->charSetArray[$langKey] ? $csConvObj->charSetArray[$langKey] : 'iso-8859-1');

				// Cache file name:
			$hashSource = substr($fileRef,strlen(PATH_site)).'|'.date('d-m-Y H:i:s',filemtime($fileRef)).'|version=2.2';
			$cacheFileName = PATH_site.'typo3temp/llxml/'.
							#str_replace('_','',ereg_replace('^.*\/','',dirname($fileRef))).
							#'_'.basename($fileRef).
							substr(basename($fileRef),10,15).
							'_'.t3lib_div::shortMD5($hashSource).'.'.$langKey.'.'.$origCharset.'.cache';

				// Check if cache file exists...
			if (!@is_file($cacheFileName))	{	// ... if it doesn't, create content and write it:

					// Read XML, parse it.
				$xmlString = t3lib_div::getUrl($fileRef);
				$xmlContent = t3lib_div::xml2array($xmlString);
				if (!is_array($xmlContent))	{
					die($fileRef.' was not XML!: '.$xmlContent);
				}

					// Set default LOCAL_LANG array content:
				$LOCAL_LANG = array();
				$LOCAL_LANG['default'] = $xmlContent['data']['default'];

					// Converting charset of default language from utf-8 to iso-8859-1 (since that is what the system would expect for default langauge in the core due to historical reasons)
					// This conversion is unneccessary for 99,99% of all default labels since they are in english, therefore ASCII.
					// However, an extension like TemplaVoila uses an extended character in its name, even in Default language. To accommodate that (special chars for default) this conversion must be made.
					// Since the output from this function is probably always cached it is considered insignificant to do this conversion.
					// - kasper
				if (is_array($LOCAL_LANG['default']))	{
					foreach($LOCAL_LANG['default'] as $labelKey => $labelValue)	{
						$LOCAL_LANG['default'][$labelKey] = $csConvObj->utf8_decode($labelValue,'iso-8859-1');
					}
				}

					// Specific language, convert from utf-8 to backend language charset:
					// NOTICE: Converting from utf-8 back to "native" language may be a temporary solution until we can totally discard "locallang.php" files altogether (and use utf-8 for everything). But doing this conversion is the quickest way to migrate now and the source is in utf-8 anyway which is the main point.
				if ($langKey!='default')	{

						// If no entry is found for the language key, then force a value depending on meta-data setting. By default an automated filename will be used:
					if (!isset($xmlContent['data'][$langKey]))	{
						$LOCAL_LANG[$langKey] = t3lib_div::llXmlAutoFileName($fileRef, $langKey);
					} else {
						$LOCAL_LANG[$langKey] = $xmlContent['data'][$langKey];
					}

						// Checking if charset should be converted.
					if (is_array($LOCAL_LANG[$langKey]) && $origCharset!='utf-8')	{
						foreach($LOCAL_LANG[$langKey] as $labelKey => $labelValue)	{
							$LOCAL_LANG[$langKey][$labelKey] = $csConvObj->utf8_decode($labelValue,$origCharset);
						}
					}
				}

					// Cache the content now:
				$serContent = array('origFile'=>$hashSource, 'LOCAL_LANG'=>$LOCAL_LANG);
				$res = t3lib_div::writeFileToTypo3tempDir($cacheFileName, serialize($serContent));
				if ($res)	die('ERROR: '.$res);
			} else {
					// Get content from cache:
				$serContent = unserialize(t3lib_div::getUrl($cacheFileName));
				$LOCAL_LANG = $serContent['LOCAL_LANG'];
			}

				// Checking for EXTERNAL file for non-default language:
			if ($langKey!='default' && is_string($LOCAL_LANG[$langKey]) && strlen($LOCAL_LANG[$langKey]))	{

					// Look for localized file:
				$localized_file = t3lib_div::getFileAbsFileName($LOCAL_LANG[$langKey]);
				if ($localized_file && @is_file($localized_file))	{

						// Cache file name:
					$hashSource = substr($localized_file,strlen(PATH_site)).'|'.date('d-m-Y H:i:s',filemtime($localized_file));
					$cacheFileName = PATH_site.'typo3temp/llxml/ext_'.
									substr(basename($localized_file),10,15).
									'_'.t3lib_div::shortMD5($hashSource).'.'.$langKey.'.'.$origCharset.'.cache';

						// Check if cache file exists...
					if (!@is_file($cacheFileName))	{	// ... if it doesn't, create content and write it:

							// Read and parse XML content:
						$local_xmlString = t3lib_div::getUrl($localized_file);
						$local_xmlContent = t3lib_div::xml2array($local_xmlString);
						$LOCAL_LANG[$langKey] = is_array($local_xmlContent['data'][$langKey]) ? $local_xmlContent['data'][$langKey] : array();

							// Checking if charset should be converted.
						if (is_array($LOCAL_LANG[$langKey]) && $origCharset!='utf-8')	{
							foreach($LOCAL_LANG[$langKey] as $labelKey => $labelValue)	{
								$LOCAL_LANG[$langKey][$labelKey] = $csConvObj->utf8_decode($labelValue,$origCharset);
							}
						}

							// Cache the content now:
						$serContent = array('extlang'=>$langKey, 'origFile'=>$LOCAL_LANG[$langKey], 'EXT_DATA'=>$LOCAL_LANG[$langKey]);
						$res = t3lib_div::writeFileToTypo3tempDir($cacheFileName, serialize($serContent));
						if ($res)	die('ERROR: '.$res);
					} else {
							// Get content from cache:
						$serContent = unserialize(t3lib_div::getUrl($cacheFileName));
						$LOCAL_LANG[$langKey] = $serContent['EXT_DATA'];
					}
				} else {
					$LOCAL_LANG[$langKey] = array();
				}
			}

			return $LOCAL_LANG;
		}
	}

	/**
	 * Returns auto-filename for locallang-XML localizations.
	 *
	 * @param	string		Absolute file reference to locallang-XML file. Must be inside system/global/local extension
	 * @param	string		Language key
	 * @return	string		Returns the filename reference for the language unless error occured (or local mode is used) in which case it will be NULL
	 */
	function llXmlAutoFileName($fileRef,$language)	{
			// Analyse file reference:
		$location = 'typo3conf/l10n/'.$language.'/';	// Default location of translations
		if (t3lib_div::isFirstPartOfStr($fileRef,PATH_typo3.'sysext/'))	{	// Is system:
			$validatedPrefix = PATH_typo3.'sysext/';
			#$location = 'EXT:csh_'.$language.'/';	// For system extensions translations are found in "csh_*" extensions (language packs)
		} elseif (t3lib_div::isFirstPartOfStr($fileRef,PATH_typo3.'ext/'))	{	// Is global:
			$validatedPrefix = PATH_typo3.'ext/';
		} elseif (t3lib_div::isFirstPartOfStr($fileRef,PATH_typo3conf.'ext/'))	{	// Is local:
			$validatedPrefix = PATH_typo3conf.'ext/';
		} else {
			$validatedPrefix = '';
		}

		if ($validatedPrefix)	{

				// Divide file reference into extension key, directory (if any) and base name:
			list($file_extKey,$file_extPath) = explode('/',substr($fileRef,strlen($validatedPrefix)),2);
			$temp = t3lib_div::revExplode('/',$file_extPath,2);
			if (count($temp)==1)	array_unshift($temp,'');	// Add empty first-entry if not there.
			list($file_extPath,$file_fileName) = $temp;

				// The filename is prefixed with "[language key]." because it prevents the llxmltranslate tool from detecting it.
			return $location.
				$file_extKey.'/'.
				($file_extPath?$file_extPath.'/':'').
				$language.'.'.$file_fileName;
		} else return NULL;
	}


	/**
	 * Loads the $TCA (Table Configuration Array) for the $table
	 *
	 * Requirements:
	 * 1) must be configured table (the ctrl-section configured),
	 * 2) columns must not be an array (which it is always if whole table loaded), and
	 * 3) there is a value for dynamicConfigFile (filename in typo3conf)
	 *
	 * Note: For the frontend this loads only 'ctrl' and 'feInterface' parts.
	 * For complete TCA use $GLOBALS['TSFE']->includeTCA() instead.
	 *
	 * Usage: 84
	 *
	 * @param	string		Table name for which to load the full TCA array part into the global $TCA
	 * @return	void
	 */
	function loadTCA($table)	{
		global $TCA,$LANG_GENERAL_LABELS;
		if (isset($TCA[$table]) && !is_array($TCA[$table]['columns']) && $TCA[$table]['ctrl']['dynamicConfigFile'])	{
			if (!strcmp(substr($TCA[$table]['ctrl']['dynamicConfigFile'],0,6),'T3LIB:'))	{
				include(PATH_t3lib.'stddb/'.substr($TCA[$table]['ctrl']['dynamicConfigFile'],6));
			} elseif (t3lib_div::isAbsPath($TCA[$table]['ctrl']['dynamicConfigFile']) && @is_file($TCA[$table]['ctrl']['dynamicConfigFile']))	{	// Absolute path...
				include($TCA[$table]['ctrl']['dynamicConfigFile']);
			} else include(PATH_typo3conf.$TCA[$table]['ctrl']['dynamicConfigFile']);
		}
	}

	/**
	 * Looks for a sheet-definition in the input data structure array. If found it will return the data structure for the sheet given as $sheet (if found).
	 * If the sheet definition is in an external file that file is parsed and the data structure inside of that is returned.
	 * Usage: 5
	 *
	 * @param	array		Input data structure, possibly with a sheet-definition and references to external data source files.
	 * @param	string		The sheet to return, preferably.
	 * @return	array		An array with two num. keys: key0: The data structure is returned in this key (array) UNLESS an error happend in which case an error string is returned (string). key1: The used sheet key value!
	 */
	function resolveSheetDefInDS($dataStructArray,$sheet='sDEF')	{
		if (!is_array ($dataStructArray)) return 'Data structure must be an array';

		if (is_array($dataStructArray['sheets']))	{
			$singleSheet = FALSE;
			if (!isset($dataStructArray['sheets'][$sheet]))	{
				$sheet='sDEF';
			}
			$dataStruct =  $dataStructArray['sheets'][$sheet];

				// If not an array, but still set, then regard it as a relative reference to a file:
			if ($dataStruct && !is_array($dataStruct))	{
				$file = t3lib_div::getFileAbsFileName($dataStruct);
				if ($file && @is_file($file))	{
					$dataStruct = t3lib_div::xml2array(t3lib_div::getUrl($file));
				}
			}
		} else {
			$singleSheet = TRUE;
			$dataStruct = $dataStructArray;
			if (isset($dataStruct['meta'])) unset($dataStruct['meta']);	// Meta data should not appear there.
			$sheet = 'sDEF';	// Default sheet
		}
		return array($dataStruct,$sheet,$singleSheet);
	}

	/**
	 * Resolves ALL sheet definitions in dataStructArray
	 * If no sheet is found, then the default "sDEF" will be created with the dataStructure inside.
	 *
	 * @param	array		Input data structure, possibly with a sheet-definition and references to external data source files.
	 * @return	array		Output data structure with all sheets resolved as arrays.
	 */
	function resolveAllSheetsInDS($dataStructArray)	{
		if (is_array($dataStructArray['sheets']))	{
			$out=array('sheets'=>array());
			foreach($dataStructArray['sheets'] as $sheetId => $sDat)	{
				list($ds,$aS) = t3lib_div::resolveSheetDefInDS($dataStructArray,$sheetId);
				if ($sheetId==$aS)	{
					$out['sheets'][$aS]=$ds;
				}
			}
		} else {
			list($ds) = t3lib_div::resolveSheetDefInDS($dataStructArray);
			$out = array('sheets' => array('sDEF' => $ds));
		}
		return $out;
	}

	/**
	 * Calls a userdefined function/method in class
	 * Such a function/method should look like this: "function proc(&$params, &$ref)	{...}"
	 * Usage: 17
	 *
	 * @param	string		Function/Method reference, '[file-reference":"]["&"]class/function["->"method-name]'. You can prefix this reference with "[file-reference]:" and t3lib_div::getFileAbsFileName() will then be used to resolve the filename and subsequently include it by "require_once()" which means you don't have to worry about including the class file either! Example: "EXT:realurl/class.tx_realurl.php:&tx_realurl->encodeSpURL". Finally; you can prefix the class name with "&" if you want to reuse a former instance of the same object call ("singleton").
	 * @param	mixed		Parameters to be pass along (typically an array) (REFERENCE!)
	 * @param	mixed		Reference to be passed along (typically "$this" - being a reference to the calling object) (REFERENCE!)
	 * @param	string		Required prefix of class or function name
	 * @param	boolean		If set, no debug() error message is shown if class/function is not present.
	 * @return	mixed		Content from method/function call
	 * @see getUserObj()
	 */
	function callUserFunction($funcName,&$params,&$ref,$checkPrefix='user_',$silent=0)	{
		global $TYPO3_CONF_VARS;

			// Check persistent object and if found, call directly and exit.
		if (is_array($GLOBALS['T3_VAR']['callUserFunction'][$funcName]))	{
			return call_user_func_array(
						array(&$GLOBALS['T3_VAR']['callUserFunction'][$funcName]['obj'],
							$GLOBALS['T3_VAR']['callUserFunction'][$funcName]['method']),
						array(&$params, &$ref)
					);
		}

			// Check file-reference prefix; if found, require_once() the file (should be library of code)
		if (strstr($funcName,':'))	{
			list($file,$funcRef) = t3lib_div::revExplode(':',$funcName,2);
			$requireFile = t3lib_div::getFileAbsFileName($file);
			if ($requireFile) t3lib_div::requireOnce($requireFile);
		} else {
			$funcRef = $funcName;
		}

			// Check for persistent object token, "&"
		if (substr($funcRef,0,1)=='&')	{
			$funcRef = substr($funcRef,1);
			$storePersistentObject = TRUE;
		} else {
			$storePersistentObject = FALSE;
		}

			// Check prefix is valid:
		if ($checkPrefix &&
			!t3lib_div::isFirstPartOfStr(trim($funcRef),$checkPrefix) &&
			!t3lib_div::isFirstPartOfStr(trim($funcRef),'tx_')
			)	{
			if (!$silent)	debug("Function/Class '".$funcRef."' was not prepended with '".$checkPrefix."'",1);
			return FALSE;
		}

			// Call function or method:
		$parts = explode('->',$funcRef);
		if (count($parts)==2)	{	// Class

				// Check if class/method exists:
			if (class_exists($parts[0]))	{

					// Get/Create object of class:
				if ($storePersistentObject)	{	// Get reference to current instance of class:
					if (!is_object($GLOBALS['T3_VAR']['callUserFunction_classPool'][$parts[0]]))	{
						$GLOBALS['T3_VAR']['callUserFunction_classPool'][$parts[0]] = &t3lib_div::makeInstance($parts[0]);
					}
					$classObj = &$GLOBALS['T3_VAR']['callUserFunction_classPool'][$parts[0]];
				} else {	// Create new object:
					$classObj = &t3lib_div::makeInstance($parts[0]);
				}

				if (method_exists($classObj, $parts[1]))	{

						// If persistent object should be created, set reference:
					if ($storePersistentObject)	{
						$GLOBALS['T3_VAR']['callUserFunction'][$funcName] = array (
							'method' => $parts[1],
							'obj' => &$classObj
						);
					}
						// Call method:
					$content = call_user_func_array(
						array(&$classObj, $parts[1]),
						array(&$params, &$ref)
					);
				} else {
					if (!$silent)	debug("<strong>ERROR:</strong> No method name '".$parts[1]."' in class ".$parts[0],1);
				}
			} else {
				if (!$silent)	debug("<strong>ERROR:</strong> No class named: ".$parts[0],1);
			}
		} else {	// Function
			if (function_exists($funcRef))	{
			 	$content = call_user_func_array($funcRef, array(&$params, &$ref));
			} else {
				if (!$silent)	debug("<strong>ERROR:</strong> No function named: ".$funcRef,1);
			}
		}
		return $content;
	}

	/**
	 * Creates and returns reference to a user defined object.
	 * This function can return an object reference if you like. Just prefix the function call with "&": "$objRef = &t3lib_div::getUserObj('EXT:myext/class.tx_myext_myclass.php:&tx_myext_myclass');". This will work ONLY if you prefix the class name with "&" as well. See description of function arguments.
	 * Usage: 5
	 *
	 * @param	string		Class reference, '[file-reference":"]["&"]class-name'. You can prefix the class name with "[file-reference]:" and t3lib_div::getFileAbsFileName() will then be used to resolve the filename and subsequently include it by "require_once()" which means you don't have to worry about including the class file either! Example: "EXT:realurl/class.tx_realurl.php:&tx_realurl". Finally; for the class name you can prefix it with "&" and you will reuse the previous instance of the object identified by the full reference string (meaning; if you ask for the same $classRef later in another place in the code you will get a reference to the first created one!).
	 * @param	string		Required prefix of class name. By default "tx_" is allowed.
	 * @param	boolean		If set, no debug() error message is shown if class/function is not present.
	 * @return	object		The instance of the class asked for. Instance is created with t3lib_div::makeInstance
	 * @see callUserFunction()
	 */
	function &getUserObj($classRef,$checkPrefix='user_',$silent=0)	{
		global $TYPO3_CONF_VARS;
			// Check persistent object and if found, call directly and exit.
		if (is_object($GLOBALS['T3_VAR']['getUserObj'][$classRef]))	{
			return $GLOBALS['T3_VAR']['getUserObj'][$classRef];
		} else {

				// Check file-reference prefix; if found, require_once() the file (should be library of code)
			if (strstr($classRef,':'))	{
				list($file,$class) = t3lib_div::revExplode(':',$classRef,2);
				$requireFile = t3lib_div::getFileAbsFileName($file);
				if ($requireFile)	t3lib_div::requireOnce($requireFile);
			} else {
				$class = $classRef;
			}

				// Check for persistent object token, "&"
			if (substr($class,0,1)=='&')	{
				$class = substr($class,1);
				$storePersistentObject = TRUE;
			} else {
				$storePersistentObject = FALSE;
			}

				// Check prefix is valid:
			if ($checkPrefix &&
				!t3lib_div::isFirstPartOfStr(trim($class),$checkPrefix) &&
				!t3lib_div::isFirstPartOfStr(trim($class),'tx_')
				)	{
				if (!$silent)	debug("Class '".$class."' was not prepended with '".$checkPrefix."'",1);
				return FALSE;
			}

				// Check if class exists:
			if (class_exists($class))	{
				$classObj = &t3lib_div::makeInstance($class);

					// If persistent object should be created, set reference:
				if ($storePersistentObject)	{
					$GLOBALS['T3_VAR']['getUserObj'][$classRef] = &$classObj;
				}

				return $classObj;
			} else {
				if (!$silent)	debug("<strong>ERROR:</strong> No class named: ".$class,1);
			}
		}
	}

	/**
	 * Make instance of class
	 * Takes the class-extensions API of TYPO3 into account
	 * Please USE THIS instead of the PHP "new" keyword. Eg. "$obj = new myclass;" should be "$obj = t3lib_div::makeInstance("myclass")" instead!
	 * Usage: 447
	 *
	 * @param	string		Class name to instantiate
	 * @return	object		The object
	 */
	function &makeInstance($className)	{

			// Load class file if not found:
		if (!class_exists($className))	{
			if (substr($className,0,6)=='t3lib_')	{
				t3lib_div::requireOnce(PATH_t3lib.'class.'.strtolower($className).'.php');
			}
		}
		
			// Return object.
		return class_exists('ux_'.$className) ? t3lib_div::makeInstance('ux_'.$className) : new $className;
	}

	/**
	 * Return classname for new instance
	 * Takes the class-extensions API of TYPO3 into account
	 * Usage: 17
	 *
	 * @param	string		Base Class name to evaluate
	 * @return	string		Final class name to instantiate with "new [classname]"
	 */
	function makeInstanceClassName($className)	{
		return class_exists('ux_'.$className) ? t3lib_div::makeInstanceClassName('ux_'.$className) : $className;
	}

	/**
	 * Find the best service and check if it works.
	 * Returns object of the service class.
	 *
	 * @param	string		Type of service (service key).
	 * @param	string		Sub type like file extensions or similar. Defined by the service.
	 * @param	mixed		List of service keys which should be exluded in the search for a service. Array or comma list.
	 * @return	object		The service object or an array with error info's.
	 * @author	Ren� Fritz <r.fritz@colorcube.de>
	 */
	function &makeInstanceService($serviceType, $serviceSubType='', $excludeServiceKeys=array())	{
		global $T3_SERVICES, $T3_VAR, $TYPO3_CONF_VARS;

		$error = FALSE;

		if (!is_array($excludeServiceKeys) ) {
			$excludeServiceKeys = t3lib_div::trimExplode(',', $excludeServiceKeys, 1);
		}
		while ($info = t3lib_extMgm::findService($serviceType, $serviceSubType, $excludeServiceKeys))	{

				// Check persistent object and if found, call directly and exit.
			if (is_object($GLOBALS['T3_VAR']['makeInstanceService'][$info['className']]))	{
					// reset service and return object
				$T3_VAR['makeInstanceService'][$info['className']]->reset();
				return $GLOBALS['T3_VAR']['makeInstanceService'][$info['className']];

				// include file and create object
			} else {
				$requireFile = t3lib_div::getFileAbsFileName($info['classFile']);
				if (@is_file($requireFile)) {
					t3lib_div::requireOnce ($requireFile);
					$obj = t3lib_div::makeInstance($info['className']);
					if (is_object($obj)) {
						if(!@is_callable(array($obj,'init')))	{
								// use silent logging??? I don't think so.
							die ('Broken service:'.t3lib_div::view_array($info));
						}
						$obj->info = $info;
						if ($obj->init()) { // service available?

								// create persistent object
							$T3_VAR['makeInstanceService'][$info['className']] = &$obj;

								// needed to delete temp files
							register_shutdown_function(array(&$obj, '__destruct'));

							return $obj; // object is passed as reference by function definition
						}
						$error = $obj->getLastErrorArray();
						unset($obj);
					}
				}
			}
				// deactivate the service
			t3lib_extMgm::deactivateService($info['serviceType'],$info['serviceKey']);
		}
		return $error;
	}
	
	/**
	 * Require a class for TYPO3
	 * Useful to require classes from inside other classes (not global scope). A limited set of global variables are available (see function)
	 */
	function requireOnce($requireFile)	{
		global $T3_SERVICES, $T3_VAR, $TYPO3_CONF_VARS;
		
		require_once ($requireFile);
	}	

	/**
	 * Simple substitute for the PHP function mail() which allows you to specify encoding and character set
	 * The fifth parameter ($encoding) will allow you to specify 'base64' encryption for the output (set $encoding=base64)
	 * Further the output has the charset set to ISO-8859-1 by default.
	 * Usage: 4
	 *
	 * @param	string		Email address to send to. (see PHP function mail())
	 * @param	string		Subject line, non-encoded. (see PHP function mail())
	 * @param	string		Message content, non-encoded. (see PHP function mail())
	 * @param	string		Headers, separated by chr(10)
	 * @param	string		Encoding type: "base64", "quoted-printable", "8bit". Default value is "quoted-printable".
	 * @param	string		Charset used in encoding-headers (only if $encoding is set to a valid value which produces such a header)
	 * @param	boolean		If set, the header content will not be encoded.
	 * @return	void
	 */
	function plainMailEncoded($email,$subject,$message,$headers='',$encoding='quoted-printable',$charset='',$dontEncodeHeader=false)	{
		if (!$charset)	{
			$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'ISO-8859-1';
		}

		if (!$dontEncodeHeader)	{
				// Mail headers must be ASCII, therefore we convert the whole header to either base64 or quoted_printable
			$newHeaders=array();
			foreach (explode(chr(10),$headers) as $line)	{	// Split the header in lines and convert each line separately
				$parts = explode(': ',$line,2);	// Field tags must not be encoded
				if (count($parts)==2)	{
					$parts[1] = t3lib_div::encodeHeader($parts[1],$encoding,$charset);
					$newHeaders[] = implode(': ',$parts);
				} else {
					$newHeaders[] = $line;	// Should never happen - is such a mail header valid? Anyway, just add the unchanged line...
				}
			}
			$headers = implode(chr(10),$newHeaders);
			unset($newHeaders);

			$email = t3lib_div::encodeHeader($email,$encoding,$charset);		// Email address must not be encoded, but it could be appended by a name which should be so (e.g. "Kasper Sk�rh�j <kasperYYYY@typo3.com>")
			$subject = t3lib_div::encodeHeader($subject,$encoding,$charset);
		}

		switch ((string)$encoding)	{
			case 'base64':
				$headers=trim($headers).chr(10).
				'Mime-Version: 1.0'.chr(10).
				'Content-Type: text/plain; charset="'.$charset.'"'.chr(10).
				'Content-Transfer-Encoding: base64';

				$message=trim(chunk_split(base64_encode($message.chr(10)))).chr(10);	// Adding chr(10) because I think MS outlook 2002 wants it... may be removed later again.
			break;
			case '8bit':
				$headers=trim($headers).chr(10).
				'Mime-Version: 1.0'.chr(10).
				'Content-Type: text/plain; charset='.$charset.chr(10).
				'Content-Transfer-Encoding: 8bit';
			break;
			case 'quoted-printable':
			default:
				$headers=trim($headers).chr(10).
				'Mime-Version: 1.0'.chr(10).
				'Content-Type: text/plain; charset='.$charset.chr(10).
				'Content-Transfer-Encoding: quoted-printable';

				$message=t3lib_div::quoted_printable($message);
			break;
		}

		$linebreak = chr(10);			// Default line break for Unix systems.
		if (TYPO3_OS=='WIN')	{
			$linebreak = chr(13).chr(10);	// Line break for Windows. This is needed because PHP on Windows systems send mails via SMTP instead of using sendmail, and thus the linebreak needs to be \r\n.
		}

		$headers=trim(implode($linebreak,t3lib_div::trimExplode(chr(10),$headers,1)));	// Make sure no empty lines are there.

		mail($email,$subject,$message,$headers);
	}

	/**
	 * Implementation of quoted-printable encode.
	 * This functions is buggy. It seems that in the part where the lines are breaked every 76th character, that it fails if the break happens right in a quoted_printable encode character!
	 * See RFC 1521, section 5.1 Quoted-Printable Content-Transfer-Encoding
	 * Usage: 2
	 *
	 * @param	string		Content to encode
	 * @param	integer		Length of the lines, default is 76
	 * @return	string		The QP encoded string
	 */
	function quoted_printable($string,$maxlen=76)	{
			// Make sure the string contains only Unix linebreaks
		$string = str_replace(chr(13).chr(10), chr(10), $string);	// Replace Windows breaks (\r\n)
		$string = str_replace(chr(13), chr(10), $string);		// Replace Mac breaks (\r)

		$linebreak = chr(10);			// Default line break for Unix systems.
		if (TYPO3_OS=='WIN')	{
			$linebreak = chr(13).chr(10);	// Line break for Windows. This is needed because PHP on Windows systems send mails via SMTP instead of using sendmail, and thus the linebreak needs to be \r\n.
		}

		$newString = '';
		$theLines = explode(chr(10),$string);	// Split lines
		foreach ($theLines as $val)	{
			$newVal = '';
			$theValLen = strlen($val);
			$len = 0;
			for ($index=0; $index < $theValLen; $index++)	{	// Walk through each character of this line
				$char = substr($val,$index,1);
				$ordVal = ord($char);
				if ($len>($maxlen-4) || ($len>(($maxlen-10)-4)&&$ordVal==32))	{
					$newVal.='='.$linebreak;	// Add a line break
					$len=0;			// Reset the length counter
				}
				if (($ordVal>=33 && $ordVal<=60) || ($ordVal>=62 && $ordVal<=126) || $ordVal==9 || $ordVal==32)	{
					$newVal.=$char;		// This character is ok, add it to the message
					$len++;
				} else {
					$newVal.=sprintf('=%02X',$ordVal);	// Special character, needs to be encoded
					$len+=3;
				}
			}
			$newVal = preg_replace('/'.chr(32).'$/','=20',$newVal);		// Replaces a possible SPACE-character at the end of a line
			$newVal = preg_replace('/'.chr(9).'$/','=09',$newVal);		// Replaces a possible TAB-character at the end of a line
			$newString.=$newVal.$linebreak;
		}
		return preg_replace('/'.$linebreak.'$/','',$newString);		// Remove last newline
	}

	/**
	 * Encode header lines
	 * Email headers must be ASCII, therefore they will be encoded to quoted_printable (default) or base64.
	 *
	 * @param	string		Content to encode
	 * @param	string		Encoding type: "base64" or "quoted-printable". Default value is "quoted-printable".
	 * @param	string		Charset used for encoding
	 * @return	string		The encoded string
	 */
	function encodeHeader($line,$enc='quoted-printable',$charset='ISO-8859-1')	{
			// Avoid problems if "###" is found in $line (would conflict with the placeholder which is used below)
		if (strstr($line,'###'))	return $line;

			// Check if any non-ASCII characters are found - otherwise encoding is not needed
		if (!preg_match('/[^'.chr(32).'-'.chr(127).']/',$line))	return $line;

			// Wrap email addresses in a special marker
		$line = preg_replace('/([^ ]+@[^ ]+)/', '###$1###', $line);

		$matches = preg_split('/(.?###.+###.?|\(|\))/', $line, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($matches as $part)	{
			$oldPart = $part;
			switch ((string)$enc)	{
				case 'base64':
					$part = '=?'.$charset.'?B?'.base64_encode($part).'?=';
				break;
				case 'quoted-printable':
				default:
					$qpValue = t3lib_div::quoted_printable($part,1000);
					if ($part!=$qpValue)	{
						$qpValue = str_replace(' ','_',$qpValue);	// Encoded words in the header should not contain non-encoded spaces. "_" is a shortcut for "=20". See RFC 2047 for details.
						$part = '=?'.$charset.'?Q?'.$qpValue.'?=';
					}
				break;
			}
			$line = str_replace($oldPart, $part, $line);
		}
		$line = preg_replace('/###(.+?)###/', '$1', $line);	// Remove the wrappers

		return $line;
	}

	/**
	 * Takes a clear-text message body for a plain text email, finds all 'http://' links and if they are longer than 76 chars they are converted to a shorter URL with a hash parameter. The real parameter is stored in the database and the hash-parameter/URL will be redirected to the real parameter when the link is clicked.
	 * This function is about preserving long links in messages.
	 * Usage: 3
	 *
	 * @param	string		Message content
	 * @param	string		URL mode; "76" or "all"
	 * @param	string		URL of index script (see makeRedirectUrl())
	 * @return	string		Processed message content
	 * @see makeRedirectUrl()
	 */
	function substUrlsInPlainText($message,$urlmode='76',$index_script_url='')	{
			// Substitute URLs with shorter links:
		foreach (array('http','https') as $protocol)	{
			$urlSplit = explode($protocol.'://',$message);
			reset($urlSplit);
			while (list($c,$v) = each($urlSplit))	{
				if ($c)	{
					$newParts = preg_split('/\s|[<>"{}|\\\^`()\']/', $v, 2);
					$newURL = $protocol.'://'.$newParts[0];

					switch ((string)$urlmode)	{
						case 'all':
							$newURL = t3lib_div::makeRedirectUrl($newURL,0,$index_script_url);
						break;
						case '76':
							$newURL = t3lib_div::makeRedirectUrl($newURL,76,$index_script_url);
						break;
					}
					$urlSplit[$c] = $newURL . substr($v,strlen($newParts[0]));
				}
			}
			$message = implode('',$urlSplit);
		}

		return $message;
	}

	/**
	 * Subfunction for substUrlsInPlainText() above.
	 * Usage: 2
	 *
	 * @param	string		Input URL
	 * @param	integer		URL string length limit
	 * @param	string		URL of "index script" - the prefix of the "?RDCT=..." parameter. If not supplyed it will default to t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR').'index.php'
	 * @return	string		Processed URL
	 * @internal
	 */
	function makeRedirectUrl($inUrl,$l=0,$index_script_url='')	{
		if (strlen($inUrl)>$l)	{
			$md5 = substr(md5($inUrl),0,20);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('md5hash', 'cache_md5params', 'md5hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($md5, 'cache_md5params'));
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res))	{
				$insertFields = array(
					'md5hash' => $md5,
					'tstamp' => time(),
					'type' => 2,
					'params' => $inUrl
				);

				$GLOBALS['TYPO3_DB']->exec_INSERTquery('cache_md5params', $insertFields);
			}
			$inUrl=($index_script_url ? $index_script_url : t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR').'index.php').
				'?RDCT='.$md5;
		}

		return $inUrl;
	}

	/**
	 * Function to compensate for FreeType2 96 dpi
	 * Usage: 21
	 *
	 * @param	integer		Fontsize for freetype function call
	 * @return	integer		Compensated fontsize based on $GLOBALS['TYPO3_CONF_VARS']['GFX']['TTFdpi']
	 */
	function freetypeDpiComp($font_size)	{
		$dpi = intval($GLOBALS['TYPO3_CONF_VARS']['GFX']['TTFdpi']);
		if ($dpi!=72)	$font_size = $font_size/$dpi*72;
		return $font_size;
	}

	/**
	 * Init system error log.
	 *
	 * @return	void
	 * @see sysLog()
	 */
	function initSysLog()	{
		global $TYPO3_CONF_VARS;

			// for CLI logging name is <fqdn-hostname>:<TYPO3-path>
		if (defined('TYPO3_cliMode') && TYPO3_cliMode)	{
			$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'] = t3lib_div::getHostname($requestHost=FALSE).':'.PATH_site;
		}
			// for Web logging name is <protocol>://<request-hostame>/<site-path>
		else {
			$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		}

			// init custom logging
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog']))	{
			$params = array('initLog'=>TRUE);
			$fakeThis = FALSE;
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $hookMethod)	{
				t3lib_div::callUserFunction($hookMethod,$params,$fakeThis);
			}
		}

			// init TYPO3 logging
		foreach (explode(';',$TYPO3_CONF_VARS['SYS']['systemLog'],2) as $log)	{
			list($type,$destination) = explode(',',$log,3);

			if ($type == 'syslog')	{
				define_syslog_variables();
				if (TYPO3_OS == 'WIN')	{
					$facility = LOG_USER;
				} else {
					$facility = constant('LOG_'.strtoupper($destination));
				}
				openlog($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'], LOG_ODELAY, $facility);
			}
		}

		$TYPO3_CONF_VARS['SYS']['systemLogLevel'] = t3lib_div::intInRange($TYPO3_CONF_VARS['SYS']['systemLogLevel'],0,4);
		$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogInit'] = TRUE;
	}

	/**
	 * System error log; This should be implemented around the source code, including the Core and both frontend and backend, logging serious errors.
	 * If you want to implement the sysLog in your applications, simply add lines like:
	 * 		t3lib_div::sysLog('[write message in English here]', 'extension key');
	 *
	 * @param	string		Message (in English).
	 * @param	string		Extension key (from which extension you are calling the log) or "Core"
	 * @param	integer		Severity: 0 is info, 1 is notice, 2 is warning, 3 is error, 4 is fatal error
	 * @return	void
	 */
	function sysLog($msg, $extKey, $severity=0) {
		global $TYPO3_CONF_VARS;

		$severity = t3lib_div::intInRange($severity,0,4);

			// is message worth logging?
		if (intval($TYPO3_CONF_VARS['SYS']['systemLogLevel']) > $severity)	return;

			// initialize logging
		if (!$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogInit'])	{
			t3lib_div::initSysLog();
		}

			// do custom logging
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog']))	{
			$params = array('msg'=>$msg, 'extKey'=>$extKey);
			if (function_exists('debug_backtrace'))	{
				$params['backTrace'] = debug_backtrace();
			}
			$fakeThis = FALSE;
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $hookMethod)	{
				t3lib_div::callUserFunction($hookMethod,$params,$fakeThis);
			}
		}

			// TYPO3 logging enabled?
		if (!$TYPO3_CONF_VARS['SYS']['systemLog'])	return;

			// use all configured logging options
		foreach (explode(';',$TYPO3_CONF_VARS['SYS']['systemLog'],2) as $log)	{
			list($type,$destination,$level) = explode(',',$log,4);

				// is message worth logging for this log type?
			if (intval($level) > $severity)	continue;

			$msgLine = ' - '.$extKey.': '.$msg;

				// write message to a file
			if ($type == 'file')	{
				$file = fopen($destination, 'a');
				if ($file)     {
					flock($file, LOCK_EX);  // try locking, but ignore if not available (eg. on NFS and FAT)
					fwrite($file, date('d/m/Y H:i').$msgLine.chr(10));
					flock($file, LOCK_UN);    // release the lock
					fclose($file);
				}
			}
				// send message per mail
			elseif ($type == 'mail')	{
				list($to,$from) = explode('/',$destination);
				mail($to, 'Warning - error in TYPO3 installation',
					'Host: '.$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost']."\n".
					'Extension: '.$extKey."\n".
					'Severity: '.$severity."\n".
					"\n".$msg,
					($from ? 'From: '.$from : '')
				);
			}
				// use the PHP error log
			elseif ($type == 'error_log')	{
				error_log($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogHost'].$msgLine, 0);
			}
				// use the system log
			elseif ($type == 'syslog')	{
				$priority = array(LOG_INFO,LOG_NOTICE,LOG_WARNING,LOG_ERR,LOG_CRIT);
				syslog($priority[(int)$severity], $msgLine);
			}
		}
	}

	/**
	 * Developer log; This should be implemented around the source code, both frontend and backend, logging everything from the flow through an application, messages, results from comparisons to fatal errors.
	 * The result is meant to make sense to developers during development or debugging of a site.
	 * The idea is that this function is only a wrapper for external extensions which can set a hook which will be allowed to handle the logging of the information to any format they might wish and with any kind of filter they would like.
	 * If you want to implement the devLog in your applications, simply add lines like:
	 * 		if (TYPO3_DLOG)	t3lib_div::devLog('[write message in english here]', 'extension key');
	 *
	 * @param	string		Message (in english).
	 * @param	string		Extension key (from which extension you are calling the log)
	 * @param	integer		Severity: 0 is info, 1 is notice, 2 is warning, 3 is fatal error, -1 is "OK" message
	 * @param	array		Additional data you want to pass to the logger.
	 * @return	void
	 */
	function devLog($msg, $extKey, $severity=0, $dataVar=FALSE)	{
		global $TYPO3_CONF_VARS;

		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog']))	{
			$params = array('msg'=>$msg, 'extKey'=>$extKey, 'severity'=>$severity, 'dataVar'=>$dataVar);
			$fakeThis = FALSE;
			foreach($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog'] as $hookMethod)	{
				t3lib_div::callUserFunction($hookMethod,$params,$fakeThis);
			}
		}
	}

	/**
	 * Converts a one dimensional array to a one line string which can be used for logging or debugging output
	 * Example: "loginType: FE; refInfo: Array; HTTP_HOST: www.example.org; REMOTE_ADDR: 192.168.1.5; REMOTE_HOST:; security_level:; showHiddenRecords: 0;"
	 *
	 * @param	array		Data array which should be outputted
	 * @param	mixed		List of keys which should be listed in the output string. Pass a comma list or an array. An empty list outputs the whole array.
	 * @param	integer		Long string values are shortened to this length. Default: 20
	 * @return	string		Output string with key names and their value as string
	 */
	function arrayToLogString($arr, $valueList=array(), $valueLength=20) {
		$str = '';
		if (is_array($arr))	{
			if (!is_array($valueList))	{
				$valueList = t3lib_div::trimExplode(',', $valueList, 1);
			}
			$valListCnt = count($valueList);
			foreach ($arr as $key => $value)	{
				if (!$valListCnt || in_array($key, $valueList))	{
					$str .= (string)$key.trim(': '.t3lib_div::fixed_lgd(str_replace("\n",'|',(string)$value), $valueLength)).'; ';
				}
			}
		}
		return $str;
	}

	/**
	 * Compile the command for running ImageMagick/GraphicsMagick.
	 *
	 * @param	string		Command to be run: identify, convert or combine/composite
	 * @param	string		The parameters string
	 * @param	string		Override the default path
	 * @return	string		Compiled command that deals with IM6 & GraphicsMagick
	 */
	function imageMagickCommand($command, $parameters, $path='')	{
		$gfxConf = $GLOBALS['TYPO3_CONF_VARS']['GFX'];
		$isExt = (TYPO3_OS=='WIN' ? '.exe' : '');
		$switchCompositeParameters=false;

		if(!$path)	{ $path = $gfxConf['im_path']; }

		$im_version = strtolower($gfxConf['im_version_5']);
		$combineScript = $gfxConf['im_combine_filename'] ? trim($gfxConf['im_combine_filename']) : 'combine';

		if($command==='combine')	{	// This is only used internally, has no effect outside
			$command = 'composite';
		}

			// Compile the path & command
		if($im_version==='gm')	{
			$switchCompositeParameters=true;
			$path .= 'gm'.$isExt.' '.$command;
		} else	{
			if($im_version==='im6')	{ $switchCompositeParameters=true; }
			$path .= (($command=='composite') ? $combineScript : $command).$isExt;
		}

		$cmdLine = $path.' '.$parameters;

		if($command=='composite' && $switchCompositeParameters)	{	// Because of some weird incompatibilities between ImageMagick 4 and 6 (plus GraphicsMagick), it is needed to change the parameters order under some preconditions
			$paramsArr = t3lib_div::unQuoteFilenames($parameters);

			if(count($paramsArr)>5)	{	// The mask image has been specified => swap the parameters
				$tmp = $paramsArr[count($paramsArr)-3];
				$paramsArr[count($paramsArr)-3] = $paramsArr[count($paramsArr)-4];
				$paramsArr[count($paramsArr)-4] = $tmp;
			}

			$cmdLine = $path.' '.implode(' ', $paramsArr);
		}

		return $cmdLine;
	}

	/**
	 * Explode a string (normally a list of filenames) with whitespaces by considering quotes in that string. This is mostly needed by the imageMagickCommand function above.
	 *
	 * @param	string		The whole parameters string
	 * @param	boolean		If set, the elements of the resulting array are unquoted.
	 * @return	array		Exploded parameters
	 */
	function unQuoteFilenames($parameters,$unQuote=FALSE)	{
		$paramsArr = explode(' ', trim($parameters));

		$quoteActive = -1;	// Whenever a quote character (") is found, $quoteActive is set to the element number inside of $params. A value of -1 means that there are not open quotes at the current position.
		foreach($paramsArr as $k=>$v)	{
			if($quoteActive > -1)	{
				$paramsArr[$quoteActive] .= ' '.$v;
				unset($paramsArr[$k]);
				if(ereg('"$', $v))	{ $quoteActive = -1; }

			} elseif(!trim($v))	{
				unset($paramsArr[$k]);	// Remove empty elements

			} elseif(ereg('^"', $v))	{
				$quoteActive = $k;
			}
		}

		if($unQuote) {
			foreach($paramsArr as $key=>$val) {
				$paramsArr[$key]=preg_replace('/(^"|"$)/','',$val);
			}
		}
		return $paramsArr;
	}


	/**
	 * Quotes a string for usage as JS parameter. Depends wheter the value is used in script tags (it doesn't need/must not get htmlspecialchared in this case)
	 *
	 * @param	string		The string to encode.
	 * @param	boolean		If the values get's used in <script> tags.
	 * @return	string		The encoded value already quoted
	 */
	function quoteJSvalue($value, $inScriptTags = false)	{
		$value = addcslashes($value, '\''.chr(10).chr(13));
		if (!$inScriptTags)	{
			$value = htmlspecialchars($value);
		}
		return '\''.$value.'\'';
	}


}

?>