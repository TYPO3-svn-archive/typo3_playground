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
	
	this.expandCollapseRecord = function(objectId, expandSingle) {
			// if only a single record should be visibly for that set of records
			// and the record clicked itself is no visible, collapse all others
		if (expandSingle && !Element.visible(objectId+'_fields')) this.collapseAllRecords(objectId);
		Element.toggle(objectId+'_fields');
		return false;
	}
	
	this.collapseAllRecords = function(objectId) {
			// get the form field, where all records are stored
		var objectName = prependFormFieldNames+'[__ctrl][records]'+this.parseFormElementName('parts', objectId, 3, 2);
		var formObj = document.getElementsByName(objectName);

		if (formObj) {
				// the uid of the calling object (last part in objectId)
			var callingUid = this.parseFormElementName('none', objectId, 1);
			var objectPrefix = this.parseFormElementName('full', objectId, 0 , 1);

			var records = formObj[0].value.split(',');
			for (var i = 0; i < records.length; i++) {
				if (records[i] != callingUid) Element.hide(objectPrefix+'['+records[i]+']_fields');
			}
		}
	}
	
	this.createNewRecord = function(objectId) {
		this.makeAjaxCall('createNewRecord', objectId);
		return false;
	}

	this.makeAjaxCall = function() {
		if (arguments.length > 1) {
			var params = '';
			for (var i = 0; i < arguments.length; i++) params += '&ajax['+i+']='+arguments[i];

			var url = 'alt_doc_ajax.php';
			var options = {
				method:		'post',
				parameters:	params,
				onSuccess:	inlineRelational.processAjaxResponse
			};
			
			new Ajax.Request(url, options);
		}
	}
	
	this.processAjaxResponse = function(xhr) {
		var json = eval('('+xhr.responseText+')');
		for (var i in json.scriptCall) eval(json.scriptCall[i]);
	}
	
	this.importNewRecord = function(objectId, foreignUid) {
		inline_createNewRecord(objectId, foreignUid);
		
		return false;
	}
	
	this.domAddNewRecord = function(method, objectId, htmlData) {
		if (method == 'bottom')
			new Insertion.Bottom(objectId, htmlData);
		else if (method == 'after')
			new Insertion.After(objectId, htmlData);
	}
	
	this.changeSorting = function(objectId, direction) {
		var objectName = prependFormFieldNames+'[__ctrl][records]'+this.parseFormElementName('parts', objectId, 3, 2);
		var formObj = document.getElementsByName(objectName);
		
		if (formObj) {
				// the uid of the calling object (last part in objectId)
			var callingUid = this.parseFormElementName('none', objectId, 1);
			var records = formObj[0].value.split(',');
			var current = records.indexOf(callingUid);
			var changed = false;
			
				// move up
			if (direction > 0 && current > 0) {
				records[current] = records[current-1];
				records[current-1] = callingUid;
				changed = true;
				
				// move down
			} else if (direction < 0 && current < records.length-1) {
				records[current] = records[current+1];
				records[current+1] = callingUid;
				changed = true;
				
			}
			
			if (changed) {
				formObj[0].value = records.join(',');
				this.redrawSection(objectId, records);
			}
		}
		
		return false;
	}
	
	this.redrawSection = function(objectId, records) {
		var i = 0;
		var tempSectionObj = new Array();
		var tempRecordObj;

		var objectPrefix = this.parseFormElementName('full', objectId, 0 , 1);
		var sectionId = this.parseFormElementName('full', objectId, 0 , 2);
		var sectionObj = $(sectionId);
		
			// if no records were passed, fetch them from form field
		if (typeof records == 'undefined') {
			records = new Array();
			var objectName = prependFormFieldNames+'[__ctrl][records]'+this.parseFormElementName('parts', objectId, 3, 2);
			var formObj = document.getElementsByName(objectName);
			if (formObj) records = formObj[0].value.split(',');
		}
		
			// clone records from the section, in the new sorting order
		for (i = 0; i < records.length; i++) {
			tempSectionObj.push(
				$(objectPrefix+'['+records[i]+']_div').cloneNode(true)
			);
		}
		
			// remove records from the section
		while (sectionObj.hasChildNodes()) {
			sectionObj.removeChild(sectionObj.firstChild);
		}
		
			// add the cloned records back to the section
		for (i = 0; i < tempSectionObj.length; i++) {
			sectionObj.appendChild(tempSectionObj[i]);
		}
		
		this.redrawSortingButtons(objectPrefix, records);
	}
	
	this.redrawSortingButtons = function(objectPrefix, records) {
		var i;
		var headerObj;
		var sortingObj = new Array();
		
			// if no records were passed, fetch them from form field
		if (typeof records == 'undefined') {
			records = new Array();
			var objectName = prependFormFieldNames+'[__ctrl][records]'+this.parseFormElementName('parts', objectPrefix, 3, 1);
			var formObj = document.getElementsByName(objectName);
			if (formObj) records = formObj[0].value.split(',');
		}
		
		for (i = 0; i < records.length; i++) {
			if (!records[i].length) continue;
			
			headerObj = $(objectPrefix+'['+records[i]+']_header');
			sortingObj[0] = headerObj.getElementsByClassName('sortingUp');
			sortingObj[1] = headerObj.getElementsByClassName('sortingDown');
			
			if (sortingObj[0].length)
				sortingObj[0][0].style.visibility = i == 0 ? 'hidden' : 'visible';
			if (sortingObj[1].length)
				sortingObj[1][0].style.visibility = i == records.length-1 ? 'hidden' : 'visible';
		}
	}
	
	this.memorizeAddRecord = function(objectPrefix, newUid, afterUid) {
		var objectName = prependFormFieldNames+'[__ctrl][records]'+this.parseFormElementName('parts', objectPrefix, 3, 1);
		var formObj = document.getElementsByName(objectName);

		if (formObj) {
			var records = new Array();
			if (formObj[0].value.length) records = formObj[0].value.split(',');
			
			if (afterUid) {
				var newRecords = new Array();
				for (var i = 0; i < records.length; i++) {
					if (records[i].length) newRecords.push(records[i]);
					if (afterUid == records[i]) newRecords.push(newUid);
				}
				records = newRecords;
			} else {
				records.push(newUid);
			}
			formObj[0].value = records.join(',');
		}

		this.redrawSortingButtons(objectPrefix, records);
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
		
		var objectPrefix = this.parseFormElementName('full', objectId, 0 , 1);
		this.redrawSortingButtons(objectPrefix);
		
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
		objectId = objectId.substr(0, objectId.lastIndexOf(']')).substr(objectId.indexOf('[')+1);
		
		if (!wrap) wrap = 'full';
		if (!skipRight) skipRight = 0;
		
		var elReturn;
		var elParts = new Array();
		var idParts = objectId.split('][');
		for (var i = 0; i < skipRight; i++) idParts.pop();

		if (rightCount > 0) {
			for (var i = 0; i < rightCount; i++) elParts.unshift(idParts.pop());
		} else {
			for (var i = 0; i < -rightCount; i++) idParts.shift();
			elParts = idParts;
		}
		
		if (wrap == 'full') {
			elReturn = prependFormFieldNames+'['+elParts.join('][')+']';
		} else if (wrap == 'parts') {
			elReturn = '['+elParts.join('][')+']';
		} else if (wrap == 'none') {
			elReturn = elParts.length > 1 ? elParts : elParts.join('');
		}

		return elReturn;
	}
	
	this.initSortable = function() {
		
	}
}

var inline = new inlineRelational();

/*]]>*/