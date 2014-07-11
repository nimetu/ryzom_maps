/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

window.Ryzom = {
    /**
     * Display debug in firebug console
     */
    DEBUG: false,

    /**
     * API base url
     */
    apiDomain: 'http://api.bmsite.net/',

    /**
     * Map tiles url path relative to apiUrl
     */
    tilePath: 'maps/{mode}/{style}/{z}/{x}/{y}.{ext}',

    /**
     * Project ingame x/y to world map
     *
     * @param {Number} x
     * @param {Number} y
     *
     * @return {L.Point}
     */
    pixel: function (x, y) {
        var xy = Ryzom.XY.fromIngameToOutgame(x, y);

        return new L.Point(xy.x, xy.y);
    },

    /**
     * Project world map x/y back to ingame
     *
     * @param {Number} x
     * @param {Number} y
     *
     * @return {L.LatLng}
     */
    location: function (x, y) {
        var xy = Ryzom.XY.fromOutgameToIngame(x, y);
        return new L.LatLng(xy.x, xy.y);
    },

    CLASS_NAME: 'Ryzom'
};
