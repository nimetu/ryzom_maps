<?php

// read regions.json (x,y, w, h) and output world.json (l, b, r, t)
//
$regions = read_regions_csv(__DIR__.'/regions.csv');
$outPath = dirname(__DIR__)."/src/Bmsite/Maps/Resources/world.json";

function read_regions_csv($csv) {
    $lines = file($csv);
	$result = [];
	$scale = 1;
    foreach($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
		}
		if (preg_match('/([^:]+):(.*)$/', $line, $match)) {
			if ($match[1] === 'scale') {
				$scale = floatval($match[2]);
				if ($scale <= 0) {
					throw new \UnexpectedValueException("scale value should be above 0, got '$scale'");
				}
			}
			continue;
		}
        $parts = preg_split('/[ \t]+/', $line, PREG_SPLIT_NO_EMPTY);
        if (!preg_match('/(\s+)?(?P<x>\d+)\s+(?P<y>\d+)\s+(?P<w>\d+)\s+(?P<h>\d+)\s+(?<name>.*)/', $line, $match)) {
            echo "WRN: line not matched '{$line}'\n";
            continue;
        }
        $result[$match['name']] = [
            [$match['x'] / $scale, $match['y'] / $scale],
            [$match['w'] / $scale, $match['h'] / $scale],
        ];
	}
    return $result;
}

$tpl = '
    "{key}": [
        [{l}, {b}],
        [{r}, {t}]
    ]';

$world = [];
$json = [];
foreach($regions as $k => $m) {
    $x = $m[0][0];
    $y = $m[0][1];
    $w = $m[1][0];
    $h = $m[1][1];
    $json[$k] = strtr($tpl, [
        '{key}' => $k,
        '{l}' =>   $x,      '{b}' => $y + $h,
        '{r}' =>   $x + $w, '{t}' => $y,
    ]);
}

// This is where unknown zones are translated to.
// Can be any size and in any location on map, preferably not matching already configured area.
if (!isset($json['grid'])) {
    $json['grid'] = strtr($tpl, [
          '{key}' => 'grid',
          '{l}' => -108000, '{b}' => 47520,
          '{r}' => 0, '{t}' => 0,
    ]);
}

$json = '{' . join(",", $json) . '}';

file_put_contents($outPath, $json);

