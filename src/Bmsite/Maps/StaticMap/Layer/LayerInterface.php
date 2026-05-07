<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Layer;

use Bmsite\Maps\StaticMap\StaticMapGenerator;
use GdImage;

interface LayerInterface
{
    public function setMap(StaticMapGenerator $map);

    public function draw(GdImage $canvas);
}
