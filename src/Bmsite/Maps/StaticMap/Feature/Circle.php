<?php

declare(strict_types=1);

namespace Bmsite\Maps\StaticMap\Feature;

use Bmsite\Maps\BaseTypes\Color;
use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\StaticMap\StaticMapGenerator;
use GdImage;

class Circle implements FeatureInterface
{
    private Color $color;

    private ?Color $fillColor = null;

    private float $radiusHoriz = 1;

    private float $radiusVert = 1;

    private int $thickness = 1;

    private ?StaticMapGenerator $map = null;

    private ?Point $pos = null;

    public function __construct(?Color $color = null)
    {
        $this->setColor($color);
    }

    public function setColor(?Color $color)
    {
        $this->color = $color ?? new Color(255, 0, 0);
    }

    public function setFillColor(?Color $color)
    {
        $this->fillColor = $color;
    }

    public function noFillColor()
    {
        $this->fillColor = null;
    }

    public function setRadius(float $h, ?float $v)
    {
        $this->radiusHoriz = $h;
        $this->radiusVert = $v ?? $h;
    }

    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    public function setPos(Point $p)
    {
        $this->pos = $p;
    }

    public function draw(GdImage $canvas)
    {
        if ($this->pos === null) {
            return;
        }

        if ($this->radiusHoriz <= 0 || $this->radiusVert <= 0) {
            return;
        }

        $pos = $this->pos;

        $c = $this->color->allocate($canvas);

        // at least 1px circle is drawed
        $scale = $this->map?->getZoomScale() ?? 1;
        $h = (int) max(1, $this->radiusHoriz * 2 * $scale);
        $v = (int) max(1, $this->radiusVert * 2 * $scale);

        if ($this->fillColor) {
            $fc = $this->fillColor->allocate($canvas);
            imagefilledellipse($canvas, (int) $pos->x, (int) $pos->y, $h, $v, $fc);
        }

        imagesetthickness($canvas, $this->thickness);
        imageellipse($canvas, (int) $pos->x, (int) $pos->y, $h, $v, $c);
    }
}
