<?php

declare(strict_types=1);

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

class StaticMapGenerator
{
    const int BASE_ZOOM = 10;

    /**
     * Zoom range we allow from URL
     */
    const int MIN_ZOOM = 1;
    const int MAX_ZOOM = 12;

    /**
     * Zoom ranges for generated tile images
     */
    private int $tileLayerMinZoom = 5;

    private int $tileLayerMaxZoom = 10;

    private int $textLayerMinZoom = 5;

    private int $textLayerMaxZoom = 12;

    private bool $debug = false;

    private string $format = 'jpg';

    private ?string $lang = null;

    /** @var string[] */
    private array $languages = ['en', 'fr', 'de', 'es', 'ru'];

    private int $width = 256;

    private int $height = 256;

    private ?Point $center = null;

    private int $zoom = self::BASE_ZOOM;

    private bool $autoZoom = true;

    private int $autoMaxZoom = 7;

    private string $mapmode = 'world';

    private string $mapname = 'atys';

    private bool $drawCenterLines = false;

    /** @var Marker[] */
    private array $markers = [];

    /** @var Polygon[] */
    private array $polys = [];

    public Bounds $viewport;

    public ?Bounds $bounds = null;

    public int $hMargin = 10;

    public int $vMargin = 10;

    private MapProjection $proj;

    /**
     * @param TileStorageInterface $tileStorage
     */
    public function __construct(
        private TileStorageInterface $tileStorage,
    ) {
        $this->viewport = new Bounds();
    }

    public function setDebug(bool $state)
    {
        $this->debug = $state;
    }

    public function getDebug(): bool
    {
        return $this->debug;
    }

    public function setProjection(MapProjection $proj)
    {
        $this->proj = $proj;
    }

    public function setSize(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Set map zoom level, value between self::MIN_ZOOM and self::MAX_ZOOM
     */
    public function setZoom(int $zoom)
    {
        $this->zoom = max(self::MIN_ZOOM, min((int) $zoom, self::MAX_ZOOM));
        $this->autoZoom = false;
    }

    public function enableAutoZoom()
    {
        $this->autoZoom = true;
    }

    public function setMaxZoom(int $zoom)
    {
        $this->autoMaxZoom = $zoom;
    }

    public function getZoomScale(): float
    {
        return $this->proj->scale($this->zoom);
    }

    public function setCenter(Point $p)
    {
        $this->center = $p;
    }

    public function setFormat(string $format)
    {
        $format = strtolower(trim($format));
        if (in_array($format, ['png', 'jpg'], true)) {
            $this->format = $format;
        }
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * atys, atys_sp, atys_su, ...
     * lang/en, lang/fr, ...
     */
    public function setMapName(string $val)
    {
        $this->mapname = $val;
    }

    public function getMapName(): string
    {
        return $this->mapname;
    }

    /**
     * @param 'world'|'server' $val
     */
    public function setMapMode(string $val)
    {
        $this->mapmode = $val;
    }

    public function getMapMode(): string
    {
        return $this->mapmode;
    }

    /**
     * @param string $val 'auto' try to use browser language
     *                    empty string or null disables text layer
     *                    otherwise 2-char supported language code
     */
    public function setLanguage(?string $val)
    {
        if ($val === null) {
            $this->lang = null;
            return;
        }

        /** @var string $val */
        $val = strtolower($val);
        if ($val === 'auto') {
            $this->lang = $this->getBrowserLanguage();
        } elseif (in_array($val, $this->languages, true)) {
            $this->lang = $val;
        } else {
            $this->lang = null;
        }
    }

    public function setDrawCenterLines(bool $val)
    {
        $this->drawCenterLines = $val;
    }

    /**
     * If available, return first language from Accept-Language HTTP header
     */
    public function getBrowserLanguage(string $default = 'en'): string
    {
        return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? $default, 0, 2);
    }

    public function extend(Point $p)
    {
        $this->bounds ??= new Bounds($p->x, $p->y, $p->x, $p->y);
        $this->bounds->extend($p->x, $p->y);
    }

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
     * @param Marker[] $markers
     */
    public function addMarkers(array $markers)
    {
        foreach ($markers as $m) {
            $this->addMarker($m);
        }
    }

    public function addPolygon(Polygon $p)
    {
        foreach ($p->xy as $pos) {
            $this->extend($pos);
        }
        $p->setMap($this);

        $this->polys[] = $p;
    }

    /**
     * @param Polygon[] $polygons
     */
    public function addPolygons(array $polygons)
    {
        foreach ($polygons as $p) {
            $this->addPolygon($p);
        }
    }

    /**
     * If using auto zoom, then zoom becomes available after render()
     */
    public function getZoom(): ?int
    {
        return $this->autoZoom ? null : $this->zoom;
    }

    public function getProjection(): MapProjection
    {
        return $this->proj;
    }

    /**
     * Viewport becomes available after render()
     */
    public function getViewport(): Bounds
    {
        return $this->viewport;
    }

    public function getTileStorage(): TileStorageInterface
    {
        return $this->tileStorage;
    }

    public function getContentType(): string
    {
        return match ($this->format) {
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            default => 'image/jpeg',
        };
    }

    /**
     * Return rendered map as string
     */
    public function render(): string
    {
        $img = $this->_background();

        ob_start();
        switch ($this->format) {
            case 'png':
                imagepng($img, null, 9);
                break;
            case 'jpg': // fall thru
            default:
                imagejpeg($img, null, 85);
                break;
        }

        return (string) ob_get_clean();
    }

    private function getBoundsZoom(): int
    {
        $zoom = $this->autoMaxZoom;

        if ($this->bounds === null) {
            return $zoom;
        }

        // area in pixels that markers/polygons occupy
        $w = $this->bounds->getWidth();
        $h = $this->bounds->getHeight();

        // map image size in pixels
        while ($zoom > 1) {
            $scale = $this->proj->scale($zoom);
            $newW = (int) (($w * $scale) + $this->hMargin);
            $newH = (int) (($h * $scale) + $this->vMargin);
            if ($newW < $this->width && $newH < $this->height) {
                break;
            }
            $zoom--;
        }
        return $zoom;
    }

    /**
     * @throws \RuntimeException
     */
    protected function _background(): \GdImage
    {
        $canvas = imagecreatetruecolor($this->width, $this->height);
        if ($canvas === false) {
            throw new \RuntimeException("Unable to create output image at size [{$this->width}x{$this->height}]");
        }

        if ($this->center === null) {
            if ($this->bounds === null) {
                // no features on map, so take center point in world zone
                $world = $this->proj->getZoneBounds($this->mapmode === 'world' ? 'world' : 'grid');
                $this->bounds = clone $world;
            }

            $cx = ($this->bounds->left + $this->bounds->right) / 2;
            $cy = ($this->bounds->top + $this->bounds->bottom) / 2;
            $this->center = new Point($cx, $cy);
        } else {
            // this comes in handy with automatic zoom
            $this->extend($this->center);
        }

        if ($this->autoZoom) {
            $this->zoom = $this->getBoundsZoom();
        }

        //calculate viewport
        $scale = $this->getZoomScale();

        // calculate viewport
        $halfWidth = $this->width / 2;
        $halfHeight = $this->height / 2;

        // absolute coords for viewport
        $vpLeft = intval(($this->center->x * $scale) - $halfWidth);
        $vpTop = intval(($this->center->y * $scale) - $halfHeight);
        $vpRight = $vpLeft + $this->width;
        $vpBottom = $vpTop + $this->height;

        $this->viewport = new Bounds($vpLeft, $vpBottom, $vpRight, $vpTop);

        $this->draw($canvas);

        return $canvas;
    }

    protected function draw(\GdImage $canvas)
    {
        if ($this->autoZoom) {
            $this->zoom = $this->getBoundsZoom();
            $this->autoZoom = false;
        }

        $layer = new TileLayer($this, $this->tileLayerMinZoom, $this->tileLayerMaxZoom);
        $layer->draw($canvas, $this->viewport, $this->zoom);

        if ($this->lang) {
            $textLayer = new LangTileLayer($this, $this->textLayerMinZoom, $this->textLayerMaxZoom);
            $textLayer->setLanguage((string) $this->lang);
            $textLayer->draw($canvas, $this->viewport, $this->zoom);
        }

        foreach ($this->polys as $poly) {
            $poly->draw($canvas);
        }

        foreach ($this->markers as $marker) {
            $marker->draw($canvas);
        }

        if ($this->drawCenterLines) {
            $halfWidth = intval($this->width / 2);
            $halfHeight = intval($this->height / 2);
            $y = (int) imagecolorallocatealpha($canvas, 255, 255, 50, 100);
            imagesetthickness($canvas, 1);
            imageline($canvas, 0, $halfHeight, $this->width, $halfHeight, $y);
            imageline($canvas, $halfWidth, 0, $halfWidth, $this->height, $y);
        }
    }
}
