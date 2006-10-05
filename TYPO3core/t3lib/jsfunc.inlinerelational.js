/*<![CDATA[*/

/***************************************************************
*  Inline-Relational-Record Editing
*
*
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

 function inlineRelational() {
	var inlineRelational = this;
	
	this.expandCollapseRecord = function(objectId) {
		var fieldsObj = document.getElementById(objectId+'_fields');
		
		if (fieldsObj) {
			fieldsObj.style.display = fieldsObj.style.display == 'none' ? '' : 'none';
		}
		
		return false;
	}
	
	this.collapseAllRecords = function(objectId) {
		
		return false;
	}
	
	this.createNewRecord = function(objectId) {
		
		return false;
	}
	
	this.enableDisableRecord = function(objectId) {
		var imageObj = document.getElementById(objectId+'_disabled');
		var valueObj = document.getElementsByName(objectId+'[hidden]');
		var formObj = document.getElementsByName(objectId+'[hidden]_0');
		var imagePath = '';
		
		if (valueObj && formObj) {
			formObj[0].click();
			imagePath = this.parsePath(imageObj.src);
			imageObj.src = imagePath+(valueObj[0].value > 0 ? 'button_unhide.gif' : 'button_hide.gif');
		}
		
		return false;
	}
	
	this.deleteRecord = function(objectId) {
		
		return false;
	}
	
	this.parseObjectId = function(objectId) {
		
		return false;
	}
	
	this.parsePath = function(path) {
		var backSlash = path.lastIndexOf('\\');
		var normalSlash = path.lastIndexOf('/');
		
		if (backSlash > 0)
			path = path.substring(0,backSlash+1);
		else if (normalSlash > 0)
			path = path.substring(0,normalSlash+1);
		else
			path = '';
			
		return path;
	}
}

var inline = new inlineRelational();

/*]]>*/