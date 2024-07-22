/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
require('./imports.js');

describe("Leaflet map Ryzom CRS", function () {
    // center point
    var xyWorld = L.point(10000, 7500);
    var xyServer = L.point(9192, -7924);

    describe("world projection", function () {
        it("server to world", function () {
            var latlng = L.latLng(xyServer.x, xyServer.y);
            var xy = L.CRS.RyzomWorld.projection.project(latlng);

            expect(xy instanceof L.Point).toBeTruthy();
            expect({x: xy.x, y: xy.y}).toEqual({x: xyWorld.x, y: xyWorld.y});
        });

        it("world to server", function () {
            var xy = L.CRS.RyzomWorld.projection.unproject(xyWorld);

            expect(xy instanceof L.LatLng).toBeTruthy();
            expect({x: xy.lat, y: xy.lng}).toEqual({x: xyServer.x, y: xyServer.y});
        });
    });

    describe("server projection", function () {
        it("server to world", function () {
            var latlng = L.latLng(xyServer.x, xyServer.y);
            var xy = L.CRS.RyzomServer.projection.project(latlng);

            expect(xy instanceof L.Point).toBeTruthy();
            expect({x: xy.x, y: xy.y}).toEqual({x: xyServer.x, y: xyServer.y});
        });

        it("world to server", function () {
            var xy = L.CRS.RyzomServer.projection.unproject(xyServer);

            expect(xy instanceof L.LatLng).toBeTruthy();
            expect({x: xy.lat, y: xy.lng}).toEqual({x: xyServer.x, y: xyServer.y});
        });
    });
});
