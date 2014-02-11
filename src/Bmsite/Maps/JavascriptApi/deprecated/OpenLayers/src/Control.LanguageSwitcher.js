//
// Ryzom Maps
// Copyright (c) 2011 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of Ryzom Maps.
//
// Ryzom Maps is free software; you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// Ryzom Maps is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program; if not, write to the Free Software Foundation,
// Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
//

/**
 * @requires OpenLayers/Control.js
 * @requires OpenLayers/Events.js
 * @requires OpenLayers/BaseTypes/Element.js
 */

/**
 * Inherits from:
 *  - <OpenLayers.Control>
 */
Ryzom.Control.LanguageSwitcher = OpenLayers.Class(OpenLayers.Control, {

	/**
	 * Ryzom.Layer.Labels instance
	 */
	layer: null,

	/**
	 * Selected language, default 'en'
	 */
	lang: 'en',

	/**
	 * domDiv for selected language
	 */
	selected: null,

	/**
	 * Language names
	 */
	languageNames: {
		'en': 'English',
		'fr': 'Français',
		'de': 'Deutsch',
		'ru': 'Pусский',
		'es': 'Español'
	},

	/**
	 * Languages to display
	 */
	languages: ['en', 'fr', 'de', 'ru', 'es'],

	/**
	 * Language change event
	 */
	onLanguageChange: function(lang){},

	/**
	 * Controller
	 *
	 * options
	 * lang             - {String} language code
	 * onLanguageChange - {Function} callback function when language is changed
     */
    initialize: function(layer, options) {
		this.layer = layer;

        OpenLayers.Control.prototype.initialize.apply(this, [options]);
	},

	/**
     * APIMethod: destroy
     */
    destroy: function() {
        OpenLayers.Event.stopObservingElement(this.div);

        OpenLayers.Control.prototype.destroy.apply(this, arguments);
    },

    /**
     * Method: draw
     *
     * Returns:
     * {DOMElement} A reference to the DIV DOMElement containing the
     *     switcher tabs.
     */
    draw: function() {
        OpenLayers.Control.prototype.draw.apply(this);

		function createLangMenuItem(lang, name){
			// flag
			var span = document.createElement('span');
			span.className = 'flag flag-'+lang;

			// row
			var a = document.createElement('a');
			a.appendChild(span);
			a.href = '?language='+lang;
			a.style.display = 'block';
			a.style.textDecoration = 'none';
			a.style.padding = '.3em';

			// name
			var txt = document.createTextNode(name);
			a.appendChild(txt);

			return a;
		}

		// control div
		OpenLayers.Event.observe(this.div, "click", OpenLayers.Function.bindAsEventListener(this.cbLanguageSelect, this));

		// always visible language indicator
		this.selected = createLangMenuItem(this.lang, this.languageNames[this.lang]);
		this.div.appendChild(this.selected);

		// select container
		this.listDiv = document.createElement('div');

		// available languages
		for(var i=0,len=this.languages.length; i<len; i++){
			var lang = this.languages[i];

			var row = createLangMenuItem(lang, this.languageNames[lang]);
			row.lang = lang;

			this.listDiv.appendChild(row);
		}
		this.div.appendChild(this.listDiv);

		this.setLanguage(this.lang);

        return this.div;
    },

	cbLanguageSelect : function(evt){
		if(evt.target.lang){
			this.setLanguage(evt.target.lang);
		}else if(evt.target.parentNode.lang){
			this.setLanguage(evt.target.parentNode.lang);
		}else{
			// toggle language list
			if(this.listDiv.style.display == ''){
				this.listDiv.style.display = 'none';
			}else{
				this.listDiv.style.display = '';
			}
		}

		OpenLayers.Event.stop(evt);
	},

	setLanguage: function(lang){
		var flag = this.selected.childNodes[0];
		var text = this.selected.childNodes[1];

		if(this.onLanguageChange){
			this.onLanguageChange(lang);
		}

		// change flag
		OpenLayers.Element.removeClass(flag, 'flag-'+this.lang);
		OpenLayers.Element.addClass(flag, 'flag-'+lang);

		// change name
		text.nodeValue = this.languageNames[lang];

		// notify layer
		this.lang = lang;
		this.layer.setLanguage(lang);

		// hide language list
		this.listDiv.style.display = 'none';
	},

	CLASS_NAME: 'Ryzom.Control.LanguageSwitcher'
});
