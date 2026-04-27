<?php
/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/18/13
 * Time: 2:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Bmsite\Maps\BaseTypes;

/**
 * Class Bounds
 */
class Bounds
{
    /** @var float */
    public $top;

    /** @var float */
    public $left;

    /** @var float */
    public $bottom;

    /** @var float */
    public $right;

    /**
     * @param float $left
     * @param float $bottom
     * @param float $right
     * @param float $top
     */
    public function __construct($left = 0, $bottom = 0, $right = 0, $top = 0)
    {
        $this->left = min($left, $right);
        $this->right = max($left, $right);

        $this->top = min($top, $bottom);
        $this->bottom = max($top, $bottom);
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return abs($this->right - $this->left);
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return abs($this->bottom - $this->top);
    }

    /**
     * @return array [width, height]
     */
    public function getSize()
    {
        return array($this->getWidth(), $this->getHeight());
    }

    /**
     * @return array [x, y]
     */
    public function getCenter()
    {
        $cx = ($this->left + $this->right) / 2;
        $cy = ($this->bottom + $this->top) / 2;

        return array($cx, $cy);
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function extend($x, $y)
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

    /**
     * @param float $left
     * @param float $bottom
     * @param float|null $right
     * @param float|null $top
     *
     * @return bool
     */
    public function contains($left, $bottom, $right = null, $top = null)
    {
        $right = $right === null ? $left : $right;
        $top = $top === null ? $bottom : $top;

        $inHorizontal = $this->left <= $left && $this->right >= $right;
        $inVertical = $this->top <= $top && $this->bottom >= $bottom;

        return $inHorizontal && $inVertical;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Bounds{[%d,%d], [%d,%d]}', $this->left, $this->bottom, $this->right, $this->top);
    }
}
