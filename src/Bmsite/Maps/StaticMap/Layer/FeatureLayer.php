<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Layer;

use Bmsite\Maps\StaticMap\Feature\FeatureInterface;
use Bmsite\Maps\StaticMap\StaticMapGenerator;

/**
 * Class FeatureLayer
 */
class FeatureLayer implements LayerInterface
{
    /** @var FeatureInterface[] */
    private $features = array();

    /** @var  StaticMapGenerator */
    private $map;

    /**
     * @param FeatureInterface $f
     */
    public function addFeature(FeatureInterface $f)
    {
        $this->features[] = $f;
    }

    /** {@inheritdoc} */
    public function setMap(StaticMapGenerator $map)
    {
        $this->map = $map;
    }

    /** {@inheritdoc} */
    public function draw($canvas)
    {
        $zoom = $this->map->getZoom();
        $proj = $this->map->getProjection();
        $vp = $this->map->getViewport();

        foreach ($this->features as $feature) {
            $feature->draw($proj, $canvas, -$vp->left, -$vp->top, $zoom);
        }
    }
}
