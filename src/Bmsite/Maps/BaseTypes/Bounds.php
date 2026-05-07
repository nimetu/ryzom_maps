<?php

declare(strict_types=1);

/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/18/13
 * Time: 2:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Bmsite\Maps\BaseTypes;

class Bounds
{
    public float $top;

    public float $left;

    public float $bottom;

    public float $right;

    public function __construct(float $left = 0, float $bottom = 0, float $right = 0, float $top = 0)
    {
        $this->left = min($left, $right);
        $this->right = max($left, $right);

        $this->top = min($top, $bottom);
        $this->bottom = max($top, $bottom);
    }

    public function getWidth(): float
    {
        return abs($this->right - $this->left);
    }

    public function getHeight(): float
    {
        return abs($this->bottom - $this->top);
    }

    public function getArea(): float
    {
        return $this->getWidth() * $this->getHeight();
    }

    /**
     * @return array{float,float} [width, height]
     */
    public function getSize(): array
    {
        return [$this->getWidth(), $this->getHeight()];
    }

    /**
     * @return array{float,float} [x, y]
     */
    public function getCenter(): array
    {
        $cx = ($this->left + $this->right) / 2;
        $cy = ($this->bottom + $this->top) / 2;

        return [$cx, $cy];
    }

    public function extend(float $x, float $y)
    {
        if ($x < $this->left) {
            $this->left = $x;
        }

        if ($x > $this->right) {
            $this->right = $x;
        }

        if ($y > $this->top) {
            $this->top = $y;
        }

        if ($y < $this->bottom) {
            $this->bottom = $y;
        }
    }

    public function contains(float $left, float $bottom, ?float $right = null, ?float $top = null): bool
    {
        $right = $right === null ? $left : $right;
        $top = $top === null ? $bottom : $top;

        $inHorizontal = $this->left <= $left && $this->right >= $right;
        $inVertical = $this->top <= $top && $this->bottom >= $bottom;

        return $inHorizontal && $inVertical;
    }

    public function __toString(): string
    {
        return sprintf('Bounds{%.2f,%.2f,%.2f,%.2f}', $this->left, $this->bottom, $this->right, $this->top);
    }
}
