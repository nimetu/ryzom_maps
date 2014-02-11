//
// Ryzom Maps
// Copyright (c) 2009 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of Ryzom Maps at http://maps.bmsite.net
//

/**
 * Translates current mouse coordinates to ingame x/y/zone and displays them
 */
function RyzomMapLabelControl(rl){
	this.langs=['en', 'fr', 'de', 'ru'];
	this.rl_=rl;
	this.doLabelChange=function(self, target){ return true;};
}
RyzomMapLabelControl.prototype=new GControl();

/**
 * Create element where info is displayed and add it to map
 * Automatically called
 */
RyzomMapLabelControl.prototype.initialize=function(map){
	var container = document.createElement('div');
	this.setStyle(container);
	
	for(var i=0;i<this.langs.length;i++){
		var d = document.createElement('img');
		d.alt=this.langs[i];
		d.style.margin='0 2px';
		d.src='http://maps.bmsite.net/images/lang/flag-'+this.langs[i]+'.png';
		container.appendChild(d);
	}
	
	map.getContainer().appendChild(container);
	
	var me=this;
	GEvent.addDomListener(container, 'click', function(e){
		if(e.target.alt){
			me.setLanguage(e.target.alt, e.target);
		}
	});
	return container;
};

RyzomMapLabelControl.prototype.setLanguage=function(lang, target){
	this.rl_.setLanguage(lang);
	this.doLabelChange(this, lang, target);
};

/**
 * Set the default location
 */
RyzomMapLabelControl.prototype.getDefaultPosition = function(){
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(2, 2));
};

/**
 * Define textbox style
 */
RyzomMapLabelControl.prototype.setStyle=function(div){
	div.style.color = "#0000cc";
	div.style.backgroundColor="transparent";//white";
	div.style.font="small Arial";
	div.style.border="none";//1px solid black";
	div.style.padding="2px";
	div.style.width="70px";
	div.style.height="1.2em";
	div.style.textAlign="center";
	div.style.cursor="pointer";
};

