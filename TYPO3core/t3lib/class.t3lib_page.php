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
 * Contains a class with "Page functions" mainly for the frontend
 *
 * $Id$
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 * XHTML-trans compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  109: class t3lib_pageSelect
 *  134:     function init($show_hidden)
 *
 *              SECTION: Selecting page records
 *  184:     function getPage($uid, $disableGroupAccessCheck=FALSE)
 *  200:     function getPage_noCheck($uid)
 *  216:     function getFirstWebPage($uid)
 *  234:     function getPageIdFromAlias($alias)
 *  250:     function getPageOverlay($pageInput,$lUid=-1)
 *  314:     function getRecordOverlay($table,$row,$sys_language_content,$OLmode='')
 *
 *              SECTION: Page related: Menu, Domain record, Root line
 *  413:     function getMenu($uid,$fields='*',$sortField='sorting',$addWhere='',$checkShortcuts=1)
 *  471:     function getDomainStartPage($domain, $path='',$request_uri='')
 *  519:     function getRootLine($uid, $MP='', $ignoreMPerrors=FALSE)
 *  640:     function getPathFromRootline($rl,$len=20)
 *  661:     function getExtURL($pagerow,$disable=0)
 *  685:     function getMountPointInfo($pageId, $pageRec=FALSE, $prevMountPids=array(), $firstPageUid=0)
 *
 *              SECTION: Selecting records in general
 *  762:     function checkRecord($table,$uid,$checkPage=0)
 *  797:     function getRawRecord($table,$uid,$fields='*',$noWSOL=FALSE)
 *  823:     function getRecordsByField($theTable,$theField,$theValue,$whereClause='',$groupBy='',$orderBy='',$limit='')
 *
 *              SECTION: Caching and standard clauses
 *  875:     function getHash($hash,$expTime=0)
 *  898:     function storeHash($hash,$data,$ident)
 *  916:     function deleteClause($table)
 *  936:     function enableFields($table,$show_hidden=-1,$ignore_array=array(),$noVersionPreview=FALSE)
 * 1008:     function getMultipleGroupsWhereClause($field, $table)
 *
 *              SECTION: Versioning Preview
 * 1055:     function fixVersioningPid($table,&$rr)
 * 1096:     function versionOL($table,&$row)
 * 1151:     function getWorkspaceVersionOfRecord($workspace, $table, $uid, $fields='*')
 *
 * TOTAL FUNCTIONS: 24
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


















/**
 * Page functions, a lot of sql/pages-related functions
 * Mainly used in the frontend but also in some cases in the backend.
 * It's important to set the right $where_hid_del in the object so that the functions operate properly
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 * @see tslib_fe::fetch_the_id()
 */
class t3lib_pageSelect {
	var $urltypes = Array('','http://','ftp://','mailto:','https://');
	var $where_hid_del = ' AND pages.deleted=0';	// This is not the final clauses. There will normally be conditions for the hidden,starttime and endtime fields as well. You MUST initialize the object by the init() function
	var $where_groupAccess = '';	// Clause for fe_group access
	var $sys_language_uid = 0;

		// Versioning preview related:
	var $versioningPreview = FALSE;		// If true, preview of other record versions is allowed. THIS MUST ONLY BE SET IF the page is not cached and truely previewed by a backend user!!!
	var $versioningWorkspaceId = 0;		// Workspace ID for preview



		// Internal, dynamic:
	var $error_getRootLine = '';		// Error string set by getRootLine()
	var $error_getRootLine_failPid = 0;		// Error uid set by getRootLine()


	/**
	 * init() MUST be run directly after creating a new template-object
	 * This sets the internal variable $this->where_hid_del to the correct where clause for page records taking deleted/hidden/starttime/endtime/t3ver_state into account
	 *
	 * @param	boolean		If $show_hidden is true, the hidden-field is ignored!! Normally this should be false. Is used for previewing.
	 * @return	void
	 * @see tslib_fe::fetch_the_id(), tx_tstemplateanalyzer::initialize_editor()
	 */
	function init($show_hidden)	{
		$this->where_groupAccess = '';
		$this->where_hid_del = ' AND pages.deleted=0 ';
		if (!$show_hidden)	{
			$this->where_hid_del.= 'AND pages.hidden=0 ';
		}
		$this->where_hid_del.= 'AND (pages.starttime<='.$GLOBALS['SIM_EXEC_TIME'].') AND (pages.endtime=0 OR pages.endtime>'.$GLOBALS['SIM_EXEC_TIME'].') ';

			// Filter out new place-holder pages in case we are NOT in a versioning preview (that means we are online!)
		if (!$this->versioningPreview)	{
			$this->where_hid_del.= ' AND NOT(pages.t3ver_state=1)';
		} else {
				// For version previewing, make sure that enable-fields are not de-selecting hidden pages - we need versionOL() to unset them only if the overlay record instructs us to.
			$this->versioningPreview_where_hid_del = $this->where_hid_del;	// Copy where_hid_del to other variable (used in relation to versionOL())
			$this->where_hid_del = ' AND pages.deleted=0 ';	// Clear where_hid_del
		}
	}

















	/*******************************************
	 *
	 * Selecting page records
	 *
	 ******************************************/

	/**
	 * Returns the $row for the page with uid = $uid (observing ->where_hid_del)
	 * Any pages_language_overlay will be applied before the result is returned.
	 * If no page is found an empty array is returned.
	 *
	 * @param	integer		The page id to look up.
	 * @param	boolean		If set, the check for group access is disabled. VERY rarely used
	 * @return	array		The page row with overlayed localized fields. Empty it no page.
	 * @see getPage_noCheck()
	 */
	function getPage($uid, $disableGroupAccessCheck=FALSE)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'uid='.intval($uid).$this->where_hid_del.($disableGroupAccessCheck ? '' : $this->where_groupAccess));
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$this->versionOL('pages',$row);
			if (is_array($row))		return $this->getPageOverlay($row);
		}
		return Array();
	}

	/**
	 * Return the $row for the page with uid = $uid WITHOUT checking for ->where_hid_del (start- and endtime or hidden). Only "deleted" is checked!
	 *
	 * @param	integer		The page id to look up
	 * @return	array		The page row with overlayed localized fields. Empty it no page.
	 * @see getPage()
	 */
	function getPage_noCheck($uid)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'uid='.intval($uid).$this->deleteClause('pages'));
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$this->versionOL('pages',$row);
			if (is_array($row))		return $this->getPageOverlay($row);
		}
		return Array();
	}

	/**
	 * Returns the $row of the first web-page in the tree (for the default menu...)
	 *
	 * @param	integer		The page id for which to fetch first subpages (PID)
	 * @return	mixed		If found: The page record (with overlayed localized fields, if any). If NOT found: blank value (not array!)
	 * @see tslib_fe::fetch_the_id()
	 */
	function getFirstWebPage($uid)	{
		$output = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid='.intval($uid).$this->where_hid_del.$this->where_groupAccess, '', 'sorting', '1');
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$this->versionOL('pages',$row);
			if (is_array($row))		$output = $this->getPageOverlay($row);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $output;
	}

	/**
	 * Returns a pagerow for the page with alias $alias
	 *
	 * @param	string		The alias to look up the page uid for.
	 * @return	integer		Returns page uid (integer) if found, otherwise 0 (zero)
	 * @see tslib_fe::checkAndSetAlias(), tslib_cObj::typoLink()
	 */
	function getPageIdFromAlias($alias)	{
		$alias = strtolower($alias);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'alias='.$GLOBALS['TYPO3_DB']->fullQuoteStr($alias, 'pages').' AND pid>=0 AND pages.deleted=0');	// "AND pid>=0" because of versioning (means that aliases sent MUST be online!)
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			return $row['uid'];
		}
		return 0;
	}

	/**
	 * Returns the relevant page overlay record fields
	 *
	 * @param	mixed		If $pageInput is an integer, it's the pid of the pageOverlay record and thus the page overlay record is returned. If $pageInput is an array, it's a page-record and based on this page record the language record is found and OVERLAYED before the page record is returned.
	 * @param	integer		Language UID if you want to set an alternative value to $this->sys_language_uid which is default. Should be >=0
	 * @return	array		Page row which is overlayed with language_overlay record (or the overlay record alone)
	 */
	function getPageOverlay($pageInput,$lUid=-1)	{

			// Initialize:
		if ($lUid<0)	$lUid = $this->sys_language_uid;
		$row = NULL;

			// If language UID is different from zero, do overlay:
		if ($lUid)	{
			$fieldArr = explode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields']);
			if (is_array($pageInput))	{
				$page_id = $pageInput['uid'];	// Was the whole record
				$fieldArr = array_intersect($fieldArr,array_keys($pageInput));		// Make sure that only fields which exist in the incoming record are overlaid!
			} else {
				$page_id = $pageInput;	// Was the id
			}

			if (count($fieldArr))	{
				/*
					NOTE to enabledFields('pages_language_overlay'):
					Currently the showHiddenRecords of TSFE set will allow pages_language_overlay records to be selected as they are child-records of a page.
					However you may argue that the showHiddenField flag should determine this. But that's not how it's done right now.
				*/

					// Selecting overlay record:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							implode(',',$fieldArr),
							'pages_language_overlay',
							'pid='.intval($page_id).'
								AND sys_language_uid='.intval($lUid).
								$this->enableFields('pages_language_overlay'),
							'',
							'',
							'1'
						);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$this->versionOL('pages_language_overlay',$row);

				if (is_array($row))	{
					$row['_PAGES_OVERLAY'] = TRUE;

						// Unset vital fields that are NOT allowed to be overlaid:
					unset($row['uid']);
					unset($row['pid']);
				}
			}
		}

			// Create output:
		if (is_array($pageInput))	{
			return is_array($row) ? array_merge($pageInput,$row) : $pageInput;	// If the input was an array, simply overlay the newfound array and return...
		} else {
			return is_array($row) ? $row : array();	// always an array in return
		}
	}

	/**
	 * Creates language-overlay for records in general (where translation is found in records from the same table)
	 *
	 * @param	string		Table name
	 * @param	array		Record to overlay. Must containt uid, pid and $table]['ctrl']['languageField']
	 * @param	integer		Pointer to the sys_language uid for content on the site.
	 * @param	string		Overlay mode. If "hideNonTranslated" then records without translation will not be returned un-translated but unset (and return value is false)
	 * @return	mixed		Returns the input record, possibly overlaid with a translation. But if $OLmode is "hideNonTranslated" then it will return false if no translation is found.
	 */
	function getRecordOverlay($table,$row,$sys_language_content,$OLmode='')	{
		global $TCA;

		if ($row['uid']>0 && $row['pid']>0)	{
			if ($TCA[$table] && $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'])	{
				if (!$TCA[$table]['ctrl']['transOrigPointerTable'])	{	// Will not be able to work with other tables (Just didn't implement it yet; Requires a scan over all tables [ctrl] part for first FIND the table that carries localization information for this table (which could even be more than a single table) and then use that. Could be implemented, but obviously takes a little more....)

						// Will try to overlay a record only if the sys_language_content value is larger than zero.
					if ($sys_language_content>0)	{

							// Must be default language or [All], otherwise no overlaying:
						if ($row[$TCA[$table]['ctrl']['languageField']]<=0)	{

								// Select overlay record:
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								$table,
								'pid='.intval($row['pid']).
									' AND '.$TCA[$table]['ctrl']['languageField'].'='.intval($sys_language_content).
									' AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.intval($row['uid']).
									$this->enableFields($table),
								'',
								'',
								'1'
							);
							$olrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
							$this->versionOL($table,$olrow);
	#debug($row);
	#debug($olrow);
								// Merge record content by traversing all fields:
							if (is_array($olrow))	{
								foreach($row as $fN => $fV)	{
									if ($fN!='uid' && $fN!='pid' && isset($olrow[$fN]))	{

										if ($GLOBALS['TSFE']->TCAcachedExtras[$table]['l10n_mode'][$fN]!='exclude'
												&& ($GLOBALS['TSFE']->TCAcachedExtras[$table]['l10n_mode'][$fN]!='mergeIfNotBlank' || strcmp(trim($olrow[$fN]),'')))	{
											$row[$fN] = $olrow[$fN];
										}
									} elseif ($fN=='uid')	{
										$row['_LOCALIZED_UID'] = $olrow['uid'];
									}
								}
							} elseif ($OLmode==='hideNonTranslated' && $row[$TCA[$table]['ctrl']['languageField']]==0)	{	// Unset, if non-translated records should be hidden. ONLY done if the source record really is default language and not [All] in which case it is allowed.
								unset($row);
							}

							// Otherwise, check if sys_language_content is different from the value of the record - that means a japanese site might try to display french content.
						} elseif ($sys_language_content!=$row[$TCA[$table]['ctrl']['languageField']])	{
							unset($row);
						}
					} else {
							// When default language is displayed, we never want to return a record carrying another language!:
						if ($row[$TCA[$table]['ctrl']['languageField']]>0)	{
							unset($row);
						}
					}
				}
			}
		}

		return $row;
	}


















	/*******************************************
	 *
	 * Page related: Menu, Domain record, Root line
	 *
	 ******************************************/

	/**
	 * Returns an array with pagerows for subpages with pid=$uid (which is pid here!). This is used for menus.
	 * If there are mount points in overlay mode the _MP_PARAM field is set to the corret MPvar.
	 * If the $uid being input does in itself require MPvars to define a correct rootline these must be handled externally to this function.
	 *
	 * @param	integer		The page id for which to fetch subpages (PID)
	 * @param	string		List of fields to select. Default is "*" = all
	 * @param	string		The field to sort by. Default is "sorting"
	 * @param	string		Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
	 * @param	boolean		check if shortcuts exist, checks by default
	 * @return	array		Array with key/value pairs; keys are page-uid numbers. values are the corresponding page records (with overlayed localized fields, if any)
	 * @see tslib_fe::getPageShortcut(), tslib_menu::makeMenu(), tx_wizardcrpages_webfunc_2, tx_wizardsortpages_webfunc_2
	 */
	function getMenu($uid,$fields='*',$sortField='sorting',$addWhere='',$checkShortcuts=1)	{

		$output = Array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'pages', 'pid='.intval($uid).$this->where_hid_del.$this->where_groupAccess.' '.$addWhere, '', $sortField);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$this->versionOL('pages',$row);

			if (is_array($row))	{
					// Keep mount point:
				$origUid = $row['uid'];
				$mount_info = $this->getMountPointInfo($origUid, $row);	// $row MUST have "uid", "pid", "doktype", "mount_pid", "mount_pid_ol" fields in it
				if (is_array($mount_info) && $mount_info['overlay'])	{	// There is a valid mount point.
					$mp_row = $this->getPage($mount_info['mount_pid']);		// Using "getPage" is OK since we need the check for enableFields AND for type 2 of mount pids we DO require a doktype < 200!
					if (count($mp_row))	{
						$row = $mp_row;
						$row['_MP_PARAM'] = $mount_info['MPvar'];
					} else unset($row);	// If the mount point could not be fetched with respect to enableFields, unset the row so it does not become a part of the menu!
				}

					// if shortcut, look up if the target exists and is currently visible
				if ($row['doktype'] == 4 && ($row['shortcut'] || $row['shortcut_mode']) && $checkShortcuts)	{
					if ($row['shortcut_mode'] == 0)	{
						$searchField = 'uid';
						$searchUid = intval($row['shortcut']);
					} else { // check subpages - first subpage or random subpage
						$searchField = 'pid';
							// If a shortcut mode is set and no valid page is given to select subpags from use the actual page.
						$searchUid = intval($row['shortcut'])?intval($row['shortcut']):$row['uid'];
					}
					$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', $searchField.'='.$searchUid.$this->where_hid_del.$this->where_groupAccess.' '.$addWhere, '', $sortField);
					if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res2))	{
						unset($row);
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($res2);
				} elseif ($row['doktype'] == 4 && $checkShortcuts)	{
						// Neither shortcut target nor mode is set. Remove the page from the menu.
					unset($row);
				}

					// Add to output array after overlaying language:
				if (is_array($row))	{
					$output[$origUid] = $this->getPageOverlay($row);
				}
			}
		}
		return $output;
	}

	/**
	 * Will find the page carrying the domain record matching the input domain.
	 * Might exit after sending a redirect-header IF a found domain record instructs to do so.
	 *
	 * @param	string		Domain name to search for. Eg. "www.typo3.com". Typical the HTTP_HOST value.
	 * @param	string		Path for the current script in domain. Eg. "/somedir/subdir". Typ. supplied by t3lib_div::getIndpEnv('SCRIPT_NAME')
	 * @param	string		Request URI: Used to get parameters from if they should be appended. Typ. supplied by t3lib_div::getIndpEnv('REQUEST_URI')
	 * @return	mixed		If found, returns integer with page UID where found. Otherwise blank. Might exit if location-header is sent, see description.
	 * @see tslib_fe::findDomainRecord()
	 */
	function getDomainStartPage($domain, $path='',$request_uri='')	{
		$domain = explode(':',$domain);
		$domain = strtolower(ereg_replace('\.$','',$domain[0]));
			// Removing extra trailing slashes
		$path = trim(ereg_replace('\/[^\/]*$','',$path));
			// Appending to domain string
		$domain.= $path;
		$domain = ereg_replace('\/*$','',$domain);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'pages.uid,sys_domain.redirectTo,sys_domain.prepend_params',
					'pages,sys_domain',
					'pages.uid=sys_domain.pid
						AND sys_domain.hidden=0
						AND (sys_domain.domainName='.$GLOBALS['TYPO3_DB']->fullQuoteStr($domain, 'sys_domain').' OR sys_domain.domainName='.$GLOBALS['TYPO3_DB']->fullQuoteStr($domain.'/', 'sys_domain').') '.
						$this->where_hid_del.$this->where_groupAccess,
					'',
					'',
					1
				);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			if ($row['redirectTo'])	{
				$rURL = $row['redirectTo'];
				if ($row['prepend_params'])	{
					$rURL = ereg_replace('\/$','',$rURL);
					$prependStr = ereg_replace('^\/','',substr($request_uri,strlen($path)));
					$rURL.= '/'.$prependStr;
				}
				Header('Location: '.t3lib_div::locationHeaderUrl($rURL));
				exit;
			} else {
				return $row['uid'];
			}
		}
	}

	/**
	 * Returns array with fields of the pages from here ($uid) and back to the root
	 * NOTICE: This function only takes deleted pages into account! So hidden, starttime and endtime restricted pages are included no matter what.
	 * Further: If any "recycler" page is found (doktype=255) then it will also block for the rootline)
	 * If you want more fields in the rootline records than default such can be added by listing them in $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']
	 *
	 * @param	integer		The page uid for which to seek back to the page tree root.
	 * @param	string		Commalist of MountPoint parameters, eg. "1-2,3-4" etc. Normally this value comes from the GET var, MP
	 * @param	boolean		If set, some errors related to Mount Points in root line are ignored.
	 * @return	array		Array with page records from the root line as values. The array is ordered with the outer records first and root record in the bottom. The keys are numeric but in reverse order. So if you traverse/sort the array by the numeric keys order you will get the order from root and out. If an error is found (like eternal looping or invalid mountpoint) it will return an empty array.
	 * @see tslib_fe::getPageAndRootline()
	 */
	function getRootLine($uid, $MP='', $ignoreMPerrors=FALSE)	{

			// Initialize:
		$selFields = t3lib_div::uniqueList('pid,uid,t3ver_oid,t3ver_wsid,t3ver_state,t3ver_swapmode,title,alias,nav_title,media,layout,hidden,starttime,endtime,fe_group,extendToSubpages,doktype,TSconfig,storage_pid,is_siteroot,mount_pid,mount_pid_ol,fe_login_mode,'.$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);
		$this->error_getRootLine = '';
		$this->error_getRootLine_failPid = 0;

			// Splitting the $MP parameters if present
		$MPA = array();
		if ($MP)	{
			$MPA = explode(',',$MP);
			reset($MPA);
			while(list($MPAk) = each($MPA))	{
				$MPA[$MPAk] = explode('-', $MPA[$MPAk]);
			}
		}

		$loopCheck = 0;
		$theRowArray = Array();
		$uid = intval($uid);

		while ($uid!=0 && $loopCheck<20)	{	// Max 20 levels in the page tree.
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selFields, 'pages', 'uid='.intval($uid).' AND pages.deleted=0 AND pages.doktype!=255');
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$this->versionOL('pages',$row);
				$this->fixVersioningPid('pages',$row);

				if (is_array($row))	{
						// Mount Point page types are allowed ONLY a) if they are the outermost record in rootline and b) if the overlay flag is not set:
					if ($GLOBALS['TYPO3_CONF_VARS']['FE']['enable_mount_pids'] && $row['doktype']==7 && !$ignoreMPerrors)	{
						$mount_info = $this->getMountPointInfo($row['uid'], $row);
						if ($loopCheck>0 || $mount_info['overlay'])	{
							$this->error_getRootLine = 'Illegal Mount Point found in rootline';
							return array();
						}
					}

					$uid = $row['pid'];	// Next uid

					if (count($MPA) && $GLOBALS['TYPO3_CONF_VARS']['FE']['enable_mount_pids'])	{
						$curMP = end($MPA);
						if (!strcmp($row['uid'],$curMP[0]))	{

							array_pop($MPA);
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selFields, 'pages', 'uid='.intval($curMP[1]).' AND pages.deleted=0 AND pages.doktype!=255');
							$mp_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

							$this->versionOL('pages',$mp_row);
							$this->fixVersioningPid('pages',$mp_row);

							if (is_array($mp_row))	{
								$mount_info = $this->getMountPointInfo($mp_row['uid'], $mp_row);
								if (is_array($mount_info) && $mount_info['mount_pid']==$curMP[0])	{
									$uid = $mp_row['pid'];	// Setting next uid

									if ($mount_info['overlay'])	{	// Symlink style: Keep mount point (current row).
										$row['_MOUNT_OL'] = TRUE;	// Set overlay mode:
										$row['_MOUNT_PAGE'] = array(
											'uid' => $mp_row['uid'],
											'pid' => $mp_row['pid'],
											'title' =>  $mp_row['title'],
										);
									} else {	// Normal operation: Insert the mount page row in rootline instead mount point.
										if ($loopCheck>0)	{
											$row = $mp_row;
										} else {
											$this->error_getRootLine = 'Current Page Id is a mounted page of the overlay type and cannot be accessed directly!';
											return array();	// Matching the page id (first run, $loopCheck = 0) with the MPvar is ONLY allowed if the mount point is the "overlay" type (otherwise it could be forged!)
										}
									}

									$row['_MOUNTED_FROM'] = $curMP[0];
									$row['_MP_PARAM'] = $mount_info['MPvar'];
								} else {
									$this->error_getRootLine = 'MP var was corrupted';
									return array();	// The MP variables did NOT connect proper mount points:
								}
							} else {
								$this->error_getRootLine = 'No moint point record found according to PID in MP var';
								return array();	// The second PID in MP var was NOT a valid page.
							}
						}
					}
				}
					// Add row to rootline with language overlaid:
				$theRowArray[] = $this->getPageOverlay($row);
			} else {
				$this->error_getRootLine = 'Broken rootline';
				$this->error_getRootLine_failPid = $uid;
				return array();	// broken rootline.
			}

			$loopCheck++;
		}

			// If the MPA array is NOT empty, we have to return an error; All MP elements were not resolved!
		if (count($MPA))	{
			$this->error_getRootLine = 'MP value remain!';
			return array();
		}

			// Create output array (with reversed order of numeric keys):
		$output = Array();
		$c = count($theRowArray);
		foreach($theRowArray as $key => $val)	{
			$c--;
			$output[$c] = $val;
		}

		return $output;
	}

	/**
	 * Creates a "path" string for the input root line array titles.
	 * Used for writing statistics.
	 *
	 * @param	array		A rootline array!
	 * @param	integer		The max length of each title from the rootline.
	 * @return	string		The path in the form "/page title/This is another pageti.../Another page"
	 * @see tslib_fe::getConfigArray()
	 */
	function getPathFromRootline($rl,$len=20)	{
		if (is_array($rl))	{
			$c=count($rl);
			$path = '';
			for ($a=0;$a<$c;$a++)	{
				if ($rl[$a]['uid'])	{
					$path.='/'.t3lib_div::fixed_lgd_cs(strip_tags($rl[$a]['title']),$len);
				}
			}
			return $path;
		}
	}

	/**
	 * Returns the URL type for the input page row IF the doktype is 3 and not disabled.
	 *
	 * @param	array		The page row to return URL type for
	 * @param	boolean		A flag to simply disable any output from here.
	 * @return	string		The URL type from $this->urltypes array. False if not found or disabled.
	 * @see tslib_fe::setExternalJumpUrl()
	 */
	function getExtURL($pagerow,$disable=0)	{
		if ($pagerow['doktype']==3 && !$disable)	{
			$redirectTo = $this->urltypes[$pagerow['urltype']].$pagerow['url'];

				// If relative path, prefix Site URL:
			$uI = parse_url($redirectTo);
			if (!$uI['scheme'] && substr($redirectTo,0,1)!='/')	{ // relative path assumed now...
				$redirectTo = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$redirectTo;
			}
			return $redirectTo;
		}
	}

	/**
	 * Returns MountPoint id for page
	 * Does a recursive search if the mounted page should be a mount page itself. It has a run-away break so it can't go into infinite loops.
	 *
	 * @param	integer		Page id for which to look for a mount pid. Will be returned only if mount pages are enabled, the correct doktype (7) is set for page and there IS a mount_pid (which has a valid record that is not deleted...)
	 * @param	array		Optional page record for the page id. If not supplied it will be looked up by the system. Must contain at least uid,pid,doktype,mount_pid,mount_pid_ol
	 * @param	array		Array accumulating formerly tested page ids for mount points. Used for recursivity brake.
	 * @param	integer		The first page id.
	 * @return	mixed		Returns FALSE if no mount point was found, "-1" if there should have been one, but no connection to it, otherwise an array with information about mount pid and modes.
	 * @see tslib_menu
	 */
	function getMountPointInfo($pageId, $pageRec=FALSE, $prevMountPids=array(), $firstPageUid=0)	{
		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['enable_mount_pids'])	{

				// Get pageRec if not supplied:
			if (!is_array($pageRec))	{
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,pid,doktype,mount_pid,mount_pid_ol,t3ver_state', 'pages', 'uid='.intval($pageId).' AND pages.deleted=0 AND pages.doktype!=255');
				$pageRec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$this->versionOL('pages',$pageRec);		// Only look for version overlay if page record is not supplied; This assumes that the input record is overlaid with preview version, if any!
			}

				// Set first Page uid:
			if (!$firstPageUid)	$firstPageUid = $pageRec['uid'];

				// Look for mount pid value plus other required circumstances:
			$mount_pid = intval($pageRec['mount_pid']);
			if (is_array($pageRec) && $pageRec['doktype']==7 && $mount_pid>0 && !in_array($mount_pid, $prevMountPids))	{

					// Get the mount point record (to verify its general existence):
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,pid,doktype,mount_pid,mount_pid_ol,t3ver_state', 'pages', 'uid='.$mount_pid.' AND pages.deleted=0 AND pages.doktype!=255');
				$mount_rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$this->versionOL('pages',$mount_rec);

				if (is_array($mount_rec))	{
						// Look for recursive mount point:
					$prevMountPids[] = $mount_pid;
					$recursiveMountPid = $this->getMountPointInfo($mount_pid, $mount_rec, $prevMountPids, $firstPageUid);

						// Return mount point information:
					return $recursiveMountPid ?
								$recursiveMountPid :
								array(
									'mount_pid' => $mount_pid,
									'overlay' => $pageRec['mount_pid_ol'],
									'MPvar' => $mount_pid.'-'.$firstPageUid,
									'mount_point_rec' => $pageRec,
									'mount_pid_rec' => $mount_rec,
								);
				} else {
					return -1;	// Means, there SHOULD have been a mount point, but there was none!
				}
			}
		}

		return FALSE;
	}

















	/*********************************
	 *
	 * Selecting records in general
	 *
	 **********************************/

	/**
	 * Checks if a record exists and is accessible.
	 * The row is returned if everything's OK.
	 *
	 * @param	string		The table name to search
	 * @param	integer		The uid to look up in $table
	 * @param	boolean		If checkPage is set, it's also required that the page on which the record resides is accessible
	 * @return	mixed		Returns array (the record) if OK, otherwise blank/0 (zero)
	 */
	function checkRecord($table,$uid,$checkPage=0)	{
		global $TCA;
		$uid=intval($uid);
		if (is_array($TCA[$table])) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, 'uid='.intval($uid).$this->enableFields($table));
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$this->versionOL($table,$row);
				$GLOBALS['TYPO3_DB']->sql_free_result($res);

				if (is_array($row))	{
					if ($checkPage)	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'uid='.intval($row['pid']).$this->enableFields('pages'));
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($res))	{
							return $row;
						} else {
							return 0;
						}
					} else {
						return $row;
					}
				}
			}
		}
	}

	/**
	 * Returns record no matter what - except if record is deleted
	 *
	 * @param	string		The table name to search
	 * @param	integer		The uid to look up in $table
	 * @param	string		The fields to select, default is "*"
	 * @param	boolean		If set, no version overlay is applied
	 * @return	mixed		Returns array (the record) if found, otherwise blank/0 (zero)
	 * @see getPage_noCheck()
	 */
	function getRawRecord($table,$uid,$fields='*',$noWSOL=FALSE)	{
		global $TCA;
		$uid = intval($uid);
		if (is_array($TCA[$table]) || $table=='pages') {	// Excluding pages here so we can ask the function BEFORE TCA gets initialized. Support for this is followed up in deleteClause()...
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, 'uid='.intval($uid).$this->deleteClause($table));
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				if (!$noWSOL)	{
					$this->versionOL($table,$row);
				}
				if (is_array($row))	return $row;
			}
		}
	}

	/**
	 * Selects records based on matching a field (ei. other than UID) with a value
	 *
	 * @param	string		The table name to search, eg. "pages" or "tt_content"
	 * @param	string		The fieldname to match, eg. "uid" or "alias"
	 * @param	string		The value that fieldname must match, eg. "123" or "frontpage"
	 * @param	string		Optional additional WHERE clauses put in the end of the query. DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	mixed		Returns array (the record) if found, otherwise blank/0 (zero)
	 */
	function getRecordsByField($theTable,$theField,$theValue,$whereClause='',$groupBy='',$orderBy='',$limit='')	{
		global $TCA;
		if (is_array($TCA[$theTable])) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						$theTable,
						$theField.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($theValue, $theTable).
							$this->deleteClause($theTable).' '.
								$whereClause,	// whereClauseMightContainGroupOrderBy
						$groupBy,
						$orderBy,
						$limit
					);
			$rows = array();
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				#$this->versionOL($theTable,$row);	// not used since records here are fetched based on other fields than uid!
				if (is_array($row)) $rows[] = $row;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			if (count($rows))	return $rows;
		}
	}














	/*********************************
	 *
	 * Caching and standard clauses
	 *
	 **********************************/

	/**
	 * Returns string value stored for the hash string in the table "cache_hash"
	 * Can be used to retrieved a cached value
	 * Can be used from your frontend plugins if you like. Is also used to store the parsed TypoScript template structures. You can call it directly like t3lib_pageSelect::getHash()
	 *
	 * @param	string		The hash-string which was used to store the data value
	 * @param	integer		Allowed expiretime in seconds. Basically a record is selected only if it is not older than this value in seconds. If expTime is not set, the hashed value will never expire.
	 * @return	string		The "content" field of the "cache_hash" table row.
	 * @see tslib_TStemplate::start(), storeHash()
	 */
	function getHash($hash,$expTime=0)	{
			//
		$expTime = intval($expTime);
		if ($expTime)	{
			$whereAdd = ' AND tstamp > '.(time()-$expTime);
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('content', 'cache_hash', 'hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($hash, 'cache_hash').$whereAdd);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			return $row['content'];
		}
	}

	/**
	 * Stores a string value in the cache_hash table identified by $hash.
	 * Can be used from your frontend plugins if you like. You can call it directly like t3lib_pageSelect::storeHash()
	 *
	 * @param	string		32 bit hash string (eg. a md5 hash of a serialized array identifying the data being stored)
	 * @param	string		The data string. If you want to store an array, then just serialize it first.
	 * @param	string		$ident is just a textual identification in order to inform about the content! May be 20 characters long.
	 * @return	void
	 * @see tslib_TStemplate::start(), getHash()
	 */
	function storeHash($hash,$data,$ident)	{
		$insertFields = array(
			'hash' => $hash,
			'content' => $data,
			'ident' => $ident,
			'tstamp' => time()
		);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_hash', 'hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($hash, 'cache_hash'));
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('cache_hash', $insertFields);
	}

	/**
	 * Returns the "AND NOT deleted" clause for the tablename given IF $TCA configuration points to such a field.
	 *
	 * @param	string		Tablename
	 * @return	string
	 * @see enableFields()
	 */
	function deleteClause($table)	{
		global $TCA;
		if (!strcmp($table,'pages'))	{	// Hardcode for pages because TCA might not be loaded yet (early frontend initialization)
			return ' AND pages.deleted=0';
		} else {
			return $TCA[$table]['ctrl']['delete'] ? ' AND '.$table.'.'.$TCA[$table]['ctrl']['delete'].'=0' : '';
		}
	}

	/**
	 * Returns a part of a WHERE clause which will filter out records with start/end times or hidden/fe_groups fields set to values that should de-select them according to the current time, preview settings or user login. Definitely a frontend function.
	 * Is using the $TCA arrays "ctrl" part where the key "enablefields" determines for each table which of these features applies to that table.
	 *
	 * @param	string		Table name found in the $TCA array
	 * @param	integer		If $show_hidden is set (0/1), any hidden-fields in records are ignored. NOTICE: If you call this function, consider what to do with the show_hidden parameter. Maybe it should be set? See tslib_cObj->enableFields where it's implemented correctly.
	 * @param	array		Array you can pass where keys can be "disabled", "starttime", "endtime", "fe_group" (keys from "enablefields" in TCA) and if set they will make sure that part of the clause is not added. Thus disables the specific part of the clause. For previewing etc.
	 * @param	boolean		If set, enableFields will be applied regardless of any versioning preview settings which might otherwise disable enableFields
	 * @return	string		The clause starting like " AND ...=... AND ...=..."
	 * @see tslib_cObj::enableFields(), deleteClause()
	 */
	function enableFields($table,$show_hidden=-1,$ignore_array=array(),$noVersionPreview=FALSE)	{
		global $TYPO3_CONF_VARS;

		if ($show_hidden==-1 && is_object($GLOBALS['TSFE']))	{	// If show_hidden was not set from outside and if TSFE is an object, set it based on showHiddenPage and showHiddenRecords from TSFE
			$show_hidden = $table=='pages' ? $GLOBALS['TSFE']->showHiddenPage : $GLOBALS['TSFE']->showHiddenRecords;
		}
		if ($show_hidden==-1)	$show_hidden=0;	// If show_hidden was not changed during the previous evaluation, do it here.

		$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
		$query='';
		if (is_array($ctrl))	{

				// Delete field check:
			if ($ctrl['delete'])	{
				$query.=' AND '.$table.'.'.$ctrl['delete'].'=0';
			}

				// Filter out new place-holder records in case we are NOT in a versioning preview (that means we are online!)
			if ($ctrl['versioningWS'] && !$this->versioningPreview)	{
				$query.=' AND '.$table.'.t3ver_state!=1';	// Shadow state for new items MUST be ignored!
			}

				// Enable fields:
			if (is_array($ctrl['enablecolumns']))	{
				if (!$this->versioningPreview || !$ctrl['versioningWS'] || $noVersionPreview) { // In case of versioning-preview, enableFields are ignored (checked in versionOL())
					if ($ctrl['enablecolumns']['disabled'] && !$show_hidden && !$ignore_array['disabled']) {
						$field = $table.'.'.$ctrl['enablecolumns']['disabled'];
						$query.=' AND '.$field.'=0';
					}
					if ($ctrl['enablecolumns']['starttime'] && !$ignore_array['starttime']) {
						$field = $table.'.'.$ctrl['enablecolumns']['starttime'];
						$query.=' AND ('.$field.'<='.$GLOBALS['SIM_EXEC_TIME'].')';
					}
					if ($ctrl['enablecolumns']['endtime'] && !$ignore_array['endtime']) {
						$field = $table.'.'.$ctrl['enablecolumns']['endtime'];
						$query.=' AND ('.$field.'=0 OR '.$field.'>'.$GLOBALS['SIM_EXEC_TIME'].')';
					}
					if ($ctrl['enablecolumns']['fe_group'] && !$ignore_array['fe_group']) {
						$field = $table.'.'.$ctrl['enablecolumns']['fe_group'];
						$query.= $this->getMultipleGroupsWhereClause($field, $table);
					}

					// Call hook functions for additional enableColumns
					// It is used by the extension ingmar_accessctrl which enables assigning more than one usergroup to content and page records
					if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns']))    {
						$_params = array(
							'table' => $table,
							'show_hidden' => $show_hidden,
							'ignore_array' => $ignore_array,
							'ctrl' => $ctrl
						);
						foreach($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns'] as $_funcRef)    {
							$query .= t3lib_div::callUserFunction($_funcRef,$_params,$this);
						}
					}
				}
			}
		} else {
			die ('NO entry in the $TCA-array for the table "'.$table.'". This means that the function enableFields() is called with an invalid table name as argument.');
		}

		return $query;
	}

	/**
	 * Creating where-clause for checking group access to elements in enableFields function
	 *
	 * @param	string		Field with group list
	 * @param	string		Table name
	 * @return	string		AND sql-clause
	 * @see enableFields()
	 */
	function getMultipleGroupsWhereClause($field, $table)	{
		$memberGroups = t3lib_div::intExplode(',',$GLOBALS['TSFE']->gr_list);
		$orChecks=array();
		$orChecks[]=$field.'=\'\'';	// If the field is empty, then OK
		$orChecks[]=$field.' IS NULL';	// If the field is NULL, then OK
		$orChecks[]=$field.'=\'0\'';	// If the field contsains zero, then OK

		foreach($memberGroups as $value)	{
			$orChecks[] = $GLOBALS['TYPO3_DB']->listQuery($field, $value, $table);
		}

		return ' AND ('.implode(' OR ',$orChecks).')';
	}















	/*********************************
	 *
	 * Versioning Preview
	 *
	 **********************************/

	/**
	 * Finding online PID for offline version record
	 * ONLY active when backend user is previewing records. MUST NEVER affect a site served which is not previewed by backend users!!!
	 * Will look if the "pid" value of the input record is -1 (it is an offline version) and if the table supports versioning; if so, it will translate the -1 PID into the PID of the original record
	 * Used whenever you are tracking something back, like making the root line.
	 * Principle; Record offline! => Find online?
	 *
	 * @param	string		Table name
	 * @param	array		Record array passed by reference. As minimum, "pid" and "uid" fields must exist! "t3ver_oid" and "t3ver_wsid" is nice and will save you a DB query.
	 * @return	void		(Passed by ref).
	 * @see t3lib_BEfunc::fixVersioningPid(), versionOL(), getRootLine()
	 */
	function fixVersioningPid($table,&$rr)	{
		global $TCA;

		if ($this->versioningPreview && is_array($rr) && $rr['pid']==-1 && ($table=='pages' || $TCA[$table]['ctrl']['versioningWS']))	{	// Have to hardcode it for "pages" table since TCA is not loaded at this moment!

				// Check values for t3ver_oid and t3ver_wsid:
			if (isset($rr['t3ver_oid']) && isset($rr['t3ver_wsid']))	{	// If "t3ver_oid" is already a field, just set this:
				$oid = $rr['t3ver_oid'];
				$wsid = $rr['t3ver_wsid'];
			} else {	// Otherwise we have to expect "uid" to be in the record and look up based on this:
				$newPidRec = $this->getRawRecord($table,$rr['uid'],'t3ver_oid,t3ver_wsid',TRUE);
				if (is_array($newPidRec))	{
					$oid = $newPidRec['t3ver_oid'];
					$wsid = $newPidRec['t3ver_wsid'];
				}
			}

				// If workspace ids matches and ID of current online version is found, look up the PID value of that:
			if ($oid && !strcmp((int)$wsid,$this->versioningWorkspaceId))	{
				$oidRec = $this->getRawRecord($table,$oid,'pid',TRUE);

				if (is_array($oidRec))	{
					# SWAP uid as well? Well no, because when fixing a versioning PID happens it is assumed that this is a "branch" type page and therefore the uid should be kept (like in versionOL()). However if the page is NOT a branch version it should not happen - but then again, direct access to that uid should not happen!
					$rr['_ORIG_pid'] = $rr['pid'];
					$rr['pid'] = $oidRec['pid'];
				}
			}
		}
	}

	/**
	 * Versioning Preview Overlay
	 * ONLY active when backend user is previewing records. MUST NEVER affect a site served which is not previewed by backend users!!!
	 * Generally ALWAYS used when records are selected based on uid or pid. If records are selected on other fields than uid or pid (eg. "email = ....") then usage might produce undesired results and that should be evaluated on individual basis.
	 * Principle; Record online! => Find offline?
	 *
	 * @param	string		Table name
	 * @param	array		Record array passed by reference. As minimum, the "uid", "pid" and "t3ver_state" fields must exist! The record MAY be set to FALSE in which case the calling function should act as if the record is forbidden to access!
	 * @return	void		(Passed by ref).
	 * @see fixVersioningPid(), t3lib_BEfunc::workspaceOL()
	 */
	function versionOL($table,&$row)	{
		global $TCA;

		if ($this->versioningPreview && is_array($row))	{
			if ($wsAlt = $this->getWorkspaceVersionOfRecord($this->versioningWorkspaceId, $table, $row['uid'], implode(',',array_keys($row))))	{	// implode(',',array_keys($row)) = Using fields from original record to make sure no additional fields are selected. This is best for eg. getPageOverlay()
				if (is_array($wsAlt))	{

						// Always fix PID (like in fixVersioningPid() above). [This is usually not the important factor for versioning OL]
					$wsAlt['_ORIG_pid'] = $wsAlt['pid'];	// Keep the old (-1) - indicates it was a version...
					$wsAlt['pid'] = $row['pid'];		// Set in the online versions PID.

						// "element" and "page" type versions:
						// For versions of single elements or page+content, preserve online UID and PID (this will produce true "overlay" of element _content_, not any references)
						// For page+content the "_ORIG_uid" should actually be used as PID for selection of tables with "versioning_followPages" enabled.
					if ($table!=='pages' || $wsAlt['t3ver_swapmode']<=0)	{
						$wsAlt['_ORIG_uid'] = $wsAlt['uid'];
						$wsAlt['uid'] = $row['uid'];

							// Translate page alias as well so links are pointing to the _online_ page:
						if ($table==='pages')	{
							$wsAlt['alias'] = $row['alias'];
						}
					} else {
							// "branch" versions:
							// Keeping overlay uid and pid so references are changed. This is only for page-versions with BRANCH below!
						$wsAlt['_ONLINE_uid'] = $row['uid'];	// The UID of the versionized record is kept and the uid of the online version is stored
					}

						// Changing input record to the workspace version alternative:
					$row = $wsAlt;

						// Check if it is deleted in workspace:
					if ((int)$row['t3ver_state']===2)	{
						$row = FALSE;	// Unset record if it turned out to be deleted in workspace
					}
				} else {
						// No version found, then check if t3ver_state =1 (online version is dummy-representation)
					if ($wsAlt==-1 || (int)$row['t3ver_state']===1)	{
						$row = FALSE;	// Unset record if it turned out to be "hidden"
					}
				}
			}
		}
	}

	/**
	 * Select the version of a record for a workspace
	 *
	 * @param	integer		Workspace ID
	 * @param	string		Table name to select from
	 * @param	integer		Record uid for which to find workspace version.
	 * @param	string		Field list to select
	 * @return	array		If found, return record, otherwise false. Returns 1 if version was sought for but not found, returns -1 if record existed but had enableFields that would disable it.
	 * @see t3lib_befunc::getWorkspaceVersionOfRecord()
	 */
	function getWorkspaceVersionOfRecord($workspace, $table, $uid, $fields='*')	{
		global $TCA;

		if ($workspace!==0 && ($table=='pages' || $TCA[$table]['ctrl']['versioningWS']))	{	// Have to hardcode it for "pages" table since TCA is not loaded at this moment!

				// Setting up enableFields for version record:
			if ($table=='pages')	{
				$enFields = $this->versioningPreview_where_hid_del;
			} else {
				$enFields = $this->enableFields($table,-1,array(),TRUE);
			}

				// Select workspace version of record, only testing for deleted.
			list($newrow) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				$fields,
				$table,
				'pid=-1 AND
				 t3ver_oid='.intval($uid).' AND
				 t3ver_wsid='.intval($workspace).
					$this->deleteClause($table)
			);

				// If version found, check if it could have been selected with enableFields on as well:
			if (is_array($newrow))	{
				if ($GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid',
					$table,
					'pid=-1 AND
						t3ver_oid='.intval($uid).' AND
						t3ver_wsid='.intval($workspace).
						$enFields
				)) {
					return $newrow;	// Return offline version, tested for its enableFields.
				} else {
					return -1;	// Return -1 because version was de-selected due to its enableFields.
				}
			} else {
					// OK, so no workspace version was found. Then check if online version can be selected with full enable fields and if so, return 1:
				if ($GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid',
					$table,
					'uid='.intval($uid).$enFields
				))	{
					return 1;	// Means search was done, but no version found.
				} else {
					return -1;	// Return -1 because the current record was de-selected due to its enableFields.
				}
			}
		}

		return FALSE;	// No look up in database because versioning not enabled / or workspace not offline
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_page.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_page.php']);
}
?>
