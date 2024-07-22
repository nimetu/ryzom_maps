<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2024 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps;

use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\StaticMap\Feature\Marker;
use Bmsite\Maps\StaticMap\StaticMapGenerator;

class StaticMapGeneratorTest extends \PHPUnit\Framework\TestCase
{
    // http://api.bmsite.net/maps/static?path=color:0xFF2233ff|fillcolor:0xFF223050|label:marker|label_color:0xFFFFFF|17200,-32970|17300,-32870|17300,-32970
    public function testAutoZoom() : void
    {
        $ts = new MockTileStorage;
        $proj = new MockMapProjection;
        $map = new StaticMapGenerator($ts);
        $map->setProjection($proj);

        $m = new Marker();
        foreach([
            [17200,-32970],
            [17300,-32870],
            [17300,-32970]
        ] as $xy) {
            $m->addPoint(new Point($xy[0], $xy[1]));
        }

        $map->addMarker($m);

        // default zoom should not be not assigned
        $this->assertNull($map->getZoom());

        $img = $map->render();
        $this->assertEquals(7, $map->getZoom());
    }
}

