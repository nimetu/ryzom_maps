<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

$version = 'leaflet';

$factory = new \Bmsite\Maps\JavascriptApi\JavascriptFactory($version);
$js = $factory->dump();
save_js("map-{$version}.js", $js, true);

// prepare map areas loader
$loader = new \Bmsite\Maps\ResourceLoader;
use Bmsite\Maps\Tools\Console\Helper\ResourceHelper;

$areasFile = $loader->getFilePath('areas.json');
$areas = file_get_contents($areasFile);

$js = <<<EOF
(function() {
	Ryzom.XY.ingame_areas = $areas;
})();
EOF;

save_js("map-areas-${version}.js", $js, true);

// all done
exit;

/**
 * Save content of the $js into $file and calculate subresource integrity hash
 * which is saved in .txt file
 *
 * If minify is true, then save minified version to ${file}.min.js
 *
 * @see https://www.srihash.org/
 *
 * @param string $file
 * @param string $js
 * @param bool $minify
 */
function save_js($file, $js, $minify = false) {
	echo "Writing $file";
	file_put_contents($file, $js);

	$type = 'sha384';
	$hash = base64_encode(hash($type, $js, true));
	$txt = '<script src="'.$file.'" integrity="'.$type.'-'.$hash.'" crossorigin="anonymous"></script>';
	file_put_contents($file.'.txt', $txt);
	echo ": {$type}-{$hash}\n";

	if ($minify) {
		$packer = new JavascriptPacker($js);
		$js = $packer->pack();

		$file = substr($file, 0, -3).'.min.js';
		save_js($file, $js, false);
	}
}

