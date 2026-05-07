<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps;

use Bmsite\Maps\BaseTypes\Color;
use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\StaticMap\Feature\Marker;
use Bmsite\Maps\StaticMap\Feature\Polygon;
use Bmsite\Maps\StaticMap\StaticMapGenerator;
use Bmsite\Maps\Tiles\FileTileStorage;

class StaticMap
{
    /**
     * Output image size limit
     */
    const int MAX_WIDTH = 512;
    const int MAX_HEIGHT = 512;

    /**
     * Feature limit
     */
    const int MARKER_LIMIT = 1000;
    const int PATH_LIMIT = 1000;

    protected StaticMapGenerator $map;

    protected string $tiledir;

    protected string $etag;

    public function __construct(string $tiledir, MapProjection $proj)
    {
        $this->tiledir = $tiledir;
        $ts = new FileTileStorage($this->tiledir);

        $this->map = new StaticMapGenerator($ts);
        $this->map->setProjection($proj);
    }

    /**
     * @param array<string,string> $params
     */
    public function configure(array $params = [])
    {
        $mapname = $this->parseMaptype($params);
        $mapmode = $this->parseMapmode($params);
        if ($mapmode === 'server') {
            // 1:1 projection
            $this->map->getProjection()->setWorldZones(['grid' => [[0, 47520], [108000, 0]]]);
        }

        $etag = [
            $this->tiledir,
            $mapmode,
            $mapname,
        ];

        $this->map->getTileStorage()->setMapMode($mapmode);
        $this->map->getTileStorage()->setMapName($mapname);

        $this->map->setSize(256, 256);
        $this->map->setMapMode($mapmode);
        $this->map->setMapName($mapname);

        // if ryzom ingame browser is used, then set default format to png
        if (isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'Ryzom')) {
            $this->map->setFormat('png');
        }

        // parse rest of url
        foreach ($params as $var => $val) {
            $found = true;
            switch ($var) {
                case 'zoom':
                    $this->map->setZoom((int) $val);
                    break;
                case 'maxzoom':
                    $this->map->setMaxZoom((int) $val);
                    break;
                case 'center':
                    $center = $this->parseLocation($val);
                    if ($center) {
                        $this->map->setCenter($center);
                    }
                    break;
                case 'size':
                    $tmp = explode('x', $val);
                    if (!isset($tmp[1])) {
                        $tmp[1] = (int) $tmp[0];
                    }
                    $width = min((int) $tmp[0], self::MAX_WIDTH);
                    $height = min((int) $tmp[1], self::MAX_HEIGHT);
                    $this->map->setSize($width, $height);

                    // modify value for etag
                    $val = $width . 'x' . $height;
                    break;
                case 'markers':
                    $markers = $this->parseMarker($val);
                    $this->map->addMarkers($markers);
                    break;
                case 'path':
                    $polys = $this->parsePolygon($val);
                    $this->map->addPolygons($polys);
                    break;
                case 'format':
                    $this->map->setFormat($val);
                    break;
                case 'lang':
                case 'language':
                    $this->map->setLanguage($val);
                    break;
                default:
                    $found = false;
                    break;
            }

            if ($found) {
                $etag[] = $val;
            }
        }

        $this->etag = sha1(serialize($etag));
    }

    public function etag(): string
    {
        return $this->etag;
    }

    public function getContentType(): string
    {
        return $this->map->getContentType();
    }

    public function render(): string
    {
        return $this->map->render();
    }

    /**
     * @param array<string,string> $params
     *
     * @return 'server'|'world'
     */
    protected function parseMapmode(array $params): string
    {
        $default = 'world';

        $mode = $params['mapmode'] ?? $default;
        if ($mode === 'world' || $mode === 'server') {
            return $mode;
        }

        return $default;
    }

    /**
     * @param array<string,string> $params
     *
     * @return 'atys'|'atys_sp'
     */
    protected function parseMaptype(array $params): string
    {
        $default = 'atys';

        $type = $params['maptype'] ?? $default;
        if ($type === 'atys' || $type === 'atys_sp') {
            return $type;
        }

        // @deprecated 'satellite'
        if ($type === 'satellite') {
            return 'atys_sp';
        }

        return $default;
    }

    /**
     * @param string|string[] $markers
     *
     * @return Marker[]
     */
    protected function parseMarker(mixed $markers): array
    {
        if (!is_array($markers)) {
            $markers = [$markers];
        }
        $result = [];

        $limit = self::MARKER_LIMIT;
        foreach ($markers as $line) {
            $marker = new Marker();

            $tmp = explode('|', $line);
            foreach ($tmp as $val) {
                if (strpos($val, ':') !== false) {
                    $pairs = explode(':', $val);
                    if (!isset($pairs[1])) {
                        continue;
                    }

                    switch (strtolower($pairs[0])) {
                        case 'color':
                            $c = $this->parseColor($pairs[1]);
                            if ($c !== null) {
                                $marker->setColor($c);
                            }
                            break;
                        case 'label':
                            $marker->setLabel(trim($pairs[1]));
                            break;
                        case 'label_size':
                            $marker->setFontSize(max(3, min((int) $pairs[1], 20)));
                            break;
                        case 'label_color':
                            $fc = $this->parseColor(trim($pairs[1]));
                            if ($fc) {
                                $marker->setFontColor($fc);
                            }
                            break;
                        case 'label_outline':
                            $fc = $this->parseColor(trim($pairs[1]));
                            if ($fc) {
                                $marker->setFontOutline($fc);
                            }
                            break;
                        case 'icon':
                            $pairs[1] = trim($pairs[1]);
                            if (preg_match('/^([a-zA-Z0-9_]+)?$/', $pairs[1])) {
                                $marker->setIcon($pairs[1]);
                            }
                            break;
                        case 'circle':
                            /** @var numeric-string[] $ellipse */
                            $ellipse = explode(',', $pairs[1]);
                            if (!isset($ellipse[1])) {
                                $ellipse[1] = $ellipse[0];
                            }
                            $marker->circleRadius((float) $ellipse[0], (float) $ellipse[1]);
                            break;
                        case 'fillcolor':
                            $fc = $this->parseColor($pairs[1]);
                            if ($fc) {
                                $marker->setCircleFillColor($fc);
                            }
                            break;
                        case 'weight':
                            $marker->setCircleStrokeWeight((int) $pairs[1]);
                            break;
                    }
                } else {
                    $xy = $this->parseLocation($val);
                    if ($xy) {
                        $marker->addPoint($xy);
                    }
                }
                $limit--;
                if ($limit < 0) {
                    break;
                }
            }

            $result[] = $marker;

            if ($limit < 0) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param string|string[] $uri
     *
     * @return Polygon[]
     */
    protected function parsePolygon(mixed $uri): array
    {
        if (!is_array($uri)) {
            $uri = [$uri];
        }

        $result = [];

        $limit = self::PATH_LIMIT;
        foreach ($uri as $path) {
            $poly = new Polygon();
            $tmp = explode('|', $path);
            foreach ($tmp as $val) {
                if (strpos($val, ':') !== false) {
                    $pairs = explode(':', $val);
                    if (!isset($pairs[1])) {
                        continue;
                    }
                    switch (strtolower($pairs[0])) {
                        case 'label':
                            $poly->label = trim($pairs[1]);
                            break;
                        case 'label_size':
                            $poly->label_size = max(3, min((int) $pairs[1], 20));
                            break;
                        case 'label_color':
                            $fc = $this->parseColor($pairs[1]);
                            if ($fc) {
                                $poly->label_color = $fc;
                            }
                            break;
                        case 'label_outline':
                            $fc = $this->parseColor(trim($pairs[1]));
                            if ($fc) {
                                $poly->label_outline = $fc;
                            }
                            break;
                        case 'color':
                            $fc = $this->parseColor($pairs[1]);
                            if ($fc) {
                                $poly->color = $fc;
                            }
                            break;
                        case 'fillcolor':
                            $fc = $this->parseColor($pairs[1]);
                            if ($fc) {
                                $poly->fillcolor = $fc;
                            }
                            break;
                        case 'weight': // limit weight to 1 to 20
                            $poly->weight = max(1, min((int) $pairs[1], 20));
                            break;
                    }
                } else {
                    $xy = $this->parseLocation($val);
                    if ($xy) {
                        $poly->addPoint($xy);
                    }
                }
                $limit--;
                if ($limit < 0) {
                    break;
                }
            }

            $result[] = $poly;
            if ($limit < 0) {
                break;
            }
        }

        return $result;
    }

    /**
     * Parse location x/y '17200,-33000' and return matched zones
     */
    protected function parseLocation(string $val): ?Point
    {
        $pairs = explode(',', $val);
        if (count($pairs) === 2 && is_numeric($pairs[0]) && is_numeric($pairs[1])) {
            $x = (float) $pairs[0];
            $y = (float) $pairs[1];
            $proj = $this->map->getProjection();
            try {
                return $proj->project(new Point($x, $y));
            } catch (\InvalidArgumentException $e) {
                // ignore point
            }
        }

        return null;
    }

    /**
     * Parse color code #RRGGBB[AA] (AA is optional) or color name
     */
    protected function parseColor(string $color): ?Color
    {
        $color = strtolower($color);
        $colorNames = [
            'black' => '#000000',
            'white' => '#FFFFFF',
            'red' => '#FF0000',
            'yellow' => '#FFFF00',
            'lime' => '#00FF00',
            'aqua' => '#00FFFF',
            'cyan' => '#00FFFF',
            'blue' => '#0000FF',
            'fuchsia' => '#FF00FF',
            'magenta' => '#FF00FF',
            'grey' => '#808080',
            'gray' => '#808080',
            'silver' => '#C0C0C0',
            'maroon' => '#800000',
            'olive' => '#808000',
            'green' => '#008000',
            'teal' => '#008080',
            'navy' => '#000080',
            'purple' => '#800080',
        ];
        if (isset($colorNames[$color])) {
            $color = strtolower($colorNames[$color]);
        }

        $matches = null;
        if (preg_match('/^(?:#|0x)?([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})?$/', $color, $matches)) {
            $result = new Color((int) hexdec($matches[1]), (int) hexdec($matches[2]), (int) hexdec($matches[3]));
            if (!empty($matches[4])) {
                $result->a = (int) hexdec($matches[4]);
            }
            return $result;
        }

        return null;
    }
}

/**
 * Parses QUERY_STRING and handles duplicate param names correctly
 *
 * @param string $url usually from $_SERVER['QUERY_STRING']
 *
 * @return array parsed parameters
 */
function parse_parameters(string $url): array
{
    $ret = [];
    $pairs = explode('&', str_replace('&amp;', '&', $url));
    foreach ($pairs as $pair) {
        $tmp = explode('=', $pair, 2);
        $k = urldecode($tmp[0]);
        $v = isset($tmp[1]) ? urldecode($tmp[1]) : '';
        // remove [] at the end of key and create empty ret[k] if it does not already exist
        if (substr($k, -2) === '[]') {
            $k = substr($k, 0, -2);
            // this must be array type, so lets make sure it's gonna be
            if (!isset($ret[$k])) {
                $ret[$k] = [];
            }
        }
        // handle duplicate name parameters / arrays
        if (isset($ret[$k])) {
            // convert ret[k] to array if not already
            if (is_scalar($ret[$k])) {
                $ret[$k] = [$ret[$k]];
            }
            $ret[$k][] = $v;
        } else {
            $ret[$k] = $v;
        }
    }
    return $ret;
}
