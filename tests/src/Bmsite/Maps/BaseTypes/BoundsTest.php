<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\BaseTypes;


class BoundsTest extends \PHPUnit_Framework_TestCase
{

    public function testPositiveBox()
    {
        $min_x = 0;
        $min_y = 0;
        $max_x = 1;
        $max_y = 1;

        // initiate bounds with wrong order of min/max values
        $b = new Bounds($max_x, $max_y, $min_x, $min_y);

        $this->assertEquals($min_x, $b->left);
        $this->assertEquals($min_y, $b->top);
        $this->assertEquals($max_x, $b->right);
        $this->assertEquals($max_y, $b->bottom);

        $this->assertEquals(1, $b->getWidth());
        $this->assertEquals(1, $b->getHeight());
        $this->assertTrue($b->contains(0, 0));
        $this->assertTrue($b->contains(1, 1));
        $this->assertFalse($b->contains(-1, 1));
    }

    public function testPositiveNegative()
    {
        $min_x = -10;
        $min_y = -10;
        $max_x = -5;
        $max_y = -5;

        $b = new Bounds($max_x, $max_y, $min_x, $min_y);

        $this->assertEquals($min_x, $b->left);
        $this->assertEquals($min_y, $b->top);
        $this->assertEquals($max_x, $b->right);
        $this->assertEquals($max_y, $b->bottom);

        $this->assertEquals(5, $b->getWidth());
        $this->assertEquals(5, $b->getHeight());
        $this->assertTrue($b->contains(-9, -9));
        $this->assertTrue($b->contains(-5, -10));
        $this->assertFalse($b->contains(-1, 1));
    }
}
