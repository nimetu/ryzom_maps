<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2020 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\BaseTypes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Polygon::class)]
class PolygonTest extends \PHPUnit\Framework\TestCase
{

	public function testContainsWithNotEnoughtPoints()
	{
		$p = new Polygon([0, 0, 1, 1]);
		$this->assertFalse($p->contains(0.5, 0.5));
	}

	public function testPointAtLineShouldBeOut()
	{
		$p = new Polygon([0, 0, 0, 1, 1, 0]);
		$this->assertFalse($p->contains(0.5, 0.5));
	}

	public function testPointShouldBeIn()
	{
		$p = new Polygon([0, 0, 0, 1, 1, 0]);
		$this->assertTrue($p->contains(0.4, 0.4));
	}

	public function testAsArray()
	{
		$p = new Polygon([0, 1, 2, 3, 4, 5]);
		$this->assertSame([0, 1, 2, 3, 4, 5], $p->asArray());
	}

	public function testToString()
	{
		$p = new Polygon([0, 1, 2, 3, 4, 5]);
		$this->assertSame('Polygon{0.00,1.00,2.00,3.00,4.00,5.00}', (string)$p);
	}

    #[DataProvider('polygonPoints')]
    public function testPolygon($expected, $x, $y)
    {
        $p = new Polygon([
            0, 0, 10, 0, 10,10,
            7,10,  5, 5,  3,10,
            0,10
        ]);
        $this->assertEquals($p->contains($x,$y), $expected);
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

