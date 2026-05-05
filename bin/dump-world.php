<?php

echo "--- world\n";
dump_file(dirname(__DIR__) . '/src/Bmsite/Maps/Resources/world.json');
echo "--- server\n";
dump_file(dirname(__DIR__) . '/src/Bmsite/Maps/Resources/server.json');

function dump_file($inPath)
{
    $json = json_decode(file_get_contents($inPath), true);
    $rows = [];
    foreach ($json as $k => $m) {
        $x = $m[0][0];
        $y = $m[1][1];
        $w = $m[1][0] - $x;
        $h = abs($m[0][1] - $y);
        $scale = 1;
        $rows[$k] = sprintf(
            '%-40s | V:% 7d, H:% 7d (% 7d, % 7d)',
            $k,
            $x * $scale,
            $y * $scale,
            $w * $scale,
            $h * $scale,
        );
    }
    unset($rows['grid']);
    ksort($rows);
    foreach ($rows as $row) {
        echo "{$row}\n";
    }
}
