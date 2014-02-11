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
 * @requires OpenLayers/Map.js
 * @requires OpenLayers/Projection.js
 * @requires OpenLayers/Util.js
 * @requires OpenLayers/BaseTypes/Bounds.js
 */

/**
 * Inherits
 *  - <OpenLayers.Map>
 */
Ryzom.Map = OpenLayers.Class(OpenLayers.Map, {

	// FIXME: min/max zoom?

	initialize: function(id, options){
		options = options || {};
		//_NbZoneX = 26 * ('Z'-'A') + ('Z'-'A') = (26*25+25) = 675
		//_NbZoneY = 297;
		//_SizeZoneX = 160;
		//
		// Server size:
		// X = 675*129760 = 108000 m
		// Y = 297*160    =  47520 m

		// map and layers projection
		var proj = options.projection || new OpenLayers.Projection('RZ:WORLD');
		var layers = [new Ryzom.Layer.WorldTiles('Atys', {projection: proj})];
		if(options.satellite === true){
			layers.push(new Ryzom.Layer.WorldTiles('Satellite', {projection: proj}, false));
		}

		OpenLayers.Util.applyDefaults(options, {
			theme: Ryzom.theme+'openlayers/default/style.css',
			// grid X/Y coordinates, tiles per zoom level
			resolutions: [1024, 512, 256, 128, 64, 32, 16, 8, 4, 2, 1, 0.5, 0.25],
			// cover entire server space
			maxExtent: new OpenLayers.Bounds(0, -47520, 108000, 0),
			units: 'm',
			projection: proj,
			layers: layers,
			controls: []
		});

		var newArguments = [];
		newArguments.push(id, options);

		OpenLayers.Map.prototype.initialize.apply(this, newArguments);

		// check existing links for equivalent url
		var theme = Ryzom.theme + 'ryzom-maps.css';
		var addNode = true;
		var nodes = document.getElementsByTagName('link');
		for(var i=0, len=nodes.length; i<len; ++i) {
			if(OpenLayers.Util.isEquivalentUrl(nodes.item(i).href, theme)) {
				addNode = false;
				break;
			}
		}
		// only add a new node if one with an equivalent url hasn't already
		// been added
		if(addNode) {
			var cssNode = document.createElement('link');
			cssNode.setAttribute('rel', 'stylesheet');
			cssNode.setAttribute('type', 'text/css');
			cssNode.setAttribute('href', theme);
			document.getElementsByTagName('head')[0].appendChild(cssNode);
		}
	},
	CLASS_NAME: 'Ryzom.Map'
});

