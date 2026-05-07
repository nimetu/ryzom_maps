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

class Polygon extends PointsCollection implements FeatureInterface
{
    public string $label = '';

    public int $label_size = 0;

    public Color $label_color;

    public Color $label_outline;

    private StaticMapGenerator $map;

    function __construct(
        public int $weight = 1,
        public Color $color = new Color(255, 255, 255),
        public ?Color $fillcolor = null,
    ) {
        //$this->color ??= new Color(255, 255, 255);
        $this->fillcolor = $fillcolor;
        $this->xy = [];
        $this->label_color = $color;
        $this->label_color->a = 255;
        $this->label_outline = new Color(0, 0, 0);
    }

    /**
     * @param StaticMapGenerator $map
     */
    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    public function draw(GdImage $canvas)
    {
        $scale = $this->map->getZoomScale();

        $vp = $this->map->getViewport();
        $xOffset = -$vp->left;
        $yOffset = -$vp->top;

        /** @var int[] */
        $poly = [];
        $xCenter = 0;
        $yCenter = 0;
        foreach ($this->xy as $pos) {
            $x = $xOffset + ($pos->x * $scale);
            $y = $yOffset + ($pos->y * $scale);

            $poly[] = (int) $x;
            $poly[] = (int) $y;

            $xCenter += $x;
            $yCenter += $y;
        }

        // we need at least 2 points to draw a line
        $nbPoints = intdiv(count($poly), 2);
        if ($nbPoints < 2) {
            return;
        }

        $xCenter /= $nbPoints;
        $yCenter /= $nbPoints;

        $color = $this->color->allocate($canvas);

        if ($this->fillcolor !== null) {
            if ($nbPoints === 2) {
                // filled polygon needs at least 3 points
                $poly[] = $poly[0];
                $poly[] = $poly[1];
            }
            imagesetthickness($canvas, 1);
            imagefilledpolygon($canvas, $poly, $this->fillcolor->allocate($canvas));

            imagesetthickness($canvas, $this->weight);
            imagepolygon($canvas, $poly, $color);
        } else {
            imagesetthickness($canvas, $this->weight);
            for ($i = 0; $i < (count($poly) - 2); $i++) {
                imageline($canvas, $poly[$i], $poly[$i + 1], $poly[$i + 2], $poly[$i + 3], $color);
                $i++;
            }
        }

        if ($this->label) {
            $label = new Label($this->label);
            if ($this->label_size) {
                $label->setFontSize($this->label_size);
            }
            $label->setPos(new Point($xCenter, $yCenter));

            $label->setColor($this->label_color);
            $label->setOutline($this->label_outline, 1);

            $label->draw($canvas);
        }
    }
}
