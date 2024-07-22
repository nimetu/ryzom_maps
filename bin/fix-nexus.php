<?php

$jsonFile = __DIR__.'/../src/Bmsite/Maps/Resources/server.json';
$json = json_decode(file_get_contents($jsonFile), true);
$json['continent_nexus'] = [
    [7840, -8320], [9760,-6080]
];
file_put_contents($jsonFile, json_encode($json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));

