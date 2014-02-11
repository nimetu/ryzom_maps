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
 * Class Marker
 */
class Marker extends PointsCollection implements FeatureInterface
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var float
     */
    protected $circleRadiusHoriz;
    /**
     * @var float
     */
    protected $circleRadiusVert;

    /**
     * @var Color
     */
    protected $circleFillColor;
    /**
     * @var int
     */
    protected $circleStrokeWeight = 1;

    /**
     * @var int
     */
    protected $fontSize;

    /**
     * @var Color
     */
    protected $fontColor;

    /**
     * outline color
     *
     * @var Color
     */
    protected $fontOutline;

    /**
     * @var Color
     */
    protected $color;

    /**
     * @var array
     */
    public $xy;

    /** @var StaticMapGenerator */
    private $map;

    /**
     * @param bool $label
     * @param bool $color
     * @param string $icon
     */
    function __construct($label = false, $color = null, $icon = null)
    {
        if ($icon === null) {
            $icon = 'lm_marker';
        }
        $this->label = $label;
        $this->color = $color;
        $this->icon = $icon;
        $this->xy = array();

        $this->fontSize = 7;
        $this->fontColor = new Color(255, 255, 255);
        $this->fontOutline = new Color(20, 20, 20);
    }

    /**
     * @param StaticMapGenerator $map
     */
    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    /** {@inheritdoc} */
    public function draw($canvas)
    {
        $icon = false;
        if ($this->icon && $this->icon !== 'none') {
            $icon = new Icon($this->icon);
            if ($this->color) {
                $icon->setColor($this->color);
            }
        }

        $label = false;
        if ($this->label) {
            $label = new Label($this->label);
            $label->setColor($this->fontColor);
            $label->setOutline($this->fontOutline, 1);
            $label->setOffset(0, 5);
            // background with same color as marker, but 50% transparency
            if ($this->color) {
                $bg = clone $this->color;
            } else {
                $bg = new Color(0, 0, 0);
            }
            $bg->a = 127;
            $label->setBackground($bg);
        }

        $scale = $this->map->getZoomScale();
        $vp = $this->map->getViewport();
        $xOffset = -$vp->left;
        $yOffset = -$vp->top;
        foreach ($this->xy as $point) {
            $p = new Point($point->x * $scale + $xOffset, $point->y * $scale + $yOffset);

            if ($this->circleRadiusHoriz) {
                // TODO: stroke width, circle color, fill color
                if ($this->color) {
                    $c = $this->color->allocate($canvas);
                } else {
                    $c = imagecolorallocate($canvas, 255, 0, 0);
                }
                // at least 1px circle is drawed
                $h = max(1, $this->circleRadiusHoriz * 2 * $this->map->getZoomScale());
                $v = max(1, $this->circleRadiusVert * 2 * $this->map->getZoomScale());

                if ($this->circleFillColor) {
                    $fc = $this->circleFillColor->allocate($canvas);
                    imagefilledellipse($canvas, $p->x, $p->y, $h, $v, $fc);
                }
                imagesetthickness($canvas, $this->circleStrokeWeight);
                imageellipse($canvas, $p->x, $p->y, $h, $v, $c);
            }

            if ($icon) {
                $icon->setPos($p);
                $icon->draw($canvas);
            }
            if ($label) {
                $label->setPos($p);

                // +5 is in pixel scale
                $label->draw($canvas);
            }
        }
    }

    /**
     * @param string $text
     */
    public function setLabel($text)
    {
        $this->label = $text;
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @param float $h
     * @param float $v
     */
    public function circleRadius($h, $v)
    {
        $this->circleRadiusHoriz = $h;
        $this->circleRadiusVert = $v;
    }

    public function setCircleFillColor($fc)
    {
        $this->circleFillColor = $fc;
    }

    public function setCircleStrokeWeight($w)
    {
        $this->circleStrokeWeight = $w;
    }

    public function setColor($c)
    {
        $this->color = $c;
    }

    public function setFontSize($s)
    {
        $this->fontSize = $s;
    }

    public function setFontColor($fc)
    {
        $this->fontColor = $fc;
    }

    public function setFontOutline($fc)
    {
        $this->fontOutline = $fc;
    }
}
