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

/*$(function(){
	// cleanup event
	GEvent.addDomListener(window, 'unload', GUnload);
});*/

var MAPS_HOST = 'http://maps.bmsite.net/';
var DEBUG=false;
/**
 * Ryzom ingame <-> pixel coorindate mapper
 * definition below
 */
var RyzomXY = {};

/**
 * Modifies icon URL such that it uses new color
 * @return GIcon
 */
GIcon.prototype.setColor=function(color){
	// if no _name is set, then we dont use RyzomIcon
	if(!this._name) return;
	//var color=r.toString(16)+g.toString(16)+b.toString(16);
	this._color=color;
	this._over=MAPS_HOST+'api/icons.php?icon='+this._name+'_over&color='+color;
	this._original = MAPS_HOST+'api/icons.php?icon='+this._name+'&color='+color;
	this.image=this._original;
	return this;
};

/**
 * Returns own copy
 * @return GIcon
 */
GIcon.prototype.copy=function(){
	var c=new GIcon(this);
	if(this._name){
		c._name=this._name;
		c._over=this._over;
		c._original=this._original;
	}
	return c;
};

/**
 * Swaps marker icon
 * @param boolean state
 */
GMarker.prototype.setOver=function(state){
	// do nothing for hidden marker
	if(this.isHidden()) return;
	var icon=this.getIcon();
	if(state){
		if(icon._over!=undefined){
			this.setImage(icon._over);
		}
	}else{
		if(icon._original!=undefined){
			this.setImage(icon._original);
		}
	}
};

var RyzomIcons = function(){
	var me=this;
	// helper function to get icon full path
	function mkurl(image){
		return MAPS_HOST+'images/icons/'+image;
	};

	/**
	 * Helper to create GIcon instance
	 *
	 * iconAnchor is set to middle of image, 
	 * infoWindowAnchor is set to top-center
	 * dragCrossAnchor is set to 0x0 from iconAnchor.
	 * icon will not rise when it's being dragged
	 */
	this.createIcon=function(name, color, size, shadowSize, imageMap){
		var icon=new GIcon();
		icon._name=name;
		icon.setColor(color); // setColor will also set icon name
		// GIcon properties
		icon.shadow        = mkurl(name+'/shadow.png');
		icon.printImage    = mkurl(name+'/printImage.gif');
		icon.mozPrintImage = mkurl(name+'/mozPrintImage.gif');
		icon.printShadow   = mkurl(name+'/printShadow.gif');
		icon.transparent   = mkurl(name+'/transparent.png');
		icon.iconSize      = size;
		icon.shadowSize    = shadowSize;
		icon.iconAnchor    = new GPoint(size.width/2, size.height/2);
		icon.infoWindowAnchor = new GPoint(size.width/2, 0);
		icon.imageMap = imageMap;
		icon.maxHeight = 0;
		//
		return icon;
	};

	/**
	 * Search icon by it's name and return it
	 * @param icon one of 'lm_marker', 'op_townhall', 'building', etc
	 * @return GIcon instance like RyzomIcons.MARKER, RyzomIcons.OP_TOWNHALL, etc
	 *         FALSE if icon was not found
	 */
	this.find = function(icon){
		for(var a in me){
			if(me[a] instanceof GIcon && me[a]._name==icon){
				return a;
			}
		}
		return false;
	};
	//
	this.MARKER        = createIcon('lm_marker',     'ef4e4e', new GSize(24,24), new GSize(24,24), [23,15,19,23,18,23,6,23,1,15,5,0,19,0]);
	this.MISSION       = this.MARKER.copy().setColor('1b9acf');
	this.OUTPOST       = this.MARKER.copy().setColor('1bcf34');
	//
	this.OP_TOWNHALL   = createIcon('op_townhall',    '', new GSize(40,40), new GSize(60,40), [0,29,12,39,28,40,40,29,28,1,8,1]);
	this.BUILDING      = createIcon('building',       '', new GSize(32,32), new GSize(48,32), [1,11,21,2,31,13,31,24,18,30,3,26]);
	//
	this.MEKTOUB       = createIcon('mektoub',        '', new GSize(24,24), new GSize(36,24), [0,16,7,23,18,24,24,16,20,0,3,0]),
	this.SPAWN         = createIcon('spawn',          '', new GSize(24,24), new GSize(36,24), [0,15,5,24,17,24,24,15,19,0,5,0]);
	this.PORTAL        = createIcon('portal',         '', new GSize( 9, 9), new GSize(36, 9), [0,9,9,9,9,0,0,0]);
	//
	this.KAMI_TP       = createIcon('tp_kami',        '', new GSize(24,24), new GSize(36,24), [3,0,2,12,6,22,17,22,21,13,21,0]);
	this.KARAVAN_TP    = createIcon('tp_karavan',     '', new GSize(24,28), new GSize(38,28), [0,19,7,28,15,28,24,19,24,10,12,0,0,0]);
	//
	this.BANDIT_TRIBE  = createIcon('camp',   'ef4e4e', new GSize(24,24), new GSize(36,24), [0,16,5,22,19,22,24,16,18,0,5,0,0,15]);
	this.KAMI_TRIBE    = this.BANDIT_TRIBE.copy().setColor('ffff00');
	this.KARAVAN_TRIBE = this.BANDIT_TRIBE.copy().setColor('00ffff');
	//
	this.NPC           = createIcon('npc',    'ffffff', new GSize(19,22), new GSize(30,22), [0,16,5,22,14,22,19,16,13,0,5,0,0,16]);
	this.NPC_VENDOR    = this.NPC.copy().setColor('005bff');
	this.NPC_MISSION   = this.NPC_VENDOR.copy().setColor('7efc00');
	this.NPC_TRAINER   = this.NPC_VENDOR.copy().setColor('febe00');
	//
	this.DIG_RESOURCE  = createIcon('dig',    'ffffff', new GSize(24,24), new GSize(36,24), [3,23,11,23,24,18,24,9,15,0,2,0]);
	this.DIG_MISSION   = this.DIG_RESOURCE.copy().setColor('9f9f9f');

	return this;
}();

/**
 * @return GMap2 instance
 */
function RyzomMap(id, defaultZoom, minZoom, maxZoom){
	// our instance for anonymous functions
	var me=this;

	var minZoom = minZoom || 1;
	var maxZoom = maxZoom || 11;
	var defaultZoom = defaultZoom || 5;

	if (!GBrowserIsCompatible()) {
		alert('Sorry. Your browser is not supported by google maps');
		return;
	}

	// default language
	var lang='en';
	me.season='sp';

	var tileserver=MAPS_HOST+'images/';


    //******************************************************************************
	// real world tiles
	var tilelayer = new GTileLayer(new GCopyrightCollection("Ryzom Maps by bmsite.net"), minZoom, maxZoom);
	tilelayer.getCopyright = function(a, b){ return {prefix: "Ryzom maps &copy;", copyrightTexts:["<a href='http://bmsite.net/'>bmsite.net</a>"]}; };
	tilelayer.getTileUrl= function (a,z) {
		return tileserver+'atys/zoom_'+z+'/atys_'+z+'_'+a.x+'x'+a.y+'.jpg';
	};
	tilelayer.isPng = function() { return false; };
	tilelayer.getOpacity = function() { return 1.0;};

    //******************************************************************************
	// ingame 'sattellite' tiles
	var igtiles = new GTileLayer(new GCopyrightCollection("Ryzom IG maps by bmsite.net"), minZoom, maxZoom);
	igtiles.getCopyright = function(a, b){ return {prefix: "Ryzom IG: &copy;", copyrightTexts:["<a href='http://bmsite.net/'>bmsite.net</a>"]}; };
	igtiles.getTileUrl= function (a,z) {
		return tileserver+'atys_'+me.season+'/zoom_'+z+'/atys_'+me.season+'_'+z+'_'+a.x+'x'+a.y+'.jpg';
	};
	igtiles.isPng = function() { return false; };
	igtiles.getOpacity = function() { return 1.0;};
	
    //******************************************************************************
	var projection=new GMercatorProjection(32);

    //******************************************************************************
	var atysmap = new GMapType([tilelayer], projection, "Atys", {
		errorMessage:"No chart data available", 
		minResolution:minZoom, 
		maxResolution:maxZoom,
		textColor:'#555',
		linkColor:'#888'
	});
	
    //******************************************************************************
	var igmap = new GMapType([igtiles], projection, "Satellite", {
		errorMessage:"No chart data available", 
		minResolution:minZoom, 
		maxResolution:maxZoom,
		textColor:'#555',
		linkColor:'#888'
	});
	
    //******************************************************************************
	// GMap instance
	var map = new GMap2(document.getElementById(id), {
		backgroundColor: '#000', 
		mapTypes:[atysmap, igmap]
	});

	map.enableContinuousZoom();
	map.enableScrollWheelZoom();
	map.addControl(new GLargeMapControl(), new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(2,25)));
	map.addControl(new GMapTypeControl());
	map.minZoom=minZoom;
	map.maxZoom=maxZoom;

	// create marker tooltip
	me.tooltip=new RyzomTooltip(new GSize(10, 10));
	map.addOverlay(me.tooltip);

	/**
	 * Event delegation - listen DOM mouseover/out events on main map container
	 */
	GEvent.addDomListener(map.getContainer(), 'mouseover', function(e){
		//draggable marker has __e_
		if(e.target.__marker__ != undefined){
			try{
				e.target.__marker__.setOver(true);
				// show tooltip
				if(e.target.__marker__.tooltip){
					var pos=map.fromLatLngToDivPixel(e.target.__marker__.getLatLng());
					me.tooltip.show(pos.x, pos.y, e.target.__marker__.tooltip);
				}
			}catch(ex){
				// incase there is error
			}
		}
	});
	GEvent.addDomListener(map.getContainer(), 'mouseout', function(e){
		if(e.target.__marker__ != undefined){
			try{
				e.target.__marker__.setOver(false);
				// show tooltip
				me.tooltip.hide();
			}catch(ex){
				// incase there is error
			}
		}
	});
	
	/**
	 * hook up some ryzom methods to GMap2 map instance 
	 */
    // Set map center point using ingame x/y
    // @param ax ingame x
    // @param ay ingame y
    // @param zoom zoom level starting from 1 (automatically adjusted to GMap zoom level
    // @param show_ig set TRUE if satellite view should be activated
    map.igCenter=function(ax, ay, zoom, show_ig){
    	map.setCenter(map.fromIngame(ax, ay), zoom, show_ig ? map.igMapType() : map.worldMapType());
    };
    // Returns GMap map type object for 'atys' view
    map.worldMapType=function(){
    	return atysmap;
    };
    // Returns GMap map type object for 'satellite' view
    map.igMapType=function(){
    	return igmap;
    };
    // returns true if ig tiles are active
    map.isIgMap=function(){
    	return map.getCurrentMapType()==igmap;
    };
    // set IG tile season
	map.setSeason = function(season){
		map.savePosition();
		me.season=season;
		map.returnToSavedPosition();
	};

	//var world=RyzomXY.outgame_coordinates.world;
	//var world_w=world.r-world.l;
	//var world_h=world.b-world.t;

	// default center spot
    map.setCenter(map.fromImage(3540, 3540), defaultZoom, atysmap);

    // Return GMap2 object
    return map;
};

/**
 * Translate Ryzom ingame x/y to LatLng and return GPoint instance
 *
 * @param ax ingame x
 * @param ay ingame y
 * @return GPoint
 */
GMap2.prototype.fromIngame=function(ax, ay){
	var p=RyzomXY.fromIngameToOutgame(ax, ay, 2);
	return this.fromImage(p.x, p.y);
};

/**
 * Convert image pixel coordinates to LatLng
 *
 * @param ax image x
 * @param ay image y
 * @return GPoint
 */
GMap2.prototype.fromImage=function(ax, ay){
	return this.getCurrentMapType().getProjection().fromPixelToLatLng(new GPoint(ax, ay), RyzomXY.BASE_ZOOM);
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
GMap2.prototype.toIngame=function(latLng){
	var p=this.getCurrentMapType().getProjection().fromLatLngToPixel(latLng, RyzomXY.BASE_ZOOM);
	var ig=RyzomXY.fromOutgameToIngame(p.x, p.y);
	// TODO: do polygon search (slow!!) for ig_x/y if we need to know exact location
	return {x:ig.x, y:ig.y, regions: ig.regions};
};

//****************************************************************************

/**
 * Translates current mouse coordinates to ingame x/y/zone and displays them
 */
function RyzomNavLabelControl(){}
RyzomNavLabelControl.prototype=new GControl();

/**
 * Create element where info is displayed and add it to map
 * Automatically called
 */
RyzomNavLabelControl.prototype.initialize=function(map){
	var container = document.createElement('div');
	
	this.setStyle(container);
	map.getContainer().appendChild(container);
	
	var start=new Date().getTime();
	GEvent.addListener(map, 'mousemove', function(latlng){
		// if less than 100ms apart, then skip coords update - html update does take a lot of 'power'
		var now=new Date().getTime();
		if(now-start<100){
			return;
		}
		start=now;
		// do latLng->Pixel->Ryzom ingame conversion
		var ig=map.toIngame(latlng);
		if(ig.regions.length == 0){
			// out of zone, so clear status
			container.innerHTML="-";
		}else{
			container.innerHTML=ig.regions.join(';')+': x='+(ig.x.toFixed(2))+', y='+(ig.y.toFixed(2))
		}
	});
	return container;
};

/**
 * Set the default location
 */
RyzomNavLabelControl.prototype.getDefaultPosition = function(){
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 30));
};

/**
 * Define textbox style
 */
RyzomNavLabelControl.prototype.setStyle=function(div){
	div.style.color = "#0000cc";
	div.style.backgroundColor="white";
	div.style.font="small Arial";
	div.style.border="1px solid black";
	div.style.padding="2px";
	div.style.width="200px";
	div.style.height="1.2em";
	div.style.textAlign="center";
	div.style.cursor="pointer";
	div.style.fontSize="10px";
};

//****************************************************************************

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
 * @zoom level  9 -- 1px =   2m ( 7080x7080 )
 * @zoom level 10 -- 1px =   1m (14160x14160)
 * @zoom level 11 -- 1px = 0.5m (28320x28320)
 *
 * FIXME: create php mapxy to javascript exporter
 *
 * this needs to be in sync with tile creator data
 */
RyzomXY.BASE_ZOOM = 9;
RyzomXY.outgame_coordinates = {
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

RyzomXY.ingame_coordinates = {
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
RyzomXY.belongsToIngame=function(x, y){
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
RyzomXY.belongsToOutgame=function(x, y){
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
RyzomXY.fromOutgameToIngame = function(x, y, ig_region){
	if(ig_region==undefined){
		// if ig_region is not set, then try to find out where the x/y belongs to
		ig_region=this.belongsToOutgame(x, y);
	}
	var zone = this.outgame_coordinates[ig_region];
	if( zone === undefined ){
		return {x:0, y:0, regions:[]};
	}

	if(DEBUG){
		console.debug('fromOutGameToIngame: [', ig_region, ']; x=',x, 'y=',y);
	}

	// if zone is world level 'cont_*', then move it to zone level, modify x and y accordingly
	if(ig_region.match(/^cont_.*/)){
		//console.debug('world level detected [',z,']');
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

		if(DEBUG){
			console.debug('cont = ', cont);
			console.debug('cont_w = ', cont.w, '; cont_h = ', cont.h);
			console.debug('cont_px = ', cont_px, '; cont_py = ', cont_py);
		}
		
		// convert cont_* to zone level
		ig_region = ig_region.replace(/^cont_/, '');
		if(DEBUG){
			console.debug('zone level [',ig_region,']');
		}
		// take new zone now
		zone = this.outgame_coordinates[ig_region];
		if( zone === undefined ){
			// zone not found ? FIXME: break or continue with cont ??
			return {x:0, y:0, regions:[]};
		}
		// .. and new x/y is...
		x=zone.l+cont_px*zone.w;
		y=zone.t+cont_py*zone.h;
		if(DEBUG){
			console.debug('zone = ', zone);
			console.debug('zone_w = ', zone.w, '; zone_h = ', zone.h);
			console.debug('zone_x = ', x, '; zone_y = ', y);
			console.debug(' --- ');
		}
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
	if(DEBUG){
		console.debug('zw = ', zone.w, '; zh = ', zone.h);
		console.debug('px = ', px, '; py = ', py);
	}
	
	// ingame zone
	var ig = this.ingame_coordinates[ig_region];
	if( ig === undefined){
		// fall back to full grid space
		ig_region='grid';
		// ingame zone not found, map to 'grid'
		ig=this.ingame_coordinates[ig_region];
		//return {x:0,y:0,regions:[]};
	}
	if(DEBUG) console.debug('matched ig', ig_region, ig);
	var ig_w=ig.r-ig.l;
	var ig_h=Math.abs(ig.b-ig.t);
	var new_x=(ig.l+px*ig_w);
	var new_y=(ig.t-py*ig_h); // FIXME: verify this

	if(DEBUG){
		console.debug('ig = ', ig_region, ig);
		console.debug('ig_w = ', ig_w, '; ig_h = ', ig_h);
		console.debug('new_x = ', new_x, '; new_y = ', new_y);
	
		console.debug(x, 'x', y, ' translated to outgame ', ig_region, zone);
	}
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
RyzomXY.fromIngameToOutgame = function(x, y){
	var zones = this.belongsToIngame(x, y);
	if(DEBUG){
		console.debug('ingame ', x, y, ' _belongsTo ingame ', zones);
	}
	
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
				if(DEBUG){
					console.debug(i, dx, dy);
				}
			}
		}
		// we found something - return this region
		if(closest!=''){
			if(DEBUG){
				console.debug('Closest zone should be ', closest);
			}
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
			if(DEBUG) console.debug('use ', point_id, point,', break');
			break;
		}
		if(DEBUG) console.debug('ignore ', point_id, point, ', take next one');
	}

	//var point = this.ingame_coordinates[point_id];
	if(DEBUG){
		console.debug('point [',point_id,'] = ', point, '; oprefix=[',oprefix,']');
	}

	// find outgame zone
	if(typeof(this.outgame_coordinates[oprefix+point_id]) != 'undefined'){
		outgame_zone = oprefix+point_id;
	}else if(typeof(this.outgame_coordinates[point_id]) != 'undefined'){
		outgame_zone = point_id;
	}else{
		// no outgame zone found
		if(DEBUG){
			console.debug('outgame zone not found [',oprefix,point_id,']', zones);
			console.debug('fall back to "grid"');
		}
		outgame_zone='grid';
	}
	var opoint = this.outgame_coordinates[outgame_zone];
	if(DEBUG) console.debug('opoint[',outgame_zone,'] = ', opoint);

	// calculate Point's position in % for given zone
	var zw=point.r-point.l;
	var zh=point.t-point.b; // this will work like -1000-(-1200)=200
	if(DEBUG) console.debug('zw = ', zw, '; zh = ', zh);
	var px=(x-point.l)/zw;
	var py=Math.abs(y-point.b)/zh; // use Bottom and not Top
	if(DEBUG) console.debug('px = ', px, '; py = ', py);
	// new x/y location inside outgame map area
	var R=1;
	var new_x=(opoint.l*R+opoint.w*R*px);
	var new_y=(opoint.t*R+opoint.h*R-opoint.h*R*py);
	if(DEBUG) console.debug('zone: ', outgame_zone, '; new_x = ', new_x, '; new_y = ', new_y);
	return { name: outgame_zone, x: new_x, y: new_y };
};

