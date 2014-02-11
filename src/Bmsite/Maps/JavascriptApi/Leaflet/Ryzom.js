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
     * Map tileserver
     */
    MAPS_HOST: 'http://maps.bmsite.net/',

    /**
     * Theme root url
     */
    theme: '/api/theme/',

    /**
     * function to turn icon url to selected icon url
     */
    selectedIcon: function (url) {
        if (url.match(/icon=[^$]+_over/)) {
            return url;
        } else {
            return url.replace(/icon=([^&]+)/, 'icon=$1_over');
        }
    },

    /**
     * Project ingame x/y to world map
     * @param x
     * @param y
     */
    pixel: function (x, y) {
        var xy = Ryzom.XY.fromIngameToOutgame(x, y);

        return new L.Point(xy.x, xy.y);
    },

    /**
     * Project world map x/y back to ingame
     *
     * @param x
     * @param y
     */
    location: function (x, y) {
        var xy = Ryzom.XY.fromOutgameToIngame(x, y);
        return new L.LatLng(xy.x, xy.y);
    },

    CLASS_NAME: 'Ryzom'
};
