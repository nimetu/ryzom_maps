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
 * @requires OpenLayers/Style.js
 * @requires OpenLayers/Util.js
 */

/**
 * Map label style with on-hover icon
 *
 * Inherits
 *  - <OpenLayers.Style>
 */
Ryzom.Style.Label = OpenLayers.Class(OpenLayers.Style, {

	initialize: function(style, options){

		style = OpenLayers.Util.applyDefaults(style, {
			'fill': false,
			'stroke': false,
			'graphic': true,
			//
			'labelAlign': 'cm',
			'graphicWidth': 32,
			'graphicHeight': 32,
			'graphicXOffset': -16,
			'graphicXOffset': -16,
			'fontFamily': 'verdana',
			// context rules
			'display': '${rule}',
			'externalGraphic': '${rule}',
			'label': '${rule}',
			'fontColor': '${rule}',
			'fontSize': '${rule}',
			'fontWeight': '${rule}'
		});

		options = options || {};
		options.context = OpenLayers.Util.applyDefaults(options.context, {
			rule: this.contextRuleFunc
		});

		OpenLayers.Style.prototype.initialize.apply(this, [style, options]);
	},

	contextRuleFunc: function(feature, prop){

		function htmldecode(str){
			return str.replace('&amp;', '&')
					  .replace('&quot;', '"')
					  .replace('&#039;', '\'')
					  .replace('&lt;', '<')
					  .replace('&gt;', '>');
		}

		var layer = feature.layer;
		var label = feature.attributes;
		var zoom = layer.map.getZoom();
		var result = '';
		var isVisible = (zoom >= label.minZoom && zoom <= label.maxZoom);

		switch(prop){
			case 'display':
				// label in in zoom range
				result = isVisible ? '' : 'none';
				break;
			case 'externalGraphic':
				// outpost flag
				result = (label.showLabel !== true && isVisible) ? label.url : '';
				if(result != '' && feature.renderIntent == 'select'){
					result = result.replace(/icon=([^&]+)/, 'icon=$1_over');
				}
				break;
			case 'label':
				if(label.showLabel === false){
					// label is hidden (like stables)
					result = '';
				}else if(label.showLabel !== true && label.showLabel > zoom){
					// if showLabel is not true, then it's zoom level (like outpost names)
					result = '';
				}else{
					result = htmldecode(label.name[layer.lang]);
				}
				// change tooltip
				label.description = label.name[layer.lang];
				break;
			case 'fontColor':
				result = '#fff';
				break;
			case 'fontSize':
				result = label.fontSize;
				break;
			case 'fontWeight':
				result = label.fontWeight;
				break;
		}
		return result;
	},

	CLASS_NAME:'Ryzom.Style.Label'
});

