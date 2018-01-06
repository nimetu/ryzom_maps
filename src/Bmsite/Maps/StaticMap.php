<?php
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

/**
 * Class StaticMap
 */
class StaticMap
{
    /**
     * Output image size limit
     */
    const MAX_WIDTH = 512;
    const MAX_HEIGHT = 512;

    /**
     * Feature limit
     */
    const MARKER_LIMIT = 1000;
    const PATH_LIMIT = 1000;

    /** @var StaticMapGenerator */
    protected $map;

    /** @var string */
    protected $etag;

    /**
     * @param string $tiledir
     * @param MapProjection $proj
     */
    public function __construct($tiledir, MapProjection $proj)
    {
        $ts = new FileTileStorage($tiledir);

        $this->map = new StaticMapGenerator($ts);
        $this->map->setProjection($proj);
    }

    /**
     * @param array $params
     */
    public function configure(array $params = array())
    {
        $etag = array();

        $mapmode = 'world';
        if (isset($params['mapmode'])) {
            $mapmode = $params['mapmode'];
            // set coordinate mode early as points are converted to image coords
            if ($mapmode == 'server') {
                // 1:1 projection
                $this->map->getProjection()->setWorldZones(array('grid' => array(array(0, 47520), array(108000, 0))));
            }
        }

        $mapname = 'atys';
        if (isset($params['maptype'])) {
            $val = $params['maptype'];
            switch ($val) {
                case 'atys':
                    $mapname = $val;
                    break;
                case 'satellite':
                    // @deprecated
                case 'atys_sp':
                    $mapname = 'atys_sp';
                    break;
                default:
                    // invalid, ignore
            }
        }
        $etag[] = $mapmode.$mapname;

        $this->map->getTileStorage()->setMapMode($mapmode, $mapname);

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
                    $this->map->setZoom($val);
                    break;
                case 'maxzoom':
                    $this->map->setMaxZoom($val);
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
                        $tmp[1] = (int)$tmp[0];
                    }
                    $width = min((int)$tmp[0], self::MAX_WIDTH);
                    $height = min((int)$tmp[1], self::MAX_HEIGHT);
                    $this->map->setSize($width, $height);

                    // modify value for etag
                    $val = $width.'x'.$height;
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

    /**
     * @return string
     */
    public function etag()
    {
        return $this->etag;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->map->getContentType();
    }

    /**
     * @return string
     */
    public function render()
    {
        $image = $this->map->render();

        return $image;
    }

    /**
     * @param mixed $markers
     *
     * @return Marker[]
     */
    protected function parseMarker($markers)
    {
        if (!is_array($markers)) {
            $markers = array($markers);
        }
        $result = array();

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
                            $marker->setColor($this->parseColor($pairs[1]));
                            break;
                        case 'label':
                            $marker->setLabel(trim($pairs[1]));
                            break;
                        case 'label_size':
                            $marker->setFontSize(max(3, min((int)$pairs[1], 20)));
                            break;
                        case 'label_color':
                            $fc = $this->parseColor(trim($pairs[1]));
                            if ($fc !== false) {
                                $marker->setFontColor($fc);
                            }
                            break;
                        case 'label_outline':
                            $fc = $this->parseColor(trim($pairs[1]));
                            if ($fc !== false) {
                                $marker->setFontOutline($fc);
                            }
                            break;
                        case 'icon':
                            // following icons are supported by icon generator
                            $allowed = array(
                                'building',
                                'camp',
                                'dig',
                                'lm_marker',
                                'npc',
                                'op_townhall',
                                'mektoub',
                                'portal',
                                'spawn',
                                'tp_kami',
                                'tp_karavan',
                                'egg',
                                'question',
                                // no-icon
                                'none'
                            );
                            $pairs[1] = trim($pairs[1]);
                            if (in_array($pairs[1], $allowed)) {
                                $marker->setIcon($pairs[1]);
                            }
                            break;
                        case 'circle':
                            $ellipse = explode(',', $pairs[1]);
                            if (!isset($ellipse[1])) {
                                $ellipse[1] = $ellipse[0];
                            }
                            $marker->circleRadius($ellipse[0], $ellipse[1]);
                            break;
                        case 'fillcolor':
                            $fc = $this->parseColor($pairs[1]);
                            if ($fc !== false) {
                                $marker->setCircleFillColor($fc);
                            }
                            break;
                        case 'weight':
                            $marker->setCircleStrokeWeight($pairs[1]);
                            break;
                    }
                } else {
                    $xy = $this->parseLocation($val);
                    if ($xy !== false) {
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
     * @param mixed $uri
     *
     * @return array
     */
    protected function parsePolygon($uri)
    {
        if (!is_array($uri)) {
            $uri = array($uri);
        }

        $result = array();

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
                        case 'color':
                            $fc = $this->parseColor($pairs[1]);
                            if ($fc !== false) {
                                $poly->color = $fc;
                            }
                            break;
                        case 'fillcolor':
                            $fc = $this->parseColor($pairs[1]);
                            if ($fc !== false) {
                                $poly->fillcolor = $fc;
                            }
                            break;
                        case 'weight': // limit weight to 1 to 20
                            $poly->weight = max(1, min((int)$pairs[1], 20));
                            break;
                    }
                } else {
                    $xy = $this->parseLocation($val);
                    if ($xy !== false) {
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
     * Parse location x/y
     *
     * @param string $val
     *
     * @return mixed
     */
    protected function parseLocation($val)
    {
        $ret = false;

        $pairs = explode(',', $val);
        if (count($pairs) == 2 && is_numeric($pairs[0]) && is_numeric($pairs[1])) {
            $x = (float)$pairs[0];
            $y = (float)$pairs[1];
            $proj = $this->map->getProjection();
            try {
                $ret = $proj->project(new Point($x, $y));
            } catch (\InvalidArgumentException $e) {
                // ignore point
            }
        }
        return $ret;
    }

    /**
     * Parse color code #RRGGBB[AA] (AA is optional) or color name
     *
     * @param $color
     *
     * @return Color
     */
    protected function parseColor($color)
    {
        $color = strtolower($color);
        $colorNames = array(
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
        );
        if (isset($colorNames[$color])) {
            $color = strtolower($colorNames[$color]);
        }

        if (preg_match('/^(?:#|0x)?([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})?$/', $color, $matches)) {
            $result = new Color();
            $result->r = hexdec($matches[1]);
            $result->g = hexdec($matches[2]);
            $result->b = hexdec($matches[3]);
            if (!empty($matches[4])) {
                $result->a = 127 - hexdec($matches[4]) / 255 * 127;
            } else {
                $result->a = 0; // fully visible
            }
            return $result;
        } else {
            return false;
        }
    }
}

/**
 * Parses QUERY_STRING and handles duplicate param names correctly
 *
 * @param string $url usually $_SERVER['QUERY_STRING']
 *
 * @return array parsed parameters
 */
function parse_parameters($url)
{
    $ret = array();
    $pairs = explode('&', str_replace('&amp;', '&', $url));
    foreach ($pairs as $pair) {
        $tmp = explode('=', $pair, 2);
        $k = urldecode($tmp[0]);
        $v = isset($tmp[1]) ? urldecode($tmp[1]) : '';
        // remove [] at the end of key and create empty ret[k] if it does not already exist
        if (substr($k, -2) == '[]') {
            $k = substr($k, 0, -2);
            // this must be array type, so lets make sure it's gonna be
            if (!isset($ret[$k])) {
                $ret[$k] = array();
            }
        }
        // handle duplicate name parameters / arrays
        if (isset($ret[$k])) {
            // convert ret[k] to array if not already
            if (is_scalar($ret[$k])) {
                $ret[$k] = array($ret[$k]);
            }
            $ret[$k][] = $v;
        } else {
            $ret[$k] = $v;
        }
    }
    return $ret;
}
