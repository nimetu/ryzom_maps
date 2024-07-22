<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2020 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\BaseTypes;

use PHPUnit\Framework\Attributes\DataProvider;

class PolygonTest extends \PHPUnit\Framework\TestCase
{

    public $polygon;

    public function setUp(): void {
        $this->polygon = new Polygon([
            0, 0, 10, 0, 10,10,
            7,10,  5, 5,  3,10,
            0,10
        ]);
    }

    #[DataProvider('polygonPoints')]
    public function testPolygon($expected, $x, $y)
    {
        $this->assertEquals($this->polygon->contains($x,$y), $expected);
    }

    public static function polygonPoints()
    {
        return [
            [true, 1,1], // in
            [true, 2,9], // in
            [true, 9,9], // in
            [false, -1, -1], // out
            [false, 5,6], // out
            [false, 11,11], // out
        ];
    }
}

