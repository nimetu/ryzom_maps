# Ryzom Maps

* Static Map generator
* Javascript map using [Leaflet](https://leafletjs.com/)

To generate tile images, you need to use [RyzomMapTiles](https://github.com/nimetu/ryzom_map_tiles.git) repository.

To generate json files, run (--ryzom parameter points to directory where to find `*.bnp` files)

```
php bin/bmmaps.php bmmaps:json --ryzom=$HOME/.local/share/Ryzom/ryzom_live/data
```

To generate map-leaflet.js, run

```
php bin/build-map.php
```

# changes to v2

* `Ryzom.apiDomain` must be without trailing slash, ie (Ryzom.apiDomain = 'https://api.bmsite.net')
* `Ryzom.tileUrl` must be with leading slash, ie (Ryzom.tilePath = '/maps/{ver}/{mode}/{style}/{z}/{x}/{y}.{ext})
* Map tiles have version in url (ie. /maps/{ver}/{mode}/{style}/{z}/{x}/{y}.{ext})
* `Ryzom.TILE_URI` is replaced with Ryzom.getTileUrl() which return `Ryzom.apiDomain + Ryzom.tilePath`
* `Ryzom.map(id, options)` if `options.layers` is defined (even if empty), no automatic tile layers are added.
* Rendered map tiles are from zoom=5 to zoom=10. Set minNativeZoom=5, maxNativeZoom=10 in L.tileLayer options)
* Label tiles are rendered from zoom=5 to zoom=12
* Continent world map pixel coordinates have changed (world.json)


## License

	Copyright (c) 2014 Meelis Mägi <nimetu@gmail.com>

	RyzomMaps is free software; you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

[LGPLv3](http://opensource.org/licenses/LGPL-3.0)
