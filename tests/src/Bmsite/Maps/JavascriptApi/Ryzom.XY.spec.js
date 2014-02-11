/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

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
            expect(xy).toEqual({x: 8552, y: 5350});

            var igr = Ryzom.XY.findIngameRegion(640, -11370);
            expect(igr).toEqual(['bagne', 'grid']);

            // as location is outside bagne, then zone from image coordinates are unreliable
            var ogr = Ryzom.XY.findOutgameRegion(8552, 5350);
            expect(ogr).toEqual(['matis', 'grid']);
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
            var xy = Ryzom.XY.fromOutgameToIngame(14160, 14160);
            expect(xy).toEqual({x: 122160, y: -14160});

            var igr = Ryzom.XY.findIngameRegion(122160, -14160);
            expect(igr).toEqual(['grid']);

            var ogr = Ryzom.XY.findOutgameRegion(14160, 14160);
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
                expect(result).toEqual(['fyros', 'grid']);
            });

            it("region matches pyr/fyros/grid", function () {
                var result = Ryzom.XY.findIngameRegion(18400, -24720);
                expect(result).toEqual(['place_pyr', 'fyros', 'grid']);
            });

            it("fyros server coordinates to image coordinates", function () {
                var sw = Ryzom.XY.fromIngameToOutgame(15840, -27040);
                var ne = Ryzom.XY.fromIngameToOutgame(20320, -23840);

                var expected = [
                    [2920,3836  ],
                    [7400, 636 ]
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
                    [5480,1516 ],
                    [6120,1036 ]
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
                var result = Ryzom.XY.findOutgameRegion(3000, 3000);
                expect(result).toEqual(['fyros', 'grid']);
            });

            it("server projection", function () {
                var bl = new L.Point(2920, 3836);
                var tr = new L.Point(7400, 636);
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
                    name: 'matis',
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
                    name: 'fyros',
                    distance: 340,
                    x: 16000,
                    y: -23840
                };
                expect(xy).toEqual(expected);
            });
        });
        describe("world location", function () {
            it("closest point is fyros north-west corner", function () {
                // fyros = [2920, 3836], [7400, 636]
                var xy = Ryzom.XY.findClosestOutgameRegion(0, 0);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'fyros',
                    distance: 2988,
                    x: 2920,
                    y: 636
                };
                expect(xy).toEqual(expected);
            });
            it("closest point is along nexus south border", function () {
                // nexus = [8196, 7896], [10116, 5656]
                var xy = Ryzom.XY.findClosestOutgameRegion(8200, 7900);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'nexus',
                    distance: 4,
                    x: 8200,
                    y: 7896
                };
                expect(xy).toEqual(expected);
            });
            it("closest point is west from nexus, inside matis zone", function () {
                var xy = Ryzom.XY.findClosestOutgameRegion(8190, 6000);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'nexus',
                    distance: 6,
                    x: 8196,
                    y: 6000
                };
                expect(xy).toEqual(expected);
            });
            it("closest point is matis west border, right west from nexus", function () {
                // matis = [7760, 7872], [13680, 352]
                var xy = Ryzom.XY.findClosestOutgameRegion(7800, 6000);
                xy.distance = parseInt(xy.distance);
                var expected = {
                    name: 'matis',
                    distance: 40,
                    x: 7760,
                    y: 6000
                };
                expect(xy).toEqual(expected);
            });
        });
    });

    it("coords from phpunit", function () {
        var data = [
            // matis - yrk (sw)
            [new L.Point(4640, -3680), new L.Point(12080, 3712), ['place_yrkanis', 'matis', 'grid']],
            // matis - yrk (ne)
            [new L.Point(4800, -3200), new L.Point(12240, 3232), ['place_yrkanis', 'matis', 'grid']],
            // matis - random spot
            [new L.Point(600, -7000), new L.Point(8040, 7032), ['matis', 'grid']],
            // fyros - random spot
            [new L.Point(17000, -25000), new L.Point(4080, 1796), ['fyros', 'grid']],
            // closest to zone
            [new L.Point(300, -2000), new L.Point(7740, 2032), ['matis', 'grid']],
            // outside any zone
            [new L.Point(100, -2000), new L.Point(-107900, 2000), ['grid']]
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
