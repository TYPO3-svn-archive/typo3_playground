/***************************************************************
*  Copyright notice
*
*  (c) 2002 interactivetools.com, inc. Authored by Mihai Bazon, sponsored by http://www.bloki.com.
*  (c) 2005 Xinha, http://xinha.gogo.co.nz/ for the original toggle borders function.
*  (c) 2004, 2005, 2006 Stanislas Rolland <stanislas.rolland(arobas)fructifor.ca>
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
*  This script is a modified version of a script published under the htmlArea License.
*  A copy of the htmlArea License may be found in the textfile HTMLAREA_LICENSE.txt.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
 * Table Operations Plugin for TYPO3 htmlArea RTE
 *
 * TYPO3 CVS ID: $Id: table-operations.js 1676 2006-08-15 04:51:33Z stanrolland $
 */

/*
 * Initialize the plugin and register its buttons
 */
TableOperations = function(editor) {
	this.editor = editor;
	var cfg = editor.config;
	var bl = TableOperations.btnList;
	var actionHandlerFunctRef = TableOperations.actionHandler(this);
	var hideToggleBorders = cfg.hideTableOperationsInToolbar && !(cfg.buttons["toggleborders"] && cfg.buttons["toggleborders"]["keepInToolbar"]);
	for(var i=0;i < bl.length;++i) {
		var btn = bl[i];
		var id = "TO-" + btn[0];
		cfg.registerButton(id, TableOperations_langArray[id], editor.imgURL(btn[0] + ".gif", "TableOperations"), false,
			actionHandlerFunctRef, btn[1], ((id == "TO-toggle-borders") ? hideToggleBorders : cfg.hideTableOperationsInToolbar));
	}
};

TableOperations.actionHandler = function(instance) {
	return (function(editor,id) {
		instance.buttonPress(editor,id);
	});
};

/*
 * Set the language file for the plugin
 */
TableOperations.I18N = TableOperations_langArray;

/*
 * The information about the plugin
 */
TableOperations._pluginInfo = {
	name		: "TableOperations",
	version 	: "3.6",
	developer 	: "Mihai Bazon & Stanislas Rolland",
	developer_url 	: "http://www.fructifor.ca/",
	c_owner 	: "Mihai Bazon & Stanislas Rolland",
	sponsor 	: "Zapatec Inc. & Fructifor Inc.",
	sponsor_url 	: "http://www.fructifor.ca/",
	license 	: "GPL"
};

/*
 * The list of buttons added by this plugin
 */
TableOperations.btnList = [
	["toggle-borders",	null],
	["table-prop",		"table"],
	["row-prop",		"tr"],
	["row-insert-above",	"tr"],
	["row-insert-under",	"tr"],
	["row-delete",		"tr"],
	["row-split",		"td,th[rowSpan!=1]"],
	["col-insert-before",	"td,th"],
	["col-insert-after",	"td,th"],
	["col-delete",		"td,th"],
	["col-split",		"td,th[colSpan!=1]"],
	["cell-prop",		"td,th"],
	["cell-insert-before",	"td,th"],
	["cell-insert-after",	"td,th"],
	["cell-delete",		"td,th"],
	["cell-merge",		"tr"],
	["cell-split",		"td,th[colSpan!=1,rowSpan!=1]"]
	];

/************************
 * UTILITIES
 ************************/

/*
 * Retrieve the closest element having the specified tagName in the list of
 * ancestors of the current selection/caret.
 */
TableOperations.prototype.getClosest = function(tagName) {
	var editor = this.editor;
	var ancestors = editor.getAllAncestors();
	var ret = null;
	tagName = ("" + tagName).toLowerCase();
	for (var i=0; i < ancestors.length; ++i) {
		var el = ancestors[i];
		if (el.tagName.toLowerCase() == tagName) {
			ret = el;
			break;
		}
	}
	return ret;
};

/*
 * Open the table properties dialog.
 */
TableOperations.prototype.dialogTableProperties = function() {
		// retrieve existing values
	var table = this.getClosest("table");
	var tablePropertiesInitFunctRef = TableOperations.tablePropertiesInit(table);
	var tablePropertiesUpdateFunctRef = TableOperations.tablePropertiesUpdate(table);
	var dialog = new PopupWin(this.editor, TableOperations.I18N["Table Properties"], tablePropertiesUpdateFunctRef, tablePropertiesInitFunctRef, 570, 600);
};

/*
 * Initialize the table properties dialog
 */
TableOperations.tablePropertiesInit = function(table) {
	return (function (dialog) {
		var doc = dialog.doc;
		var content = dialog.content;
		var i18n = TableOperations.I18N;
		TableOperations.buildTitle(doc, i18n, content, "Table Properties");
		TableOperations.buildDescriptionFieldset(doc, table, i18n, content);
		var obj = dialog.editor.config.customSelects["DynamicCSS-class"];
		if (obj && obj.loaded) TableOperations.buildStylingFieldset(doc, table, i18n, content, obj.cssArray);
		if (!dialog.editor.config.disableLayoutFieldsetInTableOperations) TableOperations.buildLayoutFieldset(doc, table, i18n, content);
		if (!dialog.editor.config.disableAlignmentFieldsetInTableOperations) TableOperations.buildAlignmentFieldset(doc, table, i18n, content, "floating");
		if (!dialog.editor.config.disableSpacingFieldsetInTableOperations) TableOperations.buildSpacingFieldset(doc, table, i18n, content);
		if (!dialog.editor.config.disableBordersFieldsetInTableOperations) TableOperations.buildBordersFieldset(dialog.dialogWindow, doc, dialog.editor, table, i18n, content);
		if (!dialog.editor.config.disableColorFieldsetInTableOperations) TableOperations.buildColorsFieldset(dialog.dialogWindow, doc, dialog.editor, table, i18n, content);
		dialog.modal = true;
		dialog.addButtons("ok", "cancel");
		dialog.showAtElement();
	});
};

/*
 * Update the table properties and close the dialog
 */
TableOperations.tablePropertiesUpdate = function(table) {
	return (function (dialog,params) {
		dialog.editor.focusEditor();
		TableOperations.processStyle(params, table);
		table.removeAttribute("border");
		for (var i in params) {
			var val = params[i];
			switch (i) {
			    case "f_caption":
				if (/\S/.test(val)) {
					// contains non white-space characters
					var caption = table.getElementsByTagName("caption")[0];
					if (!caption) {
						caption = dialog.editor._doc.createElement("caption");
						table.insertBefore(caption, table.firstChild);
					}
					caption.innerHTML = val;
				} else {
					// search for caption and delete it if found
					var caption = table.getElementsByTagName("caption")[0];
					if (caption) caption.parentNode.removeChild(caption);
				}
				break;
			    case "f_summary":
				table.summary = val;
				break;
			    case "f_width":
				table.style.width = ("" + val) + params.f_unit;
				break;
			    case "f_align":
				table.align = val;
				break;
			    case "f_spacing":
				table.cellSpacing = val;
				break;
			    case "f_padding":
				table.cellPadding = val;
				break;
			    case "f_frames":
				table.frame = (val != "not set") ? val : "";
				break;
			    case "f_rules":
				if (val != "not set") table.rules = val;
			    		else table.removeAttribute("rules");
				break;
			    case "f_class":
			    case "f_class_tbody":
			    case "f_class_thead":
			    case "f_class_tfoot":
			    	var tpart = table;
			    	if (i.length > 7) tpart = table.getElementsByTagName(i.substring(8,13))[0];
				var cls = tpart.className.trim().split(" ");
				for (var j = cls.length;j > 0;) {
					if (!HTMLArea.reservedClassNames.test(cls[--j])) HTMLArea._removeClass(tpart,cls[j]);
				}
				if (val != 'none') HTMLArea._addClass(tpart,val);
				break;
			}
		}
		dialog.editor.focusEditor();
		dialog.editor.updateToolbar();
	});
};

/*
 * Open the row/cell properties dialog.
 * This function requires the file PopupWin to be loaded.
 */
TableOperations.prototype.dialogRowCellProperties = function(cell) {
		// retrieve existing values
	if (cell) {
		var element = this.getClosest("td");
		if (!element) var element = this.getClosest("th");
	} else {
		var element = this.getClosest("tr");
	}
	if(element) {
		var rowCellPropertiesInitFunctRef = TableOperations.rowCellPropertiesInit(element, cell);
		var rowCellPropertiesUpdateFunctRef = TableOperations.rowCellPropertiesUpdate(element);
		var dialog = new PopupWin(this.editor, TableOperations.I18N[cell ? "Cell Properties" : "Row Properties"], rowCellPropertiesUpdateFunctRef, rowCellPropertiesInitFunctRef, 700, 425);
	}
};

/*
 * Initialize the row/cell properties dialog
 */
TableOperations.rowCellPropertiesInit = function(element,cell) {
	return (function (dialog) {
		var doc = dialog.doc;
		var content = dialog.content;
		var i18n = TableOperations.I18N;
		TableOperations.buildTitle(doc, i18n, content, (cell ? "Cell Properties" : "Row Properties"));
		if (cell) TableOperations.buildCellTypeFieldset(dialog.dialogWindow, doc, dialog.editor, element, i18n, content);
			else TableOperations.buildRowGroupFieldset(dialog.dialogWindow, doc, dialog.editor, element, i18n, content);
		var obj = dialog.editor.config.customSelects["DynamicCSS-class"];
		if (obj && obj.loaded) TableOperations.buildStylingFieldset(doc, element, i18n, content, obj.cssArray);
			else TableOperations.insertSpace(doc, content);
		if (!dialog.editor.config.disableLayoutFieldsetInTableOperations) TableOperations.buildLayoutFieldset(doc, element, i18n, content, "floating");
		if (!dialog.editor.config.disableAlignmentFieldsetInTableOperations) TableOperations.buildAlignmentFieldset(doc, element, i18n, content);
		if (!dialog.editor.config.disableBordersFieldsetInTableOperations) TableOperations.buildBordersFieldset(dialog.dialogWindow, doc, dialog.editor, element, i18n, content);
		if (!dialog.editor.config.disableColorFieldsetInTableOperations) TableOperations.buildColorsFieldset(dialog.dialogWindow, doc, dialog.editor, element, i18n, content);
		dialog.modal = true;
		dialog.addButtons("ok", "cancel");
		dialog.showAtElement();
	});
};

/*
 * Update the row/cell properties and close the dialog
 */
TableOperations.rowCellPropertiesUpdate = function(element) {
	return (function (dialog,params) {
		dialog.editor.focusEditor();
		TableOperations.processStyle(params, element);
		var convertCellType = false;
		for (var i in params) {
			var val = params[i];
			switch (i) {
			    case "f_scope":
			    	if (val != "not set") element.scope = val;
			    		else element.removeAttribute('scope');
				break;
			    case "f_cell_type":
			    		// Set all cell attributes before cloning it with a new tag
			    	if (val != element.tagName.toLowerCase()) {
					var newCellType = val;
					convertCellType = true;
				}
				break;
			    case "f_rowgroup":
			   	var section = element.parentNode;
				var tagName = section.tagName.toLowerCase();
				if (val != tagName) {
					var table = section.parentNode;
					var newSection = table.getElementsByTagName(val)[0];
					if (!newSection) var newSection = table.insertBefore(dialog.editor._doc.createElement(val), table.getElementsByTagName("tbody")[0]);
					if (tagName == "thead" && val == "tbody") var newElement = newSection.insertBefore(element, newSection.firstChild);
						else var newElement = newSection.appendChild(element);
					if (!section.hasChildNodes()) table.removeChild(section);
				}
				break;
			    case "f_char":
				element.ch = val;
				break;
			    case "f_class":
				var cls = element.className.trim().split(" ");
				for (var j = cls.length;j > 0;) {
					if (!HTMLArea.reservedClassNames.test(cls[--j])) HTMLArea._removeClass(element,cls[j]);
				}
				if (val != 'none') HTMLArea._addClass(element,val);
				break;
			}
		}
		if (convertCellType) {
			var newCell = dialog.editor._doc.createElement(newCellType), p = element.parentNode, a, attrName, name;
			var attrs = element.attributes;
			for (var i = attrs.length; --i >= 0 ;) {
				a = attrs.item(i);
				attrName = a.nodeName;
				name = attrName.toLowerCase();
					// IE5.5 reports wrong values. For this reason we extract the values directly from the root node.
				if (typeof(element[attrName]) != "undefined" && name != "style" && !/^on/.test(name)) {
					if (element[attrName]) newCell.setAttribute(attrName, element[attrName]);
				} else {
					if (a.nodeValue) newCell.setAttribute(attrName, a.nodeValue);
				}
			}
				// In IE, the above fails to update the classname and style attributes.
			if (HTMLArea.is_ie) {
				if (element.style.cssText) newCell.style.cssText = element.style.cssText;
				if (element.className) {
					newCell.setAttribute("className", element.className);
				} else { 
					newCell.className = element.className;
					newCell.removeAttribute("className");
				}
			}
			while (element.firstChild) newCell.appendChild(element.firstChild);
			p.insertBefore(newCell, element);
			p.removeChild(element);
			dialog.editor.selectNodeContents(newCell, false);
		}
		dialog.editor.updateToolbar();
	});
};

/*
 * this function gets called when some button from the TableOperations toolbar was pressed.
 */
TableOperations.prototype.buttonPress = function(editor,button_id) {
	this.editor = editor;
	var mozbr = HTMLArea.is_gecko ? "<br />" : "";
	var tableParts = ["tfoot", "thead", "tbody"];
	var tablePartsIndex = { tfoot : 0, thead : 1, tbody : 2 };

	// helper function that clears the content in a table row
	function clearRow(tr) {
		var tds = tr.getElementsByTagName("td");
		for (var i = tds.length; --i >= 0;) {
			var td = tds[i];
			td.rowSpan = 1;
			td.innerHTML = mozbr;
		}
		var tds = tr.getElementsByTagName("th");
		for (var i = tds.length; --i >= 0;) {
			var td = tds[i];
			td.rowSpan = 1;
			td.innerHTML = mozbr;
		}
	};

	function splitRow(td) {
		var n = parseInt("" + td.rowSpan);
		var colSpan = td.colSpan;
		var tagName = td.tagName.toLowerCase();
		td.rowSpan = 1;
		var tr = td.parentNode;
		var sectionRowIndex = tr.sectionRowIndex;
		var rows = tr.parentNode.rows;
		var index = td.cellIndex;
		while (--n > 0) {
			tr = rows[++sectionRowIndex];
				// Last row
			if (!tr) tr = td.parentNode.parentNode.appendChild(editor._doc.createElement("tr"));
			var otd = editor._doc.createElement(tagName);
			otd.colSpan = colSpan;
			otd.innerHTML = mozbr;
			tr.insertBefore(otd, tr.cells[index]);
		}
	};

	function splitCol(td) {
		var nc = parseInt("" + td.colSpan);
		var tagName = td.tagName.toLowerCase();
		td.colSpan = 1;
		var tr = td.parentNode;
		var ref = td.nextSibling;
		while (--nc > 0) {
			var otd = editor._doc.createElement(tagName);
			otd.rowSpan = td.rowSpan;
			otd.innerHTML = mozbr;
			tr.insertBefore(otd, ref);
		}
	};

	function splitCell(td) {
		var nc = parseInt("" + td.colSpan);
		splitCol(td);
		var cells = td.parentNode.cells;
		var index = td.cellIndex;
		while (nc-- > 0) {
			splitRow(cells[index++]);
		}
	};

	function selectNextNode(el) {
		var node = el.nextSibling;
		while (node && node.nodeType != 1) {
			node = node.nextSibling;
		}
		if (!node) {
			node = el.previousSibling;
			while (node && node.nodeType != 1) {
				node = node.previousSibling;
			}
		}
		if (!node) node = el.parentNode;
		editor.selectNodeContents(node);
	};
	
	function getSelectedCells(sel) {
		var cell, range, i = 0, cells = [];
		try {
			while (range = sel.getRangeAt(i++)) {
				cell = range.startContainer.childNodes[range.startOffset];
				while (!/^(td|th|body)$/.test(cell.tagName.toLowerCase())) cell = cell.parentNode;
				if (/^(td|th)$/.test(cell.tagName.toLowerCase())) cells.push(cell);
			}
		} catch(e) {
		/* finished walking through selection */
		}
		return cells;
	};
	
	function deleteEmptyTable(table) {
		var lastPart = true;
		for (var j = tableParts.length; --j >= 0;) {
			var tablePart = table.getElementsByTagName(tableParts[j])[0];
			if (tablePart) lastPart = false;
		}
		if (lastPart) {
			selectNextNode(table);
			table.parentNode.removeChild(table);
		}
	};
	
	switch (button_id) {
		// ROWS
	    case "TO-row-insert-above":
	    case "TO-row-insert-under":
		var tr = this.getClosest("tr");
		if (!tr) break;
		var otr = tr.cloneNode(true);
		clearRow(otr);
		tr.parentNode.insertBefore(otr, (/under/.test(button_id) ? tr.nextSibling : tr));
		editor.forceRedraw();
		editor.focusEditor();
		break;
	    case "TO-row-delete":
		var tr = this.getClosest("tr");
		if (!tr) break;
		var part = tr.parentNode;
		var table = part.parentNode;
		if(part.rows.length == 1) {  // this the last row, delete the whole table part
			selectNextNode(part);
			table.removeChild(part);
			deleteEmptyTable(table);
		} else {
				// set the caret first to a position that doesn't disappear.
			selectNextNode(tr);
			part.removeChild(tr);
		}
		editor.forceRedraw();
		editor.focusEditor();
		editor.updateToolbar();
		break;
	    case "TO-row-split":
		var cell = this.getClosest("td");
		if (!cell) var cell = this.getClosest("th");
		if (!cell) break;
		var sel = editor._getSelection();
		if (HTMLArea.is_gecko && !sel.isCollapsed && !HTMLArea.is_safari && !HTMLArea.is_opera) {
			var cells = getSelectedCells(sel);
			for (i = 0; i < cells.length; ++i) splitRow(cells[i]);
		} else {
			splitRow(cell);
		}
		editor.forceRedraw();
		editor.updateToolbar();
		break;

		// COLUMNS
	    case "TO-col-insert-before":
	    case "TO-col-insert-after":
		var cell = this.getClosest("td");
		if (!cell) var cell = this.getClosest("th");
		if (!cell) break;
		var index = cell.cellIndex;
		var table = cell.parentNode.parentNode.parentNode;
		for (var j = tableParts.length; --j >= 0;) {
			var tablePart = table.getElementsByTagName(tableParts[j])[0];
			if (tablePart) {
				var rows = tablePart.rows;
				for (var i = rows.length; --i >= 0;) {
					var tr = rows[i];
					var ref = tr.cells[index + (/after/.test(button_id) ? 1 : 0)];
					if (!ref) {
						var otd = editor._doc.createElement(tr.lastChild.tagName.toLowerCase());
						otd.innerHTML = mozbr;
						tr.appendChild(otd);
					} else {
						var otd = editor._doc.createElement(ref.tagName.toLowerCase());
						otd.innerHTML = mozbr;
						tr.insertBefore(otd, ref);
					}
				}
			}
		}
		editor.focusEditor();
		break;
	    case "TO-col-split":
		var cell = this.getClosest("td");
		if (!cell) var cell = this.getClosest("th");
		if (!cell) break;
		var sel = editor._getSelection();
		if (HTMLArea.is_gecko && !sel.isCollapsed && !HTMLArea.is_safari && !HTMLArea.is_opera) {
			var cells = getSelectedCells(sel);
			for (i = 0; i < cells.length; ++i) splitCol(cells[i]);
		} else {
			splitCol(cell);
		}
		editor.forceRedraw();
		editor.updateToolbar();
		break;
	    case "TO-col-delete":
		var cell = this.getClosest("td");
		if (!cell) var cell = this.getClosest("th");
		if (!cell) break;
		var index = cell.cellIndex;
		var part = cell.parentNode.parentNode;
		var table = part.parentNode;
		var lastPart = true;
		for (var j = tableParts.length; --j >= 0;) {
			var tablePart = table.getElementsByTagName(tableParts[j])[0];
			if (tablePart) {
				var rows = tablePart.rows;
				var lastColumn = true;
				for (var i = rows.length; --i >= 0;) {
					if(rows[i].cells.length > 1) lastColumn = false;
				}
				if (lastColumn) {
						// this is the last column, delete the whole tablepart
						// set the caret first to a position that doesn't disappear
					selectNextNode(tablePart);
					table.removeChild(tablePart);
				} else {
						// set the caret first to a position that doesn't disappear
					if (part == tablePart) selectNextNode(cell);
					for (var i = rows.length; --i >= 0;) {
						if(rows[i].cells[index]) rows[i].removeChild(rows[i].cells[index]);
					}
					lastPart = false;
				}
			}
		}
		if (lastPart) {
				// the last table section was deleted: delete the whole table
				// set the caret first to a position that doesn't disappear
			selectNextNode(table);
			table.parentNode.removeChild(table);
		}
		editor.forceRedraw();
		editor.focusEditor();
		editor.updateToolbar();
		break;

		// CELLS
	    case "TO-cell-split":
		var cell = this.getClosest("td");
		if (!cell) var cell = this.getClosest("th");
		if (!cell) break;
		var sel = editor._getSelection();
		if (HTMLArea.is_gecko && !sel.isCollapsed && !HTMLArea.is_safari && !HTMLArea.is_opera) {
			var cells = getSelectedCells(sel);
			for (i = 0; i < cells.length; ++i) splitCell(cells[i]);
		} else {
			splitCell(cell);
		}
		editor.forceRedraw();
		editor.updateToolbar();
		break;
	    case "TO-cell-insert-before":
	    case "TO-cell-insert-after":
		var cell = this.getClosest("td");
		if (!cell) var cell = this.getClosest("th");
		if (!cell) break;
		var tr = cell.parentNode;
		var otd = editor._doc.createElement(cell.tagName.toLowerCase());
		otd.innerHTML = mozbr;
		tr.insertBefore(otd, (/after/.test(button_id) ? cell.nextSibling : cell));
		editor.forceRedraw();
		editor.focusEditor();
		break;
	    case "TO-cell-delete":
		var cell = this.getClosest("td");
		if (!cell) var cell = this.getClosest("th");
		if (!cell) break;
		var row = cell.parentNode;
		if(row.cells.length == 1) {  // this is the only cell in the row, delete the row
			var part = row.parentNode;
			var table = part.parentNode;
			if (part.rows.length == 1) {  // this the last row, delete the whole table part
				selectNextNode(part);
				table.removeChild(part);
				deleteEmptyTable(table);
			} else {
				selectNextNode(row);
				part.removeChild(row);
			}
		} else {
				// set the caret first to a position that doesn't disappear
			selectNextNode(cell);
			row.removeChild(cell);
		}
		editor.forceRedraw();
		editor.focusEditor();
		editor.updateToolbar();
		break;
	    case "TO-cell-merge":
		var sel = editor._getSelection();
		var range, i = 0;
		var rows = new Array();
		for (var k = tableParts.length; --k >= 0;) rows[k] = [];
		var row = null;
		var cells = null;
		if (HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera) {
			try {
				while (range = sel.getRangeAt(i++)) {
					var td = range.startContainer.childNodes[range.startOffset];
					if (td.parentNode != row) {
						(cells) && rows[tablePartsIndex[row.parentNode.tagName.toLowerCase()]].push(cells);
						row = td.parentNode;
						cells = [];
					}
					cells.push(td);
				}
			} catch(e) {
			/* finished walking through selection */
			}
			rows[tablePartsIndex[row.parentNode.tagName.toLowerCase()]].push(cells);
		} else {
			// Internet Explorer, Safari and Opera
			var cell = this.getClosest("td");
			if (!cell) var cell = this.getClosest("th");
			if (!cell) {
				alert(TableOperations.I18N["Please click into some cell"]);
				break;
			}
			var tr = cell.parentElement;
			var no_cols = prompt(TableOperations.I18N["How many columns would you like to merge?"], 2);
			if (!no_cols) break;
			var no_rows = prompt(TableOperations.I18N["How many rows would you like to merge?"], 2);
			if (!no_rows) break;
			var cell_index = cell.cellIndex;
			while (no_rows-- > 0) {
				td = tr.cells[cell_index];
				cells = [td];
				for (var i = 1; i < no_cols; ++i) {
					td = td.nextSibling;
					if (!td) break;
					cells.push(td);
				}
				rows[tablePartsIndex[tr.parentNode.tagName.toLowerCase()]].push(cells);
				tr = tr.nextSibling;
				if (!tr) break;
			}
		}
		for (var k = tableParts.length; --k >= 0;) {
			var cellHTML = "";
			for (var i = 0; i < rows[k].length; ++i) {
					// i && (cellHTML += "<br />");
				var cells = rows[k][i];
				if(!cells) continue;
				for (var j=0; j < cells.length; ++j) {
					// j && (cellHTML += "&nbsp;");
				var cell = cells[j];
				cellHTML += cell.innerHTML;
				if(i || j) {
					if(cell.parentNode.cells.length == 1) cell.parentNode.parentNode.removeChild(cell.parentNode);
						else cell.parentNode.removeChild(cell);
				}
				}
			}
			try {
				var td = rows[k][0][0];
				td.innerHTML = cellHTML;
				td.rowSpan = rows[k].length;
				td.colSpan = rows[k][0].length;
				editor.selectNodeContents(td);
			} catch(e) { }
		}
		
		editor.forceRedraw();
		editor.focusEditor();
		break;

		// PROPERTIES
	    case "TO-table-prop":
		this.dialogTableProperties();
		break;
	    case "TO-row-prop":
		this.dialogRowCellProperties(false);
		break;
	    case "TO-cell-prop":
		this.dialogRowCellProperties(true);
		break;
	    case "TO-toggle-borders":
		var tables = editor._doc.getElementsByTagName("table");
		if (tables.length != 0) {
			editor.borders = true;
			for (var ix=0; ix < tables.length; ix++) editor.borders = editor.borders && /htmlarea-showtableborders/.test(tables[ix].className);
			for (ix=0; ix < tables.length; ix++) {
				if (!editor.borders) HTMLArea._addClass(tables[ix],'htmlarea-showtableborders');
					else HTMLArea._removeClass(tables[ix],'htmlarea-showtableborders');
			}
		}
		break;
	    default:
		alert("Button [" + button_id + "] not yet implemented");
	}
};

TableOperations.getLength = function(value) {
	var len = parseInt(value);
	if (isNaN(len)) len = "";
	return len;
};

// Applies the style found in "params" to the given element.
TableOperations.processStyle = function(params,element) {
	var style = element.style;
	for (var i in params) {
		var val = params[i];
		switch (i) {
		    case "f_st_backgroundColor":
			style.backgroundColor = val;
			break;
		    case "f_st_color":
			style.color = val;
			break;
		    case "f_st_backgroundImage":
			if (/\S/.test(val)) {
				style.backgroundImage = "url(" + val + ")";
			} else {
				style.backgroundImage = "";
			}
			break;
		    case "f_st_borderWidth":
		    	if (/\S/.test(val)) {
				style.borderWidth = val + "px";
			} else {
				style.borderWidth = "";
			}
			if (params["f_st_borderStyle"] == "none") style.borderWidth = "0px";
			if (params["f_st_borderStyle"] == "not set") style.borderWidth = "";
			break;
		    case "f_st_borderStyle":
			style.borderStyle = (val != "not set") ? val : "";
			break;
		    case "f_st_borderColor":
			style.borderColor = val;
			break;
		    case "f_st_borderCollapse":
			style.borderCollapse = val ? "collapse" : "";
			break;
		    case "f_st_width":
			if (/\S/.test(val)) {
				style.width = val + params["f_st_widthUnit"];
			} else {
				style.width = "";
			}
			break;
		    case "f_st_height":
			if (/\S/.test(val)) {
				style.height = val + params["f_st_heightUnit"];
			} else {
				style.height = "";
			}
			break;
		    case "f_st_textAlign":
			if (val == "character") {
				var ch = params["f_st_textAlignChar"];
				if (ch == '"') {
					ch = '\\"';
				}
				style.textAlign = '"' + ch + '"';
			} else {
				style.textAlign = (val != "not set") ? val : "";
			}
			break;
		    case "f_st_vertAlign":
			style.verticalAlign = (val != "not set") ? val : "";
			break;
		    case "f_st_float":
			if (HTMLArea.is_ie) { 
				style.styleFloat = (val != "not set") ? val : "";
			} else { 
				style.cssFloat = (val != "not set") ? val : "";
			}
			break;
// 		    case "f_st_margin":
// 			style.margin = val + "px";
// 			break;
// 		    case "f_st_padding":
// 			style.padding = val + "px";
// 			break;
		}
	}
};

// Returns an HTML element for a widget that allows color selection.  That is,
// a button that contains the given color, if any, and when pressed will popup
// the sooner-or-later-to-be-rewritten select_color.html dialog allowing user
// to select some color.  If a color is selected, an input field with the name
// "f_st_"+name will be updated with the color value in #123456 format.
TableOperations.createColorButton = function(w, doc, editor, color, name) {
	if (!color) {
		color = "";
	} else if (!/#/.test(color)) {
		color = HTMLArea._colorToRgb(color);
	}

	var df = doc.createElement("span");
 	var field = doc.createElement("input");
	field.type = "hidden";
	df.appendChild(field);
 	field.name = "f_st_" + name;
 	field.id = "f_st_" + name;
	field.value = color;
	var button = doc.createElement("span");
	button.className = "buttonColor";
	df.appendChild(button);
	var span = doc.createElement("span");
	span.className = "chooser";
	span.style.backgroundColor = color;
	button.appendChild(span);
	button.onmouseover = function() { if (!this.disabled) this.className += " buttonColor-hilite"; };
	button.onmouseout = function() { if (!this.disabled) this.className = "buttonColor"; };
	span.onclick = function() {
		if (this.parentNode.disabled) return false;
		var selectColorPlugin = editor.plugins.SelectColor;
		if (selectColorPlugin) selectColorPlugin = selectColorPlugin.instance;
		if (selectColorPlugin) {
			selectColorPlugin.dialogSelectColor("color", span, field, w);
		} else { 
			editor._popupDialog("select_color.html", function(color) {
				if (color) {
					span.style.backgroundColor = "#" + color;
					field.value = "#" + color;
				}
			}, color, 200, 182, w);
		}
	};
	var span2 = doc.createElement("span");
	span2.innerHTML = "&#x00d7;";
	span2.className = "nocolor";
	span2.title = TableOperations.I18N["Unset color"];
	button.appendChild(span2);
	span2.onmouseover = function() { if (!this.parentNode.disabled) this.className += " nocolor-hilite"; };
	span2.onmouseout = function() { if (!this.parentNode.disabled) this.className = "nocolor"; };
	span2.onclick = function() {
		span.style.backgroundColor = "";
		field.value = "";
	};
	return df;
};
TableOperations.buildTitle = function(doc,i18n,content,title) {
	var div = doc.createElement("div");
	div.className = "title";
	div.innerHTML = i18n[title];
	content.appendChild(div);
	doc.title = i18n[title];
};
TableOperations.buildDescriptionFieldset = function(doc,el,i18n,content) {
	var fieldset = doc.createElement("fieldset");
	TableOperations.insertLegend(doc, i18n, fieldset, "Description");
	TableOperations.insertSpace(doc, fieldset);
	var f_caption = "";
	var capel = el.getElementsByTagName("caption")[0];
	if (capel) f_caption = capel.innerHTML;
	TableOperations.buildInput(doc, el, i18n, fieldset, "f_caption", "Caption:", "Description of the nature of the table", "", "", f_caption, "fr", "value", "");
	TableOperations.insertSpace(doc, fieldset);
	TableOperations.buildInput(doc, el, i18n, fieldset, "f_summary", "Summary:", "Summary of the table purpose and structure", "", "", el.summary, "fr", "value", "");
	TableOperations.insertSpace(doc, fieldset);
	content.appendChild(fieldset);
};
TableOperations.buildRowGroupFieldset = function(w,doc,editor,el,i18n,content) {
	var fieldset = doc.createElement("fieldset");
	TableOperations.insertLegend(doc, i18n, fieldset, "Row group");
	TableOperations.insertSpace(doc, fieldset);
	selected = el.parentNode.tagName.toLowerCase();
	var selectScope = TableOperations.buildSelectField(doc, el, i18n, fieldset, "f_rowgroup", "Row group:", "fr", "", "Table section", ["Table body", "Table header", "Table footer"], ["tbody", "thead", "tfoot"], new RegExp((selected ? selected : "tbody"), "i"));
	TableOperations.insertSpace(doc, fieldset);
	content.appendChild(fieldset);
};
TableOperations.buildCellTypeFieldset = function(w,doc,editor,el,i18n,content) {
	var fieldset = doc.createElement("fieldset");
	TableOperations.insertLegend(doc, i18n, fieldset, "Cell Type and Scope");
	TableOperations.insertSpace(doc, fieldset);
	var ul = doc.createElement("ul");
	fieldset.appendChild(ul);
	var li = doc.createElement("li");
	ul.appendChild(li);
	var selectType = TableOperations.buildSelectField(doc, el, i18n, li, "f_cell_type", "Type of cell", "fr", "", "Specifies the type of cell", ["Normal", "Header"], ["td", "th"], new RegExp(el.tagName.toLowerCase(), "i"));
	selectType.onchange = function() { TableOperations.setStyleOptions(doc, editor, el, i18n, this); };
	var li = doc.createElement("li");
	ul.appendChild(li);
	selected = el.scope.toLowerCase();
	(selected.match(/([^\s]*)\s/)) && (selected = RegExp.$1);
	var selectScope = TableOperations.buildSelectField(doc, el, i18n, li, "f_scope", "Scope", "fr", "", "Scope of header cell", ["Not set", "scope_row", "scope_column", "scope_rowgroup"], ["not set", "row", "col", "rowgroup"], new RegExp((selected ? selected : "not set"), "i"));
	TableOperations.insertSpace(doc, fieldset);
	content.appendChild(fieldset);
};
TableOperations.getCssLabelsClasses = function(cssArray,i18n,tagName,selectedIn) {
	var cssLabels = new Array();
	var cssClasses = new Array();
	cssLabels[0] = i18n["Default"];
	cssClasses[0] = "none";
	var selected = selectedIn;
	var cls = selected.split(" ");
	var nonReservedClassName = false;
	for (var ia = cls.length; ia > 0;) {
		if(!HTMLArea.reservedClassNames.test(cls[--ia])) {
			selected = cls[ia];
			nonReservedClassName = true;
			break;
		}
	}
	var found = false, i = 1, cssClass;
	if(cssArray[tagName]) {
		for(cssClass in cssArray[tagName]){
			if(cssClass != "none") {
				cssLabels[i] = cssArray[tagName][cssClass];
				cssClasses[i] = cssClass;
				if(cssClass == selected) found = true;
				i++;
			} else {
				cssLabels[0] = cssArray[tagName][cssClass];
			}
		}
	}
	if(cssArray['all']){
		for(cssClass in cssArray['all']){
			cssLabels[i] = cssArray['all'][cssClass];
			cssClasses[i] = cssClass;
			if(cssClass == selected) found = true;
			i++;
		}
	}
	if(selected && nonReservedClassName && !found) {
		cssLabels[i] = i18n["Undefined"];
		cssClasses[i] = selected;
	}
	return [cssLabels, cssClasses, selected];
};
TableOperations.setStyleOptions = function(doc,editor,el,i18n,typeSelect) {
	var tagName = typeSelect.value;
	var select = doc.getElementById("f_class");
	if (!select) return false;
	var obj = editor.config.customSelects["DynamicCSS-class"];
	if (obj && obj.loaded) var cssArray = obj.cssArray;
		else return false;
	var cssLabelsClasses = TableOperations.getCssLabelsClasses(cssArray,i18n,tagName,el.className);
	var options = cssLabelsClasses[0];
	var values = cssLabelsClasses[1];
	var selected = cssLabelsClasses[2];
	var selectedReg = new RegExp((selected ? selected : "none"), "i");
	while(select.options.length>0) select.options[select.length-1] = null;
	select.selectedIndex = 0;
	var option;
	for (var i = 0; i < options.length; ++i) {
		option = doc.createElement("option");
		select.appendChild(option);
		option.value = values[i];
		option.appendChild(doc.createTextNode(options[i]));
		option.selected = selectedReg.test(values[i]);
	}
	if(select.options.length>1) select.disabled = false;
		else select.disabled = true;
};
TableOperations.buildStylingFieldset = function(doc,el,i18n,content,cssArray) {
	var tagName = el.tagName.toLowerCase();
	var table = (tagName == "table");
	var cssLabelsClasses = TableOperations.getCssLabelsClasses(cssArray,i18n,tagName,el.className);
	var cssLabels = cssLabelsClasses[0];
	var cssClasses = cssLabelsClasses[1];
	var selected = cssLabelsClasses[2];
	var fieldset = doc.createElement("fieldset");
	TableOperations.insertLegend(doc, i18n, fieldset, "CSS Style");
	TableOperations.insertSpace(doc, fieldset);
	var ul = doc.createElement("ul");
	ul.className = "floating";
	fieldset.appendChild(ul);
	var li = doc.createElement("li");
	ul.appendChild(li);
	TableOperations.buildSelectField(doc, el, i18n, li, "f_class", (table ? "Table class:" : "Class:"), "fr", "", (table ? "Table class selector" : "Class selector"), cssLabels, cssClasses, new RegExp((selected ? selected : "none"), "i"), "", false);
	if (table) {
		var tbody = el.getElementsByTagName("tbody")[0];
		if (tbody) {
			var li = doc.createElement("li");
			ul.appendChild(li);
			cssLabelsClasses = TableOperations.getCssLabelsClasses(cssArray, i18n, "tbody", tbody.className);
			cssLabels = cssLabelsClasses[0];
			cssClasses = cssLabelsClasses[1];
			selected = cssLabelsClasses[2];
			TableOperations.buildSelectField(doc, el, i18n, li, "f_class_tbody", "Table body class:", "fr", "", "Table body class selector", cssLabels, cssClasses, new RegExp((selected ? selected : "none"), "i"), "", false);
		}
		ul = null;
		var thead = el.getElementsByTagName("thead")[0];
		if (thead) {
			var ul = doc.createElement("ul");
			fieldset.appendChild(ul);
			var li = doc.createElement("li");
			ul.appendChild(li);
			cssLabelsClasses = TableOperations.getCssLabelsClasses(cssArray, i18n, "thead", thead.className);
			cssLabels = cssLabelsClasses[0];
			cssClasses = cssLabelsClasses[1];
			selected = cssLabelsClasses[2];
			TableOperations.buildSelectField(doc, el, i18n, li, "f_class_thead", "Table header class:", "fr", "", "Table header class selector", cssLabels, cssClasses, new RegExp((selected ? selected : "none"), "i"), "", false);
		}
		var tfoot = el.getElementsByTagName("tfoot")[0];
		if (tfoot) {
			if (!ul) {
				var ul = doc.createElement("ul");
				fieldset.appendChild(ul);
			}
			var li = doc.createElement("li");
			ul.appendChild(li);
			cssLabelsClasses = TableOperations.getCssLabelsClasses(cssArray, i18n, "tfoot", tfoot.className);
			cssLabels = cssLabelsClasses[0];
			cssClasses = cssLabelsClasses[1];
			selected = cssLabelsClasses[2];
			TableOperations.buildSelectField(doc, el, i18n, li, "f_class_tfoot", "Table footer class:", "fr", "", "Table footer class selector", cssLabels, cssClasses, new RegExp((selected ? selected : "none"), "i"), "", false);
		}
	}
	TableOperations.insertSpace(doc, fieldset);
	content.appendChild(fieldset);
};
TableOperations.buildLayoutFieldset = function(doc,el,i18n,content,fieldsetClass) {
	var select, selected;
	var fieldset = doc.createElement("fieldset");
	if(fieldsetClass) fieldset.className = fieldsetClass;
	TableOperations.insertLegend(doc, i18n, fieldset, "Layout");
	var f_st_width = TableOperations.getLength(el.style.width);
	var f_st_height = TableOperations.getLength(el.style.height);
	var selectedWidthUnit = /%/.test(el.style.width) ? '%' : (/px/.test(el.style.width) ? 'px' : 'em');	
	var selectedHeightUnit = /%/.test(el.style.height) ? '%' : (/px/.test(el.style.height) ? 'px' : 'em');
	var tag = el.tagName.toLowerCase();
	var ul = doc.createElement("ul");
	fieldset.appendChild(ul);
	switch(tag) {
		case "table" :
			var li = doc.createElement("li");
			ul.appendChild(li);
			TableOperations.buildInput(doc, el, i18n, li, "f_st_width", "Width:", "Table width", "", "5", f_st_width, "fr");
			select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_widthUnit", "", "", "", "Width unit", ["percent", "pixels", "em"], ["%", "px", "em"], new RegExp((f_st_width ? selectedWidthUnit : "%"), "i"));
			var li = doc.createElement("li");
			ul.appendChild(li);
			TableOperations.buildInput(doc, el, i18n, li, "f_st_height", "Height:", "Table height", "", "5", f_st_height, "fr");
			select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_heightUnit", "", "", "", "Height unit", ["percent", "pixels", "em"], ["%", "px", "em"], new RegExp((f_st_height ? selectedHeightUnit : "%"), "i"));
			selected = (HTMLArea._is_ie) ? el.style.styleFloat : el.style.cssFloat;
			select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_float", "Float:", "", "", "Specifies where the table should float", ["Not set", "Non-floating", "Left", "Right"], ["not set", "none", "left", "right"], new RegExp((selected ? selected : "not set"), "i"));
			break;
		case "tr" :
			var li = doc.createElement("li");
			ul.appendChild(li);
			TableOperations.buildInput(doc, el, i18n, li, "f_st_width", "Width:", "Row width", "", "5", f_st_width, "fr");
			select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_widthUnit", "", "", "", "Width unit", ["percent", "pixels", "em"], ["%", "px", "em"], new RegExp((f_st_width ? selectedWidthUnit : "%"), "i"));
			var li = doc.createElement("li");
			ul.appendChild(li);
			TableOperations.buildInput(doc, el, i18n, li, "f_st_height", "Height:", "Row height", "", "5", f_st_height, "fr");
			select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_heightUnit", "", "", "", "Height unit", ["percent", "pixels", "em"], ["%", "px", "em"], new RegExp((f_st_height ? selectedHeightUnit : "%"), "i"));
			break;
		case "td" :
		case "th" :
			var li = doc.createElement("li");
			ul.appendChild(li);
			TableOperations.buildInput(doc, el, i18n, li, "f_st_width", "Width:", "Cell width", "", "5", f_st_width, "fr");
			select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_widthUnit", "", "", "", "Width unit", ["percent", "pixels", "em"], ["%", "px", "em"], new RegExp((f_st_width ? selectedWidthUnit : "%"), "i"));
			var li = doc.createElement("li");
			ul.appendChild(li);
			TableOperations.buildInput(doc, el, i18n, li, "f_st_height", "Height:", "Cell height", "", "5", f_st_height, "fr");
			select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_heightUnit", "", "", "", "Height unit", ["percent", "pixels", "em"], ["%", "px", "em"], new RegExp((f_st_height ? selectedHeightUnit : "%"), "i"));		
	}
	content.appendChild(fieldset);
};
TableOperations.buildAlignmentFieldset = function(doc,el,i18n,content,fieldsetClass) {
	var select;
	var tag = el.tagName.toLowerCase();
	var fieldset = doc.createElement("fieldset");
	if(fieldsetClass) fieldset.className = fieldsetClass;
	TableOperations.insertLegend(doc, i18n, fieldset, "Alignment");
	var options = ["Not set", "Left", "Center", "Right", "Justify"];
	var values = ["not set", "left", "center", "right", "justify"];
	var selected = el.style.textAlign;
	(selected.match(/([^\s]*)\s/)) && (selected = RegExp.$1);
/*
	if (tag == "td") {
		options.push("Character");
		values.push("character");
		if(f_st_textAlign.charAt(0) == '"') {
			var splitArray = f_st_textAlign.split('"');
			var f_st_textAlignChar = splitArray[0];
			f_st_textAlign = "character";
		}
	}
*/
	var ul = doc.createElement("ul");
	fieldset.appendChild(ul);
	var li = doc.createElement("li");
	ul.appendChild(li);
	select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_textAlign", "Text alignment:", "fl", "", "Horizontal alignment of text within cell", options, values, new RegExp((selected ? selected : "not set"), "i"));
/*
	if (tag == "td") {
		var characterFields = [];
		TableOperations.buildInput(doc, el, i18n, fieldset, "f_st_textAlignChar", "", "Align on this character", "", "1", f_st_textAlignChar, "", "floating", "", characterFields);
		function setCharVisibility(value) {
			for (var i = 0; i < characterFields.length; ++i) {
				var characterFieldElement = characterFields[i];
				characterFieldElement.style.visibility = value ? "visible" : "hidden";
				if (value && (characterFieldElement.tagName.toLowerCase() == "input" )) {
					characterFieldElement.focus();
					characterFieldElement.select();
				}
			}
		};
		select.onchange = function() { setCharVisibility(this.value == "character"); };
		setCharVisibility(select.value == "character");
	}
*/
	var li = doc.createElement("li");
	ul.appendChild(li);
	selected = el.style.verticalAlign;
	(selected.match(/([^\s]*)\s/)) && (selected = RegExp.$1);
	select = TableOperations.buildSelectField(doc, el, i18n, li, "f_st_vertAlign", "Vertical alignment:", "fl", "", "Vertical alignment of content within cell", ["Not set", "Top", "Middle", "Bottom", "Baseline"], ["not set", "top", "middle", "bottom", "baseline"], new RegExp((selected ? selected : "not set"), "i"));
	content.appendChild(fieldset);
};
TableOperations.buildSpacingFieldset = function(doc,el,i18n,content) {
	var fieldset = doc.createElement("fieldset");
	TableOperations.insertLegend(doc, i18n, fieldset, "Spacing and padding");
	var ul = doc.createElement("ul");
	fieldset.appendChild(ul);
	var li = doc.createElement("li");
	ul.appendChild(li);
	TableOperations.buildInput(doc, el, i18n, li, "f_spacing", "Cell spacing:", "Space between adjacent cells", "pixels", "5", el.cellSpacing, "fr", "", "postlabel");
	var li = doc.createElement("li");
	ul.appendChild(li);
	TableOperations.buildInput(doc, el, i18n, li, "f_padding", "Cell padding:", "Space between content and border in cell", "pixels", "5", el.cellPadding, "fr", "", "postlabel");
	content.appendChild(fieldset);
};
TableOperations.buildBordersFieldset = function(w,doc,editor,el,i18n,content,fieldsetClass) {
	var select;
	var selected;
	var borderFields = [];
	function setBorderFieldsVisibility(value) {
		for (var i = 0; i < borderFields.length; ++i) {
			var borderFieldElement = borderFields[i];
			borderFieldElement.style.visibility = value ? "hidden" : "visible";
			if (!value && (borderFieldElement.tagName.toLowerCase() == "input")) {
				borderFieldElement.focus();
				borderFieldElement.select();
			}
		}
	};
	var fieldset = doc.createElement("fieldset");
	fieldset.className = fieldsetClass;
	TableOperations.insertLegend(doc, i18n, fieldset, "Frame and borders");
	TableOperations.insertSpace(doc, fieldset);
		// Gecko reports "solid solid solid solid" for "border-style: solid".
		// That is, "top right bottom left" -- we only consider the first value.
	selected = el.style.borderStyle;
	(selected.match(/([^\s]*)\s/)) && (selected = RegExp.$1);
	selectBorderStyle = TableOperations.buildSelectField(doc, el, i18n, fieldset, "f_st_borderStyle", "Border style:", "fr", "floating", "Border style", ["Not set", "No border", "Dotted", "Dashed", "Solid", "Double", "Groove", "Ridge", "Inset", "Outset"], ["not set", "none", "dotted", "dashed", "solid", "double", "groove", "ridge", "inset", "outset"], new RegExp((selected ? selected : "not set"), "i"));
	selectBorderStyle.onchange = function() { setBorderFieldsVisibility(this.value == "none"); };
	TableOperations.buildInput(doc, el, i18n, fieldset, "f_st_borderWidth", "Border width:", "Border width", "pixels", "5", TableOperations.getLength(el.style.borderWidth), "fr", "floating", "postlabel", borderFields);
	TableOperations.insertSpace(doc, fieldset, borderFields);

	if (el.tagName.toLowerCase() == "table") {
		TableOperations.buildColorField(w, doc, editor, el, i18n, fieldset, "", "Color:", "fr", "colorButton", el.style.borderColor, "borderColor", borderFields);
		var label = doc.createElement("label");
		label.className = "fl-borderCollapse";
		label.htmlFor = "f_st_borderCollapse";
		label.innerHTML = i18n["Collapsed borders"];
		fieldset.appendChild(label);
		borderFields.push(label);
		var input = doc.createElement("input");
		input.className = "checkbox";
		input.type = "checkbox";
		input.name = "f_st_borderCollapse";
		input.id = "f_st_borderCollapse";
		input.defaultChecked = /collapse/i.test(el.style.borderCollapse);
		input.checked = input.defaultChecked;
		fieldset.appendChild(input);
		borderFields.push(input);
		TableOperations.insertSpace(doc, fieldset, borderFields);
		select = TableOperations.buildSelectField(doc, el, i18n, fieldset, "f_frames", "Frames:", "fr", "floating", "Specifies which sides should have a border", ["Not set", "No sides", "The top side only", "The bottom side only", "The top and bottom sides only", "The right and left sides only", "The left-hand side only", "The right-hand side only", "All four sides"], ["not set", "void", "above", "below", "hsides", "vsides", "lhs", "rhs", "box"], new RegExp((el.frame ? el.frame : "not set"), "i"), borderFields);
		TableOperations.insertSpace(doc, fieldset, borderFields);
		select = TableOperations.buildSelectField(doc, el, i18n, fieldset, "f_rules", "Rules:", "fr", "floating", "Specifies where rules should be displayed", ["Not set", "No rules", "Rules will appear between rows only", "Rules will appear between columns only", "Rules will appear between all rows and columns"], ["not set", "none", "rows", "cols", "all"], new RegExp((el.rules ? el.rules : "not set"), "i"), borderFields);
	} else {
		TableOperations.insertSpace(doc, fieldset, borderFields);
		TableOperations.buildColorField(w, doc, editor, el, i18n, fieldset, "", "Color:", "fr", "colorButton", el.style.borderColor, "borderColor", borderFields);
	}
	setBorderFieldsVisibility(selectBorderStyle.value == "none");
	TableOperations.insertSpace(doc, fieldset);
	content.appendChild(fieldset);
};
TableOperations.buildColorsFieldset = function(w,doc,editor,el,i18n,content) {
	var fieldset = doc.createElement("fieldset");
	TableOperations.insertLegend(doc, i18n, fieldset, "Background and colors");
	var ul = doc.createElement("ul");
	fieldset.appendChild(ul);
	var li = doc.createElement("li");
	ul.appendChild(li);
	TableOperations.buildColorField(w, doc, editor, el, i18n, li, "", "FG Color:", "fr", "colorButtonNoFloat", el.style.color, "color");
	var li = doc.createElement("li");
	ul.appendChild(li);
	TableOperations.buildColorField(w, doc, editor, el, i18n, li, "", "Background:", "fr", "colorButtonNoFloat", el.style.backgroundColor, "backgroundColor");
	var url;
	if (el.style.backgroundImage.match(/url\(\s*(.*?)\s*\)/)) url = RegExp.$1;
	TableOperations.buildInput(doc, el, i18n, li, "f_st_backgroundImage", "Image URL:", "URL of the background image", "", "", url, "", "shorter-value");
	content.appendChild(fieldset);
};
TableOperations.insertLegend = function(doc,i18n, fieldset,legend) {
	var legendNode = doc.createElement("legend");
	legendNode.innerHTML = i18n[legend];
	fieldset.appendChild(legendNode);
};
TableOperations.insertSpace =	function(doc,fieldset,fields) {
	var space = doc.createElement("div");
	space.className = "space";
	fieldset.appendChild(space);
	if(fields) fields.push(space);
};
TableOperations.buildInput = function(doc,el,i18n,fieldset,fieldName,fieldLabel,fieldTitle,postLabel,fieldSize,fieldValue,labelClass,inputClass,postClass,fields) {
	var label;
		// Field label
	if(fieldLabel) {
		label = doc.createElement("label");
		if(labelClass) label.className = labelClass;
		label.innerHTML = i18n[fieldLabel];
		label.htmlFor = fieldName;
		fieldset.appendChild(label);
		if(fields) fields.push(label);
	}
		// Input field
	var input = doc.createElement("input");
	input.type = "text";
	input.id = fieldName;
	input.name =  fieldName;
	if(inputClass) input.className = inputClass;
	if(fieldTitle) input.title = i18n[fieldTitle];
	if(fieldSize) input.size = fieldSize;
	if(fieldValue) input.value = fieldValue;
	fieldset.appendChild(input);
	if(fields) fields.push(input);
		// Field post label
	if(postLabel) {
		label = doc.createElement("span");
		if(postClass) label.className = postClass;
		label.innerHTML = i18n[postLabel];
		fieldset.appendChild(label);
		if(fields) fields.push(label);
	}
};
TableOperations.buildSelectField = function(doc,el,i18n,fieldset,fieldName,fieldLabel,labelClass,selectClass,fieldTitle,options,values,selected,fields,translateOptions) {
	if(typeof(translateOptions) == "undefined") var translateOptions = true;
		// Field Label
	if(fieldLabel) {
		var label = doc.createElement("label");
		if(labelClass) label.className = labelClass;
		label.innerHTML = i18n[fieldLabel];
		label.htmlFor = fieldName;
		fieldset.appendChild(label);
		if(fields) fields.push(label);
	}
		// Text Alignment Select Box
	var select = doc.createElement("select");
	if (selectClass) select.className = selectClass;
	select.id = fieldName;
	select.name =  fieldName;
	select.title= i18n[fieldTitle];
	select.selectedIndex = 0;
	var option;
	for (var i = 0; i < options.length; ++i) {
		option = doc.createElement("option");
		select.appendChild(option);
		option.value = values[i];
		if(translateOptions) option.appendChild(doc.createTextNode(i18n[options[i]]));
			else option.appendChild(doc.createTextNode(options[i]));
		option.selected = selected.test(option.value);
	}
	if (select.options.length>1) select.disabled = false;
		else select.disabled = true;
	fieldset.appendChild(select);
	if(fields) fields.push(select);
	return select;
};
TableOperations.buildColorField = function(w,doc,editor,el,i18n,fieldset,fieldName,fieldLabel,labelClass, buttonClass, fieldValue,fieldType,fields) {
		// Field Label
	if(fieldLabel) {
		var label = doc.createElement("label");
		if(labelClass) label.className = labelClass;
		label.innerHTML = i18n[fieldLabel];
		fieldset.appendChild(label);
		if(fields) fields.push(label);
	}
	var colorButton = TableOperations.createColorButton(w, doc, editor, fieldValue, fieldType);
	colorButton.className = buttonClass;
	fieldset.appendChild(colorButton);
	if(fields) fields.push(colorButton);
};