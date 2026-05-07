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

#[CoversClass(Bounds::class)]
class BoundsTest extends \PHPUnit\Framework\TestCase
{
    public function testPositiveBox()
    {
        $min_x = 0;
        $min_y = 0;
        $max_x = 2;
        $max_y = 2;

        // initiate bounds with wrong order of min/max values
        $b = new Bounds($max_x, $max_y, $min_x, $min_y);

        $this->assertEqualsWithDelta($min_x, $b->left, PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta($min_y, $b->top, PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta($max_x, $b->right, PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta($max_y, $b->bottom, PHP_FLOAT_EPSILON);

        $this->assertEqualsWithDelta(2.0, $b->getWidth(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(2.0, $b->getHeight(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta([2.0, 2.0], $b->getSize(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta([1.0, 1.0], $b->getCenter(), PHP_FLOAT_EPSILON);
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

        $this->assertEqualsWithDelta($min_x, $b->left, PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta($min_y, $b->top, PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta($max_x, $b->right, PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta($max_y, $b->bottom, PHP_FLOAT_EPSILON);

        $this->assertEqualsWithDelta(5.0, $b->getWidth(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(5.0, $b->getHeight(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta([5.0, 5.0], $b->getSize(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta([-7.5, -7.5], $b->getCenter(), PHP_FLOAT_EPSILON);
        $this->assertTrue($b->contains(-9, -9));
        $this->assertTrue($b->contains(-5, -10));
        $this->assertFalse($b->contains(-1, 1));
    }

    public function testExtend()
    {
        $b = new Bounds();
        $this->assertEqualsWithDelta([0, 0, 0, 0], [$b->left, $b->bottom, $b->right, $b->top], PHP_FLOAT_EPSILON);
        $b->extend(-1.1, 0);
        $this->assertEqualsWithDelta([-1.1, 0, 0, 0], [$b->left, $b->bottom, $b->right, $b->top], PHP_FLOAT_EPSILON);
        $b->extend(0, -1.1);
        $this->assertEqualsWithDelta([-1.1, -1.1, 0, 0], [$b->left, $b->bottom, $b->right, $b->top], PHP_FLOAT_EPSILON);
        $b->extend(1.1, 0);
        $this->assertEqualsWithDelta(
            [-1.1, -1.1, 1.1, 0],
            [$b->left, $b->bottom, $b->right, $b->top],
            PHP_FLOAT_EPSILON,
        );
        $b->extend(0, 1.1);
        $this->assertEqualsWithDelta(
            [-1.1, -1.1, 1.1, 1.1],
            [$b->left, $b->bottom, $b->right, $b->top],
            PHP_FLOAT_EPSILON,
        );
    }

    public function testToString()
    {
        $b = new Bounds(-0.12, -1.23, 2.45, 3.67);
        $this->assertSame('Bounds{-0.12,3.67,2.45,-1.23}', (string) $b);
    }
}
