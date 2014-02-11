
/**
 * @requires OpenLayers/Control/MousePosition.js
 */

/**
 * Inherits
 *  - <OpenLayers.Control.MousePosition>
 */

// FIXME: debug
Ryzom.Control.MousePosition = OpenLayers.Class(OpenLayers.Control.MousePosition, {
	numDigits: 2,

	redraw: function(evt){
		//OpenLayers.Control.MousePosition.apply(this, arguments);

		var lonLat;
		var layerxy;

		if (evt == null) {
			this.reset();
			return;
		} else {
			if (this.lastXy == null ||
				Math.abs(evt.xy.x - this.lastXy.x) > this.granularity ||
				Math.abs(evt.xy.y - this.lastXy.y) > this.granularity)
			{
				this.lastXy = evt.xy;
				return;
			}

			lonLat = this.map.getLonLatFromPixel(evt.xy);
			if (!lonLat) {
				// map has not yet been properly initialized
				return;
			}
			layerxy = lonLat.clone();

			this.lastXy = evt.xy;
		}
		var n = Ryzom.XY.belongsToOutgame(layerxy.lon, layerxy.lat);

		var newHtml='';
		newHtml = '<table>';
		newHtml += '<tr><td>lonLat</td><td align="right">'+layerxy.lon.toFixed(2)+'</td><td align="right">'+layerxy.lat.toFixed(2)+'</td><td>['+n+']</td></tr>';

		// convert world xy to server and back to world again
		var ig = Ryzom.XY.fromOutgameToIngame(layerxy.lon, layerxy.lat);
		newHtml += '<tr><td>fromOutgame(lonLat)</td><td align="right">'+ig.x.toFixed(2)+'</td><td align="right">'+ig.y.toFixed(2)+'</td><td>['+ig.regions+']</td></tr>';

		var og = Ryzom.XY.fromIngameToOutgame(ig.x, ig.y);
		newHtml += '<tr><td>fromIngame(ig)</td><td align="right">'+og.x.toFixed(2)+'</td><td align="right">'+og.y.toFixed(2)+'</td><td>['+og.regions+']</td></tr>';
		newHtml += '</table>';

		var name = Ryzom.XY.belongsToIngame(lonLat.lon, lonLat.lat, true);
		newHtml += 'layerxy:['+n+'] '+this.formatOutput(layerxy)+'<br />';
		newHtml += 'lonLat:['+name+'] '+this.formatOutput(lonLat);

		if (newHtml != this.element.innerHTML) {
			this.element.innerHTML = newHtml;
		}
	}
});

