/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

Ryzom.XY = {
    // zoom level for these coordinates (1px = 1m)
    BASE_ZOOM: 10,

    outgame_coordinates: {},

    ingame_coordinates: {},

    initialize: function (wz, sz) {
        this.outgame_coordinates = this._build(wz);
        this.ingame_coordinates = this._build(sz);
    },

    findIngameRegion: function (x, y) {
        return this._belongsTo(x, y, this.ingame_coordinates);
    },

    findOutgameRegion: function (x, y) {
        return this._belongsTo(x, y, this.outgame_coordinates);
    },

    findClosestIngameRegion: function (x, y) {
        return this._findClosest(x, y, this.ingame_coordinates);
    },

    findClosestOutgameRegion: function (x, y) {
        return this._findClosest(x, y, this.outgame_coordinates);
    },

    /*
     * Maps ingame coordinates to pixel coordinates
     *
     * @param {Number} x
     * @param {Number} y
     *
     * @return {x, y}
     */
    fromIngameToOutgame: function (x, y) {
        return this._project(x, y, this.ingame_coordinates, this.outgame_coordinates);
    },

    /**
     * Converts Outgame coordinates to ingame ones
     *
     * @param {Number} x  world coordinate
     * @param {Number} y  world coordinate
     *
     * @return Object{x, y}
     */
    fromOutgameToIngame: function (x, y) {
        return this._project(x, y, this.outgame_coordinates, this.ingame_coordinates);
    },

    /**
     * Match x,y to ingame areas
     *
     * Return sorted array of Object{key, order} where order is
     * continent=0, region, capital, village, stable, place, street, outpost, unknown = 8
     *
     * e.g: xy [17162, -32906] returns [
     *   {key: "place_fairhaven_p_1", order: 6},
     *   {key: "place_fairhaven", order: 2},
     *   {key: "region_libertylake", order: 1},
     *   {key: "tryker", order: 0}
     * ]
     *
     * @param {Number} x
     * @param {Number} y
     *
     * @return {Array} [{key, order}]
     */
    findIngameAreas: function (x, y) {
        var match = this._matchAreas(x, y, Ryzom.XY.ingame_areas);
        return match.sort(function (a, b) {
            return a.order < b.order;
        });
    },

    /**
     * @param {Array} wz
     * @returns {Array}
     * @private
     */
    _build: function (wz) {
        var result = {};
        for (var prop in wz) {
            if (wz.hasOwnProperty(prop)) {
                var bounds, segments, size;
                var lb = wz[prop][0];
                var rt = wz[prop][1];
                bounds = L.bounds(lb, rt);
                size = bounds.getSize();
                segments = [
                    {x1: lb[0], y1: rt[1], x2: lb[0], y2: lb[1]},
                    {x1: lb[0], y1: lb[1], x2: rt[0], y2: lb[1]},
                    {x1: rt[0], y1: lb[1], x2: rt[0], y2: rt[1]},
                    {x1: rt[0], y1: rt[1], x2: lb[0], y2: rt[1]}
                ];
                result[prop] = {
                    bounds: bounds,
                    size: size.x * size.y,
                    segments: segments
                };
            }
        }

        return result;
    },

    /**
     * Return all zone names where x/y belongs to
     * example: [place_fairhaven, tryker, grid]
     *
     * @param {Number} x
     * @param {Number} y
     * @param {Array} zones
     * @returns {Array}
     * @private
     */
    _getZones: function (x, y, zones) {
        function compare(i, j) {
            return zones[i].size - zones[j].size;
        }

        var matches = [];
        var p = L.point(x, y);
        for (var prop in zones) {
            if (zones.hasOwnProperty(prop)) {
                if (zones[prop].bounds.contains(p)) {
                    matches.push(prop);
                }
            }
        }
        matches.sort(compare);

        if (matches.length == 0 || matches[matches.length - 1] != 'grid') {
            matches.push('grid');
        }

        if (Ryzom.DEBUG) console.debug('(_getZones) return', matches, ', input was ', [x, y, zones]);
        return matches;
    },

    /**
     * Try to find closest server zone
     *
     * @param {Number} x
     * @param {Number} y
     * @param {Array} zones
     *
     * @return {Object} {name: zone, dist: distance}
     */
    _findClosest: function (x, y, zones) {
        if (Ryzom.DEBUG) console.debug('(_findClosest) enter', [x, y]);

        var current = {
            name: [],
            distance: Infinity,
            x: null,
            y: null
        };
        var point = L.point(x, y);
        for (var prop in zones) {
            if (prop == 'grid' || prop == 'world') {
                continue;
            }

            if (zones.hasOwnProperty(prop)) {
                var zone = zones[prop];

                for (var i = 0, len = zone.segments.length; i < len && current.distance > 0; i++) {
                    var dist = OpenLayers.Geometry.distanceToSegment(point, zone.segments[i]);
                    if (dist.distance < current.distance) {
                        if (Ryzom.DEBUG) console.debug('>> new match', [prop, dist, i], 'replacing', current);
                        current.name = prop;
                        current.distance = dist.distance;
                        current.x = dist.x;
                        current.y = dist.y;
                    }
                }
            }
        }//for

        if (Ryzom.DEBUG) console.debug('(find_closest) return', current);
        return current;
    },

    /**
     * Searches in which region(s) X,Y belongs to
     *
     * @param {Number} x
     * @param {Number} y
     * @param {Array} zones array of zones to test
     * @param {Number} closest distance in meters to consider close
     *
     * @return {Array} regions sorted from smallest first (town, zone, grid)
     */
    _belongsTo: function (x, y, zones, closest) {
        // zorai-nexus is 160m from each other
        // must use 80m in here or some locations
        // image->server->image will be translated wrong
        closest = closest !== undefined ? closest : 80;
        if (Ryzom.DEBUG) console.debug('(_belongsTo) enter', [x, y, zones, closest]);

        var matches = this._getZones(x, y, zones);

        // if no match or match is against grid, then check how close we are to defined zone
        // some prime root portal/spawn are little outside the zone
        if (closest && (matches.length == 0 || matches[0] == 'grid')) {
            if (Ryzom.DEBUG) console.debug('>> zone match not found, try to find closest zone');
            var match = this._findClosest(x, y, zones);
            if (matches.length == 0 || match.distance <= closest) {
                matches = [match.name, 'grid'];
            }
        }

        if (Ryzom.DEBUG) console.debug('(_belongsTo) return', [x, y], 'matches', matches);
        return matches;
    },

    /**
     * Convert X/Y from one list of zones to another list of zones
     *
     * @param {Number} x   coordinate in src system
     * @param {Number} y   coordinate in src system
     * @param {Object} src array of zones to map from
     * @param {Object} dst array of zones to map into
     *
     * @return Object{x, y}
     */
    _project: function (x, y, src, dst) {
        if (Ryzom.DEBUG) console.debug('(fromToZone)', [x, y]);

        var srczone, dstzone, zonename;

        // where is this point located
        var regions = this._belongsTo(x, y, src);

        // which of those we have mapped in destination
        for (var i = 0; i < regions.length; i++) {
            zonename = regions[i];
            if (dst[zonename] !== undefined) {
                if (Ryzom.DEBUG) console.debug('>> src and dst both have', [zonename]);
                srczone = src[zonename];
                dstzone = dst[zonename];
                break;
            }
        }

        if (!srczone) {
            if (Ryzom.DEBUG)console.debug('ERROR: no match', [srczone, dstzone, zonename, regions]);
            return {x: 0, y: 0}
        }

        // % coordinates inside source zone
        var srcSize = srczone.bounds.getSize();
        var srcBottomLeft = srczone.bounds.getBottomLeft();
        var srcTopRight = srczone.bounds.getTopRight();

        var pLeft = (x - srcBottomLeft.x) / srcSize.x;
        var pTop = (y - srcTopRight.y) / srcSize.y;
        if (Ryzom.DEBUG) console.debug('>> source', [x, y], [pLeft.toFixed(3), pTop.toFixed(3)], [zonename]);

        // new xy coordinates in destination
        var dstSize = dstzone.bounds.getSize();
        var dstBottomLeft = dstzone.bounds.getBottomLeft();
        var left = dstBottomLeft.x + pLeft * dstSize.x;
        var top = dstBottomLeft.y - pTop * dstSize.y;

        if (Ryzom.DEBUG) console.debug('(fromToZone) return', [left, top]);

        return {x: Math.round(left), y: Math.round(top)};
    },

    /**
     * Recursivly match X,Y for area polygons
     *
     * @param {Number} x
     * @param {Number} y
     * @param {Object}
     *
     * @return {Array} {key, order}
     */
    _matchAreas: function (x, y, areas, match) {
        match = match || [];
        for (var prop in areas) {
            if (areas.hasOwnProperty(prop)) {
                var area = areas[prop];
                if (this._inPolygon(x, y, area.points)) {
                    match.push({
                        key: prop,
                        order: area.order
                    });
                    if (area.hasOwnProperty("areas")) {
                        match = this._matchAreas(x, y, area.areas, match);
                    }
                }
            }
        }

        return match;
    },

    /**
     *  https://wrf.ecse.rpi.edu//Research/Short_Notes/pnpoly.html
     *
     * @param {Number} x
     * @param {Number} y
     * @param {Array} points flat array of x,y coordinates
     *
     * @return {Boolean}
     */
    _inPolygon: function (x, y, points) {
        var nbPoints = points.length;
        if (nbPoints < 6) {
            return false;
        }

        var success = false;
        for (var i = 0, j = nbPoints - 2; i < nbPoints; j = i, i += 2) {
            var iX = points[i],
                iY = points[i + 1];
            var jX = points[j],
                jY = points[j + 1];
            if (((iY > y) != (jY > y)) &&
                (x < (jX - iX) * (y - iY) / (jY - iY) + iX)
            ) {
                success = !success;
            }
        }

        return success;
    },

    CLASS_NAME: 'Ryzom.XY'
};

// this must be called immediately
(function () {
    console.debug('Ryzom.XY.initialize');
    var worldZones = {
        "world": [
            [0, 14160 ],
            [14160, 0]
        ],
        "continent_fyros": [
            [2920, 3836 ],
            [7400, 636]
        ],
        "continent_matis": [
            [7760, 7872 ],
            [13680, 352]
        ],
        "continent_tryker": [
            [7716, 13664 ],
            [13956, 8224]
        ],
        "continent_zorai": [
            [188, 13800 ],
            [5788, 8840 ]
        ],
        "continent_nexus": [
            [8196, 7896 ],
            [10116, 5656 ]
        ],
        "continent_bagne": [
            [8392, 5340 ],
            [9512, 3740 ]
        ],
        "continent_route_gouffre": [
            [5792, 11504 ],
            [7712, 4144]
        ],
        "continent_terre": [
            [2372, 7680 ],
            [5252, 4960 ]
        ],
        "continent_sources": [
            [692, 8600 ],
            [1972, 7000 ]
        ],
        "cont_newbieland": [
            [0, 16380 ],
            [3200, 14300 ]
        ],
        "cont_kitiniere": [
            [5000, 15880 ],
            [6280, 14600 ]
        ],
        "place_matis_island_1": [
            [8500, 15880 ],
            [9780, 14600 ]
        ],
        "place_matis_island_2": [
            [12880, 15880 ],
            [13840, 14600 ]
        ],
        "grid": [
            [-108000, 47520],
            [0, 0]
        ]
    };
    var serverZones = {
        "grid": [
            [0, -47520],
            [108000, 0]
        ],
        "continent_fyros": [
            [15840, -27040],
            [20320, -23840]
        ],
        "continent_matis": [
            [320, -7840],
            [6240, -320]
        ],
        "continent_tryker": [
            [13760, -34880],
            [20000, -29440]
        ],
        "continent_zorai": [
            [6880, -5920],
            [12480, -960]
        ],
        "place_pyr": [
            [18400, -24720],
            [19040, -24240]
        ],
        "continent_bagne": [
            [480, -11360],
            [1600, -9760]
        ],
        "continent_route_gouffre": [
            [5440, -16960],
            [7360, -9600]
        ],
        "continent_sources": [
            [2560, -11360],
            [3840, -9760]
        ],
        "continent_terre": [
            [160, -15840],
            [3040, -13120]
        ],
        "continent_nexus": [
            [7840, -8320],
            [9760, -6080]
        ],
        "place_yrkanis": [
            [4640, -3680],
            [4800, -3200]
        ],
        "place_natae": [
            [3600, -3840],
            [3840, -3680]
        ],
        "place_davae": [
            [4160, -4240],
            [4320, -4000]
        ],
        "place_avalae": [
            [4800, -4480],
            [4960, -4320]
        ],
        "place_dyron": [
            [16480, -24800],
            [16720, -24480]
        ],
        "place_thesos": [
            [19520, -26400],
            [19760, -26080]
        ],
        "place_avendale": [
            [18000, -31200],
            [18240, -30960]
        ],
        "place_crystabell": [
            [17760, -32000],
            [18000, -31760]
        ],
        "place_fairhaven": [
            [16960, -33280],
            [17440, -32720]
        ],
        "place_windermeer": [
            [15440, -33120],
            [15760, -32880]
        ],
        "place_zora": [
            [8480, -3040],
            [8800, -2720]
        ],
        "place_hoi_cho": [
            [9440, -3680],
            [9680, -3360]
        ],
        "place_jen_lai": [
            [8640, -3840],
            [8960, -3520]
        ],
        "place_min_cho": [
            [9760, -4320],
            [10080, -4000]
        ],
        "place_matis_island_1": [
            [14080, -1600],
            [15360, -320]
        ],
        "place_matis_island_2": [
            [15680, -1600],
            [16640, -320]
        ],
        "cont_newbieland": [
            [8160, -12320],
            [11360, -10240]
        ],
        "cont_kitiniere": [
            [1760, -17440],
            [3040, -16160]
        ]
    };

    // world conflict with grid.
    // (only used in php tile generator)
    delete worldZones['world'];

    Ryzom.XY.initialize(worldZones, serverZones);
})();
