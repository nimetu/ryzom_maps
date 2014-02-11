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
 * Class Polygon
 */
class Polygon extends PointsCollection implements FeatureInterface
{
    public $weight;

    /**
     * @var Color
     */
    public $color;

    /**
     * @var Color
     */
    public $fillcolor;

    public $label;

    /** @var StaticMapGenerator */
    private $map;

    /**
     * @param int $weight
     * @param Color $color
     * @param Color $fillcolor
     */
    function __construct($weight = 1, $color = null, $fillcolor = null)
    {
        $this->weight = $weight;
        $this->color = $color ? : new Color(255, 255, 255);
        $this->fillcolor = $fillcolor;
        $this->xy = array();
        $this->label = false;
        $this->bbox = false;
    }

    /**
     * @param StaticMapGenerator $map
     */
    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    /**
     * @param resource $canvas
     */
    public function draw($canvas)
    {
        $scale = $this->map->getZoomScale();

        $vp = $this->map->getViewport();
        $xOffset = -$vp->left;
        $yOffset = -$vp->top;

        $poly = array();
        $xCenter = 0;
        $yCenter = 0;
        foreach ($this->xy as $pos) {
            $x = $xOffset + $pos->x * $scale;
            $y = $yOffset + $pos->y * $scale;

            $poly[] = $x;
            $poly[] = $y;

            $xCenter += $x;
            $yCenter += $y;
        }

        // we need at least 2 points to draw a line
        $nbPoints = count($poly) / 2;
        if ($nbPoints < 2) {
            return;
        }

        $xCenter = $xCenter / $nbPoints;
        $yCenter = $yCenter / $nbPoints;

        $color = $this->color->allocate($canvas);

        if ($this->fillcolor) {
            if ($nbPoints == 2) {
                // filled polygon needs at least 3 points
                $poly[] = $poly[0];
                $poly[] = $poly[1];
                ++$nbPoints;
            }
            imagesetthickness($canvas, 1);
            imagefilledpolygon($canvas, $poly, $nbPoints, $this->fillcolor->allocate($canvas));

            imagesetthickness($canvas, $this->weight);
            imagepolygon($canvas, $poly, $nbPoints, $color);
        } else {
            imagesetthickness($canvas, $this->weight);

            imagesetthickness($canvas, $this->weight);
            for ($i = 0; $i < count($poly) - 2; $i++) {
                imageline($canvas, $poly[$i], $poly[$i + 1], $poly[$i + 2], $poly[$i + 3], $color);
                $i++;
            }
        }

        if ($this->label) {
            // white/black
            $label = new Label($this->label);
            $label->setPos(new Point($xCenter, $yCenter));
            $label->setColor($this->color);
            $label->setOutline(new Color(0, 0, 0), 1);
            $label->draw($canvas);
        }
    }

}
