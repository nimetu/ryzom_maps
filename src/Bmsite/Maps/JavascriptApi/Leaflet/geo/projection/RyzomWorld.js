/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Project ingame coordinates to world map
 */
L.Projection.RyzomWorld = {
    /**
     * Search matching ingame zone and convert coords to world map
     *
     * @param latlng
     * @returns {L.Point}
     */
    project: function (latlng) {
        return Ryzom.pixel(latlng.lat, latlng.lng);
    },

    /**
     * Search matching world map zone and convert to ingame coords
     *
     * @param point
     * @returns {L.LatLng}
     */
    unproject: function (point) {
        return Ryzom.location(point.x, point.y);
    }
};
