<?php

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2026 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\StaticMap\Layer;

use Bmsite\Maps\MockMapProjection;
use Bmsite\Maps\MockTileStorage;
use Bmsite\Maps\StaticMap\StaticMapGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(TileLayer::class)]
class TileLayerTest extends \PHPUnit\Framework\TestCase
{
    protected StaticMapGenerator $map;

    public function setUp(): void
    {
        $ts = new MockTileStorage();
        $proj = new MockMapProjection();
        $this->map = new StaticMapGenerator($ts);
        $this->map->setProjection($proj);
    }

    public function testDefaultZoomBetweenZoomRange()
    {
        $tile = new TileLayer($this->map, 5, 10);
        $this->assertSame(256, $tile->getTileSize());
    }

    public function testZoomSmallerThanMinZoom()
    {
        $tile = new TileLayer($this->map, 5, 10);
        $this->changeZoom($tile, 4);
        $this->assertEqualsWithDelta((float) (256 >> (5 - 4)), $tile->getTileSize(), PHP_FLOAT_EPSILON);
        $this->changeZoom($tile, 3);
        $this->assertEqualsWithDelta((float) (256 >> (5 - 3)), $tile->getTileSize(), PHP_FLOAT_EPSILON);
    }

    public function testZoomGreaterThanMaxZoom()
    {
        $tile = new TileLayer($this->map, 5, 10);
        $this->changeZoom($tile, 11);
        $this->assertEqualsWithDelta((float) (256 << (11 - 10)), $tile->getTileSize(), PHP_FLOAT_EPSILON);
        $this->changeZoom($tile, 12);
        $this->assertEqualsWithDelta((float) (256 << (12 - 10)), $tile->getTileSize(), PHP_FLOAT_EPSILON);
    }

    protected function changeZoom(TileLayer $layer, int $zoom)
    {
        $ref = new ReflectionClass(TileLayer::class);
        $prop = $ref->getProperty('zoom');
        $prop->setAccessible(true);
        $prop->setValue($layer, $zoom);
    }
}
