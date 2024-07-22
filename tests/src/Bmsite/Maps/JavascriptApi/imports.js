
import L from './leaflet-src.js';

// from leaflet
function getGlobalObject() {                                                                                                                                                
	if (typeof globalThis !== 'undefined') { return globalThis; }                                                                                                         
	if (typeof self !== 'undefined') { return self; }                                                                                                                     
	if (typeof window !== 'undefined') { return window; }                                                                                                                 
	if (typeof global !== 'undefined') { return global; }                                                                                                                 
																																										  
	throw new Error('Unable to locate global object.');                                                                                                                   
}
// create global 'window' in node
let g = getGlobalObject();
if (typeof g.window === 'undefined') {
	g.window = g;
}

require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/Ryzom.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/Ryzom.XY.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/Ryzom.Map.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/Ryzom.Icon.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/OpenLayers.Geometry.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/geo/projection/RyzomWorld.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/geo/projection/RyzomServer.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/geo/crs/RyzomWorld.js');
require('./../../../../../src/Bmsite/Maps/JavascriptApi/Leaflet/geo/crs/RyzomServer.js');

