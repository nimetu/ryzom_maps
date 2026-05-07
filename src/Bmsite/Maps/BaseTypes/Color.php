<?php

declare(strict_types=1);

/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/29/13
 * Time: 11:49 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Bmsite\Maps\BaseTypes;

use GdImage;

class Color
{
    function __construct(
        public int $r = 0,
        public int $g = 0,
        public int $b = 0,
        public int $a = 255,
    ) {}

    public function allocate(GdImage $canvas): int
    {
        return (int) imagecolorallocatealpha($canvas, $this->r, $this->g, $this->b, 127 - ($this->a >> 1));
    }

    public function __toString(): string
    {
        return sprintf('Color{%d,%d,%d,%d}', $this->r, $this->g, $this->b, $this->a);
    }
}
