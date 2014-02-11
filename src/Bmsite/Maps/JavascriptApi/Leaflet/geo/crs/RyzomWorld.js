/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Uses Ryzom World map zone placement
 */
L.CRS.RyzomWorld = L.extend({}, L.CRS.RyzomServer, {
    projection: L.Projection.RyzomWorld,
    transformation: new L.Transformation(1, 0, 1, 0)
});
