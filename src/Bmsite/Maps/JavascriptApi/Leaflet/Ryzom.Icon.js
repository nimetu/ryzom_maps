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

