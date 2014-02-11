<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Layer;

use Bmsite\Maps\BaseTypes\Bounds;
use Bmsite\Maps\MapProjection;
use Bmsite\Maps\StaticMap\StaticMapGenerator;
use Bmsite\Maps\Tiles\TileStorageInterface;

/**
 * Class TileLayer
 */
class TileLayer
{
    protected $debug = true;

    /** @var MapProjection */
    protected $proj;

    /** @var string */
    protected $mapname = 'atys';

    /** @var string */
    protected $tileExtension = 'jpg';

    /** @var int */
    protected $zoom;

    /** @var int */
    protected $maxZoom;

    /**
     * @param \Bmsite\Maps\StaticMap\StaticMapGenerator $map
     * @param int $maxZoom
     */
    public function __construct(StaticMapGenerator $map, $maxZoom)
    {
        $this->map = $map;
        $this->proj = $map->getProjection();
        $this->debug = $map->getDebug();
        $this->mapname = $map->getMapName();

        $this->maxZoom = $maxZoom;
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return float
     */
    public function getTileSize()
    {
        $size = TileStorageInterface::TILE_SIZE;
        if ($this->zoom > $this->maxZoom) {
            $size = $this->proj->scale($this->zoom) / $this->proj->scale($this->maxZoom) * $size;
        }
        return $size;
    }

    /**
     * @param $canvas
     * @param Bounds $vp
     * @param int $zoom
     */
    public function draw($canvas, Bounds $vp, $zoom)
    {
        $tileStorage = $this->map->getTileStorage();
        $tileStorage->setImageExt($this->tileExtension);
        $tileStorage->setMapName($this->mapname);

        $this->zoom = $zoom;

        $tileSize = $this->getTileSize($this->zoom);
        $tileZoom = min($this->zoom, $this->maxZoom);

        // tile offset (px)
        $vpOffsetX = floor($vp->left / $tileSize) * $tileSize - $vp->left;
        $vpOffsetY = floor($vp->top / $tileSize) * $tileSize - $vp->top;

        // tiles affected
        $tx1 = floor($vp->left / $tileSize);
        $ty1 = floor($vp->top / $tileSize);
        $tx2 = ceil($vp->right / $tileSize);
        $ty2 = ceil($vp->bottom / $tileSize);

        for ($i = $tx1; $i < $tx2; $i++) {
            for ($j = $ty1; $j < $ty2; $j++) {
                $img = $tileStorage->get($tileZoom, $i, $j);
                if (!$img && $this->debug) {
                    $img = $this->debugTile($tileZoom, $i, $j);
                }

                if ($img) {
                    $x1 = $vpOffsetX + ($i - $tx1) * $tileSize;
                    $y1 = $vpOffsetY + ($j - $ty1) * $tileSize;
                    imagecopyresampled(
                        $canvas,
                        $img,
                        $x1,
                        $y1,
                        0,
                        0,
                        $tileSize,
                        $tileSize,
                        TileStorageInterface::TILE_SIZE,
                        TileStorageInterface::TILE_SIZE
                    );
                    imagedestroy($img);
                }
            }
        }
    }

    /**
     * @param int $z
     * @param int $x
     * @param int $y
     *
     * @return bool|resource
     */
    protected function debugTile($z, $x, $y)
    {
        $r = $x * 255;
        $g = $y * 255;
        $b = $z * 255;
        $mod = max($r, max($g, $b));
        $r = $r * 255 / $mod;
        $g = $g * 255 / $mod;
        $b = $b * 255 / $mod;

        $result = imagecreatetruecolor(TileStorageInterface::TILE_SIZE, TileStorageInterface::TILE_SIZE);
        $c = imagecolorallocatealpha($result, $r, $g, $b, 80);
        imagefill($result, 0, 0, $c);

        $c = imagecolorallocate($result, 255, 0, 0);
        imagestring($result, 1, 5, 15, "{$z}/{$x}/{$y}.{$this->tileExtension}", $c);

        return $result;
    }
}
