<?php
/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/18/13
 * Time: 2:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Bmsite\Maps\BaseTypes;

/**
 * Class Point
 */
class Point
{

    /** @var float */
    public $x;

    /** @var float */
    public $y;

    /**
     * @param float $x
     * @param float $y
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param float $x
     * @param float $y
     *
     * @return float
     */
    public function getDistance($x, $y)
    {
        $dx = pow(abs($this->x - $x), 2);
        $dy = pow(abs($this->y - $y), 2);

        return sqrt($dx + $dy);
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array($this->x, $this->y);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Point{%d,%d}', $this->x, $this->y);
    }
}
