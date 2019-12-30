<?php

use Bmsite\Maps\MapProjection;
use Bmsite\Maps\ResourceLoader;
use Bmsite\Maps\StaticMap;

require_once __DIR__.'/../vendor/autoload.php';

$tiledir = '/srv/websites/maps/htdocs/webroot/api/tiles';

$params = array(
    'maptype' => 'atys',
    'size' => '512x300',
    'maxzoom' => 10,
    'markers' => array(
        'icon:lm_marker|color:0xff5050|label:Glue|8749.71,-3163.33',
        'icon:lm_marker|color:0xff5050|label:Zun|8812.64,-3243.71',
        'icon:lm_marker|color:0xff5050|label:Koorin|8732.69,-3387.78',
        'icon:lm_marker|color:0xff5050|label:Splinter|8909.87,-3028.54',
        'icon:lm_marker|color:0xff5050|label:Oath|8991.02,-3053.90',
        'icon:map_event|label:event|8900,-3050',
        'icon:map_event|color:0xff0000|label:redEvent|8900,-3000',
        'icon:map_event|color:0x00ff00|label:greenEvent|8900,-3100',
        'icon:map_event|color:0x0000ff|label:blueEvent|8900,-3200',
        'icon:map_event|color:0xffffff|label:whiteEvent|8900,-3300',
    ),
);

$loader = new ResourceLoader();

$proj = new MapProjection();
$proj->setServerZones($loader->loadJson('server.json'));
$proj->setWorldZones($loader->loadJson('world.json'));

$map = new StaticMap($tiledir, $proj);
$map->configure($params);
$etag = $map->etag();
echo "etag:[{$etag}]\n";

$img = $map->render();
file_put_contents(__DIR__.'/../static-map-result.png', $img);
$size = strlen($img);
echo "size:[$size]\n";

