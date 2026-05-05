<?php

/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/29/13
 * Time: 11:49 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Bmsite\Maps\BaseTypes;

/**
 * Class Color
 */
class Color
{
    /** @var int */
    public $r;
    /** @var int */
    public $g;
    /** @var int */
    public $b;
    /** @var int */
    public $a;

    /**
     * @param int $r
     * @param int $g
     * @param int $b
     * @param int $a
     */
    function __construct($r = 0, $g = 0, $b = 0, $a = 255)
    {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
        $this->a = $a;
    }

    /**
     * @param \GdImage $canvas
     *
     * @return int
     */
    public function allocate($canvas)
    {
        return imagecolorallocatealpha($canvas, $this->r, $this->g, $this->b, 127 - ($this->a >> 1));
    }

    public function __toString()
    {
        return sprintf('Color{%d,%d,%d,%d}', $this->r, $this->g, $this->b, $this->a);
    }
}
