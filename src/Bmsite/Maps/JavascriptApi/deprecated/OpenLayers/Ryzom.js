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
 * @requires OpenLayers/BaseTypes/Pixels.js
 * @requires OpenLayers/BaseTypes/LonLat.js
 * @requires OpenLayers/BaseTypes/Bounds.js
 * @requires OpenLayers/Geometry/Point.js
 * @requires OpenLayers/BaseTypes.js
 */

window.Ryzom = {
	/**
	 * Display debug in firebug console
	 */
	DEBUG: false,

	/**
	 * Map tileserver
	 */
	MAPS_HOST: 'http://maps.bmsite.net/',

	/**
	 * Theme root url
	 */
	theme: '/api/theme/',

	/**
	 * function to turn icon url to selected icon url
	 */
	selectedIcon: function(url){
		if(url.match(/icon=[^$]+_over/)){
			return url;
		}else{
			return url.replace(/icon=([^&]+)/, 'icon=$1_over');
		}
	},

	/**
	 * Ryzom.Control.* classes
	 */
	Control : {},

	/**
	 * Ryzom.Layer.* classes
	 */
	Layer: {},

	/**
	 * Ryzom.Style.* classes
	 */
	Style: {},

	/**
	 * Ryzom.Geometry.* classes
	 */
	Geometry: {
		/**
		 * Convert server coordinates to world coordinates
		 *
		 * @eturn OpenLayers.Geometry.Point instance in world coordinates
		 */
		Point : function(x, y){
			var px = Ryzom.XY.toWorld(x, y);

			return new OpenLayers.Geometry.Point(px.x, px.y);
		}
	},

	/**
	 * Convert server coordinates to world coordinates
	 *
	 * @eturn OpenLayers.Pixel instance in world coordinates
	 */
	Pixel : function(x, y){
		var px = Ryzom.XY.toWorld(x, y);

		return new OpenLayers.Pixel(px.x, px.y);
	},

	/**
	 * Convert server coordinates to world coordinates
	 *
	 * @eturn OpenLayers.LonLat instance in world coordinates
	 */
	Location : function(igx, igy){
		var px = Ryzom.XY.toWorld(igx, igy);

		return new OpenLayers.LonLat(px.x, px.y);
	},

	/**
	 * Convert server coordinates to world coordinates
	 *
	 * @eturn OpenLayers.Bounds instance in world coordinates
	 */
	Bounds : function(l, b, r, t){
		var lb = Ryzom.XY.toWorld(l, b);
		var rt = Ryzom.XY.toWorld(r, t);

		return new OpenLayers.Bounds(lb.x, lb.y, rt.x, rt.y);
	},

	CLASS_NAME : 'Ryzom'
};

OpenLayers.ImgPath = '/api/theme/openlayers/default/img/';
