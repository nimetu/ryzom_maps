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
    public $r;
    public $g;
    public $b;
    public $a;

    /**
     * @param int $r
     * @param int $g
     * @param int $b
     * @param int $a
     */
    function __construct($r = 0, $g = 0, $b = 0, $a = 0)
    {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
        $this->a = $a;
    }

    /**
     * @param resource $canvas
     *
     * @return int
     */
    public function allocate($canvas)
    {
        return imagecolorallocatealpha($canvas, $this->r, $this->g, $this->b, $this->a / 255 * 127);
    }
}
