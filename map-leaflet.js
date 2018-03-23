/* Copyright (c) 2006-2013 by OpenLayers Contributors (see authors.txt for
 * full list of contributors). Published under the 2-clause BSD license.
 * See license.txt in the OpenLayers distribution or repository for the
 * full text of the license. */

/**
 * Snippets from OpenLayers source
 */
var OpenLayers = {};
OpenLayers.Geometry = {};

/**
 * Function: OpenLayers.Geometry.distanceToSegment
 *
 * Parameters:
 * point - {Object} An object with x and y properties representing the
 *     point coordinates.
 * segment - {Object} An object with x1, y1, x2, and y2 properties
 *     representing endpoint coordinates.
 *
 * Returns:
 * {Object} An object with distance, along, x, and y properties.  The distance
 *     will be the shortest distance between the input point and segment.
 *     The x and y properties represent the coordinates along the segment
 *     where the shortest distance meets the segment. The along attribute
 *     describes how far between the two segment points the given point is.
 */
OpenLayers.Geometry.distanceToSegment = function (point, segment) {
    var result = OpenLayers.Geometry.distanceSquaredToSegment(point, segment);
    result.distance = Math.sqrt(result.distance);
    return result;
};

/**
 * Function: OpenLayers.Geometry.distanceSquaredToSegment
 *
 * Usually the distanceToSegment function should be used. This variant however
 * can be used for comparisons where the exact distance is not important.
 *
 * Parameters:
 * point - {Object} An object with x and y properties representing the
 *     point coordinates.
 * segment - {Object} An object with x1, y1, x2, and y2 properties
 *     representing endpoint coordinates.
 *
 * Returns:
 * {Object} An object with squared distance, along, x, and y properties.
 *     The distance will be the shortest distance between the input point and
 *     segment. The x and y properties represent the coordinates along the
 *     segment where the shortest distance meets the segment. The along
 *     attribute describes how far between the two segment points the given
 *     point is.
 */

OpenLayers.Geometry.distanceSquaredToSegment = function (point, segment) {
    var x0 = point.x;
    var y0 = point.y;
    var x1 = segment.x1;
    var y1 = segment.y1;
    var x2 = segment.x2;
    var y2 = segment.y2;
    var dx = x2 - x1;
    var dy = y2 - y1;
    var along = ((dx * (x0 - x1)) + (dy * (y0 - y1))) /
        (Math.pow(dx, 2) + Math.pow(dy, 2));
    var x, y;
    if (along <= 0.0) {
        x = x1;
        y = y1;
    } else if (along >= 1.0) {
        x = x2;
        y = y2;
    } else {
        x = x1 + along * dx;
        y = y1 + along * dy;
    }
    return {
        distance: Math.pow(x - x0, 2) + Math.pow(y - y0, 2),
        x: x, y: y,
        along: along
    };
};
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
    apiDomain: 'https://api.bmsite.net/',

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
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Return new instance or L.Map object with Ryzom layers
 *
 * @param {String} id - id of html element where map is displayed
 * @param {Object} options - options passed to L.Map
 * @param {String} options.rzMode - map coordinate mode (world or server)
 * @param {Boolean} options.rzWorld - display world map (atys style tiles)
 * @param {Boolean} options.rzSatellite - display satellite map (atys_sp style tiles)
 * @param {String} options.rzLang - language tiles to use, set to false to disable
 * @param {String} options.rzTileUri - map tiles uri template to use, default is Ryzom.apiDomain+Ryzom.tilePath
 *
 * @return {L.Map}
 */
Ryzom.map = function(id, options) {
    options = L.extend({
        // world | server
        // also selects L.CRS.RyzomServer or L.CRS.RyzomWorld
        rzMode: 'world',
        rzWorld: true,
        rzSatellite: true,
        rzLang: 'en',
        rzTileUri: Ryzom.apiDomain + Ryzom.tilePath,
        //
        center: [6500, -12500],
        zoom: 5,
        minZoom: 3,
        maxZoom: 12,
        layers: []
    }, options);

    // select coordinate systems for used in tiles
    if (options.rzMode == 'world') {
        options.crs = L.CRS.RyzomWorld;
    } else {
        options.crs = L.CRS.RyzomServer;
    }

    var tileLayers = {};
    var overlays = {};

    var worldLayer = false, satLayer = false, langLayer = false;
    if (options.rzWorld) {
        worldLayer = Ryzom.tileLayer(options.rzTileUri, {
            mode: options.rzMode
        });
        options.layers.push(worldLayer);
        tileLayers['Atys'] = worldLayer;
    }

    if (options.rzSatellite) {
        satLayer = Ryzom.tileLayer(options.rzTileUri, {
            mode: options.rzMode,
            style: 'atys_sp'
        });
        if (!options.rzWorld) {
            options.layers.push(satLayer);
        }

        tileLayers['Satellite'] = satLayer;
    }

    if (options.rzLang) {
        langLayer = Ryzom.tileLayer(options.rzTileUri, {
            mode: options.rzMode,
            style: 'lang_' + options.rzLang
        });
        options.layers.push(langLayer);

        overlays['Labels'] = langLayer;
    }

    var map = new L.Map(id, options);
    if (tileLayers['Atys'] && tileLayers['Satellite']) {
        L.control.layers(tileLayers, overlays).addTo(map);
    }
    return map;
};

/**
 * Creates new Ryzom tile layer.
 *
 * @param {String} uri - tile uri template, default is Ryzom.TILE_URI
 * @param {Object} options - options passed to leaflet L.TileLayer
 * @param {String} options.mode - tile coordinate mode, either 'world' (default) or 'server'
 * @param {String} options.style - tile style, 'atys' (default) or 'atys_sp', 'lang_en', etc
 * @param {String} options.ext - tile extension (jpg, png), autodetected from style if empty
 *
 * @returns {L.TileLayer}
 */
Ryzom.tileLayer = function(uri, options) {
    if (typeof uri == "object") {
        options = uri;
        uri = Ryzom.TILE_URI;
    }
    options = L.extend({
        // ryzom options
        mode: 'world',
        style: 'atys',
        // leaflet options
        continuousWorld: true,
        zIndex: 1
    }, options);

    var isLang = options.style && options.style.match(/^lang_/);
    if (!options.maxNativeZoom) {
        options.maxNativeZoom = isLang ? 12 : 11;
    }
    if (!options.ext) {
        options.ext = isLang ? 'png' : 'jpg';
    }
    return L.tileLayer(uri, options);
};

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Return new instance of L.Icon object for icons from icon api
 *
 * @param {String} name - icon name, default lm_marker
 * @param {String} color - (optional) color in 3 char hex (RGB), default none
 * @param {Integer} size - (optional) size, default 24
 *
 * @return {L.Icon}
 */
Ryzom.icon = function(name, color, size) {
    name = name || 'lm_marker';

    // verify name for api
    var iconNames = [
        'building', 'camp', 'dig', 'lm_marker',
        'mektoub', 'npc', 'op_townhall', 'portal',
        'spawn', 'tp_kami', 'tp_karavan',
        'egg', 'question'
    ];
    var found = false;
    // var found = iconNames.indexOf(name) > -1;
    for (var i=0;i<iconNames.length;++i) {
        if (iconNames[i] == name) {
            found = true;
            break;
        }
    }
    if (!found) {
        name = 'lm_marker';
    }

    if (typeof color == "number") {
        size = color;
        color = false;
    }

    // verify color for api
    if (color && !color.match(/^[0-9a-zA-F]{3}$/)) {
        color = false;
    }

    // verify size for api
    if (typeof size == "undefined") {
        size = 24;
    }
    if (size != 16 && size != 24 && size !=32) {
        size = 24;
    }

    var halfSize = Math.floor(size /2) ;
    var uri = Ryzom.apiDomain + 'maps/icon/' + size + '/';
    if (color) {
        uri += color + '/';
    }
    uri += name + '.png';

    return L.icon({
        iconUrl: uri,
        //iconRetinaUrl: '',
        iconSize: [size, size],
        iconAnchor: [halfSize, halfSize],
        popupAnchor: [0, -halfSize]
        //shadowUrl: '',
        //shadowRetinaUrl: '',
        //shadowSize: [w, h],
        //shadowANchor: [w/2, h/2]
    });
};

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

/**
 * Display mouse position
 */
L.Control.MousePosition = L.Control.extend({
    options: {
        position: 'bottomright',
        marker: null
    },
    onAdd: function (map) {
        this._container = L.DomUtil.create('div', 'leaflet-control-mouseposition');
        L.DomEvent.disableClickPropagation(this._container);
        map.on('mousemove', this._onMouseMove, this);

        return this._container;
    },
    onRemove: function (map) {
        map.off('mousemove', this._onMouseMove);
    },
    _onMouseMove: function (e) {
        // server coordinates
        var x = Math.floor(e.latlng.lat);
        var y = Math.floor(e.latlng.lng);

        var regions = Ryzom.XY.findIngameRegion(e.latlng.lat, e.latlng.lng);
        var html = 'x:' + x + ', y:' + y + ' (' + regions.join(',') + ')';

        var p = this._map.project(e.latlng, 10);
        html += "<br>Image @(zoom=10): x:" + Math.floor(p.x) + ', y:' + Math.floor(p.y);

        // convert image back to latlng
        //var l = this._map.unproject(p, 10);
        //html += "<br>(new) latlng:" + Math.floor(l.lat) + ', ' + Math.floor(l.lng);

        if (this.options.marker) {
            this.options.marker.setLatLng(l);
        }

        this._container.innerHTML = html;
        //console.debug('event', e);
    }
});
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
    },

    // leaflet 1.0
    bounds: (function () {
        return L.bounds([-100000, -100000], [100000, 100000]);
    })()
};

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
    },

    // leaflet 1.0
    bounds: (function () {
        return L.bounds([-100000, -100000], [100000, 100000]);
    })()
};

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
