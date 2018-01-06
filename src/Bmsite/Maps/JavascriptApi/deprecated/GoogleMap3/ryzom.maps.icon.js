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
google.maps.Marker.prototype.setColor=function(color){
	if(this.icon_ !== undefined && this.opt_.color == color){
		return this;
	}

	this.opt_.color= color;
	var icon = new google.maps.MarkerImage(
		ryzom.maps.icon.getUrlFor(this.opt_.name, this.opt_.color),                                 // url
		new google.maps.Size(this.opt_.size.w, this.opt_.size.h),     // size
		new google.maps.Point(0, 0),                      // origin
		new google.maps.Point(this.opt_.size.w/2, this.opt_.size.h/2) // anchor
	);
	
	var over = new google.maps.MarkerImage(
		ryzom.maps.icon.getOverUrlFor(this.opt_.name, this.opt_.color),
		new google.maps.Size(this.opt_.size.w, this.opt_.size.h),
		new google.maps.Point(0, 0),
		new google.maps.Point(this.opt_.size.w/2, this.opt_.size.h/2)
	);
	
	this.icon_ = icon;
	this.over_ = over;
	
	this.setIcon(this.icon_);
	this.setOptions({
		shape: {
			coord: this.opt_.coord
		}
	});
	return this;
};
/**
 * Return current color
 * 
 * @return string | bool false if not using ryzom icon
 */
google.maps.Marker.prototype.getColor=function(){
	if(this.opt_){
		return this.opt_.color;
	}
	return false;
};
/**
 * Change marker icon to one of Ryzom icons.
 * 
 * @param name  - icon template
 * @param color - icon color
 * @return false if unsuccessful
 */
google.maps.Marker.prototype.setRyzomIcon=function(name, color){
	var opts = ryzom.maps.icon.getOptionsByName(name);
	if(opts === false){
		return false;
	}
	// this should be before we change opt_ which holds current color
	color = color === undefined ? this.getColor() : color;
	
	this.opt_ = opts;
	this.icon_ = undefined; // this forces new icons
	this.setColor(color);
	return true;
};
/**
 * Get current icon name
 * 
 * @return string | bool false if not using ryzom icon
 */
google.maps.Marker.prototype.getRyzomIcon=function(){
	if(this.opt_){
		return this.opt_.name;
	}
	console.debug('opt_ undefined', this);
	return false;
};

/**
 * Collection of ryzom icons
 * 
 * FIXME: resize icons to one constant size
 */
ryzom.maps.icon = new (function(){
	function createMarker(map, xy, opt, color, marker_opt){
		var options = marker_opt || {};
		options.position=map.fromIngame(xy.x, xy.y);
		options.map=map;
		options.shape={coord: opt.coord, type: 'poly'};
		
		var marker = new google.maps.Marker(options);
		opt.color = color;
		marker.opt_   = opt;
		marker.setColor(color);
		
		google.maps.event.addListener(marker, 'mouseover', function(){
			this.setIcon(this.over_);
		});
		google.maps.event.addListener(marker, 'mouseout', function(){
			this.setIcon(this.icon_);
		});
		return marker;
	};

	var options = {
		marker      : {name: 'lm_marker',   size:{w:24,h:24}, coord:[23,15,19,23,18,23,6,23,1,15,5,0,19,0]},
		op_townhall : {name: 'op_townhall', size:{w:40,h:40}, coord:[0,29,12,39,28,40,40,29,28,1,8,1]},
		building    : {name: 'building',    size:{w:32,h:32}, coord:[1,11,21,2,31,13,31,24,18,30,3,26]},
		mektoub     : {name: 'mektoub',     size:{w:24,h:24}, coord:[0,16,7,23,18,24,24,16,20,0,3,0]},
		spawn       : {name: 'spawn',       size:{w:24,h:24}, coord:[0,15,5,24,17,24,24,15,19,0,5,0]},
		portal      : {name: 'portal',      size:{w: 9,h: 9}, coord:[0,9,9,9,9,0,0,0]},
		kami_tp     : {name: 'tp_kami',     size:{w:24,h:24}, coord:[3,0,2,12,6,22,17,22,21,13,21,0]},
		karavan_tp  : {name: 'tp_karavan',  size:{w:24,h:28}, coord:[0,19,7,28,15,28,24,19,24,10,12,0,0,0]},
		tribe       : {name: 'camp',        size:{w:24,h:24}, coord:[0,16,5,22,19,22,24,16,18,0,5,0,0,15]},
		npc         : {name: 'npc',         size:{w:19,h:22}, coord:[0,16,5,22,14,22,19,16,13,0,5,0,0,16]},
		resource    : {name: 'dig',         size:{w:24,h:24}, coord:[3,23,11,23,24,18,24,9,15,0,2,0]},
		egg         : {name: 'egg',         size:{w:24,h:24}, coord:[0,24,24,24,24,0,0,0]},
		question    : {name: 'question',    size:{w:24,h:24}, coord:[0,24,24,24,24,0,0,0]}
	};

	return {
		MARKER      : function(map, xy, opts){ return createMarker(map, xy, options.marker, 'ef4e4e', opts); },
		MISSION     : function(map, xy, opts){ return createMarker(map, xy, options.marker, '1b9acf', opts); },
		OUTPOST     : function(map, xy, opts){ return createMarker(map, xy, options.marker, '1bcf34', opts); },
		//
		OP_TOWNHALL : function(map, xy, opts){ return createMarker(map, xy, options.op_townhall,'', opts); },
		BUILDING    : function(map, xy, opts){ return createMarker(map, xy, options.building,   '', opts); },
		//
		MEKTOUB     : function(map, xy, opts){ return createMarker(map, xy, options.mektoub,    '', opts); },
		SPAWN       : function(map, xy, opts){ return createMarker(map, xy, options.spawn,      '', opts); },
		PORTAL      : function(map, xy, opts){ return createMarker(map, xy, options.portal,     'ef4e4e', opts); },
		KAMI_TP     : function(map, xy, opts){ return createMarker(map, xy, options.kami_tp,    'ffff00', opts); },
		KARAVAN_TP  : function(map, xy, opts){ return createMarker(map, xy, options.karavan_tp, '00ffff', opts); },
		//
		NPC         : function(map, xy, opts){ return createMarker(map, xy, options.npc,        'ffffff', opts); },
		NPC_VENDOR  : function(map, xy, opts){ return createMarker(map, xy, options.npc,        '005bff', opts); },
		NPC_MISSION : function(map, xy, opts){ return createMarker(map, xy, options.npc,        '7efc00', opts); },
		NPC_TRAINER : function(map, xy, opts){ return createMarker(map, xy, options.npc,        'febe00', opts); },
		//
		DIG_RESOURCE: function(map, xy, opts){ return createMarker(map, xy, options.resource,   'ffffff', opts); },
		DIG_MISSION : function(map, xy, opts){ return createMarker(map, xy, options.resource,   '9f9f9f', opts); },
		//
		EGG         : function(map, xy, opts){ return createMarker(map, xy, options.resource,   '',       opts); },
		QUESTION    : function(map, xy, opts){ return createMarker(map, xy, options.marker,     '',       opts); },
		//
		getUrlFor: function url(name, color){
			return MAPS_HOST+'api/icons.php?icon='+name+'&color='+color;
		},
		getOverUrlFor: function url(name, color){
			return MAPS_HOST+'api/icons.php?icon='+name+'_over&color='+color;
		},
		//
		getOptionsByName : function(name){
			for(var i in options){
				if(options[i].name == name){
					return options[i];
				}
			}
			return false;
		},
		getIconNames : function(){
			var names = [];
			for(var i in options){
				names.push(options[i]);
			}
			return names;
		}
	};
})();

