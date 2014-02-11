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


