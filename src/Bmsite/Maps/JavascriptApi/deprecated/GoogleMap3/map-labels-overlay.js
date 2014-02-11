//
// Ryzom Maps
// Copyright (c) 2009 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of Ryzom Maps at http://maps.bmsite.net
//


/**
 * Labels for map - comes with it's own marker manager
 *
 * @param string lang label language - en, fr, de
 * @param bool show_region_level if set to TRUE, then show colored region names - default is FALSE
 */
function RyzomMapLabelOverlay(lang, show_region_level){
	// default language is 'en'
	this.lang=lang;
	this.show_region_level = false; 
	this.dragged=false;

	//
	this.labels = [];

	//
	this.hidden = false;
};
RyzomMapLabelOverlay.prototype=new GOverlay();

/**
 * Set label language and calls redraw
 */
RyzomMapLabelOverlay.prototype.setLanguage=function(lang){
	if(this.lang==lang) return;
	
	var me=this;
	// change language
	this.lang=lang;

	setTimeout(function(){
		for(var i=0,len=me.labels.length;i<len;i++){
			var name='<nobr>'+ryzomLabels[me.labels[i].idx].name[lang]+'</nobr>';
			if(me.labels[i].marker.tooltip != undefined){
				me.labels[i].marker.tooltip=name;
			}else{
				try{
					me.labels[i].marker.setContents(name);
				}catch(ex){
					me.labels[i].marker.html=name;
				}
			}
		}
	}, 0);
};

/**
 * Attach or remove region color CSS rules
 *
 * @param bool state
 */
RyzomMapLabelOverlay.prototype.showRegionLevel=function(state){
	if(this.show_region_level==state){
		return;
	}


	// FIXME: must look thru all the labels and change their class name
	//        there is no one single DIV unfortunately

	this.show_region_level=state;
};

/**
 * @param GMap2 map
 */
RyzomMapLabelOverlay.prototype.initialize=function(map){
// called by map after the overlay is added to the map using GMap2.addOverlay()
	this.map_ = map;

	// disable redraw when map is being dragged
	var me=this;
	GEvent.addListener(this.map_, 'dragstart', function(){
		me.dragged=true;
	});
	GEvent.addListener(this.map_, 'dragend', function(){
		me.dragged=false;
		me.redraw();
	});

	// prep labels
	this.doLabels();

	// set css to show colorized region labels
	this.showRegionLevel(this.show_region_level);
};

RyzomMapLabelOverlay.prototype.remove=function(){
	// remove labels from map
	for(var i=0,len=this.labels.length;i<len;i++){
		this.labels[i].remove();
	}
};

/**
 * @return GOverlay
 */
RyzomMapLabelOverlay.prototype.copy=function(){
	// make clone 
	return new RyzomMapLabelOverlay(this.lang, this.show_region_level);
};

/**
 * @param bool force
 */
RyzomMapLabelOverlay.prototype.redraw=function(force){

	if(this.hidden || this.dragged) return;

	// FIXME: will be called when user is moving map too
	var bounds=this.map_.getBounds();
	var zoom = this.map_.getZoom();

	for(var i=0,len=this.labels.length; i<len; i++){
		var m=this.labels[i];
		var latlng=m.latlng;
		var opts = this.zoomRange_(m.type, zoom);

		if(bounds.containsLatLng(latlng) && zoom >=opts.min && zoom<=opts.max
		&& ((m.is_label && opts.label) || (!m.is_label && opts.icon))) {
			if(!m.marker.attached){
				this.map_.addOverlay(m.marker);
				m.marker.attached=true;
			}
			m.marker.show();
		}else{
			m.marker.hide();
		}
	}
};

RyzomMapLabelOverlay.prototype.getKml=function(callback){
	return false;
};

RyzomMapLabelOverlay.prototype.hide=function(){
	this.hidden=true;
	for(var i=0,len=this.labels.length;i<len;i++){
		if(this.labels[i].attached) this.labels[i].marker.hide();
	}
};

RyzomMapLabelOverlay.prototype.show=function(){
	this.hidden=false;
	this.redraw(true);
};
RyzomMapLabelOverlay.prototype.supportsHide=function(){
	return true;
};
RyzomMapLabelOverlay.prototype.isHidden=function(){
	return this.hidden;
};

RyzomMapLabelOverlay.prototype.zoomRange_ = function(type, zoom){
	// defaults
	var minZoom=5;
	var maxZoom=99;
	var showIcon=true;
	var showLabel=true;
	// by type
	switch(type){
		case -1: // continent. biggest zone
			minZoom=5; maxZoom=7;
			break;
		case 0: // capital city
			minZoom=6; maxZoom=10;
			break;
		case 1: // towns
			minZoom=6;
			break;
		case 2: // outposts
			showLabel=zoom>9;
			minZoom=7;
			break;
		case 3: // stable
			minZoom=7;
			showLabel=false;
			break;
		case 4: // region
			minZoom=7;
			break;
		case 5: // area
			minZoom=8;
			break;
		case 6: // street
			minZoom=10;
			break;
	}
	return { min:minZoom, max:maxZoom, icon:showIcon, label:showLabel};
};

/**
 * Creates label elements and adds them to map
 */
RyzomMapLabelOverlay.prototype.doLabels=function(){
	//
	this.disabledRegions=[
	    //
		'corrupted_moor',
		// Ring region text
		'r2_desert', 'r2_lakes', 'r2_jungle', 'r2_forest', 'r2_roots',
		// these mask 'Old Land' text
		//'tryker_island', 'matis_island', 'zorai_island', 'fyros_island'
	];
	
	var markers=[];
	for(var i=0,len=ryzomLabels.length;i<len;i++){
		// disable some region labels
		var dst = RyzomXY.belongsToIngame(ryzomLabels[i].x, ryzomLabels[i].y);
		if(dst.length>0 && this.disabledRegions.indexOf(dst[0])>-1){
			continue;
		}

		var latlng=this.map_.fromIngame(ryzomLabels[i].x, ryzomLabels[i].y);
		
		if(ryzomLabels[i].icon!=''){
			// icon might have a tooltip
			var icon=new GMarker(latlng, {
				icon: RyzomIcons.OUTPOST
			});
			icon.tooltip = '<nobr>'+ryzomLabels[i].name[this.lang]+'</nobr>';

			this.labels.push({
				idx    : i,
				type   : ryzomLabels[i].type,
				latlng : latlng,
				marker : icon,
				is_label: false,
				attached: false // is marker added to map ?
			});
		}

		var level=ryzomLabels[i].level || '0';
		var label = new ELabel(latlng, ryzomLabels[i].name[this.lang], "mapLabel mapLabel"+ryzomLabels[i].type+" region-q"+level, new GSize(-100, 0));
		
		// FIXME: dup code
		this.labels.push({
			idx    : i,
			type   : ryzomLabels[i].type,
			latlng : latlng,
			marker : label,
			is_label: true,
			attached: false // is marker added to map ?
		});
	}
};

