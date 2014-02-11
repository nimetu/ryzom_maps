<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps;

use Bmsite\Maps\BaseTypes\Bounds;
use Bmsite\Maps\BaseTypes\Point;

/**
 * Class MapProjectionTest
 */
class MapProjectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MapProjection
     */
    private $proj;

    private $worldZones = array(
        'world' => array(array(0, 14160), array(14160, 0)),
        'fyros' => array(array(2920, 3836), array(7400, 636)),
        'matis' => array(array(7760, 7872), array(13680, 352)),
        'grid' => array(array(-108000, 47520), array(0, 0))
    );
    private $worldProj = array(
        'place_pyr' => array(array(5480, 1516), array(6120, 1036)),
        'place_yrkanis' => array(array(12080, 3712), array(12240, 3232)),
    );

    private $serverZones = array(
        'fyros' => array(array(15840, -27040), array(20320, -23840)),
        'matis' => array(array(320, -7840), array(6240, -320)),
        'place_pyr' => array(array(18400, -24720), array(19040, -24240)),
        'place_yrkanis' => array(array(4640, -3680), array(4800, -3200)),
        'grid' => array(array(0, -47520), array(108000, 0))
    );

    public function setUp()
    {
        $this->proj = new MapProjection();
        $this->proj->setWorldZones($this->worldZones);
        $this->proj->setServerZones($this->serverZones);
    }

    public function testSetWorldZones()
    {
        $wz = array('wz' => array(array(0, 1), array(1, 0)));
        $this->proj->setWorldZones($wz, 5);

        $scale = $this->proj->scale(5);
        $bounds = $this->proj->getZoneBounds('wz');
        $expected = new Bounds(0, 1, 1, 0);

        $this->assertEquals(1, $scale);
        $this->assertEquals($expected, $bounds);
    }

    public function testGetInnerZoneBounds()
    {
        $expected = $this->worldProj['place_pyr'];
        $inner = $this->proj->getZoneBounds('place_pyr');

        $result = array(
            array($inner->left, $inner->bottom),
            array($inner->right, $inner->top),
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @param Point $latlng
     * @param Point $expectedPoint
     * @param array $expectedRegions
     *
     * @dataProvider projectProvider
     */
    public function testProject(Point $latlng, Point $expectedPoint = null, $expectedRegions = null)
    {
        $point = $this->proj->project($latlng);
        $regions = $this->proj->getTargetRegions($latlng);

        $this->assertEquals(
            $expectedRegions,
            $regions,
            "Target regions for point {$latlng} are not whats expected [".join(',', $expectedRegions)."], got [".join(
                ', ',
                $regions
            )."]"
        );

        $this->assertEquals(
            $expectedPoint->asArray(),
            $point->asArray(),
            "Failed to translate server coordinates {$latlng} to world coordinates {$expectedPoint}, got {$point}"
        );
    }

    public function testUnknownZone()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Coordinates outside known server zone');

        $this->proj->project(new Point(0, 1));
    }

    public function testMissingServerZone()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Unknown server zone (unknown-server-zone)');
        $this->proj->projectZone('unknown-server-zone');
    }

    public function testMissingWorldZone()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Missing server to world zone projection (unknown-world-zone)'
        );

        $sz = array('unknown-world-zone' => array(array(0, -10), array(10, 0)));
        $this->proj->setServerZones($sz);
        $this->proj->projectZone('unknown-world-zone');
    }

    /**
     * @param int $zoom
     * @param float $expected
     *
     * @dataProvider zoomScaleProvider
     */
    public function testScale($zoom, $expected)
    {
        $mod = $this->proj->scale($zoom);
        $this->assertEquals($expected, $mod);
    }

    /**
     * @return array
     */
    public function zoomScaleProvider()
    {
        return array(
            array(5, 0.03125),
            array(6, 0.0625),
            array(7, 0.125),
            array(8, 0.25),
            array(9, 0.5),
            array(10, 1),
            array(11, 2),
            array(12, 4),
            array(13, 8),
        );
    }

    /**
     * @return array
     */
    public function projectProvider()
    {
        return array(
            // matis - yrk (sw)
            array(new Point(4640, -3680), new Point(12080, 3712), array('place_yrkanis', 'matis', 'grid')),
            // matis - yrk (ne)
            array(new Point(4800, -3200), new Point(12240, 3232), array('place_yrkanis', 'matis', 'grid')),
            // matis - random spot
            array(new Point(600, -7000), new Point(8040, 7032), array('matis', 'grid')),
            // fyros - random spot
            array(new Point(17000, -25000), new Point(4080, 1796), array('fyros', 'grid')),
            // outside any zone
            array(new Point(100, -2000), new Point(-107900, 2000), array('grid')),
            // closest to zones - FIXME: this breaks test as 'closest' is not implemented
            //array(new Point(300, -2000), new Point(7740, 2032), array('matis', 'grid')),
        );
    }
}
