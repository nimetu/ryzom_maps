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
        var x = Math.floor(e.latlng.lat);
        var y = Math.floor(e.latlng.lng);

        var regions = Ryzom.XY.findIngameRegion(e.latlng.lat, e.latlng.lng);
        var html = 'x:' + x + ', y:' + y + ' (' + regions.join(',') + ')';

        var p = this._map.project(e.latlng, 10);
        html += "<br>Image @(zoom=10): x:" + Math.floor(p.x) + ', y:' + Math.floor(p.y);

        // convert image back to latlng
        //var l = this._map.unproject(p, 10);
        //html += "<br>(new) latlng:" + Math.floor(l.lat) + ', ' + Math.floor(l.lng);

        if (this.options.marker) {
            this.options.marker.setLatLng(l);
        }

        this._container.innerHTML = html;
        //console.debug('event', e);
    }
});
