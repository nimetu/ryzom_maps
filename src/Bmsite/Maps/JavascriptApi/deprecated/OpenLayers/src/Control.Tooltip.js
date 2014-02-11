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
 * @requires OpenLayers/Control/SelectFeature.js
 */

/**
 * Inherits:
 *  - <OpenLayers.Control.SelectFeature>
 */
Ryzom.Control.Tooltip = OpenLayers.Class(OpenLayers.Control.SelectFeature, {

	/**
	 * dom elements
	 */
	ttDiv: null,
	ttContentDiv: null,

	initialize: function(layers) {
		this.ttDiv = document.createElement('div');
		this.ttDiv.className = 'ryzom-ui ryzom-ui-tooltip';
		this.ttDiv.style.display = 'none';

		this.ttContentDiv = document.createElement('div');
		this.ttContentDiv.className = 'ryzom-ui-tooltip-content';

		this.ttDiv.appendChild(this.ttContentDiv);

		var options = {
			hover: true,
			onSelect: this.onFeatureSelect,
			onUnselect: this.onFeatureUnselect
		};

		OpenLayers.Control.SelectFeature.prototype.initialize.apply(this, [layers, options]);
	},

	destroy: function(){
		this.map.div.removeChild(this.ttDiv);
		this.ttDiv = null;
		this.ttContentDiv = null;

		OpenLayers.Control.SelectFeature.prototype.destroy.apply(this, arguments);
	},

	onFeatureSelect: function(feature){

		if(feature.geometry && feature.attributes){
			var attr = feature.attributes;
			if(attr.description && attr.description != ''){
				// FIXME: draw at mouse position
				var xy = feature.geometry.getCentroid();

				// lonlat and geometry are 1:1
				var layerPx = this.map.getLayerPxFromLonLat({lon:xy.x, lat:xy.y});
				var px = this.map.getViewPortPxFromLayerPx(layerPx);

				this.ttDiv.style.position = 'absolute';
				this.ttDiv.style.left = (px.x+10)+'px';
				this.ttDiv.style.top  = (px.y+10)+'px';
				this.ttDiv.style.display = '';
				this.ttDiv.style.zIndex = '5000';

				this.ttContentDiv.innerHTML = attr.description;

				// make copy from style and change it's externalGraphic
				var style = OpenLayers.Util.extend({}, feature.style);
				if(style.externalGraphic){
					style.externalGraphic = Ryzom.selectedIcon(style.externalGraphic);
					feature.layer.drawFeature(feature, style);
				}
			}
		}
	},

	setMap: function(map){
		OpenLayers.Control.SelectFeature.prototype.setMap.apply(this, arguments);

		this.map.div.appendChild(this.ttDiv);
	},

	onFeatureUnselect: function(feature){
		this.ttDiv.style.display = 'none';
	},

	CLASS_NAME: 'Ryzom.Control.Tooltip'
});

