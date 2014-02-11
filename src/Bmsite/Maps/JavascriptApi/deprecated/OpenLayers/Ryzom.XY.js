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
 * @requires OpenLayers/BaseTypes/Bounds.js
 * @requires OpenLayers/Geometry.js
 */
Ryzom.XY = {
	// zoom level for these coordinates (1px = 1m)
	BASE_ZOOM : 10,

	outgame_coordinates: {
		//
		fyros         : {l:2920, t:-636},
		matis         : {l:7760, t:-352},
		tryker        : {l:7716, t:-8224},
		zorai         : {l: 188, t:-8840},
		nexus         : {l:8196, t:-5656},
		bagne         : {l:8392, t:-3740},
		route_gouffre : {l:5792, t:-4144},
		terre         : {l:2372, t:-4960},
		sources       : {l:692,  t:-7000},
		//
		newbieland    : {l:   0, t:-16000},
		kitiniere     : {l:4000, t:-16000},
		corrupted_moor: {l:8000, t:-16000},
		//
		fyros_newbie  : {l:16000,t:     0},
		matis_newbie  : {l:16000,t: -4000},
		tryker_newbie : {l:16000,t: -8000},
		zorai_newbie  : {l:16000,t:-12000},
		//
		fyros_island  : {l:22000,t:     0},
		matis_island  : {l:22000,t: -4000},
		tryker_island : {l:22000,t: -8000},
		zorai_island  : {l:22000,t:-12000},
		//
		almati        : {l:22000,t: -4000},
		olkern        : {l:25680,t: -8160},
		aelius        : {l:25680,t:     0},
  // TODO: map image is not quite correct - should be horizontally stretched
  //       fix it here for now
  place_matis_island_2: {l:23500,t: -4000, r:24400},
		//
		indoors       : {l:    0,t:-20000},
		//testroom      : {l:    0,t:-21000},
		//
		r2_desert     : {l:40000,t:     0},
		r2_jungle     : {l:52000,t:     0},
		r2_lakes      : {l:40000,t:-12000},
		r2_forest     : {l:52000,t:-12000},
		r2_roots      : {l:64000,t:     0},

	// full world grid
		grid:{l: 0, t:0, r: 108000, b: -47520, w: 108000, h:47520}
	},

	ingame_coordinates : {
	// l=left, b=bottom, r=right, t=top, p=parent)
			   fyros:{l:15840, b:-27040, r:20320, t:-23840, p:''},
			   matis:{l:320,   b:-7840,  r:6240,  t:-320,   p:''},
			  tryker:{l:13760, b:-34880, r:20000, t:-29440, p:''},
			   zorai:{l:6880,  b:-5920,  r:12480, t:-960,   p:''},
			   bagne:{l:480,   b:-11360, r:1600,  t:-9760,  p:''}, // abyss of ichor
	   route_gouffre:{l:5440,  b:-16960, r:7360,  t:-9600,  p:''}, // land of umbra
			 sources:{l:2560,  b:-11360, r:3840,  t:-9760,  p:''}, // under spring
			   terre:{l:160,   b:-15840, r:3040,  t:-13120, p:''}, // wasteland
			   nexus:{l:7840,  b:-8320,  r:9760,  t:-6080,  p:''},
		  newbieland:{l:8160,  b:-12320, r:11360, t:-10240, p:''}, // silan
	// city: fyros
		   place_pyr:{l:18400, b:-24720, r:19040, t:-24240, p:'fyros'},
		 place_dyron:{l:16480, b:-24800, r:16720, t:-24480, p:'fyros'},
		place_thesos:{l:19520, b:-26400, r:19760, t:-26080, p:'fyros'},
	// city: matis
	   place_yrkanis:{l:4640,  b:-3680,  r:4800,  t:-3200,  p:'matis'},
		 place_natae:{l:3600,  b:-3840,  r:3840,  t:-3680,  p:'matis'},
		 place_davae:{l:4160,  b:-4240,  r:4320,  t:-4000,  p:'matis'},
		place_avalae:{l:4800,  b:-4480,  r:4960,  t:-4320,  p:'matis'},
	// city: tryker
	  place_avendale:{l:18000, b:-31200, r:18240, t:-30960, p:'tryker'},
	place_crystabell:{l:17760, b:-32000, r:18000, t:-31760, p:'tryker'},
	 place_fairhaven:{l:16960, b:-33280, r:17440, t:-32720, p:'tryker'},
	place_windermeer:{l:15440, b:-33120, r:15760, t:-32880, p:'tryker'},
	// city: zorai
		 place_zorai:{l:8480,  b:-3040,  r:8800,  t:-2720,  p:'zorai'},
	   place_hoi_cho:{l:9440,  b:-3680,  r:9680,  t:-3360,  p:'zorai'},
	   place_jen_lai:{l:8640,  b:-3840,  r:8960,  t:-3520,  p:'zorai'},
	   place_min_cho:{l:9760,  b:-4320,  r:10080, t:-4000,  p:'zorai'},
	// old pvp challenge areas
		fyros_island:{l:21120, b:-24960, r:25920, t:-23840, p:''}, // FIXME:
	   tryker_island:{l:21120, b:-30560, r:27200, t:-29440, p:''},
		matis_island:{l:14080, b:-1600,  r:18400, t:-320,   p:''},
		zorai_island:{l:13920, b:-4800,  r:18720, t:-3520,  p:''},
	// episode 2 areas
			  almati:{l:14080, b:-1600,  r:15360, t:-320,   p:'matis_island'},   // Almati
			  olkern:{l:24800, b:-30560, r:25760, t:-29600, p:'tryker_island'}, // Olkern
			  aelius:{l:24800, b:-24480, r:25920, t:-23840, p:'fyros_island'}, // Aelius
place_matis_island_2:{l:15680, b:-1600,  r:16640, t:-320,   p:'matis_island'},
	// old starter zones
		fyros_newbie:{l:20960, b:-27040, r:23200, t:-25280, p:''},
	   tryker_newbie:{l:20800, b:-34880, r:23200, t:-32960, p:''},
		matis_newbie:{l:320,   b:-7680,  r:2720,  t:-5760,  p:''},
		zorai_newbie:{l:7040,  b:-5600,  r:8960,  t:-4160,  p:''},
	//
		   kitiniere:{l:1760,  b:-17440, r:3040,  t:-16160, p:''}, // cont_kitiniere, 'kitiniere' has 160,-17600 .. 3040,-13120
	// moor does not have ingame zone, API will never give indoor coordinates
	  corrupted_moor:{l:12480, b:-12200, r:15840, t:-9940,  p:''},// FIXME:
			 indoors:{l:20000, b:-640,   r:21280, t:-320,   p:''},
			//testroom:{l:33000, b:-5630,  r:39400, t: -400,  p:'', map:''},// collision with r2_forest
	// note: r2_desert/forest/lakes/jungle all have 1px removed from bottom because of collision
		   r2_desert:{l:20960, b:-10799, r:30959, t:-800,   p:''},// b-1, collision with other systems, make them 1px smaller
		   r2_forest:{l:30960, b:-10799, r:40959, t:-800,   p:''},// b-1
			r2_lakes:{l:20960, b:-20799, r:30959, t:-10800, p:''},// b-1
		   r2_jungle:{l:30960, b:-20799, r:40959, t:-10800, p:''},// b-1
			r2_roots:{l:30960, b:-30800, r:40960, t:-20800, p:''},
			// FIXME: individual r2 zones + maps?

	// map real server space out of our way
				grid: {l:-108000, t:0, r: 0, b:-47520}
	},

	/**
	 * Initialize Ryzom.XY
	 */
	initialize: function(){
		var prop, inzone, outzone;

		for(prop in this.ingame_coordinates){
			inzone  = this.ingame_coordinates[prop];
			inzone.w = inzone.r- inzone.l;
			inzone.h = inzone.t- inzone.b;
			// used for sorting
			inzone.area = inzone.w * inzone.h;
			// bounds is used for Bounds::contain()
			inzone.bounds = new OpenLayers.Bounds(inzone.l, inzone.b, inzone.r, inzone.t);
			// segment is used for distance calculations
			inzone.segments = [
				{x1: inzone.l, y1: inzone.t, x2: inzone.l, y2: inzone.b},
				{x1: inzone.l, y1: inzone.b, x2: inzone.r, y2: inzone.b},
				{x1: inzone.r, y1: inzone.b, x2: inzone.r, y2: inzone.t},
				{x1: inzone.r, y1: inzone.t, x2: inzone.l, y2: inzone.t}
			];
		}

		for(prop in this.outgame_coordinates){
			outzone = this.outgame_coordinates[prop];

			// only calculate right/bottom when they are missing
			// each defined outgame zone must have ingame match
			if(outzone.r === undefined) outzone.r = outzone.l + this.ingame_coordinates[prop].w;
			if(outzone.b === undefined) outzone.b = outzone.t - this.ingame_coordinates[prop].h;

			outzone.w = outzone.r - outzone.l;
			outzone.h = Math.abs(outzone.b - outzone.t);
			outzone.area = outzone.w * outzone.h;
			outzone.bounds = new OpenLayers.Bounds(outzone.l, outzone.b, outzone.r, outzone.t);
			outzone.segments = [
				{x1: outzone.l, y1: outzone.t, x2: outzone.l, y2: outzone.b},
				{x1: outzone.l, y1: outzone.b, x2: outzone.r, y2: outzone.b},
				{x1: outzone.r, y1: outzone.b, x2: outzone.r, y2: outzone.t},
				{x1: outzone.r, y1: outzone.t, x2: outzone.l, y2: outzone.t}
			];
		}
	},

	/**
	 * return all zone names where x/y belongs to
	 * example: [place_fairhaven, tryker, grid]
	 */
	find_zones : function(x, y, zones){
		function compare(i, j){
			var a = zones[i].area, b = zones[j].area;
			if(a < b) return -1;
			if(a > b) return  1;
			return 0;
		}

		var matches = [];
		for(var prop in zones){
			if(zones[prop].bounds.contains(x, y)){
				matches.push(prop);
			}
		}
		matches.sort(compare);

		if(matches.length == 0 || matches[matches.length-1] != 'grid'){
			matches.push('grid');
		}

		if(Ryzom.DEBUG) console.debug('(find_zones) return', matches, ', input was ', [x, y, zones]);
		return matches;
	},

	/**
	 * Try to find closest from server zones
	 *
	 * @param float x
	 * @param float y
	 *
	 * @return object {name: zone, dist: distance}
	 */
	find_closest: function(x, y, zones){
		if(Ryzom.DEBUG) console.debug('(find_closest) enter', [x, y]);

		var closest = {
			name: [],
			distance: Infinity,
			x: null,
			y: null
		};
		var point = {x:x, y:y};
		for(var prop in zones){
			var zone = zones[prop];
			// skip inner zones
			if(zones.hasOwnProperty(prop) !== true || (zone.p != undefined && zone.p != '')){
				continue;
			}
			for(var i=0, len=zone.segments.length; i<len || closest.distance == 0; i++){
				var dist = OpenLayers.Geometry.distanceToSegment(point, zone.segments[i]);
				if(closest.distance < dist.distance){
					continue;
				}
				if(Ryzom.DEBUG) console.debug('>> new match', [prop, dist], 'replacing', closest);
				closest.name = prop;
				closest.distance = dist.distance;
				closest.x = dist.x;
				closest.y = dist.y;
			}
		}//for

		if(Ryzom.DEBUG) console.debug('(find_closest) return', closest);
		return closest;
	},


	/**
	 * Searches in which region(s) X,Y belongs to
	 *
	 * @param x
	 * @param y
	 * @param zones array of zones to test
	 *
	 * @return array of regions sorted from smallest first (town, zone, grid)
	 */
	_belongsTo : function(x, y, zones){
		if(Ryzom.DEBUG) console.debug('(_belongsTo) enter', [x, y, zones]);

		// always matches grid
		var matches = this.find_zones(x, y, zones);

		// if no match or match is against grid, then check how close we are to defined zone
		// some prime root portal/spawn are little outside the zone
		if(matches.length == 0 || matches[0] == 'grid'){
			if(Ryzom.DEBUG) console.debug('>> zone match not found, try to find closest zone');
			var closest = this.find_closest(x, y, zones);
			// FIXME: check US/Icor ingame coords for max distance to use
			if(matches.length == 0 || closest.distance < 150){
				matches = [closest.name];
			}
		}

		if(Ryzom.DEBUG) console.debug('(_belongsTo) return', [x, y], 'matches', matches);
		return matches;
	},

	/**
	 * Convert X/Y from one list of zones to another list of zones
	 *
	 * @param x   coordinate in src system
	 * @param y   coordinate in src system
	 * @param src array of zones to map from
	 * @param dst array of zones to map into
	 *
	 * @return {x: x, y:y, regions: regions}
	 *			  x coordinate in dst system
	 *			  y coordinate in dst system
	 *			  dst regions x/y falls into
	 */
	fromToZone : function(x, y, src, dst){
		if(Ryzom.DEBUG) console.debug('(fromToZone)', [x, y, src, dst]);

		var srczone, dstzone, zonename;

		// where is this point located
		var regions = this._belongsTo(x, y, src);

		// which of those we have mapped in destination
		for(var i=0; i<regions.length; i++){
			zonename = regions[i];
			if(dst[zonename] !== undefined){
				if(Ryzom.DEBUG) console.debug('>> src and dst both have', [zonename]);
				srczone = src[zonename];
				dstzone = dst[zonename];
				break;
			}
		}

		// % coordinates inside source zone
		var px = (x - srczone.l) / srczone.w;
		var py = Math.abs(y - srczone.t) / srczone.h;
		if(Ryzom.DEBUG) console.debug('>> source', [x, y], [px.toFixed(3), py.toFixed(3)], [zonename]);

		// new xy coordinates in destination
		x = (dstzone.l + dstzone.w*px);
		y = (dstzone.t - dstzone.h*py);

		if(Ryzom.DEBUG) console.debug('(fromToZone) return', [x, y]);
		return {x: x, y:y};
	},

	/**
	 * Searches ingame regions where x/y belongs to
	 * Finds all zones that match, sorts them by size and returns smallest (town, zone, grid)
	 *
	 * @see _belongsTo()
	 */
	belongsToIngame : function(x, y){
		return this._belongsTo(x, y, this.ingame_coordinates);
	},

	/**
	 * Searches outgame regions where x/y belongs to
	 * Finds all zones that match, sorts them by size and returns smallest (town, zone, grid)
	 *
	 * @see _belongsTo()
	 */
	belongsToOutgame : function(x, y){
		return this._belongsTo(x, y, this.outgame_coordinates);
	},

	/*
	 * Maps ingame coordinates to pixel coordinates
	 *
	 * @param x
	 * @param y
	 *
	 * @return {x:x, y:y, regions: regions}
	 */
	fromIngameToOutgame : function(x, y){
		var result = this.fromToZone(x, y, this.ingame_coordinates, this.outgame_coordinates);

		result.regions = this._belongsTo(x, y, this.ingame_coordinates);
		return result;
	},

	/**
	 * Converts Outgame coordinates to ingame ones
	 *
	 * @param x  world coordinate
	 * @param y  world coordinate
	 *
	 * @return {x:x, y:y, regions: regions}
	 */
	fromOutgameToIngame : function(x, y){
		var result = this.fromToZone(x, y, this.outgame_coordinates, this.ingame_coordinates);

		result.regions = this._belongsTo(result.x, result.y, this.ingame_coordinates);
		return result;
	},

	/**
	 * translates outgame(world) coordinates to ingame(server) coordinates
	 *
	 * callback for OpenLayers.Projection.addTransform
	 */
	toServer: function(px, y){
		if(px.x === undefined){
			px = {x:px, y:y};
		}
		var xy = Ryzom.XY.fromOutgameToIngame(px.x, px.y);
		px.x = xy.x;
		px.y = xy.y;

		return px;
	},

	/**
	 * translates ingame(server) coordinates to outgame(world) coordinates
	 *
	 * callback for OpenLayers.Projection.addTransform
	 */
	toWorld: function(px, y){
		if(px.x === undefined){
			px = {x:px, y:y};
		}
		var xy = Ryzom.XY.fromIngameToOutgame(px.x, px.y);
		px.x = xy.x;
		px.y = xy.y;

		return px;
	},

	CLASS_NAME: 'Ryzom.XY'
};

/**
 * Initialize ryzom coords
 */
(function(){
	Ryzom.XY.initialize();

	//
	OpenLayers.Projection.addTransform('RZ:WORLD', 'RZ:SERVER', Ryzom.XY.toServer);
	OpenLayers.Projection.addTransform('RZ:SERVER', 'RZ:WORLD', Ryzom.XY.toWorld);
})();
