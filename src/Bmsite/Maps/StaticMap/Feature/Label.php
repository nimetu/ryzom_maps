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

use Bmsite\Maps\BaseTypes\Bounds;
use Bmsite\Maps\BaseTypes\Color;
use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\StaticMap\StaticMapGenerator;
use GdImage;

/**
 * Class Label
 */
class Label implements FeatureInterface
{
    private ?Point $pos;

    private int $fontSize;

    private string $fontFamily;

    private string $text;

    private Color $color;

    private ?Color $background = null;

    private ?Color $outlineColor = null;

    /** @var int */
    private int $outlineWidth;

    private ?StaticMapGenerator $map = null;

    private string $fontPath;

    private int $relOffsetX;

    private int $relOffsetY;

    public function __construct(string $text, ?Point $pos = null, ?Color $color = null)
    {
        $this->pos = $pos;
        $this->text = $text;

        $this->setColor($color ?? new Color(255, 255, 255));
        $this->setOutline(new Color(0, 0, 0), 1);

        $this->fontSize = 7;
        $this->fontFamily = 'ryzom';
        $this->fontPath = __DIR__ . '/../../Resources/fonts';

        $this->relOffsetX = 0;
        $this->relOffsetY = 0;
    }

    public function setOffset(int $x, int $y)
    {
        $this->relOffsetX = $x;
        $this->relOffsetY = $y;
    }

    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    public function setPos(Point $p)
    {
        $this->pos = $p;
    }

    public function setColor(Color $c)
    {
        $this->color = $c;
    }

    public function setBackground(Color $c)
    {
        $this->background = $c;
    }

    public function setOutline(Color $c = null, int $w = 1)
    {
        $this->outlineColor = $c;
        $this->outlineWidth = $w;
    }

    public function setFontSize(int $s)
    {
        $this->fontSize = $s;
    }

    public function setFontFamily(string $f)
    {
        $this->fontFamily = $f;
    }

    public function setFontPath(string $p)
    {
        $this->fontPath = $p;
    }

    public function getFont(): string
    {
        return $this->fontPath . '/' . $this->fontFamily . '.ttf';
    }

    /** {@inheritdoc} */
    public function draw(GdImage $canvas)
    {
        if ($this->pos === null) {
            return;
        }

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
        $x = intval($p->x - ($bbox->getWidth() / 2) - $bbox->left + $xOffset + $this->relOffsetX);
        $y = intval($p->y + ($bbox->getHeight() / 2) - $bbox->bottom + $yOffset + $this->relOffsetY);

        if ($this->background) {
            $s = $this->background->allocate($canvas);
            imagefilledrectangle(
                $canvas,
                intval($x + $bbox->left),
                intval($y + $bbox->bottom + 1),
                intval($x + $bbox->right - 1),
                intval($y + $bbox->top),
                $s,
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
    }

    protected function getTextBbox(): Bounds
    {
        $font = $this->getFont();

        /** @var int[] */
        $bbox = imagettfbbox($this->fontSize, 0, $font, $this->text);

        return new Bounds($bbox[0], $bbox[1], $bbox[4], $bbox[5]);
    }
}
