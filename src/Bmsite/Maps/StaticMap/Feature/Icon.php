<?php
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

/**
 * Class Icon
 */
class Icon implements FeatureInterface
{

    /** @var string */
    private $icon;

    /** @var Point */
    private $pos;

    /** @var Color */
    private $color;

    /** @var string */
    private $iconPath;

    /** @var StaticMapGenerator */
    private $map;

    /** @var array */
    private $size;

    /** @var bool */
    private $over;

    /**
     * @param string $icon
     * @param Point $pos
     * @param Color $color
     * @param bool $over;
     */
    public function __construct($icon, Point $pos = null, Color $color = null, $over = false)
    {
        $this->icon = $icon;
        $this->pos = $pos;
        $this->color = $color;
        $this->size = array(24, 24);
        $this->over = $over;

        $this->iconPath = __DIR__.'/../../Resources/icons';
    }

    /**
     * Return true if image file exists
     *
     * @return bool
     */
    public function isSupported()
    {
        return $this->over
            ? file_exists($this->getIconFullPath('_over.png'))
            : file_exists($this->getIconFullPath());
    }

    /**
     * @param StaticMapGenerator $map
     */
    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    /**
     * @param Point $pos
     */
    public function setPos(Point $pos)
    {
        $this->pos = $pos;
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @param string $path
     */
    public function setIconPath($path)
    {
        $this->iconPath = $path;
    }

    /**
     * @param string $ext
     *
     * @return string
     */
    public function getIconFullPath($ext = '.png')
    {
        return $this->iconPath.'/'.$this->icon.'/image'.$ext;
    }

    /**
     * @param Color $c
     */
    public function setColor(Color $c)
    {
        $this->color = $c;
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function setSize($width, $height)
    {
        $this->size = array($width, $height);
    }

    /**
     * @param bool $b
     */
    public function setOver($b)
    {
        $this->over = $b;
    }

    /**
     * @return array
     */
    public function getSize()
    {
        return $this->size;
    }

    /** {@inheritdoc} */
    public function draw($canvas)
    {
        if ($this->map) {
            $scale = $this->map->getZoomScale();
            $vp = $this->map->getViewport();
            $xOffset = -$vp->left;
            $yOffset = -$vp->top;
            $p = new Point($this->pos->x * $scale, $this->pos->y * $scale);
        } else {
            $p = $this->pos;
            $xOffset = 0;
            $yOffset = 0;
        }

        $icon = $this->loadIcon();
        if (!$icon) {
            return false;
        }
        $width = imagesx($icon);
        $height = imagesy($icon);

        $w = $this->size[0];
        $h = $this->size[1];

        $x = $p->x - $w / 2 + $xOffset;
        $y = $p->y - $h / 2 + $yOffset;

        if ($w != $width || $h != $height) {
            imagecopyresampled($canvas, $icon, $x, $y, 0, 0, $w, $h, $width, $height);
        } else {
            imagecopy($canvas, $icon, $x, $y, 0, 0, $width, $height);
        }
        imagedestroy($icon);

        return true;
    }

    /**
     * Load icon from file, apply colorize if needed
     *
     * @return resource|bool
     */
    private function loadIcon()
    {
        $imgFile = $this->getIconFullPath($this->over ? '_over.png'  : '.png');
        if (!file_exists($imgFile)) {
            return false;
        }

        $icon = imagecreatefrompng($imgFile);
        if ($this->color) {
            $this->colorize($icon);
        }

        return $icon;
    }

    /**
     * Tint input image $im with required color
     */
    private function colorize($icon)
    {
        $maskFile = $this->getIconFullPath('_mask.png');
        if (!file_exists($maskFile)) {
            return;
        }
        $mask = imagecreatefrompng($maskFile);
        $cr = $this->color->r;
        $cg = $this->color->g;
        $cb = $this->color->b;

        $w = imagesx($mask);
        $h = imagesy($mask);
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgba = imagecolorsforindex($mask, imagecolorat($mask, $x, $y));
                $r = $rgba['red'] * $cr / 255;
                $g = $rgba['green'] * $cg / 255;
                $b = $rgba['blue'] * $cb / 255;

                $max = max($r, $g, $b);
                if ($max > 255) {
                    $r = $r / $max * 255;
                    $g = $g / $max * 255;
                    $b = $b / $max * 255;
                }
                $c = imagecolorallocatealpha($icon, $r, $g, $b, $rgba['alpha']);
                imagesetpixel($icon, $x, $y, $c);
            }
        }

        imagedestroy($mask);
    }
}
