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

// creates one instance of Tooltip. Show/Hide is done with closure
google.maps.Marker.prototype.setTooltip = function(html, opt){
	if(this.tooltip_ === undefined){
		this.tooltip_ = new Tooltip();
		this.tooltip_.setMap(map);
		this.tooltip_.setPosition(this.getPosition());
		this.tooltip_.html_ = html;
		
		this.bindTo('position', this.tooltip_, 'position');
		this.bindTo('map',      this.tooltip_, 'map');
		
		this.tooltip_.setOptions(opt);
		//
		google.maps.event.addListener(this, 'mouseover', function(){
			this.tooltip_.show();
		});
		google.maps.event.addListener(this, 'mouseout', function(){
			this.tooltip_.hide();
		});
	}else{
		this.tooltip_.setContent(html);
	}
};
google.maps.Marker.prototype.getTooltip = function(){
	return this.tooltip_.getContent();
};


// FIXME: rewrite to take advantage ov MVCObject
function Tooltip(opt_options){
	this.setOptions(opt_options);
	this.div_ = undefined;
	
	// default options for tooltip.
	this.opt_ = {
		className  : 'tooltip',
		offset     : {x:0, y:12}
	};
}

Tooltip.prototype = new google.maps.OverlayView();

// called by API, only once
Tooltip.prototype.onAdd = function(){
	this.div_ = document.createElement('div');
	this.div_.className = this.opt_.className || '';
	this.div_.style.position = 'absolute';
	this.div_.style.display = 'none';
	this.div_.innerHTML = this.html_;
	
	var panes = this.getPanes();
	var paneID= 'floatPane';
	panes[paneID].appendChild(this.div_);
}
//called by API
Tooltip.prototype.onRemove = function(){
	this.div_.parentNode.removeChild(this.div_);
	this.div_ = undefined;
};
// called by API
Tooltip.prototype.draw = function(){
	if(!this.div_){
		return;
	}
	var xy = this.getProjection().fromLatLngToDivPixel(this.pos_);
	if(xy){
		this.div_.style.left = (xy.x+this.opt_.offset.x)+'px';
		this.div_.style.top  = (xy.y+this.opt_.offset.y)+'px';
	}
}

Tooltip.prototype.setOptions= function(opt){
	opt = opt || {};
	this.opt_ = this.opt_ || {};
	for(var i in opt){
		this.opt_[i] = opt[i];
	}
}
// called when Marker position changes (drag)
Tooltip.prototype.setPosition=function(latLng){
	this.pos_ = latLng;
	this.draw();
};
Tooltip.prototype.getPosition=function(){
	return this.pos_;
};

Tooltip.prototype.show = function(){
	if(this.div_ && this.html_ != ''){
		this.div_.style.display='';
	}
};

Tooltip.prototype.hide = function(){
	if(this.div_){
		this.div_.style.display='none';
	}
};

Tooltip.prototype.setContent = function(html){
	this.html_ = html;
	if(this.div_){
		this.div_.innerHTML = html;
	}
};
Tooltip.prototype.getContent = function(){
	return this.html_;
};

Tooltip.prototype.isVisible = function(){
	var mapBounds = this.getMap().getBounds();
	return mapBounds.contains(this.pos_);
};

Tooltip.prototype.setCenter = function(){
	if(this.isVisible()){
		this.getMap().panTo(this.pos_);
	}else{
		this.getMap().setCenter(this.pos_);
	}
};
