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

class Marker extends PointsCollection implements FeatureInterface
{
    protected float $circleRadiusHoriz = 0;

    protected float $circleRadiusVert = 0;

    protected ?Color $circleFillColor = null;

    protected int $circleStrokeWeight = 1;

    protected int $fontSize;

    protected Color $fontColor;

    protected Color $fontOutline;

    private StaticMapGenerator $map;

    function __construct(
        private ?string $label = null,
        private ?Color $color = null,
        private string $icon = 'lm_marker',
    ) {
        $this->fontSize = 7;
        $this->fontColor = new Color(255, 255, 255);
        $this->fontOutline = new Color(20, 20, 20);
    }

    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    public function draw(GdImage $canvas)
    {
        $icon = null;
        if ($this->icon !== 'none') {
            $over = substr($this->icon, -5) === '_over';
            if ($over) {
                $icon = new Icon(substr($this->icon, 0, -5));
            } else {
                $icon = new Icon($this->icon);
            }
            if (!$icon->isSupported()) {
                $icon->setIcon('lm_marker');
            }
            $icon->setOver($over);

            if ($this->color !== null) {
                $icon->setColor($this->color);
            }
        }

        $label = null;
        if ($this->label) {
            $label = new Label($this->label);
            $label->setColor($this->fontColor);
            $label->setOutline($this->fontOutline, 1);
            // background with same color as marker, but 50% transparency
            if ($this->color) {
                $bg = clone $this->color;
            } else {
                $bg = new Color(0, 0, 0);
            }
            $bg->a = 127;
            $label->setBackground($bg);
        }

        $circle = null;
        if ($this->circleRadiusHoriz > 0 && $this->circleRadiusVert > 0) {
            $circle = new Circle();
            $circle->setRadius($this->circleRadiusHoriz, $this->circleRadiusVert);
            $circle->setColor($this->color);
            $circle->setFillColor($this->circleFillColor);
        }

        $scale = $this->map->getZoomScale();
        $vp = $this->map->getViewport();
        $xOffset = -$vp->left;
        $yOffset = -$vp->top;
        foreach ($this->xy as $point) {
            $p = new Point(($point->x * $scale) + $xOffset, ($point->y * $scale) + $yOffset);

            if ($circle) {
                $circle->setScale($scale);
                $circle->setPos($p);
                $circle->draw($canvas);
            }

            if ($icon) {
                $icon->setPos($p);
                $icon->draw($canvas);
            }

            if ($label) {
                $label->setPos($p);

                // offset is in pixel scale
                if ($icon) {
                    $iconSize = $icon->getSize();
                    $label->setOffset(0, intdiv($iconSize[1], 2));
                }

                $label->draw($canvas);
            }
        }
    }

    public function setLabel(string $text)
    {
        $this->label = $text;
    }

    public function setIcon(string $icon)
    {
        $this->icon = $icon;
    }

    public function circleRadius(float $h, float $v)
    {
        $this->circleRadiusHoriz = $h;
        $this->circleRadiusVert = $v;
    }

    public function setCircleFillColor(Color $fc)
    {
        $this->circleFillColor = $fc;
    }

    public function setCircleStrokeWeight(int $w)
    {
        $this->circleStrokeWeight = $w;
    }

    public function setColor(Color $c)
    {
        $this->color = $c;
    }

    public function setFontSize(int $s)
    {
        $this->fontSize = $s;
    }

    public function setFontColor(Color $fc)
    {
        $this->fontColor = $fc;
    }

    public function setFontOutline(Color $fc)
    {
        $this->fontOutline = $fc;
    }
}
