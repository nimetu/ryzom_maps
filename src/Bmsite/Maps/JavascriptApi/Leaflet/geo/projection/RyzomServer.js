/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Same as L.Projection.LonLat, but with Lat=X and Lng=Y
 * This is 1:1 conversion between image and server x/y
 */
L.Projection.RyzomServer = {
    /**
     * From server to image
     *
     * @param latlng
     * @returns {L.Point}
     */
    project: function (latlng) {
        // TODO: do proper grid projection
        return new L.Point(latlng.lat, latlng.lng);
    },

    /**
     * From image to server
     *
     * @param point
     * @returns {L.LatLng}
     */
    unproject: function (point) {
        // TODO: do proper from grid projection
        return new L.LatLng(point.x, point.y);
    }
};
