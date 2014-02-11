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
 * @requires OpenLayers/Layer/Vector.js
 * @requires OpenLayers/StyleMap.js
 * @requires OpenLayers/Util.js
 * @requires OpenLayers/Feature/Vector.js
 */

/**
 * Draws places text and outpost icons on map
 *
 * [code]
 *	var labels = new Ryzom.Layer.Labels();
 *	map.addLayer(labels);
 * [/code]
 *
 * Inherits from
 *  - <OpenLayers.Layer.Vector>
 */
Ryzom.Layer.Labels = OpenLayers.Class(OpenLayers.Layer.Vector, {
	/**
	 * Currently used language, default is 'en'
	 */
	lang: null,

	/**
	 * Initialize text overlay
	 */
	initialize: function(name, options){

		var style = new Ryzom.Style.Label();

		options = OpenLayers.Util.extend({
			lang: 'en',
			styleMap: new OpenLayers.StyleMap(style)
		}, options);

		OpenLayers.Layer.Vector.prototype.initialize.apply(this, [name, options]);
	},

	/**
	 * Change map language
	 */
	setLanguage: function(lang){
		if(this.lang == lang){
			return;
		}
		this.lang = lang;

		this.redraw();
	},

	display: function(){
		if(!this.loaded){
			this.createLabels();
		}
		OpenLayers.Layer.Vector.prototype.display.apply(this, arguments);
	},

	/**
	 * Inserts labels to layer
	 */
	createLabels: function(){

		// get url for outpost icons
		var url = new Ryzom.Icon.OUTPOST().url;

		var features = [];
		for(var prop in Ryzom.locations){
			// label data
			var attributes = OpenLayers.Util.extend({
				sheetid: prop,
				url: url
			}, Ryzom.locations[prop]);

			// include display rules
			OpenLayers.Util.extend(attributes, Ryzom.Layer.Labels.rules['type' + attributes.type]);

			var px = new Ryzom.Geometry.Point(attributes.x, attributes.y);
			// attributes:
			//  - url, minZoom, maxZoom, fontSize, fontWeight, showLabel
			//  - x, y, level, type, name:{lang:transl}
			// style will dynamically set:
			//  - description
			features.push(new OpenLayers.Feature.Vector(px, attributes));
		}
		this.addFeatures(features);

		this.loaded = true;
	},

	CLASS_NAME: 'Ryzom.Layer.Labels'
});

/**
 * Rules how to display different labels
 */
Ryzom.Layer.Labels.rules = {
	'default': {
		'minZoom': 5,
		'maxZoom': 99,
		'fontSize': '8pt',
		'fontWeight': 'normal',
		'showLabel': true
	},
	// continent, biggest zone
	'type-1' : {
		'minZoom': 4,
		'maxZoom': 7,
		'fontSize': '10pt',
		'fontWeight': 'bold',
		'showLabel': true
	},
	// capital city
	'type0' : {
		'minZoom': 6,
		'maxZoom': 10,
		'fontSize': '9pt',
		'fontWeight': 'bold',
		'showLabel': true
	},
	// town
	'type1' : {
		'minZoom': 7,
		'maxZoom': 99,
		'fontSize': '9pt',
		'fontWeight': 'normal',
		'showLabel': true
	},
	// region
	'type4' : {
		'minZoom': 6,
		'maxZoom': 99,
		'fontSize': '8pt',
		'fontWeight': 'bold',
		'showLabel': true
	},
	// area
	'type5' : {
		'minZoom': 8,
		'maxZoom': 99,
		'fontSize': '8pt',
		'fontWeight': 'bold',
		'showLabel': true
	},
	// outpost
	'type2' : {
		'minZoom': 7,
		'maxZoom': 99,
		'fontSize': '7pt',
		'fontWeight': 'normal',
		'showLabel': 9
	},
	// stable
	'type3' : {
		'minZoom': 9,
		'maxZoom': 99,
		'fontSize': '7pt',
		'fontWeight': 'normal',
		'showLabel' : false
	},
	// street
	'type6' : {
		'minZoom': 10,
		'maxZoom': 99,
		'fontSize': '7pt',
		'fontWeight': 'normal',
		'showLabel' : true
	}
};
