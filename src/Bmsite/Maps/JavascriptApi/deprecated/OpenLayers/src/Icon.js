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
 * @requires OpenLayers/BaseTypes/Size.js
 * @requires OpenLayers/BaseTypes/Pixel.js
 * @requires OpenLayers/Icon.js
 */

/**
 * Inherits
 *  - <OpenLayers.Icon>
 */
Ryzom.Icon = OpenLayers.Class(OpenLayers.Icon, {

	markers: {
		// FIXME: anchor, resize images to 32x32
		// TODO: svn icons?
		'lm_marker'  : {anchor:[16,16]},
		'op_townhall': {anchor:[16,16]},
		'building'   : {anchor:[16,16]},
		'mektoub'    : {anchor:[16,16]},
		'spawn'      : {anchor:[16,16]},
		'portal'     : {anchor:[16,16]},
		'tp_kami'    : {anchor:[16,16]},
		'tp_karavan' : {anchor:[16,16]},
		'camp'       : {anchor:[16,16]},
		'npc'        : {anchor:[16,16]},
		'dig'        : {anchor:[16,16]}
	},

	icon: null,

	color: null,

	initialize: function(icon, color) {
		// FIXME: construct proper icon
		this.icon = icon;
		this.color = color;

		var size = new OpenLayers.Size(32, 32);
		var offset;
		/*if(this.markers[icon]){
			var m = this.markers[icon];
			offset = new OpenLayers.Pixel(-m.anchor[0], -m.anchor[1]);
		}else*/{
			offset = new OpenLayers.Pixel(-(size.w/2), -(size.h/2));
		}

		OpenLayers.Icon.prototype.initialize.apply(this, [this.getIconUrl(), size, offset, function(newsize){
			console.debug('calculateOffset (', [icon, color, newsize], ')');
			return offset;
		}]);
	},

	updateUrl : function(){
		this.setUrl(this.getIconUrl());
	},

	getIconUrl: function(){
		var url = Ryzom.MAPS_HOST + 'api/icons.php?resize=true&icon=' + this.icon;
		if(this.color){
			url = url + '&color=' + this.color.replace(/^#/, '0x');
		}
		return url;
	},

	setColor : function(color){
		this.color = color;

		this.updateUrl();
	},

	CLASS_NAME: 'Ryzom.Icon'
});

/**
 * [code]
 *	var icon1 = new Ryzom.Icon.MARKER();
 *	var icon2 = new Ryzom.Icon.MARKER('#FF0000');
 * [/code]
 */
Ryzom.Icon.MARKER        = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#ef4e4e';
		Ryzom.Icon.prototype.initialize.apply(this, ['lm_marker', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.MARKER'
});
Ryzom.Icon.MISSION       = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#1b9acf';
		Ryzom.Icon.prototype.initialize.apply(this, ['lm_marker', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.MISSION'
});
Ryzom.Icon.OUTPOST       = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#1bcf34';
		Ryzom.Icon.prototype.initialize.apply(this, ['lm_marker', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.OUTPOST'
});
//
Ryzom.Icon.OP_TOWNHALL   = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		Ryzom.Icon.prototype.initialize.apply(this, ['op_townhall', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.OP_TOWNHALL'
});
Ryzom.Icon.BUILDING      = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		Ryzom.Icon.prototype.initialize.apply(this, ['building', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.BUILDING'
});
//
Ryzom.Icon.MEKTOUB       = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		Ryzom.Icon.prototype.initialize.apply(this, ['mektoub', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.MEKTOUB'
});
Ryzom.Icon.SPAWN         = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		Ryzom.Icon.prototype.initialize.apply(this, ['spawn', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.SPAWN'
});
Ryzom.Icon.PORTAL        = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		Ryzom.Icon.prototype.initialize.apply(this, ['portal', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.PORTAL'
});
//
Ryzom.Icon.KAMI_TP       = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		Ryzom.Icon.prototype.initialize.apply(this, ['tp_kami', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.KAMI_TP'
});
Ryzom.Icon.KARAVAN_TP    = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		Ryzom.Icon.prototype.initialize.apply(this, ['tp_karavan', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.KARAVAN_TP'
});
//
Ryzom.Icon.BANDIT_TRIBE  = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#ef4e4e';
		Ryzom.Icon.prototype.initialize.apply(this, ['camp', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.BANDIT_TRIBE'
});
Ryzom.Icon.KAMI_TRIBE    = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#ffff00';
		Ryzom.Icon.prototype.initialize.apply(this, ['camp', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.KAMI_TRIBE'
});
Ryzom.Icon.KARAVAN_TRIBE = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#00ffff';
		Ryzom.Icon.prototype.initialize.apply(this, ['camp', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.KARAVAN_TRIBE'
});
//
Ryzom.Icon.NPC           = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#ffffff';
		Ryzom.Icon.prototype.initialize.apply(this, ['npc', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.NPC'
});
Ryzom.Icon.NPC_VENDOR    = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#005bff';
		Ryzom.Icon.prototype.initialize.apply(this, ['npc', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.NPC_VENDOR'
});
Ryzom.Icon.NPC_MISSION   = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#7efc00';
		Ryzom.Icon.prototype.initialize.apply(this, ['npc', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.NPC_MISSION'
});
Ryzom.Icon.NPC_TRAINER   = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#febe00';
		Ryzom.Icon.prototype.initialize.apply(this, ['npc', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.NPC_TRAINER'
});
//
Ryzom.Icon.DIG_RESOURCE  = OpenLayers.Class(Ryzom.Icon, {
	initialize: function(color){
		color = color || '#ffffff';
		Ryzom.Icon.prototype.initialize.apply(this, ['dig', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.DIG_RESOURCE'
});
Ryzom.Icon.DIG_MISSION   = OpenLayers.Class(Ryzom.Icon, {
	initialize:function(color){
		color = color || '#9f9f9f';
		Ryzom.Icon.prototype.initialize.apply(this, ['dig', color]);
	},
	CLASS_NAME: 'Ryzom.Icon.DIG_MISSION'
});

