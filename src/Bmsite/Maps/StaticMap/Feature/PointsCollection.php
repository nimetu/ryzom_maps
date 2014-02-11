<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Feature;

use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\MapProjection;

/**
 * Class PointsCollection
 */
class PointsCollection
{
    public $xy = array();

    /**
     * @var MapProjection $proj
     */
    protected $proj;

    /**
     * @param Point $xy
     */
    function addPoint(Point $xy)
    {
        $this->xy[] = $xy;
    }
}
