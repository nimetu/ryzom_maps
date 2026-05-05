<?php

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\BaseTypes;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Point::class)]
class PointTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDistance()
    {
        $p = new Point(1.1, 2.2);
        $dist = $p->getDistance(0, 0);
        $this->assertEqualsWithDelta(2.459, $p->getDistance(0, 0), 0.001);
    }

    public function testAsArray()
    {
        $p = new Point(1.1, 2.2);
        $this->assertSame([1.1, 2.2], $p->asArray());
    }

    public function testToString()
    {
        $p = new Point(1.1, 2.2);
        $this->assertSame('Point{1.10,2.20}', (string) $p);
    }
}
