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
 * @param {String} options.rzTileUri - map tiles uri template to use, default is Ryzom.TILE_URI
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
        rzTileUri: Ryzom.TILE_URI,
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

