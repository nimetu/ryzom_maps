<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps;

use PHPUnit\Framework\Attributes\DataProvider;
use Bmsite\Maps\BaseTypes\Bounds;
use Bmsite\Maps\BaseTypes\Point;

/**
 * Class MapProjectionTest
 */
class MapProjectionTest extends \PHPUnit\Framework\TestCase
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

    private $serverAreas = array(
        'sources' => [
            'order' => 0,
            'points' => [ 3046.25, -9640.7, 2519.27, -10223.71, 2445.95, -10941.11, 3011.5, -11437.4, 3548.51, -11400.74, 3901.13, -10752.59, 3901.09, -10223.71, 3777.78, -9626.95],
            'areas' => [
                'region_the_under_spring' => [
                    'order' => 1,
                    'points' => [3129.56, -9685.19, 2559.13, -10244.1, 2559.13, -10879.91, 3040.93, -11363.91, 3516.13, -11361.71, 3841.73, -10714.91, 3841.73, -10239.71, 3681.13, -9680.9],
                ],
                'place_outpost_pr_17' => [
                    'order' => 7,
                    'points' => [3145.09, -10100.68, 3080.79, -10121.28, 3070.46, -10171.58, 3110.11, -10220.17, 3174.98, -10189.07, 3174.03, -10116.22],
                ],
            ],
        ],
    );


    public function setUp(): void
    {
        $this->proj = new MapProjection();
        $this->proj->setWorldZones($this->worldZones);
        $this->proj->setServerZones($this->serverZones);
        $this->proj->setServerAreas($this->serverAreas);
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
     */
    #[DataProvider('projectProvider')]
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
            [(int)$point->x, (int)$point->y],
            "Failed to translate server coordinates {$latlng} to world coordinates {$expectedPoint}, got {$point}"
        );
    }

    /**
     * @param Point $latlng
     * @param array $expectedRegions
     */
    #[DataProvider('areasProvider')]
    public function testTargetAreas(Point $latlng, $expectedAreas = null)
    {
            $areas = $this->proj->getTargetAreas($latlng);
            $this->assertEquals(
                    $expectedAreas,
                    $areas,
                    "Target areas for point {$latlng} are not whats expected (".var_export($expectedAreas, true)."), got [".var_export($areas, true)."]"
            );
    }

    public function testUnknownZone()
    {
        $this->expectException('\InvalidArgumentException', 'Coordinates outside known server zone');

        $this->proj->project(new Point(0, 1));
    }

    public function testMissingServerZone()
    {
        $this->expectException('\InvalidArgumentException', 'Unknown server zone (unknown-server-zone)');
        $this->proj->projectZone('unknown-server-zone');
    }

    public function testMissingWorldZone()
    {
        $this->expectException(
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
     */
    #[DataProvider('zoomScaleProvider')]
    public function testScale($zoom, $expected)
    {
        $mod = $this->proj->scale($zoom);
        $this->assertEquals($expected, $mod);
    }

    /**
     * @return array
     */
    public static function zoomScaleProvider()
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
    public static function projectProvider()
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

    /**
     * @return array
     */
    static public function areasProvider()
    {
        return array(
            array(new Point(3110, -10190), array(
                array('key' => 'place_outpost_pr_17', 'order' => 7),
                array('key' => 'region_the_under_spring', 'order' => 1),
                array('key' => 'sources', 'order' => 0),
            )),
            array(new Point(3200, -10300), array(
                array('key' => 'region_the_under_spring', 'order' => 1),
                array('key' => 'sources', 'order' => 0),
            )),
            array(new Point(3047, -9641), array(
                array('key' => 'sources', 'order' => 0),
            )),
            // outside any zone
            array(new Point(0, 0), array()),
        );
    }
}

