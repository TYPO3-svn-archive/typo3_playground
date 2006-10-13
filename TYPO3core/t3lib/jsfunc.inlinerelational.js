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
	var prependFormFieldNames = 'inline';
	
	this.setPrependFormFieldNames = function(value) {
		prependFormFieldNames = value;
	}
	
	this.expandCollapseRecord = function(objectId) {
		Element.toggle(objectId+'_fields');
		return false;
	}
	
	this.collapseAllRecords = function(objectId) {
		
		return false;
	}
	
	this.createNewRecord = function(objectId) {
		inline_createNewRecord(objectId);
		return false;
	}
	
	this.domAddNewRecord = function(method, objectId, htmlData) {
		if (method == 'bottom')
			new Insertion.Bottom(objectId, htmlData);
		else if (method == 'after')
			new Insertion.After(objectId, htmlData);
	}
	
	this.memorizeAddRecord = function(objectName, newUid, afterUid) {
		var formObj = document.getElementsByName(objectName);
		if (formObj) {
			var parts = new Array();
			if (formObj[0].value.length) parts = formObj[0].value.split(',');
			
			if (afterUid) {
				var newParts = new Array();
				for (var i = 0; i < parts.length; i++) {
					if (parts[i].length) newParts.push(parts[i]);
					if (afterUid == parts[i]) newParts.push(newUid);
				}
				parts = newParts;
			} else {
				parts.push(newUid);
			}
			formObj[0].value = parts.join(',');
		}
	}
	
	this.memorizeRemoveRecord = function(objectName, removeUid) {
		var formObj = document.getElementsByName(objectName);
		if (formObj) {
			var parts = new Array();
			var newParts = new Array();
			if (formObj[0].value.length) parts = formObj[0].value.split(',');
			
			for (var i = 0; i < parts.length; i++) {
				if (parts[i] != removeUid) newParts.push(parts[i]);
			}
			
			formObj[0].value = newParts.join(',');
		}
	}
	
	this.enableDisableRecord = function(objectId) {
		var elName = this.parseFormElementName('full', objectId, 2);
		var imageObj = $(objectId+'_disabled');
		var valueObj = document.getElementsByName(elName+'[hidden]');
		var formObj = document.getElementsByName(elName+'[hidden]_0');
		var imagePath = '';
		
		if (valueObj && formObj) {
			formObj[0].click();
			imagePath = this.parsePath(imageObj.src);
			imageObj.src = imagePath+(valueObj[0].value > 0 ? 'button_unhide.gif' : 'button_hide.gif');
		}
		
		return false;
	}
	
	this.deleteRecord = function(objectId) {
		if ($(objectId+'_div') && $(objectId+'_div').getAttribute('isnewrecord') == '1') {
			Element.remove(objectId+'_div');
		} else {
			var elName = this.parseFormElementName('full', objectId, 2);
			document.getElementsByName(elName+'[__deleted]')[0].value = 'deleted';
			Element.hide(objectId+'_div');
		}

		this.memorizeRemoveRecord(
			prependFormFieldNames+'[__ctrl][records]'+this.parseFormElementName('parts', objectId, 3, 2),
			this.parseFormElementName('none', objectId, 1)
		);
		
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
	
	this.parseFormElementName = function(wrap, objectId, rightCount, skipRight) {
			// remove left and right side "inline[...|...]" -> '...|...'
		objectId = objectId.substr(0, objectId.lastIndexOf(']')).substr(objectId.indexOf('['+1));
		if (!wrap) wrap = 'full';
		if (!skipRight) skipRight = 0;
		
		var elReturn;
		var elParts = new Array();
		var idParts = objectId.split('][');
		for (var i = 0; i < skipRight; i++) idParts.pop();
		for (var i = 0; i < rightCount; i++) elParts.unshift(idParts.pop());
		
		if (wrap == 'full') {
			elReturn = prependFormFieldNames+'['+elParts.join('][')+']';
		} else if (wrap == 'parts') {
			elReturn = '['+elParts.join('][')+']';
		} else if (wrap == 'none') {
			elReturn = elParts.length > 1 ? elParts : elParts.join('');
		}

		return elReturn;
	}
}

var inline = new inlineRelational();

/*]]>*/