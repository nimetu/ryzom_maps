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

/*
 * X/Y remapping
 *                              world map size
 * @zoom level  1 -- 1px ~ 512m (   27x27   )
 * @zoom level  2 -- 1px ~ 256m (   55x55   )
 * @zoom level  3 -- 1px ~ 128m (  110x110  )
 * @zoom level  4 -- 1px ~  64m (  221x221  )
 * @zoom level  5 -- 1px ~  32m (  442x442  )
 * @zoom level  6 -- 1px ~  16m (  885x885  )
 * @zoom level  7 -- 1px =   8m ( 1770x1770 )
 * @zoom level  8 -- 1px =   4m ( 3540x3540 )
 * @zoom level  9 -- 1px =   2m ( 7080x7080 ) <-- base zoom
 * @zoom level 10 -- 1px =   1m (14160x14160)
 * @zoom level 11 -- 1px = 0.5m (28320x28320)
 *
 * this needs to be in sync with tile creator data
 */

"use strict";

window.ryzom = ryzom || {};
ryzom.maps = ryzom.maps || {};

ryzom.maps.BASE_ZOOM = 9;
ryzom.maps.outgame_coordinates = {
	//world         : {l:0, t:0, r:7080, b:7080},
	//
	fyros         : {l:1460, t:318,  w:2240, h:1600},
	matis         : {l:3880, t:176,  w:2960, h:3760},
	tryker        : {l:3858, t:4112, w:3120, h:2720},
	zorai         : {l:  94, t:4442, w:2800, h:2480},
	nexus         : {l:4098, t:2828, w: 960, h:1120},
	bagne         : {l:4196, t:1870, w: 560, h: 800},
	route_gouffre : {l:2896, t:2072, w: 960, h:3680},
	terre         : {l:1186, t:2480, w:1440, h:1360},
	sources       : {l:346,  t:3500, w: 640, h: 800},
	//
	newbieland    : {l:   0, t:8000, w:1600, h:1040},
	kitiniere     : {l:2000, t:8000, w: 640, h: 640},
	corrupted_moor: {l:4000, t:8000, w:1680, h:1130},
	//
	fyros_newbie  : {l:8000, t:   0, w:1120, h: 880},
	matis_newbie  : {l:8000, t:2000, w:1200, h: 960},
	tryker_newbie : {l:8000, t:4000, w:1200, h: 960},
	zorai_newbie  : {l:8000, t:6000, w: 960, h: 720},
	//
	fyros_island  : {l:11000,t:   0, w:2400,  h:560},
	matis_island  : {l:11000,t:2000, w:2160,  h:640},
	tryker_island : {l:11000,t:4000, w:3040,  h:560},
	zorai_island  : {l:11000,t:6000, w:2400,  h:640},
	//
	almati        : {l:11000,t: 2000,w:  640,h:  640},
	olkern        : {l:12840,t: 4080,w:  480,h:  480},
	aelius        : {l:12840,t:    0,w:  560,h:  320},
	//
	testroom      : {l:20000,t:    0,w:3200, h: 2615},
	indoors       : {l:    0,t:10000,w: 640, h:  160},
	//
	r2_desert     : {l:20000,t:    0,w: 4999,h: 5000},
	r2_jungle     : {l:26000,t:    0,w: 4999,h: 5000},
	r2_lakes      : {l:20000,t: 6000,w: 4999,h: 5000},
	r2_forest     : {l:26000,t: 6000,w: 4999,h: 5000},
	r2_roots      : {l:32000,t:    0,w: 5000,h: 5000},

	//mask 'last comma' and also catch coords from full default zoom level grid size
	grid:{l: 0, t:0, w: 131072, h:131072}
};

ryzom.maps.ingame_coordinates = {
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
	// silan
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
        fyros_island:{l:21120, b:-24960, r:25920, t:-23840, p:''},
       tryker_island:{l:21120, b:-30560, r:27200, t:-29440, p:''},
        matis_island:{l:14080, b:-1600,  r:18400, t:-320,   p:''},
        zorai_island:{l:13920, b:-4800,  r:18720, t:-3520,  p:''},
	// episode 2 areas
              almati:{l:14080, b:-1600,  r:15360, t:-320,   p:'matis_island'},   // Almati
              olkern:{l:24800, b:-30560, r:25760, t:-29600, p:'tryker_island'}, // Olkern
              aelius:{l:24800, b:-24480, r:25920, t:-23840, p:'fyros_island'}, // Aelius
	// old starter zones
        fyros_newbie:{l:20960, b:-27040, r:23200, t:-25280, p:''},
       tryker_newbie:{l:20800, b:-34880, r:23200, t:-32960, p:''},
        matis_newbie:{l:320,   b:-7680,  r:2720,  t:-5760,  p:''},
        zorai_newbie:{l:7040,  b:-5600,  r:8960,  t:-4160,  p:''},
	//
      corrupted_moor:{l:12480, b:-12200, r:15840, t:-9940,  p:''},
           kitiniere:{l:1760,  b:-17440, r:3040,  t:-16160, p:''}, // cont_kitiniere, 'kitiniere' has 160,-17600 .. 3040,-13120
	//
			 indoors:{l:20000, b:-640,   r:21280, t:-320,   p:''},
			testroom:{l:33000, b:-5630,  r:39400, t: -400,  p:''},
			// note: r2 t/b is reversed
			r2_roots:{l:30960, t:-20800, r:40960, b:-30800, p:''},
		   r2_desert:{l:20960, t:-800,   r:30959, b:-10800, p:''},
		    r2_lakes:{l:20960, t:-10800, r:30959, b:-20800, p:''},
		   r2_forest:{l:30960, t:-800,   r:40959, b:-10800, p:''},
		   r2_jungle:{l:30960, t:-10800, r:40959, b:-20800, p:''},
	// mask 'last comma'
    grid: {l:-262144, b:-262144, r:0, t:0}
};

/**
 * Searches in which region(s) X,Y belongs to
 * @return array of regions that match, first will always be smallest match (first is pyr, second is fyros for example)
 */
ryzom.maps.belongsToIngame=function(x, y){
	var me=this;
	function compare(i, j){
		var a=me.ingame_coordinates[i], b=me.ingame_coordinates[j];
		var aw=a.r-a.l, ah=a.t-a.b;
		var bw=b.r-b.l, bh=b.t-b.b;
		if(aw<bw && ah<bh) return -1;
		else if(aw>bw || ah>bh) return 1;
		else return 0;
	}
	var matches=[];
	for(var i in this.ingame_coordinates){
		var z=this.ingame_coordinates[i];
		if(x>=z.l && x<=z.r && y>=z.b && y<=z.t) { // (y>z.b && y<z.y) is CORRECT
			matches.push(i);
		}
	}
	// sort by zone size
	matches.sort(compare);
	return matches;
};

/**
 * Searches outgame regions where x/y belongs to
 * Finds all zones that match, sorts them by size and returns smallest (Matis covers AoI/Nexus)
 *
 * @return outgame region name
 */
ryzom.maps.belongsToOutgame=function(x, y){
	var me=this;
	function compare(i, j){
		var a=me.outgame_coordinates[i], b=me.outgame_coordinates[j];
		if(a.w<b.w && a.h<b.h) return -1;
		else if(a.w>b.w || a.h>b.h) return 1;
		else return 0;
	}
	var matches=[];
	for(var i in this.outgame_coordinates){
		var z=this.outgame_coordinates[i];
		// skip those thos l/t is 0
		//if(z.l==0 &&z.t==0) continue;
		var r=z.l+z.w;
		var b=z.t+z.h;
		if(x>=z.l && x<=r && y>=z.t && y<=b){
			matches.push(i);
		}
	}
	matches.sort(compare);
	if(matches.length>0){
		// first is smallest
		return matches[0];
	}else{
		return 'grid'; // should reach here anyway
	}
};

/**
 * Converts Outgame coordinates to ingame ones
 * TODO: dive into towns and other inner regions / zones
 *
 * @param z  ingame zone
 * @param x  pixel coordinate
 * @param y  pixel coordinate
 */
ryzom.maps.fromOutgameToIngame = function(x, y, ig_region){
	if(ig_region==undefined){
		// if ig_region is not set, then try to find out where the x/y belongs to
		ig_region=this.belongsToOutgame(x, y);
	}
	var zone = this.outgame_coordinates[ig_region];
	if( zone === undefined ){
		return {x:0, y:0, regions:[]};
	}

	// if zone is world level 'cont_*', then move it to zone level, modify x and y accordingly
	if(ig_region.match(/^cont_.*/)){
		// world level zone area might be different size so need to do careful x/y conversion
		// zone's continent coordinates
		var cont=this.outgame_coordinates[ig_region];
		if(typeof cont.w == 'undefined'){
			// fill in the blanks
			cont.w=cont.r-cont.l;
			cont.h=cont.b-cont.t;
		}
		var cont_px=(x-cont.l)/cont.w;
		var cont_py=(y-cont.t)/cont.h;

		// convert cont_* to zone level
		ig_region = ig_region.replace(/^cont_/, '');

		// take new zone now
		zone = this.outgame_coordinates[ig_region];
		if( zone === undefined ){
			// zone not found
			return {x:0, y:0, regions:[]};
		}
		
		// .. and new x/y is...
		x=zone.l+cont_px*zone.w;
		y=zone.t+cont_py*zone.h;
	}
	// fill in the blanks
	if(typeof zone.w == 'undefined'){
		// fill in the blanks
		zone.w=zone.r-zone.l;
		zone.h=zone.b-zone.t;
	}

	// outgame zone - x/y in 0x0 to WxH box
	var px=(x-zone.l)/zone.w;
	var py=(y-zone.t)/zone.h;
	
	// ingame zone
	var ig = this.ingame_coordinates[ig_region];
	if( ig === undefined){
		// fall back to full grid space
		ig_region='grid';
		// ingame zone not found, map to 'grid'
		ig=this.ingame_coordinates[ig_region];
		//return {x:0,y:0,regions:[]};
	}

	var ig_w=ig.r-ig.l;
	var ig_h=Math.abs(ig.b-ig.t);
	var new_x=(ig.l+px*ig_w);
	var new_y=(ig.t-py*ig_h); // FIXME: verify this

	// get ig regions where this spot really belongs to
	var igr=this.belongsToIngame(new_x, new_y);
	return { x: new_x, y: new_y, regions: igr };
};

/*
 * Maps ingame coordinates to pixel coordinates
 *
 * @param x
 * @param y
 *
 * @return object with {name, x, y} parameters where name is map name and x/y map coords
 *           
 */
ryzom.maps.fromIngameToOutgame = function(x, y){
	var zones = this.belongsToIngame(x, y);
	
	// no match ??
	if(zones.length==0){
		// try to find closest zone and attach the point
		var prev_dx=Infinity;
		var prev_dy=Infinity;
		var closest='';
		for(var i in this.ingame_coordinates){
			var z=this.ingame_coordinates[i];
			// distance for 4 sides
			var dxl=Math.abs(z.l-x);
			var dyb=Math.abs(z.b-y);
			var dxr=Math.abs(z.r-x);
			var dyt=Math.abs(z.t-y);

			var dx, dy;
			if(dxl<dxr) dx=dxl; else dx=dxr;
			if(dyt<dyb) dy=dyt; else dy=dyb;

			if(dx<prev_dx && dy<prev_dy){
				closest=i;
				prev_dx=dx;
				prev_dy=dy;
			}
		}
		// we found something - return this region
		if(closest!=''){
			zones=[closest];
		}else{
			// should never happen. we should always find something...
			return -1;
		}
	}

	var point_id = zones[zones.length-1]; // last from the list
	var oprefix='cont_';

	// find the outgame parent zone. for 'Pyr', parent zone would be 'fyros'
	var point;
	for(var i=0;i<zones.length;i++){
		point_id=zones[i];
		point=this.ingame_coordinates[point_id];
		if(point.p==''){
			break;
		}
	}

	// find outgame zone
    var outgame_zone;
	if(typeof(this.outgame_coordinates[oprefix+point_id]) != 'undefined'){
		outgame_zone = oprefix+point_id;
	}else if(typeof(this.outgame_coordinates[point_id]) != 'undefined'){
		outgame_zone = point_id;
	}else{
		// no outgame zone found
		outgame_zone='grid';
	}
	var opoint = this.outgame_coordinates[outgame_zone];

	// calculate Point's position in % for given zone
	var zw=point.r-point.l;
	var zh=point.t-point.b; // this will work like -1000-(-1200)=200

	var px=(x-point.l)/zw;
	var py=Math.abs(y-point.b)/zh; // use Bottom and not Top

	// new x/y location inside outgame map area
	var R=1;
	var new_x=(opoint.l*R+opoint.w*R*px);
	var new_y=(opoint.t*R+opoint.h*R-opoint.h*R*py);

	return { name: outgame_zone, x: new_x, y: new_y };
};
