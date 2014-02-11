<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Feature;

use Bmsite\Maps\StaticMap\StaticMapGenerator;

/**
 * Class DrawFeature
 */
interface FeatureInterface
{

    /**
     * @param StaticMapGenerator $map
     *
     * @return
     */
    public function setMap(StaticMapGenerator $map);

    /**
     * @param resource $canvas
     *
     * @return
     */
    public function draw($canvas);

}
