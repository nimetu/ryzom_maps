<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Feature;

use Bmsite\Maps\BaseTypes\Bounds;
use Bmsite\Maps\BaseTypes\Color;
use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\StaticMap\StaticMapGenerator;

/**
 * Class Label
 */
class Label implements FeatureInterface
{
    /** @var Point */
    private $pos;

    /** @var int */
    private $fontSize;

    /** @var string */
    private $fontFamily;

    /** @var string */
    private $text;

    /** @var Color */
    private $color;

    /** @var Color */
    private $background;

    /** @var Color */
    private $outlineColor;

    /** @var int */
    private $outlineWidth;

    /** @var StaticMapGenerator */
    private $map;

    /**
     * @param string $text
     * @param Point $pos
     * @param Color $color
     */
    public function __construct($text, Point $pos = null, Color $color = null)
    {
        $this->pos = $pos;
        $this->text = $text;

        $this->setColor($color ? : new Color(255, 255, 255));
        $this->setOutline(new Color(0, 0, 0), 1);

        $this->fontSize = 7;
        $this->fontFamily = 'Vera';
        $this->fontPath = __DIR__.'/../../Resources/fonts';

        $this->relOffsetX = 0;
        $this->relOffsetY = 0;
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function setOffset($x, $y)
    {
        $this->relOffsetX = $x;
        $this->relOffsetY = $y;
    }

    /**
     * @param StaticMapGenerator $map
     */
    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    /**
     * @param Point $p
     */
    public function setPos(Point $p)
    {
        $this->pos = $p;
    }

    /**
     * @param Color $c
     */
    public function setColor(Color $c)
    {
        $this->color = $c;
    }

    /**
     * @param Color $c
     */
    public function setBackground(Color $c)
    {
        $this->background = $c;
    }

    /**
     * @param Color $c
     * @param int $w
     */
    public function setOutline(Color $c = null, $w = 1)
    {
        $this->outlineColor = $c;
        $this->outlineWidth = $w;
    }

    /**
     * @param int $s
     */
    public function setFontSize($s)
    {
        $this->fontSize = $s;
    }

    /**
     * @param string $f
     */
    public function setFontFamily($f)
    {
        $this->fontFamily = $f;
    }

    /**
     * @param string $p
     */
    public function setFontPath($p)
    {
        $this->fontPath = $p;
    }

    /**
     * @return string
     */
    public function getFont()
    {
        return $this->fontPath.'/'.$this->fontFamily.'.ttf';
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

        $font = $this->getFont();
        $bbox = $this->getTextBbox();

        // bottom-left corner
        $x = $p->x - ($bbox->getWidth() / 2) - $bbox->left + $xOffset + $this->relOffsetX;
        $y = $p->y + ($bbox->getHeight() / 2) - $bbox->bottom + $yOffset + $this->relOffsetY;

        if ($this->background) {
            $s = $this->background->allocate($canvas);
            imagefilledrectangle(
                $canvas,
                $x + $bbox->left,
                $y + $bbox->bottom + 1,
                $x + $bbox->right - 1,
                $y + $bbox->top,
                $s
            );
        }

        if ($this->outlineColor) {
            $s = $this->outlineColor->allocate($canvas);
            $dd = $this->outlineWidth;
            for ($dx = -$dd; $dx <= $dd; $dx++) {
                for ($dy = -$dd; $dy <= $dd; $dy++) {
                    imagettftext($canvas, $this->fontSize, 0, $x + $dx, $y + $dy, $s, $font, $this->text);
                }
            }
        }

        $c = $this->color->allocate($canvas);
        imagettftext($canvas, $this->fontSize, 0, $x, $y, $c, $font, $this->text);

        return true;
    }

    /**
     * @return Bounds
     */
    protected function getTextBbox()
    {
        $font = $this->getFont();

        $bbox = imagettfbbox($this->fontSize, 0, $font, $this->text);

        return new Bounds($bbox[0], $bbox[1], $bbox[4], $bbox[5]);
    }
}
