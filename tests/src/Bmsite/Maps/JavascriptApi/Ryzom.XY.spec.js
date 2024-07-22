/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
require('./imports.js');

describe("Map projection", function () {
    describe("un-mapped server coordinates", function () {
        it("server top-left", function () {
            var xy = Ryzom.XY.fromIngameToOutgame(0, 0);
            expect(xy).toEqual({x: -108000, y: 0});

            var igr = Ryzom.XY.findIngameRegion(0, 0);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(-108000, 0);
            expect(ogr).toEqual(['grid']);
        });

        it("server bottom-right", function () {
            var xy = Ryzom.XY.fromIngameToOutgame(108000, -47520);
            expect(xy).toEqual({x: 0, y: 47520});

            var igr = Ryzom.XY.findIngameRegion(108000, -47520);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(0, 47520);
            expect(ogr).toEqual(['grid']);
        });

        it("outside known server space (top-left)", function () {
            var xy = Ryzom.XY.fromIngameToOutgame(-1, 1);
            expect(xy).toEqual({x: -108001, y: -1});

            var igr = Ryzom.XY.findIngameRegion(-1, 1);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(-108001, -1);
            expect(ogr).toEqual(['grid']);
        });

        it("outside known server space (bottom-right)", function () {
            var xy = Ryzom.XY.fromIngameToOutgame(308000, -50000);
            expect(xy).toEqual({x: 200000, y: 50000});

            var igr = Ryzom.XY.findIngameRegion(308000, -50000);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(200000, 50000);
            expect(ogr).toEqual(['grid']);
        });

        it("outside Abyss Of Ichor must be matched inside", function () {
            // correct bottom coords is -11360
            var igc = Ryzom.XY.findClosestIngameRegion(640, -11370);
            expect(parseInt(igc.distance)).toBe(10);

            var xy = Ryzom.XY.fromIngameToOutgame(640, -11370);
            expect(xy).toEqual({x: 9120, y: 5350});

            var igr = Ryzom.XY.findIngameRegion(640, -11370);
            expect(igr).toEqual(['continent_bagne', 'grid']);

            // as location is outside bagne, then zone from image coordinates are unreliable
            var ogr = Ryzom.XY.findOutgameRegion(9120, 5350);
            expect(ogr).toEqual(['continent_matis', 'grid']);
        });
    });

    describe("unmapped world coordinates", function () {
        it("grid top-left", function () {
            var xy = Ryzom.XY.fromOutgameToIngame(0, 0);
            expect(xy).toEqual({x: 108000, y: 0});

            var igr = Ryzom.XY.findIngameRegion(108000, 0);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(0, 0);
            expect(ogr).toEqual(['grid']);
        });
        it("grid bottom-right", function () {
            var xy = Ryzom.XY.fromOutgameToIngame(20000, 15000);
            expect(xy).toEqual({x: 128000, y: -15000});

            var igr = Ryzom.XY.findIngameRegion(128000, -15000);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(20000, 15000);
            expect(ogr).toEqual(['grid']);
        });
        it("coordinates outside grid (negative)", function () {
            // y:-1 in here gives rounding errors
            var xy = Ryzom.XY.fromOutgameToIngame(-10, -10);
            expect(xy).toEqual({x: 107990, y: 10});

            var igr = Ryzom.XY.findIngameRegion(107990, 10);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(-10, -10);
            expect(ogr).toEqual(['grid']);
        });
        it("coordinates outside grid (positive)", function () {
            var xy = Ryzom.XY.fromOutgameToIngame(200000, 50000);
            expect(xy).toEqual({x: 308000, y: -50000});

            var igr = Ryzom.XY.findIngameRegion(308000, -50000);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(200000, 50000);
            expect(ogr).toEqual(['grid']);
        });
    });

    describe("fyros", function () {
        describe("server to world", function () {
            it("region matches fyros/grid", function () {
                var result = Ryzom.XY.findIngameRegion(19000, -25000);
                expect(result).toEqual(['continent_fyros', 'grid']);
            });

            it("region matches pyr/fyros/grid", function () {
                var result = Ryzom.XY.findIngameRegion(18400, -24720);
                expect(result).toEqual(['place_pyr', 'continent_fyros', 'grid']);
            });

            it("fyros server coordinates to image coordinates", function () {
                var sw = Ryzom.XY.fromIngameToOutgame(15840, -27040);
                var ne = Ryzom.XY.fromIngameToOutgame(20320, -23840);

                var expected = [
                    [3504, 3836],
                    [7984, 636]
                ];
                var result = [
                    [sw.x, sw.y],
                    [ne.x, ne.y]
                ];
                expect(result).toEqual(expected);
            });

            it("pyr server coordinates to image coordinates", function () {
                var sw = Ryzom.XY.fromIngameToOutgame(18400, -24720);
                var ne = Ryzom.XY.fromIngameToOutgame(19040, -24240);
                var expected = [
                    [6064, 1516],
                    [6704, 1036]
                ];
                var result = [
                    [sw.x, sw.y],
                    [ne.x, ne.y]
                ];
                expect(result).toEqual(expected);
            });
        });

        describe("world to server", function () {
            it("region matches fyros/grid", function () {
                var result = Ryzom.XY.findOutgameRegion(6000, 2000);
                expect(result).toEqual(['continent_fyros', 'grid']);
            });

            it("server projection", function () {
                var bl = new L.Point(3504, 3836);
                var tr = new L.Point(7984, 636);
                var sw = Ryzom.XY.fromOutgameToIngame(bl.x, bl.y);
                var ne = Ryzom.XY.fromOutgameToIngame(tr.x, tr.y);

                var expected = [
                    [15840, -27040],
                    [20320, -23840]
                ];
                var result = [
                    [sw.x, sw.y],
                    [ne.x, ne.y]
                ];
                expect(result).toEqual(expected);
            });
        });
    });

    describe("find closest", function () {
        describe("server location", function () {
            it("closest point is matis north-west corner", function () {
                // matis = [320, -7840], [6240, -320]
                var xy = Ryzom.XY.findClosestIngameRegion(0, 0);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'continent_matis',
                    distance: 452,
                    x: 320,
                    y: -320
                };
                expect(xy).toEqual(expected);
            });
            it("closest point is fyros north border", function () {
                // fyros = [15840, -27040], [20320, -23840]
                var xy = Ryzom.XY.findClosestIngameRegion(16000, -23500);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'continent_fyros',
                    distance: 340,
                    x: 16000,
                    y: -23840
                };
                expect(xy).toEqual(expected);
            });
        });
        describe("world location", function () {
            it("closest point is fyros north-west corner", function () {
                var xy = Ryzom.XY.findClosestOutgameRegion(0, 0);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'continent_fyros',
                    distance: 3561,
                    x: 3504,
                    y: 636
                };
                expect(xy).toEqual(expected);
            });
            it("closest point is along nexus south border", function () {
                var xy = Ryzom.XY.findClosestOutgameRegion(8500, 9020);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'continent_nexus',
                    distance: 4,
                    x: 8500,
                    y: 9016
                };
                expect(xy).toEqual(expected);
            });
        });
    });

    it("coords from phpunit", function () {
        var data = [
            // matis - yrk (nw)
            [new L.Point(4640, -3200), new L.Point(13120, 3596), ['place_yrkanis', 'continent_matis', 'grid']],
            // matis - yrk (se)
            [new L.Point(4800, -3680), new L.Point(13280, 4076), ['place_yrkanis', 'continent_matis', 'grid']],
            // matis - random spot
            [new L.Point(600, -7000), new L.Point(9080, 7396), ['continent_matis_newbie', 'continent_matis', 'grid']],
            // fyros - random spot
            [new L.Point(17000, -25000), new L.Point(4664, 1796), ['continent_fyros', 'grid']],
            // closest to zone
            [new L.Point(300, -2000), new L.Point(8780, 2396), ['continent_matis', 'grid']],
            // outside any zone
            [new L.Point(100, -2000), new L.Point(-107900, 2000), ['grid']],
            // kitiniere
            [new L.Point(2540,-17400), new L.Point(17493, 7098), ['cont_kitiniere', 'grid']],
        ];
        for (var i = 0; i < data.length; i++) {
            var p = data[i][0];
            var e = data[i][1];
            var r = data[i][2];

            var xy = Ryzom.XY.fromIngameToOutgame(p.x, p.y);
            var regions = Ryzom.XY.findIngameRegion(p.x, p.y);
            expect({x: xy.x, y: xy.y, regions: regions}).toEqual({x: e.x, y: e.y, regions: r});
        }
    });
});
