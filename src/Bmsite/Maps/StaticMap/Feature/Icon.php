<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Feature;

use Bmsite\Maps\BaseTypes\Color;
use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\StaticMap\StaticMapGenerator;
use GdImage;

class Icon implements FeatureInterface
{
    private string $iconPath;

    private ?StaticMapGenerator $map = null;

    /** @var array{int,int} */
    private array $size;

    public function __construct(
        private string $icon,
        private ?Point $pos = null,
        private ?Color $color = null,
        private $over = false,
    ) {
        $this->size = [24, 24];

        $this->iconPath = __DIR__ . '/../../Resources/icons';
    }

    /**
     * Return true if image file exists
     */
    public function isSupported(): bool
    {
        return $this->over ? file_exists($this->getIconFullPath('_over.png')) : file_exists($this->getIconFullPath());
    }

    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    public function setPos(Point $pos)
    {
        $this->pos = $pos;
    }

    public function setIcon(string $icon)
    {
        $this->icon = $icon;
    }

    public function setIconPath(string $path)
    {
        $this->iconPath = $path;
    }

    public function getIconFullPath(string $ext = '.png'): string
    {
        return $this->iconPath . '/' . $this->icon . '/image' . $ext;
    }

    public function setColor(Color $c)
    {
        $this->color = $c;
    }

    public function setSize(int $width, int $height)
    {
        $this->size = [$width, $height];
    }

    /** @mago-expect lint:no-boolean-flag-parameter */
    public function setOver(bool $b)
    {
        $this->over = $b;
    }

    /**
     * @return array{int,int}
     */
    public function getSize(): array
    {
        return $this->size;
    }

    public function draw(GdImage $canvas)
    {
        if ($this->pos === null) {
            return;
        }

        $p = $this->pos;
        $xOffset = 0;
        $yOffset = 0;
        if ($this->map) {
            $scale = $this->map->getZoomScale();
            $vp = $this->map->getViewport();
            $xOffset = -$vp->left;
            $yOffset = -$vp->top;
            $p = new Point($this->pos->x * $scale, $this->pos->y * $scale);
        }

        $icon = $this->loadIcon();
        if (!$icon) {
            return false;
        }

        $width = imagesx($icon);
        $height = imagesy($icon);

        $w = $this->size[0];
        $h = $this->size[1];

        $x = intval($p->x - ($w / 2) + $xOffset);
        $y = intval($p->y - ($h / 2) + $yOffset);

        if ($w !== $width || $h !== $height) {
            imagecopyresampled($canvas, $icon, $x, $y, 0, 0, $w, $h, $width, $height);
        } else {
            imagecopy($canvas, $icon, $x, $y, 0, 0, $width, $height);
        }
    }

    /**
     * Load icon from file, apply colorize if needed
     */
    private function loadIcon(): GdImage|false
    {
        $imgFile = $this->getIconFullPath($this->over ? '_over.png' : '.png');
        if (!file_exists($imgFile)) {
            return false;
        }

        $icon = imagecreatefrompng($imgFile);
        $this->colorize($icon);

        return $icon;
    }

    /**
     * Tint input image $im with required color
     */
    private function colorize(GdImage $icon)
    {
        if ($this->color === null) {
            return;
        }
        $cr = $this->color->r;
        $cg = $this->color->g;
        $cb = $this->color->b;

        $maskFile = $this->getIconFullPath('_mask.png');
        if (!file_exists($maskFile)) {
            return;
        }
        $mask = imagecreatefrompng($maskFile);

        $w = imagesx($mask);
        $h = imagesy($mask);
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $c = imagecolorat($mask, $x, $y);
                if ($c === false) {
                    continue;
                }
                /** @var array{red:int, green:int, blue: int, alpha: int} $rgba */
                $rgba = imagecolorsforindex($mask, $c);
                $r = ($rgba['red'] * $cr) / 255;
                $g = ($rgba['green'] * $cg) / 255;
                $b = ($rgba['blue'] * $cb) / 255;

                $max = max($r, $g, $b);
                if ($max > 255) {
                    $r = ($r / $max) * 255;
                    $g = ($g / $max) * 255;
                    $b = ($b / $max) * 255;
                }
                $c = imagecolorallocatealpha($icon, (int) $r, (int) $g, (int) $b, (int) $rgba['alpha']);
                if ($c !== false) {
                    imagesetpixel($icon, $x, $y, $c);
                }
            }
        }
    }
}
