/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Uses Ryzom Server zone placement
 * Define Zoom Level 10 as 1:1 scale for X/Y coordinates
 */
L.CRS.RyzomServer = L.extend({}, L.CRS, {
    projection: L.Projection.RyzomServer,
    transformation: new L.Transformation(1, 0, -1, 0),

    scale: function (zoom) {
        // pow(2, 10) == 1024
        return Math.pow(2, zoom) / 1024;
    }
});
