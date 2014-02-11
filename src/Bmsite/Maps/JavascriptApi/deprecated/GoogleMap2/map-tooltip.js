//
// Ryzom Maps
// Copyright (c) 2009 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of Ryzom Maps at http://maps.bmsite.net
//

function RyzomTooltip(padding){
	this.x=0;
	this.y=0;
	this.padding_=padding;
}
RyzomTooltip.prototype= new GOverlay();

RyzomTooltip.prototype.initialize=function(map){
	var div=document.createElement('div');
	div.className='ryzom-ui-tooltip';
	div.style.position='absolute';
	div.style.display='none';
	
	map.getPane(G_MAP_FLOAT_PANE).appendChild(div);

	this.div_=div;
	this.map_=map;
};

RyzomTooltip.prototype.remove = function(){
	this.div_.parentNode.removeChild(this.div_);
};

RyzomTooltip.prototype.copy = function(){
	return new Tooltip(this.padding_);
};

RyzomTooltip.prototype.redraw = function(force){
	if(!force) return;

	var xPos=this.x+this.padding_.width;
	var yPos=this.y+this.padding_.height;

	this.div_.style.left = xPos+'px';
	this.div_.style.top  = yPos+'px';
};

RyzomTooltip.prototype.show = function(x, y, text){
	if(x !== undefined && y !== undefined){
		this.x=x; this.y=y;
	}
	if(text !== undefined){
		this.div_.innerHTML=text;
	}
	this.div_.style.display='';
	this.redraw(true);
};

RyzomTooltip.prototype.hide = function(){
	this.div_.style.display='none';
};

