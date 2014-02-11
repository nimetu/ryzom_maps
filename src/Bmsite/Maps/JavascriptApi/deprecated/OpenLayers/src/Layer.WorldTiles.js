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
 * @requires OpenLayers/BaseTypes/LonLat.js
 * @requires OpenLayers/Layer/Grid.js
 * @requires OpenLayers/Util.js
 */

/**
 * Inherits
 *  - <OpenLayers.Layer.Grid>
 */
Ryzom.Layer.WorldTiles = OpenLayers.Class(OpenLayers.Layer.Grid, {
	isBaseLayer: true,
	tileOrigin: null,
	wrapDateLine: false,

	// ingame map tiles or rendered 'satellite' tiles
	igTiles: true,

	// only 'spring' tiles are being hosted
	season: 'sp',

	initialize: function(name, options, ig, url){
		if(ig == undefined || ig === true){
			this.igTiles = true;
		}else{
			this.igTiles = false;
		}

		url = url || Ryzom.MAPS_HOST;

		options = OpenLayers.Util.applyDefaults(options, {
			projection: new OpenLayers.Projection('RZ:WORLD')
		});

		var newArguments = [];
		newArguments.push(name, url, {}, options);

		OpenLayers.Layer.Grid.prototype.initialize.apply(this, newArguments);
	},

	getURL: function(bounds){
		bounds = this.adjustBounds(bounds);
		var res = this.map.getResolution();
		var x = Math.round ((bounds.left - this.tileOrigin.lon) / (res * this.tileSize.w));
		var y = Math.round ((this.tileOrigin.lat - bounds.top) / (res * this.tileSize.h));
		var z = this.map.getZoom();

		var path;
		if(this.igTiles){
			path = 'images/atys/zoom_'+ z + '/atys_' + z + '_' + x + 'x' + y + ".jpg";
		}else{
			path = 'images/atys_'+this.season+'/zoom_'+ z + '/atys_'+this.season+'_' + z + '_' + x + 'x' + y + ".jpg";
		}
		var url = this.url;
		if (url instanceof Array) {
			url = this.selectUrl(path, url);
		}
		return url + path;
	},

	setMap: function(map){
		OpenLayers.Layer.Grid.prototype.setMap.apply(this, arguments);
		if(!this.tileOrigin){
			this.tileOrigin = new OpenLayers.LonLat(this.maxExtent.left, this.maxExtent.top);
		}
	},

	CLASS_NAME: 'Ryzom.WorldTiles'
});

