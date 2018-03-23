<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

$version = 'leaflet';

$factory = new \Bmsite\Maps\JavascriptApi\JavascriptFactory($version);
$js = $factory->dump();
$mapjs = "map-{$version}.js";
echo "Writing $mapjs\n";
file_put_contents($mapjs, $js);

// <script src="map-leaflet.js" integrity="sha256-...."></script>
$srihash = base64_encode(hash('sha256', $js, true));
echo "sha256-{$srihash}\n";


