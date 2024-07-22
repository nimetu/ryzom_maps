<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

/**
 * Simple wrapper around JavascriptPacker
 */
class JsMinifier implements \Bmsite\Maps\JavascriptApi\MinifyInterface
{
    public function minify($str) {
        $packer = new JavascriptPacker($str);
        return $packer->pack();
    }
}

$path = dirname(__DIR__);

// javascript
$ver = 'leaflet';
$mapjs = get_mapjs($ver);
save_js($mapjs['js'],    $mapjs['hash'],    "${path}/map-{$ver}.js");
save_js($mapjs['jsmin'], $mapjs['hashmin'], "${path}/map-{$ver}.min.js");

$mapjs_areas = get_mapjs_areas();
save_js($mapjs_areas['js'],    $mapjs_areas['hash'],    "${path}/map-areas-{$ver}.js");
save_js($mapjs_areas['jsmin'], $mapjs_areas['hashmin'], "${path}/map-areas-{$ver}.min.js");

exit;

function save_js($js, $hash, $file) {
    echo "Writing $file";
    file_put_contents($file, $js);

    $txt = js_script($file, $hash);

    file_put_contents($file.'.txt', $txt);
    echo ": {$hash}\n";
}

/**
 * Build map.js.
 *
 * Return normal and minified versions
 *
 * @param string $ver
 *
 * @return string[] [js, min]
 */
function get_mapjs($ver = 'leaflet') {
    $factory = new \Bmsite\Maps\JavascriptApi\JavascriptFactory($ver, new JsMinifier);

    $ret = [];
    $ret['js'] = $factory->dump();
    $ret['hash'] = $factory->integrity();

    $ret['jsmin'] = $factory->minify();
    $ret['hashmin'] = $factory->integrity(true);

    return $ret;
}

/**
 * Build areas.json loader
 *
 * Return normal and minified versions
 *
 * @return string[] [js, min]
 */
function get_mapjs_areas() {
    $minify = new JsMinifier;
    // prepare map areas loader
    $loader = new \Bmsite\Maps\ResourceLoader;

    $areasFile = $loader->getFilePath('areas.json');
    $areas = file_get_contents($areasFile);

    $js = "(function() {\n\tRyzom.XY.ingame_areas = $areas;\n})();";
    $hash = integrity_hash($js);

    $jsmin = $minify->minify($js);
    $hashmin = integrity_hash($jsmin);

    return [
        'js' => $js,
        'hash' => $hash,
        'jsmin' => $jsmin,
        'hashmin' => $hashmin,
    ];
}

/**
 * Calculate integrity hash for given string
 *
 * @param string $js
 *
 * @return string
 */
function integrity_hash($js) {
    $type = 'sha384';
    $hash = base64_encode(hash($type, $js, true));
    return "$type-$hash";
}

/**
 * Return html <script> tag for js file
 *
 * @param string $file
 * @param string $hash
 *
 * @return string
 */
function js_script($file, $hash) {
    return '<script src="'.$file.'" integrity="'.$hash.'" crossorigin="anonymous"></script>';
}

