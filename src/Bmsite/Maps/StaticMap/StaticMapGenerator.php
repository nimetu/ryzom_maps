<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap;

use Bmsite\Maps\BaseTypes\Bounds;
use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\MapProjection;
use Bmsite\Maps\StaticMap\Feature\Marker;
use Bmsite\Maps\StaticMap\Feature\Polygon;
use Bmsite\Maps\StaticMap\Layer\LangTileLayer;
use Bmsite\Maps\StaticMap\Layer\TileLayer;
use Bmsite\Maps\Tiles\TileStorageInterface;

/**
 * Class StaticMapGenerator
 */
class StaticMapGenerator
{
    const BASE_ZOOM = 10;

    /**
     * Zoom range we allow from URL
     */
    const MIN_ZOOM = 1;
    const MAX_ZOOM = 12;
    /**
     * max zoom for generated tile images
     */
    const MAP_TILE_ZOOM = 11;
    const LANG_TILE_ZOOM = 12;

    /** @var bool */
    private $debug = false;

    /** @var string */
    private $format = 'jpg';

    /** @var string */
    private $lang = false;
    private $languages = array('en', 'fr', 'de', 'es', 'ru');

    private $width = 256;
    private $height = 256;

    /** @var Point */
    private $center;

    /** @var int */
    private $zoom;

    /** @var int */
    private $autoMaxZoom = 10;

    /** @var string */
    private $mapmode = 'world';

    /** @var string */
    private $mapname = 'atys';

    /** @var Marker[] */
    private $markers = array();

    /** @var Polygon[] */
    private $polys = array();

    /** @var Bounds */
    public $viewport;

    /** @var Bounds */
    public $bounds;

    /** margins for automatic zoom (+5 pixels on either side) */
    public $hMargin = 10;
    public $vMargin = 10;

    /** @var \Bmsite\Maps\MapProjection */
    private $proj;

    /** @var TileStorageInterface */
    private $tileStorage;

    /**
     * @param TileStorageInterface $tileStorage
     */
    public function __construct(TileStorageInterface $tileStorage)
    {
        $this->tileStorage = $tileStorage;
    }

    /**
     * @param bool $d
     */
    public function setDebug($d)
    {
        $this->debug = $d;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param MapProjection $proj
     */
    public function setProjection(MapProjection $proj)
    {
        $this->proj = $proj;
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function setSize($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param int $zoom
     */
    public function setZoom($zoom)
    {
        $this->zoom = max(self::MIN_ZOOM, min((int)$zoom, self::MAX_ZOOM));
    }

    /**
     * @param int $zoom
     */
    public function setMaxZoom($zoom)
    {
        $this->autoMaxZoom = $zoom;
    }

    /**
     * @return float
     */
    public function getZoomScale()
    {
        return $this->proj->scale($this->zoom);
    }

    /**
     * @param Point $p
     */
    public function setCenter(Point $p)
    {
        $this->center = $p;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $format = strtolower(trim($format));
        if (in_array($format, array('png', 'jpg'))) {
            $this->format = $format;
        }
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * atys, atys_sp, atys_su, ...
     * lang/en, lang/fr, ...
     *
     * @param string $val
     */
    public function setMapName($val)
    {
        $this->mapname = $val;
    }

    /**
     * @return string
     */
    public function getMapName()
    {
        return $this->mapname;
    }

    /**
     * world, server
     *
     * @param string $val
     */
    public function setMapMode($val)
    {
        $this->mapmode = $val;
    }

    /**
     * @return string
     */
    public function getMapMode()
    {
        return $this->mapmode;
    }

    /**
     * @param string $val
     */
    public function setLanguage($val)
    {
        $val = strtolower($val);
        if (in_array($val, $this->languages)) {
            $this->lang = $val;
        } else {
            if ($val == 'auto') {
                $this->lang = $this->getBrowserlanguage();
            } else {
                if ($val == '') {
                    $this->lang = false;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getBrowserLanguage()
    {
        return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }

    /**
     * @param Point $p
     */
    public function extend(Point $p)
    {
        if ($this->bounds === null) {
            $this->bounds = new Bounds($p->x, $p->y, $p->x, $p->y);
        } else {
            $this->bounds->extend($p->x, $p->y);
        }
    }

    /**
     * @param Marker $m
     */
    public function addMarker(Marker $m)
    {
        // use ingame coords here
        foreach ($m->xy as $pos) {
            $this->extend($pos);
        }
        $m->setMap($this);

        $this->markers[] = $m;
    }

    /**
     * @param array $markers
     */
    public function addMarkers(array $markers)
    {
        foreach ($markers as $m) {
            $this->addMarker($m);
        }
    }

    /**
     * @param Polygon $p
     */
    public function addPolygon(Polygon $p)
    {
        foreach ($p->xy as $pos) {
            $this->extend($pos);
        }
        $p->setMap($this);

        $this->polys[] = $p;
    }

    /**
     * @param array $polygons
     */
    public function addPolygons(array $polygons)
    {
        foreach ($polygons as $p) {
            $this->addPolygon($p);
        }
    }

    /**
     * @return int
     */
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
     * @return MapProjection
     */
    public function getProjection()
    {
        return $this->proj;
    }

    /**
     * @return Bounds
     */
    public function getViewport()
    {
        return $this->viewport;
    }

    /**
     * @return TileStorageInterface
     */
    public function getTileStorage()
    {
        return $this->tileStorage;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        switch ($this->format) {
            case 'png':
                $type = 'image/png';
                break;
            case 'jpg': // fall thru
            default   :
                $type = 'image/jpeg';
                break;
        }
        return $type;
    }

    /**
     * @return string
     */
    public function render()
    {
        $img = $this->_background();

        ob_start();
        switch ($this->format) {
            case 'png':
                imagepng($img, null, 9);
                break;
            case 'jpg': // fall thru
            default   :
                imagejpeg($img, null, 85);
                break;
        }
        imagedestroy($img);
        $img = ob_get_clean();

        return $img;
    }

    /**
     * @return int
     */
    private function getBoundsZoom()
    {
        $zoom = $this->autoMaxZoom;

        // area in pixels that markers/polygons occupy
        $w = $this->bounds->getWidth();
        $h = $this->bounds->getHeight();

        // map image size in pixels
        while ($zoom > 1) {
            $scale = $this->proj->scale($zoom);
            $newW = (int)($w * $scale + $this->hMargin);
            $newH = (int)($h * $scale + $this->vMargin);
            if ($newW < $this->width && $newH < $this->height) {
                break;
            }
            $zoom--;
        }
        return $zoom;
    }

    /**
     * @return resource
     * @throws \RuntimeException
     */
    protected function _background()
    {
        $canvas = imagecreatetruecolor($this->width, $this->height);
        if ($canvas === false) {
            throw new \RuntimeException("Unable to create output image at size [{$this->width}x{$this->height}]");
        }

        if ($this->center === null) {
            if ($this->bounds === null) {
                // no features on map, so take center point in world zone
                $world = $this->proj->getZoneBounds('world');
                $this->bounds = clone $world;
            }

            $cx = ($this->bounds->left + $this->bounds->right) / 2;
            $cy = ($this->bounds->top + $this->bounds->bottom) / 2;
            $this->center = new Point($cx, $cy);
        } else {
            // this comes in handy with automatic zoom
            $this->extend($this->center);
        }

        if ($this->zoom === null) {
            $this->zoom = $this->getBoundsZoom();
        }

        //calculate viewport
        $scale = $this->getZoomScale();

        // calculate viewport
        $halfWidth = $this->width / 2;
        $halfHeight = $this->height / 2;

        // absolute coords for viewport
        $vpLeft = $this->center->x * $scale - $halfWidth;
        $vpTop = $this->center->y * $scale - $halfHeight;
        $vpRight = $vpLeft + $this->width;
        $vpBottom = $vpTop + $this->height;

        $this->viewport = new Bounds($vpLeft, $vpBottom, $vpRight, $vpTop);

        $this->draw($canvas);

        return $canvas;
    }

    /**
     * @param resource $canvas
     */
    protected function draw($canvas)
    {
        // background map layer with maxZoom = 9
        $layer = new TileLayer($this, self::MAP_TILE_ZOOM);
        $layer->draw($canvas, $this->viewport, $this->zoom);

        if ($this->lang) {
            // background text layer with maxZoom = 10
            $textLayer = new LangTileLayer($this, self::LANG_TILE_ZOOM);
            $textLayer->setLanguage($this->lang);
            $textLayer->draw($canvas, $this->viewport, $this->zoom);
        }

        foreach ($this->polys as $poly) {
            $poly->draw($canvas);
        }

        foreach ($this->markers as $marker) {
            $marker->draw($canvas);
        }

        // FIXME: remove center lines
        $halfWidth = $this->width / 2;
        $halfHeight = $this->height / 2;
        $y = imagecolorallocatealpha($canvas, 255, 255, 50, 100);
        imagesetthickness($canvas, 1);
        imageline($canvas, 0, $halfHeight, $this->width, $halfHeight, $y);
        imageline($canvas, $halfWidth, 0, $halfWidth, $this->height, $y);
    }

}
