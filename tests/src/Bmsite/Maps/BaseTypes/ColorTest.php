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

#[CoversClass(Color::class)]
class ColorTest extends \PHPUnit\Framework\TestCase
{
	public function testColorDefault()
	{
		$c = new Color();
		$this->assertSame(0, $c->r);
		$this->assertSame(0, $c->g);
		$this->assertSame(0, $c->b);
		$this->assertSame(255, $c->a);
	}

	public function testColor()
	{
		$c = new Color(1, 2, 3, 4);
		$this->assertSame(1, $c->r);
		$this->assertSame(2, $c->g);
		$this->assertSame(3, $c->b);
		$this->assertSame(4, $c->a);
	}

	public function testAllocate()
	{
		$c = new Color(1, 2, 3, 128);
		$im = imagecreatetruecolor(1, 1);
		$color = $c->allocate($im);
		// a must be (127 - alpha / 2)
		// a << 24 | r << 16 | g << 8 | b
		$this->assertSame('3f010203', dechex($color));
		$im = null;
	}

	public function testToString()
	{
		$c = new Color(1, 2, 3, 4);
		$this->assertSame('Color{1,2,3,4}', (string)$c);
	}
}
