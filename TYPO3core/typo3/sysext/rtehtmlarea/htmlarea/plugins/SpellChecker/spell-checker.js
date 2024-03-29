/***************************************************************
*  Copyright notice
*
*  (c) 2003 dynarch.com. Authored by Mihai Bazon, sponsored by www.americanbible.org.
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
 * Spell Checker Plugin for TYPO3 htmlArea RTE
 *
 * TYPO3 CVS ID: $Id: spell-checker.js 1421 2006-04-10 09:27:15Z mundaun $
 */

SpellChecker = function(editor) {
	this.editor = editor;
	var cfg = editor.config;
	var actionHandlerFunctRef = SpellChecker.actionHandler(this);

	cfg.registerButton("SpellCheck", 
		SpellChecker_langArray["SC-spell-check"],
		editor.imgURL("spell-check.gif", "SpellChecker"),
		false,
		actionHandlerFunctRef
	);
};

SpellChecker.I18N = SpellChecker_langArray;

SpellChecker._pluginInfo = {
	name 		: "SpellChecker",
	version 	: "2.1",
	developer 	: "Mihai Bazon & Stanislas Rolland",
	developer_url 	: "http://dynarch.com/mishoo/",
	c_owner 	: "Mihai Bazon & Stanislas Rolland",
	sponsor 	: "American Bible Society & Fructifor Inc.",
	sponsor_url 	: "http://www.fructifor.ca/",
	license 	: "GPL"
};

SpellChecker.actionHandler = function(instance) {
	return (function(editor,id) {
		instance.buttonPress(editor, id);
	});
};

SpellChecker.prototype.buttonPress = function(editor, id) {
	var editorNumber = editor._editorNumber;
	switch (id) {
	    case "SpellCheck":
		SpellChecker.editor = editor;
		SpellChecker.init = true;
		SpellChecker.f_dictionary = _spellChecker_lang;
		SpellChecker.f_charset = _spellChecker_charset;
		SpellChecker.f_pspell_mode = _spellChecker_mode;
		SpellChecker.enablePersonalDicts = RTEarea[editorNumber]["enablePersonalDicts"];
		SpellChecker.userUid = RTEarea[editorNumber]["userUid"];
		var param = new Object();
		param.editor = editor;
		param.HTMLArea = HTMLArea;
		if (SpellChecker.f_charset.toLowerCase() == 'iso-8859-1') editor._popupDialog("plugin://SpellChecker/spell-check-ui-iso-8859-1", null, param, 670, 515);
    			else editor._popupDialog("plugin://SpellChecker/spell-check-ui", null, param, 670, 515);
		break;
	}
};

// this needs to be global, it's accessed from spell-check-ui.html
SpellChecker.editor = null;
