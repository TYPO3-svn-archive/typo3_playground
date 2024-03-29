/*<![CDATA[*/

/***************************************************************
*  Inline-Relational-Record Editing
*
* $Id: jsfunc.inline.js 1899 2007-01-09 12:55:52Z kurfuerst $
*
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

var inline = {
	prependFormFieldNames: 'data',
	noTitleString: '[No title]',
	data: {},

	addToDataArray: function(object) { for (var i in object) { this.data[i] = $H(this.data[i]).merge(object[i]); } },
	setPrependFormFieldNames: function(value) {	this.prependFormFieldNames = value; },
	setNoTitleString: function(value) { this.noTitleString = value; },

	expandCollapseRecord: function(objectId, expandSingle) {
		var currentUid = this.parseFormElementName('none', objectId, 1);
		var objectPrefix = this.parseFormElementName('full', objectId, 0, 1);

		var currentState = '';
		var collapse = new Array();
		var expand = new Array();

			// if only a single record should be visibly for that set of records
			// and the record clicked itself is no visible, collapse all others
		if (expandSingle && !Element.visible(objectId+'_fields'))
			collapse = this.collapseAllRecords(objectId, objectPrefix, currentUid);

		Element.toggle(objectId+'_fields');
		currentState = Element.visible(objectId+'_fields') ? 1 : 0

		if (this.isNewRecord(objectId))
			this.updateExpandedCollapsedStateLocally(objectId, currentState);
		else if (currentState)
			expand.push(currentUid);
		else if (!currentState)
			collapse.push(currentUid);

		this.setExpandedCollapsedState(objectId, expand.join(','), collapse.join(','));

		return false;
	},

	collapseAllRecords: function(objectId, objectPrefix, callingUid) {
			// get the form field, where all records are stored
		var objectName = this.prependFormFieldNames+this.parseFormElementName('parts', objectId, 3, 2);
		var formObj = document.getElementsByName(objectName);
		var collapse = [];

		if (formObj.length) {
				// the uid of the calling object (last part in objectId)
			var recObjectId = '';

			var records = formObj[0].value.split(',');
			for (var i=0; i<records.length; i++) {
				recObjectId = objectPrefix+'['+records[i]+']';
				if (records[i] != callingUid && Element.visible(recObjectId+'_fields')) {
					Element.hide(recObjectId+'_fields');
					if (this.isNewRecord(recObjectId)) this.updateExpandedCollapsedStateLocally(recObjectId, 0);
					else collapse.push(records[i]);
				}
			}
		}

		return collapse;
	},

	updateExpandedCollapsedStateLocally: function(objectId, value) {
		var ucName = 'uc'+this.parseFormElementName('parts', objectId, 3, 2);
		var ucFormObj = document.getElementsByName(ucName);
		if (ucFormObj.length) ucFormObj[0].value = value;
	},

	createNewRecord: function(objectId,prevRecordUid) {
		if (this.isBelowMax(objectId)) this.makeAjaxCall('createNewRecord', objectId+(prevRecordUid ? '['+prevRecordUid+']' : ''));
		else alert('There are no more relations possible at this moment!');
		return false;
	},

	setExpandedCollapsedState: function(objectId, expand, collapse) {
		this.makeAjaxCall('setExpandedCollapsedState', objectId, expand, collapse);
	},

	makeAjaxCall: function() {
		if (arguments.length > 1) {
			var params = '';
			for (var i=0; i<arguments.length; i++) params += '&ajax['+i+']='+arguments[i];

			var url = 'alt_doc_ajax.php';
			var options = {
				method:		'post',
				parameters:	params,
				onSuccess:	inline.processAjaxResponse,
				onFailure:	inline.showAjaxFailure
			};

			new Ajax.Request(url, options);
		}
	},

	processAjaxResponse: function(xhr) {
		var json = eval('('+xhr.responseText+')');
		for (var i in json.scriptCall) eval(json.scriptCall[i]);
	},

	showAjaxFailure: function(xhr) {
		alert('Error: '+xhr.status+"\n"+xhr.statusText);
	},

		// foreign_selector: used by selector box (type='select')
	importNewRecord: function(objectId) {
		var selector = $(objectId+'_selector');
		if (selector.selectedIndex != -1) {
			var selectedValue = selector.options[selector.selectedIndex].value;
			if (!this.data.unique || !this.data.unique[objectId]) {
				selector.options[selector.selectedIndex].selected = false;
			}
			this.makeAjaxCall('createNewRecord', objectId, selectedValue);
		}
		return false;
	},

		// foreign_selector: used by element browser (type='group/db')
	importElement: function(objectId, table, uid, type) {
		window.setTimeout(
			function() {
				inline.makeAjaxCall('createNewRecord', objectId, uid);
			},
			10
		);
	},

		// Check uniqueness for element browser:
	checkUniqueElement: function(objectId, table, uid, type) {
		if (this.checkUniqueUsed(objectId, uid, table)) {
			return {passed: false,message: 'There is already a relation to the selected element!'};
		} else {
			return {passed: true};
		}
	},

		// Checks if a record was used and should be unique:
	checkUniqueUsed: function(objectId, uid, table) {
		if (this.data.unique && this.data.unique[objectId]) {
			var unique = this.data.unique[objectId];
			var values = $H(unique.used).values();

				// for select: only the uid is stored
			if (unique['type'] == 'select') {
				if (values.indexOf(uid) != -1) return true;
				
				// for group/db: table and uid is stored in a assoc array
			} else if (unique.type == 'groupdb') {
				for (var i=values.length-1; i>=0; i--) {
						// if the pair table:uid is already used:
					if (values[i].table==table && values[i].uid==uid) return true;
				}
			}
		}
		return false;
	},

	setUniqueElement: function(objectId, table, uid, type, elName) {
		var recordUid = this.parseFormElementName('none', elName, 1, 1);
		// alert(objectId+'/'+table+'/'+uid+'/'+recordUid);
		this.setUnique(objectId, recordUid, uid);
	},

		// this function is applied to a newly inserted record by AJAX
		// it removes the used select items, that should be unique
	setUnique: function(objectId, recordUid, selectedValue) {
		if (this.data.unique && this.data.unique[objectId]) {
			var unique = this.data.unique[objectId];

			if (unique.type == 'select') {
					// remove used items from each select-field of the child records
				if (!(unique.selector && unique.max == -1)) {
					var elName = this.parseFormElementName('full', objectId, 1)+'['+recordUid+']['+unique.field+']';
					var formName = this.prependFormFieldNames+this.parseFormElementName('parts', objectId, 3, 1);

					var fieldObj = document.getElementsByName(elName);
					var values = $H(unique.used).values();

					if (fieldObj.length) {
							// remove all before used items from the new select-item
						for (var i=0; i<values.length; i++) this.removeSelectOption(fieldObj[0], values[i]);
							// set the selected item automatically to the first of the remaining items
						selectedValue = fieldObj[0].options[0].value;
						fieldObj[0].options[0].selected = true;
						this.updateUnique(fieldObj[0], objectId, formName, recordUid);
						this.handleChangedField(fieldObj[0], objectId+'['+recordUid+']');
						if (typeof this.data.unique[objectId].used.length != 'undefined') {
							this.data.unique[objectId].used = {};
						}
						this.data.unique[objectId].used[recordUid] = selectedValue;
					}
				}
			} else if (unique.type == 'groupdb') {
					// add the new record to the used items:
				this.data.unique[objectId].used[recordUid] = {'table':unique.elTable, 'uid':selectedValue};
			}

				// remove used items from a selector-box
			if (unique.selector == 'select' && selectedValue) {
				var selector = $(objectId+'_selector');
				this.removeSelectOption(selector, selectedValue);
				this.data.unique[objectId]['used'][recordUid] = selectedValue;
			}
		}
	},

	domAddNewRecord: function(method, insertObject, objectPrefix, htmlData) {
		if (this.isBelowMax(objectPrefix)) {
			if (method == 'bottom')
				new Insertion.Bottom(insertObject, htmlData);
			else if (method == 'after')
				new Insertion.After(insertObject, htmlData);
		}
	},

	changeSorting: function(objectId, direction) {
		var objectName = this.prependFormFieldNames+this.parseFormElementName('parts', objectId, 3, 2);
		var objectPrefix = this.parseFormElementName('full', objectId, 0, 1);
		var formObj = document.getElementsByName(objectName);

		if (formObj.length) {
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
				var cAdj = direction > 0 ? 1 : 0; // adjustment
				$(objectId+'_div').parentNode.insertBefore(
					$(objectPrefix+'['+records[current-cAdj]+']_div'),
					$(objectPrefix+'['+records[current+1-cAdj]+']_div')
				);
				this.redrawSortingButtons(objectPrefix, records);
			}
		}

		return false;
	},

	dragAndDropSorting: function(element) {
		var objectId = element.getAttribute('id').replace(/_records$/, '');
		var objectName = inline.prependFormFieldNames+inline.parseFormElementName('parts', objectId, 3);
		var formObj = document.getElementsByName(objectName);

		if (formObj.length) {
			var checked = new Array();
			var order = Sortable.sequence(element);
			var records = formObj[0].value.split(',');
			
				// check if ordered uid is really part of the records
				// virtually deleted items might still be there but ordering shouldn't saved at all on them
			for (var i=0; i<order.length; i++) {
				if (records.indexOf(order[i]) != -1) {
					checked.push(order[i]);
				}
			}

			formObj[0].value = checked.join(',');

			if (inline.data.config && inline.data.config[objectId]) {
				var table = inline.data.config[objectId].table;
				inline.redrawSortingButtons(objectId+'['+table+']', checked);
			}
		}
	},

	createDragAndDropSorting: function(objectId) {
		Sortable.create(
			objectId,
			{
				format: /^[^_\-](?:[A-Za-z0-9\[\]\-\_]*)\[(.*)\]_div$/,
				onUpdate: inline.dragAndDropSorting,
				tag: 'div',
				handle: 'sortableHandle',
				overlap: 'vertical',
				constraint: 'vertical'
			}
		);
	},

	destroyDragAndDropSorting: function(objectId) {
		Sortable.destroy(objectId);
	},

	redrawSortingButtons: function(objectPrefix, records) {
		var i;
		var headerObj;
		var sortingObj = new Array();

			// if no records were passed, fetch them from form field
		if (typeof records == 'undefined') {
			records = new Array();
			var objectName = this.prependFormFieldNames+this.parseFormElementName('parts', objectPrefix, 3, 1);
			var formObj = document.getElementsByName(objectName);
			if (formObj.length) records = formObj[0].value.split(',');
		}

		for (i=0; i<records.length; i++) {
			if (!records[i].length) continue;

			headerObj = $(objectPrefix+'['+records[i]+']_header');
			sortingObj[0] = headerObj.getElementsByClassName('sortingUp');
			sortingObj[1] = headerObj.getElementsByClassName('sortingDown');

			if (sortingObj[0].length)
				sortingObj[0][0].style.visibility = i == 0 ? 'hidden' : 'visible';
			if (sortingObj[1].length)
				sortingObj[1][0].style.visibility = i == records.length-1 ? 'hidden' : 'visible';
		}
	},

	memorizeAddRecord: function(objectPrefix, newUid, afterUid, selectedValue) {
		if (this.isBelowMax(objectPrefix)) {
			var objectName = this.prependFormFieldNames+this.parseFormElementName('parts', objectPrefix, 3, 1);
			var formObj = document.getElementsByName(objectName);

			if (formObj.length) {
				var records = new Array();
				if (formObj[0].value.length) records = formObj[0].value.split(',');

				if (afterUid) {
					var newRecords = new Array();
					for (var i=0; i<records.length; i++) {
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

			if (this.data.unique && this.data.unique[objectPrefix]) {
				var unique = this.data.unique[objectPrefix];
				this.setUnique(objectPrefix, newUid, selectedValue);
			}
		}

			// if we reached the maximum off possible records after this action, hide the new buttons
		if (!this.isBelowMax(objectPrefix)) {
			this.hideElementsWithClassName('inlineNewButton',  this.parseFormElementName('full', objectPrefix, 0 , 1));
		}

		if (TBE_EDITOR) TBE_EDITOR.fieldChanged_fName(objectName, formObj);
	},

	memorizeRemoveRecord: function(objectName, removeUid) {
		var formObj = document.getElementsByName(objectName);
		if (formObj.length) {
			var parts = new Array();
			if (formObj[0].value.length) {
				parts = formObj[0].value.split(',');
				parts = parts.without(removeUid);
				formObj[0].value = parts.join(',');
				if (TBE_EDITOR) TBE_EDITOR.fieldChanged_fName(objectName, formObj);
				return parts.length;
			}
		}
		return false;
	},

	updateUnique: function(srcElement, objectPrefix, formName, recordUid) {
		if (this.data.unique && this.data.unique[objectPrefix]) {
			var unique = this.data.unique[objectPrefix];
			var oldValue = unique.used[recordUid];

			if (unique.selector == 'select') {
				var selector = $(objectPrefix+'_selector');
				this.removeSelectOption(selector, srcElement.value);
				if (typeof oldValue != 'undefined') this.readdSelectOption(selector, oldValue, unique);
			}

			if (!(unique.selector && unique.max == -1)) {
				var formObj = document.getElementsByName(formName);
				if (unique && formObj.length) {
					var records = formObj[0].value.split(',');
					var recordObj;
					for (var i=0; i<records.length; i++) {
						recordObj = document.getElementsByName(this.prependFormFieldNames+'['+unique.table+']['+records[i]+']['+unique.field+']');
						if (recordObj.length && recordObj[0] != srcElement) {
							this.removeSelectOption(recordObj[0], srcElement.value);
							if (typeof oldValue != 'undefined') this.readdSelectOption(recordObj[0], oldValue, unique);
						}
					}
					this.data.unique[objectPrefix].used[recordUid] = srcElement.value;
				}
			}
		}
	},

	revertUnique: function(objectPrefix, elName, recordUid) {
		var unique = this.data.unique[objectPrefix];
		var fieldObj = elName ? document.getElementsByName(elName+'['+unique.field+']') : null;

		if (unique.type == 'select') {
			if (fieldObj && fieldObj.length) {
				delete(this.data.unique[objectPrefix].used[recordUid])
				
				if (unique.selector == 'select') {
					if (!isNaN(fieldObj[0].value)) {
						var selector = $(objectPrefix+'_selector');
						this.readdSelectOption(selector, fieldObj[0].value, unique);
					}
				}

				if (!(unique.selector && unique.max == -1)) {
					var formName = this.prependFormFieldNames+this.parseFormElementName('parts', objectPrefix, 3, 1);
					var formObj = document.getElementsByName(formName);
					if (formObj.length) {
						var records = formObj[0].value.split(',');
						var recordObj;
							// walk through all inline records on that level and get the select field
						for (var i=0; i<records.length; i++) {
							recordObj = document.getElementsByName(this.prependFormFieldNames+'['+unique.table+']['+records[i]+']['+unique.field+']');
							if (recordObj.length) this.readdSelectOption(recordObj[0], fieldObj[0].value, unique);
						}
					}
				}
			}
		} else if (unique.type == 'groupdb') {
			// alert(objectPrefix+'/'+recordUid);
			delete(this.data.unique[objectPrefix].used[recordUid])
		}
	},

	enableDisableRecord: function(objectId) {
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
	},

	deleteRecord: function(objectId) {
		var i, j, inlineRecords, records, childObjectId, childTable;
		var objectPrefix = this.parseFormElementName('full', objectId, 0 , 1);
		var elName = this.parseFormElementName('full', objectId, 2);
		var shortName = this.parseFormElementName('parts', objectId, 2);
		var recordUid = this.parseFormElementName('none', objectId, 1);
		var beforeDeleteIsBelowMax = this.isBelowMax(objectPrefix);

			// revert the unique settings if available
		if (this.data.unique && this.data.unique[objectPrefix]) this.revertUnique(objectPrefix, elName, recordUid);

			// if the record is new and was never saved before, just remove it from DOM
		if (this.isNewRecord(objectId)) {
			new Effect.Fade(objectId+'_div', { afterFinish: function() { Element.remove(objectId+'_div'); }	});
			// if the record already exists in storage, mark it to be deleted on clicking the save button
		} else {
			document.getElementsByName('cmd'+shortName+'[delete]')[0].disabled = false;
			new Effect.Fade(objectId+'_div');
		}

			// Remove from TBE_EDITOR (required fields, required range, etc.):
		if (TBE_EDITOR && TBE_EDITOR.removeElement) {
			inlineRecords = document.getElementsByClassName('inlineRecord', objectId+'_div');
				// Remove nested child records from TBE_EDITOR required/range checks:
			for (i=inlineRecords.length-1; i>=0; i--) {
				if (inlineRecords[i].value.length) {
					records = inlineRecords[i].value.split(',');
					childObjectId = this.data.map[inlineRecords[i].name];
					childTable = this.data.config[childObjectId].table;
					for (j=records.length-1; j>=0; j--) {
						TBE_EDITOR.removeElement(this.prependFormFieldNames+'['+childTable+']['+records[j]+']');
					}
				}
			}
			TBE_EDITOR.removeElement(this.prependFormFieldNames+shortName);
		}

		var recordCount = this.memorizeRemoveRecord(
			this.prependFormFieldNames+this.parseFormElementName('parts', objectId, 3, 2),
			recordUid
		);

		if (recordCount <= 1) {
			this.destroyDragAndDropSorting(this.parseFormElementName('full', objectId, 0 , 2)+'_records');
		}
		this.redrawSortingButtons(objectPrefix);

			// if the NEW-button was hidden and now we can add again new children, show the button
		if (!beforeDeleteIsBelowMax && this.isBelowMax(objectPrefix))
			this.showElementsWithClassName('inlineNewButton', this.parseFormElementName('full', objectPrefix, 0 , 1));

		return false;
	},

	parsePath: function(path) {
		var backSlash = path.lastIndexOf('\\');
		var normalSlash = path.lastIndexOf('/');

		if (backSlash > 0)
			path = path.substring(0,backSlash+1);
		else if (normalSlash > 0)
			path = path.substring(0,normalSlash+1);
		else
			path = '';

		return path;
	},

	parseFormElementName: function(wrap, objectId, rightCount, skipRight) {
			// remove left and right side "data[...|...]" -> '...|...'
		objectId = objectId.substr(0, objectId.lastIndexOf(']')).substr(objectId.indexOf('[')+1);

		if (!wrap) wrap = 'full';
		if (!skipRight) skipRight = 0;

		var elReturn;
		var elParts = new Array();
		var idParts = objectId.split('][');
		for (var i=0; i<skipRight; i++) idParts.pop();

		if (rightCount > 0) {
			for (var i=0; i<rightCount; i++) elParts.unshift(idParts.pop());
		} else {
			for (var i=0; i<-rightCount; i++) idParts.shift();
			elParts = idParts;
		}

		if (wrap == 'full') {
			elReturn = this.prependFormFieldNames+'['+elParts.join('][')+']';
		} else if (wrap == 'parts') {
			elReturn = '['+elParts.join('][')+']';
		} else if (wrap == 'none') {
			elReturn = elParts.length > 1 ? elParts : elParts.join('');
		}

		return elReturn;
	},

	handleChangedField: function(formField, objectId) {
		var formObj;
		if (typeof formField == 'object') {
			formObj = formField;
		} else {
			formObj = document.getElementsByName(formField);
			if (formObj.length) formObj = formObj[0];
		}

		if (formObj != undefined) {
			var value;
			if (formObj.nodeName == 'SELECT') value = formObj.options[formObj.selectedIndex].text;
			else value = formObj.value;
			$(objectId+'_label').innerHTML = value.length ? value : this.noTitleString;
		}
		return true;
	},

	arrayAssocCount: function(object) {
		var count = 0;
		if (typeof object.length != 'undefined') {
			count = object.length;
		} else {
			for (var i in object) count++;
		}
		return count;
	},

	isBelowMax: function(objectPrefix) {
		var isBelowMax = true;
		var objectName = this.prependFormFieldNames+this.parseFormElementName('parts', objectPrefix, 3, 1);
		var formObj = document.getElementsByName(objectName);

		if (this.data.config && this.data.config[objectPrefix] && formObj.length) {
			var recordCount = formObj[0].value ? formObj[0].value.split(',').length : 0;
			if (recordCount >= this.data.config[objectPrefix].max) isBelowMax = false;
		}
		if (isBelowMax && this.data.unique && this.data.unique[objectPrefix]) {
			var unique = this.data.unique[objectPrefix];
			if (this.arrayAssocCount(unique.used) >= unique.max && unique.max >= 0) isBelowMax = false;
		}
		return isBelowMax;
	},

	getOptionsHash: function(selectObj) {
		var optionsHash = {};
		for (var i=0; i<selectObj.options.length; i++) optionsHash[selectObj.options[i].value] = i;
		return optionsHash;
	},

	removeSelectOption: function(selectObj, value) {
		var optionsHash = this.getOptionsHash(selectObj);
		if (optionsHash[value] != undefined) selectObj.options[optionsHash[value]] = null;
	},

	readdSelectOption: function(selectObj, value, unique) {
		var index = null;
		var optionsHash = this.getOptionsHash(selectObj);
		var possibleValues = $H(unique.possible).keys();

		for (var possibleValue in unique.possible) {
			if (possibleValue == value) break;
			if (optionsHash[possibleValue] != undefined) index = optionsHash[possibleValue];
		}

		if (index == null) index = 0;
		else if (index < selectObj.options.length) index++;
			// recreate the <option> tag
		var readdOption = document.createElement('option');
		readdOption.text = unique.possible[value];
		readdOption.value = value;
			// add the <option> at the right position
		selectObj.add(readdOption, document.all ? index : selectObj.options[index]);
	},

	hideElementsWithClassName: function(className, parentElement) {
		this.setVisibilityOfElementsWithClassName('hide', className, parentElement);
	},

	showElementsWithClassName: function(className, parentElement) {
		this.setVisibilityOfElementsWithClassName('show', className, parentElement);
	},

	setVisibilityOfElementsWithClassName: function(action, className, parentElement) {
		var domObjects = document.getElementsByClassName(className, parentElement);
		for (var i=0; i<domObjects.length; i++) {
			if (action == 'hide')
				new Effect.Fade(domObjects[i]);
			else if (action = 'show')
				new Effect.Appear(domObjects[i]);
		}
	},

	fadeOutFadeIn: function(objectId) {
		var optIn = { duration:0.5, transition:Effect.Transitions.linear, from:0.50, to:1.00 };
		var optOut = { duration:0.5, transition:Effect.Transitions.linear, from:1.00, to:0.50 };
		optOut.afterFinish = function() { new Effect.Opacity(objectId, optIn); };
		new Effect.Opacity(objectId, optOut);
	},

	isNewRecord: function(objectId) {
		return $(objectId+'_div') && $(objectId+'_div').hasClassName('inlineIsNewRecord')
			? true
			: false;
	}
}

Object.extend(Array.prototype, {
	diff: function(current) {
		var diff = new Array();
		if (this.length == current.length) {
			for (var i=0; i<this.length; i++) {
				if (this[i] !== current[i]) diff.push(i);
			}
		}
		return diff;
	}
});

/*]]>*/