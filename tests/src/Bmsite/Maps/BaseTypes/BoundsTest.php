<?php

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\BaseTypes;

class BoundsTest extends \PHPUnit\Framework\TestCase
{
    public function testPositiveBox()
    {
        $min_x = 0;
        $min_y = 0;
        $max_x = 1;
        $max_y = 1;

        // initiate bounds with wrong order of min/max values
        $b = new Bounds($max_x, $max_y, $min_x, $min_y);

        $this->assertSame($min_x, $b->left);
        $this->assertSame($min_y, $b->top);
        $this->assertSame($max_x, $b->right);
        $this->assertSame($max_y, $b->bottom);

        $this->assertSame(1, $b->getWidth());
        $this->assertSame(1, $b->getHeight());
        $this->assertSame([1, 1], $b->getSize());
        $this->assertSame([0.5, 0.5], $b->getCenter());
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

        $this->assertSame($min_x, $b->left);
        $this->assertSame($min_y, $b->top);
        $this->assertSame($max_x, $b->right);
        $this->assertSame($max_y, $b->bottom);

        $this->assertSame(5, $b->getWidth());
        $this->assertSame(5, $b->getHeight());
        $this->assertSame([5, 5], $b->getSize());
        $this->assertSame([-7.5, -7.5], $b->getCenter());
        $this->assertTrue($b->contains(-9, -9));
        $this->assertTrue($b->contains(-5, -10));
        $this->assertFalse($b->contains(-1, 1));
    }

    public function testExtend()
    {
        $b = new Bounds();
        $this->assertSame([0, 0, 0, 0], [$b->left, $b->bottom, $b->right, $b->top]);
        $b->extend(-1, 0);
        $this->assertSame([-1, 0, 0, 0], [$b->left, $b->bottom, $b->right, $b->top]);
        $b->extend(0, -1);
        $this->assertSame([-1, -1, 0, 0], [$b->left, $b->bottom, $b->right, $b->top]);
        $b->extend(1, 0);
        $this->assertSame([-1, -1, 1, 0], [$b->left, $b->bottom, $b->right, $b->top]);
        $b->extend(0, 1);
        $this->assertSame([-1, -1, 1, 1], [$b->left, $b->bottom, $b->right, $b->top]);
    }

    public function testToString()
    {
        $b = new Bounds(-0.12, -1.23, 2.45, 3.67);
        $this->assertSame('Bounds{-0.12,3.67,2.45,-1.23}', (string) $b);
    }
}
