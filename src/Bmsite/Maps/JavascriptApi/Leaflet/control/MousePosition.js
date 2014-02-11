/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Display mouse position
 */
L.Control.MousePosition = L.Control.extend({
    options: {
        position: 'bottomright',
        marker: null
    },
    onAdd: function (map) {
        this._container = L.DomUtil.create('div', 'leaflet-control-mouseposition');
        L.DomEvent.disableClickPropagation(this._container);
        map.on('mousemove', this._onMouseMove, this);

        return this._container;
    },
    onRemove: function (map) {
        map.off('mousemove', this._onMouseMove);
    },
    _onMouseMove: function (e) {
        // server coordinates
        var x = L.Util.formatNum(e.latlng.lat, 0);
        var y = L.Util.formatNum(e.latlng.lng, 0);
        var regions = Ryzom.XY.findIngameRegion(e.latlng.lat, e.latlng.lng);
        var html = x + ', ' + y + ' (' + regions.join(',') + ')';

        // this will give image coordinates for zoom level 10
        var maxZoom = this._map.getMaxZoom();
        var p = this._map.project(e.latlng, maxZoom);
        html += "<br>Image coords at zoom " + maxZoom + " :" + L.Util.formatNum(p.x, 0) + ', ' + L.Util.formatNum(p.y, 0);

        // convert image back to latlng
        var l = this._map.unproject(p, maxZoom);
        html += "<br>(new) latlng:" + L.Util.formatNum(l.lat, 0) + ', ' + L.Util.formatNum(l.lng, 0);

        if (this.options.marker) {
            this.options.marker.setLatLng(l);
        }

        this._container.innerHTML = html;
        //console.debug('event', e);
    }
});
