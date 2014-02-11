//
// Ryzom Maps
// Copyright (c) 2009 Meelis Mägi <nimetu@gmail.com>
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
//
if(!Array.prototype.indexOf){
	Array.prototype.indexOf = function(obj, n){
		var len=this.length;
		n=n||0;
		for(var i=n; i<len; i++){
			if(this[i]==obj){
				return i;
			}
		}
		return -1;
	};
}

var MAPS_HOST = 'http://maps.bmsite.net/';
var DEBUG=false;

/**
 * start
 */
window.ryzom = window.ryzom || {};
ryzom.maps = ryzom.maps || {};

// constants
ryzom.maps.tile_url = MAPS_HOST+'images/';
ryzom.maps.season   = 'sp';
ryzom.maps.lang     = 'en';

ryzom.maps.AtysMapType = {
	ID : 'atys',
	options : {
		alt: 'Atys map',
		name: 'Atys',
		minZoom: 1,
		maxZoom: 11,
		getTileUrl: function(a, z){
			return ryzom.maps.tile_url+'atys/zoom_'+z+'/atys_'+z+'_'+a.x+'x'+a.y+'.jpg';
		},
		tileSize: new google.maps.Size(256, 256),
		isPng: false
	}
};
ryzom.maps.AtysIgMapType = {
	ID : 'atys_ig',
	options : {
		alt: 'Atys ingame map',
		name: 'Satellite',
		minZoom: 1,
		maxZoom: 11,
		getTileUrl: function(a, z){
			return ryzom.maps.tile_url+'atys_'+ryzom.maps.season+'/zoom_'+z+'/atys_'+ryzom.maps.season+'_'+z+'_'+a.x+'x'+a.y+'.jpg';
		},
		tileSize: new google.maps.Size(256, 256),
		isPng: false
	}
};
/**
 * NOTE to self:
 * world coordinate : float number at 0 zoom level inside 256x256 block - will be higher than 1 if outside that area
 * pixel coordinate : world*(2^zoom) - @zoom 9, matches ryzom.maps.outgame_coordinates
 * container coords : useful for mouse tooltip
 * div coords       : image tile coords on 256x256 block
 * 
 * for base outgame coordinates -> getMap().getProjection().fromLatLngToPoint()*pow(2, base_zoom)
 * from base outgame to lat-lng -> getMap().getProjection().fromPointToLatLng(x/pow(2, base_zoom), y/pow(2, base_zoom))
 */

// create and return gmap v3 map instance
ryzom.maps.create = function(id, defaultZoom){
	
	defaultZoom = defaultZoom || 10;
	
	var options = {
		backgroundColor: '#000', // FIXME: set background to black
		zoom           : defaultZoom,
		center         : new google.maps.LatLng(84.153, -170.255),
		mapTypeId      : ryzom.maps.AtysMapType.ID,
		mapTypeControlOptions : {
			mapTypeIds: [
			    ryzom.maps.AtysMapType.ID,
			    ryzom.maps.AtysIgMapType.ID
			],
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
		},
		streetViewControl: false
	};
	var map = new google.maps.Map(document.getElementById(id), options);
	
	// convert base coords to/from world coorinates
    map.baseScale_ = Math.pow(2, ryzom.maps.BASE_ZOOM);

    //
	var atType = new google.maps.ImageMapType(ryzom.maps.AtysMapType.options);
	var igType = new google.maps.ImageMapType(ryzom.maps.AtysIgMapType.options);
	
	map.mapTypes.set(ryzom.maps.AtysMapType.ID,   atType);
	map.mapTypes.set(ryzom.maps.AtysIgMapType.ID, igType);
	
	// we need to set maptype here or getProjection() in igCenter() will fail
	map.setMapTypeId(ryzom.maps.AtysMapType.ID);
	
//	// Create new control to display latlng and coordinates under mouse.
//    var latLngControl = new LatLngControl(map);
//    
//    // Register event listeners
//    google.maps.event.addListener(map, 'mouseover', function(mEvent) {
//      latLngControl.set('visible', true);
//    });
//    google.maps.event.addListener(map, 'mouseout', function(mEvent) {
//      latLngControl.set('visible', false);
//    });
//    google.maps.event.addListener(map, 'mousemove', function(mEvent) {
//      latLngControl.updatePosition(mEvent.latLng);
//    });
    
	return map;
};

google.maps.Map.prototype.igCenter = function(ax, ay, zoom, show_ig){
	var center = this.fromIngame(ax, ay);
	// only touch map type if show_ig is set
	if(show_ig != undefined){
		var mapTypeId = this.getMapTypeId();
		if(show_ig && mapTypeId != ryzom.maps.AtysIgMapType.ID){
			this.setMapTypeId(ryzom.maps.AtysIgMapType.ID);
		}else if(mapTypeId != ryzom.maps.AtysMapType.ID){
			this.setMapTypeId(ryzom.maps.AtysMapType.ID);
		}
	}
	this.setCenter(center);
	if(zoom != undefined){
		this.setZoom(zoom);
	}
};

// Returns GMap map type object for 'atys' view
ryzom.maps.worldMapType=function(){
	return atysmap;
};
// Returns GMap map type object for 'satellite' view
ryzom.maps.igMapType=function(){
	return igmap;
};
// returns true if ig tiles are active
ryzom.maps.isIgMap=function(){
	return map.getCurrentMapType()==igmap;
};
// set IG tile season
ryzom.maps.setSeason = function(season){
	map.savePosition();
	me.season=season;
	map.returnToSavedPosition();
};

/**
 * Translate Ryzom ingame x/y to LatLng
 *
 * @param  ax ingame x
 * @param  ay ingame y
 * @return LatLng
 */
google.maps.Map.prototype.fromIngame=function(ax, ay){
	var p=ryzom.maps.fromIngameToOutgame(ax, ay, 2);
	return this.fromImage(p.x, p.y);
};

/**
 * Convert image pixel coordinates to LatLng
 *
 * @param ax image x
 * @param ay image y
 * @return GPoint
 */
google.maps.Map.prototype.fromImage=function(ax, ay){
	return this.getProjection().fromPointToLatLng(new google.maps.Point(ax/this.baseScale_, ay/this.baseScale_));
};

/**
 * Convert GPoint latLng to Ryzom ingame x/y
 *
 * @param latLng GPoint instance
 * @return {x:ig_x, y:ig_y, regions:[ig_street, ig_area, ig_region, ig_zone...]}
 * 		x is ingame x
 * 		y is ingame y
 * 		TODO: region only has lists one zone currently
 * 		regions is array of ingame regions that matched. sorted by region size, so 'smallest first'
 */
google.maps.Map.prototype.toIngame=function(latLng){
	var world = this.getProjection().fromLatLngToPoint(latLng);
	var ig=ryzom.maps.fromOutgameToIngame(world.x*this.baseScale_, world.y*this.baseScale_);
	// TODO: do polygon search (slow!!) for ig_x/y if we need to know exact location
	return {x:ig.x, y:ig.y, regions: ig.regions};
};
