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

## License

	Copyright (c) 2014 Meelis Mägi <nimetu@gmail.com>

	RyzomMaps is free software; you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

[LGPLv3](http://opensource.org/licenses/LGPL-3.0)
