<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Layer;

use Bmsite\Maps\StaticMap\StaticMapGenerator;

/**
 * Class LayerInterface
 */
interface LayerInterface
{

    /**
     * @param StaticMapGenerator $map
     *
     * @return void
     */
    public function setMap(StaticMapGenerator $map);

    /**
     * @param resource $canvas
     *
     * @return void
     */
    public function draw($canvas);
}
