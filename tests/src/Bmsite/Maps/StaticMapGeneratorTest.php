<?php

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2024 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps;

use Bmsite\Maps\BaseTypes\Color;
use Bmsite\Maps\BaseTypes\Point;
use Bmsite\Maps\StaticMap\Feature\Marker;
use Bmsite\Maps\StaticMap\Feature\Polygon;
use Bmsite\Maps\StaticMap\StaticMapGenerator;
use GdImage;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(StaticMapGenerator::class)]
class StaticMapGeneratorTest extends \PHPUnit\Framework\TestCase
{
	public function testFunctional()
	{
		$map = $this->createMockMap();
		$map->setLanguage('en');
		$map->setDrawCenterLines(true);

		$poly = new Polygon();
		$poly->setMap($map);
		$poly->addPoint(new Point(0, 0));
		$poly->addPoint(new Point(1, 1));
		$poly->addPoint(new Point(2, 2));
		$map->addPolygon($poly);

		$marker = new Marker();
		$marker->setMap($map);
		$marker->addPoint(new Point(1, 1));
		$marker->setLabel('marker');
		$marker->circleRadius(5,5);
		$marker->setCircleFillColor(new Color(255,0,0));
		$marker->setCircleStrokeWeight(2);
		$map->addMarker($marker);

		$ret = $map->render();
		$this->assertIsString($ret);

		$im = imagecreatefromstring($ret);
		$this->assertInstanceOf(GdImage::class, $im);
	}

    public function testSetSize()
    {
        $map = $this->createMockMap();
        $map->setSize(123, 456);

        $ret = $map->render();
        $this->assertIsString($ret);

        $im = imagecreatefromstring($ret);
		$this->assertInstanceOf(GdImage::class, $im);
        $this->assertEquals(123, imagesx($im));
        $this->assertEquals(456, imagesy($im));
    }

    public function testMapName()
    {
        $map = $this->createMockMap();
        $map->setMapName('custom_map');
        $this->assertEquals('custom_map', $map->getMapName(), 'Map name should accept any string');
    }

    public function testMapMode()
    {
        $map = $this->createMockMap();
        $map->setMapMode('custom_mode');
        $this->assertEquals('custom_mode', $map->getMapMode(), 'Map mode should accept any string');
    }

    public function testSetZoom()
    {
        $map = $this->createMockMap();
        $this->assertNull($map->getZoom());
        $this->assertTrue($this->getProp($map, 'autoZoom'), 'autoZoom should be enabled by default');

        $map->setZoom(10);
        $this->assertEquals(10, $map->getZoom());
        $this->assertFalse($this->getProp($map, 'autoZoom'), 'Setting zoom level should disable autoZoom');

        $map->setZoom(PHP_INT_MIN);
        $this->assertEquals(StaticMapGenerator::MIN_ZOOM, $map->getZoom(), 'Zoom should be clamped to min zoom');
        $map->setZoom(PHP_INT_MAX);
        $this->assertEquals(StaticMapGenerator::MAX_ZOOM, $map->getZoom(), 'Zoom should be clamped to max zoom');
    }

    // http://api.bmsite.net/maps/static?path=color:0xFF2233ff|fillcolor:0xFF223050|label:marker|label_color:0xFFFFFF|17200,-32970|17300,-32870|17300,-32970
    public function testAutoZoom()
    {
        $map = $this->createMockMap();

        $m = new Marker();
        foreach ([
            [17200, -32970],
            [17300, -32870],
            [17300, -32970],
        ] as $xy) {
            $m->addPoint(new Point($xy[0], $xy[1]));
        }

        $map->addMarker($m);

        // default zoom should not be assigned
        $this->assertNull($map->getZoom());

        // calculates zoom
        $map->render();
        $this->assertFalse($this->getProp($map, 'autoZoom'), 'render should disable auto zoom');
        $this->assertEquals(7, $map->getZoom());

        $map->enableAutoZoom();
        $this->assertTrue($this->getProp($map, 'autoZoom'), 'autoZoom should be enabled again');
        $this->assertNull($map->getZoom(), 'Must return null when autoZoom is enabled');
    }

    public function testGetFormat()
    {
        $map = $this->createMockMap();
        $this->assertSame('jpg', $map->getFormat());

        $map->setFormat('png');
        $this->assertSame('png', $map->getFormat());

        $map->setFormat('jpg');
        $this->assertSame('jpg', $map->getFormat());

        // switch to non-default format
        $map->setFormat('png');

        // unsupported format
        $map->setFormat('gif');
        $this->assertSame('png', $map->getFormat(), 'setFormat() with unknown type should not change actual format');
    }

    public function testGetContentType()
    {
        $map = $this->createMockMap();
        $this->assertSame('image/jpeg', $map->getContentType());

        $map->setFormat('png');
        $this->assertSame('image/png', $map->getContentType());

        $map->setFormat('jpg');
        $this->assertSame('image/jpeg', $map->getContentType());

        // switch to non-default format
        $map->setFormat('png');

        // unsupported format
        $map->setFormat('gif');
        $this->assertSame(
            'image/png',
            $map->getContentType(),
            'setFormat() with unknown type should not change actual format',
        );
    }

    public function testRenderFormat()
    {
        $map = $this->createMockMap();

        $ret = $map->render();
        $this->assertSame("JFIF\0", substr($ret, 6, 5), 'Default format');

        $map->setFormat('png');
        $ret = $map->render();
        $this->assertSame("\x89PNG\r\n\032\n", substr($ret, 0, 8), 'Returned image should be PNG');
    }

    public function testSetAutoLanguage()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '12lang';

        $map = $this->createMockMap();
        $map->setLanguage('auto');
        $this->assertEquals('12', $this->getProp($map, 'lang'), 'Browser language should take first two symbols only');
    }

    public function testSetAutoLanguageFallback()
    {
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);

        $map = $this->createMockMap();
        $map->setLanguage('auto');
        $this->assertEquals('en', $this->getProp($map, 'lang'), '"auto" language fallback');
    }

    public function testSetKnownLanguage()
    {
        $map = $this->createMockMap();
        $this->assertNull($this->getProp($map, 'lang'));

        $map->setLanguage('fr');
        $this->assertEquals('fr', $this->getProp($map, 'lang'));
    }

    public function testResetLanguage()
    {
        $map = $this->createMockMap();
        $map->setLanguage('en');
        $this->assertSame('en', $this->getProp($map, 'lang'));

        $map->setLanguage(null);
        $this->assertNull($this->getProp($map, 'lang'));
    }

    public function testSetUnknownLanguage()
    {
        $map = $this->createMockMap();
        $this->assertNull($this->getProp($map, 'lang'));

        $map->setLanguage('unknown');
        $this->assertNull($this->getProp($map, 'lang'));
    }

    protected function createMockMap(): StaticMapGenerator
    {
        $ts = new MockTileStorage();
        $proj = new MockMapProjection();
        $map = new StaticMapGenerator($ts);
        $map->setProjection($proj);
        return $map;
    }

    protected function getProp(StaticMapGenerator $map, string $name): mixed
    {
        $ref = new ReflectionClass(StaticMapGenerator::class);
        $prop = $ref->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($map);
    }
}
