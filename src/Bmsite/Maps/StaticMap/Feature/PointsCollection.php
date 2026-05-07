<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Feature;

use Bmsite\Maps\BaseTypes\Point;

/**
 * Class PointsCollection
 */
class PointsCollection
{
    /** @var Point[] */
    public array $xy = [];

    function addPoint(Point $xy)
    {
        $this->xy[] = $xy;
    }
}
