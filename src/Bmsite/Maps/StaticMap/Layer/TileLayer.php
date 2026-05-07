<?php

declare(strict_types=1);

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
use GdImage;

/**
 * Class TileLayer
 */
class TileLayer
{
    protected bool $debug = true;

    protected MapProjection $proj;

    protected string $mapname;

    protected string $tileExtension;

    protected int $zoom = 5;

    public function __construct(
        protected StaticMapGenerator $map,
        protected int $minZoom,
        protected int $maxZoom,
    ) {
        $this->proj = $map->getProjection();
        $this->mapname = $map->getMapName();
        $this->tileExtension = $map->getFormat();
        $this->debug = $map->getDebug();
    }

    /** @mago-expect lint:no-boolean-flag-parameter */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    public function getTileSize(): int
    {
        $size = TileStorageInterface::TILE_SIZE;
        if ($this->zoom < $this->minZoom) {
            $size = ($this->proj->scale($this->zoom) / $this->proj->scale($this->minZoom)) * $size;
        } elseif ($this->zoom > $this->maxZoom) {
            $size = ($this->proj->scale($this->zoom) / $this->proj->scale($this->maxZoom)) * $size;
        }
        return intval($size);
    }

    public function draw(GdImage $canvas, Bounds $vp, int $zoom)
    {
        $tileStorage = $this->map->getTileStorage();
        $tileStorage->setImageExt($this->tileExtension);
        $tileStorage->setMapName($this->mapname);

        $this->zoom = $zoom;

        $tileSize = $this->getTileSize();
        $tileZoom = max($this->minZoom, min($this->zoom, $this->maxZoom));

        // tile offset (px)
        $vpOffsetX = (floor($vp->left / $tileSize) * $tileSize) - $vp->left;
        $vpOffsetY = (floor($vp->top / $tileSize) * $tileSize) - $vp->top;

        // tiles affected
        $tx1 = (int) floor($vp->left / $tileSize);
        $ty1 = (int) floor($vp->top / $tileSize);
        $tx2 = (int) ceil($vp->right / $tileSize);
        $ty2 = (int) ceil($vp->bottom / $tileSize);

        for ($i = $tx1; $i < $tx2; $i++) {
            for ($j = $ty1; $j < $ty2; $j++) {
                $img = $tileStorage->get($tileZoom, $i, $j);
                if ($img === null) {
                    if (!$this->debug) {
                        continue;
                    }
                    $img = $this->debugTile($tileZoom, $i, $j);
                }

                $x1 = (int) ($vpOffsetX + (($i - $tx1) * $tileSize));
                $y1 = (int) ($vpOffsetY + (($j - $ty1) * $tileSize));
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
                    TileStorageInterface::TILE_SIZE,
                );
            }
        }
    }

    protected function debugTile(int $z, int $x, int $y): \GdImage
    {
        $r = $x * 255;
        $g = $y * 255;
        $b = $z * 255;
        $mod = max($r, max($g, $b));
        $r = intdiv($r * 255, $mod);
        $g = intdiv($g * 255, $mod);
        $b = intdiv($b * 255, $mod);

        $result = imagecreatetruecolor(TileStorageInterface::TILE_SIZE, TileStorageInterface::TILE_SIZE);
        $c = imagecolorallocatealpha($result, $r, $g, $b, 80);
        imagefill($result, 0, 0, $c);

        $c = imagecolorallocate($result, 255, 0, 0);
        imagestring($result, 1, 5, 15, "{$z}/{$x}/{$y}.{$this->tileExtension}", $c);

        return $result;
    }
}
