<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps;

/*
 * X/Y remapping
 *                              world map size
 * @zoom level  1 -- 1px ~ 512m (   27x27   )
 * @zoom level  2 -- 1px ~ 256m (   55x55   )
 * @zoom level  3 -- 1px ~ 128m (  110x110  )
 * @zoom level  4 -- 1px ~  64m (  221x221  )
 * @zoom level  5 -- 1px ~  32m (  442x442  )
 * @zoom level  6 -- 1px ~  16m (  885x885  )
 * @zoom level  7 -- 1px =   8m ( 1770x1770 )
 * @zoom level  8 -- 1px =   4m ( 3540x3540 )
 * @zoom level  9 -- 1px =   2m ( 7080x7080 )
 * @zoom level 10 -- 1px =   1m (14160x14160)
 * @zoom level 11 -- 1px = 0.5m (28320x28320)
 */

use Bmsite\Maps\BaseTypes\Bounds;
use Bmsite\Maps\BaseTypes\Point;

/**
 * Class MapProjection
 */
class MapProjection
{
    /** @var Bounds[] */
    protected $zones = array();

    /** @var Bounds[] */
    protected $serverZones = array();

    /** @var int */
    protected $baseZoom = 10;

    /** @var float */
    protected $baseScale = 1024;

    /**
     * @param array $zones
     * @param int $baseZoom
     */
    public function setWorldZones(array $zones, $baseZoom = 10)
    {
        $this->baseZoom = $baseZoom;
        $this->baseScale = pow(2, $this->baseZoom);

        $this->zones = array();
        foreach ($zones as $key => $pos) {
            $this->zones[$key] = new Bounds($pos[0][0], $pos[0][1], $pos[1][0], $pos[1][1]);
        }
    }

    /**
     * @param array $zones
     */
    public function setServerZones(array $zones)
    {
        $this->serverZones = array();
        foreach ($zones as $key => $pos) {
            $this->serverZones[$key] = new Bounds($pos[0][0], $pos[0][1], $pos[1][0], $pos[1][1]);
        }
    }

    /**
     * @param $zoom
     *
     * @return float
     */
    public function scale($zoom)
    {
        return pow(2, $zoom) / $this->baseScale;
    }

    /**
     * @param string $id
     *
     * @return Bounds|bool
     * @throws \InvalidArgumentException
     */
    public function getZoneBounds($id)
    {
        if (!isset($this->zones[$id])) {
            $zone = $this->projectZone($id);
        } else {
            $zone = $this->zones[$id];
        }

        return $zone;
    }

    /**
     * @param string $zoneName
     *
     * @throws \InvalidArgumentException
     * @return Bounds
     */
    public function projectZone($zoneName)
    {
        if (!isset($this->serverZones[$zoneName])) {
            throw new \InvalidArgumentException("Unknown server zone ({$zoneName})");
        }

        $zone = $this->serverZones[$zoneName];
        $parent = $this->findParentZone(new Point($zone->left, $zone->bottom));
        if (!$parent) {
            throw new \InvalidArgumentException("Missing server to world zone projection ({$zoneName})");
        }

        $parentZone = $this->serverZones[$parent];
        $worldZone = $this->zones[$parent];

        return $this->translateZone($zone, $parentZone, $worldZone);
    }

    /**
     * Project latLng (server coords) into image coords
     *
     * @param Point $p
     * @param int $zoom
     *
     * @throws \InvalidArgumentException
     * @return Point|bool
     */
    public function project(Point $p, $zoom = null)
    {
        $parent = $this->findParentZone($p);
        if (!$parent) {
            throw new \InvalidArgumentException("Coordinates outside known server zone ({$p})");
        }

        $bb = $this->translate($p, $this->serverZones[$parent], $this->zones[$parent]);
        if ($zoom !== null) {
            $scale = $this->scale($zoom);
            $bb->x = $bb->x * $scale;
            $bb->y = $bb->y * $scale;
        }
        return $bb;
    }

    /**
     * Return all regions where point lands
     * Sorted by smallest
     *
     * @param Point $p
     * @param int $closest
     *
     * @return array
     */
    public function getTargetRegions(Point $p, $closest = 0)
    {
        $result = array();
        foreach ($this->serverZones as $id => $zone) {
            if ($zone->contains($p->x, $p->y)) {
                $size = $zone->getSize();
                $result[$id] = $size[0] * $size[1];
            }
        }
        asort($result);

        if (empty($result) && $closest > 0) {
            // TODO: find closest zone
        }

        // always match grid
        //if (empty($result)) {
        //    $result['grid'] = 0;
        //}

        return array_keys($result);
    }

    /**
     * Find parent zone that is present in world map using server coords
     *
     * @param Point $point
     *
     * @return bool|int|string
     */
    protected function findParentZone(Point $point)
    {
        $parent = null;

        $regions = $this->getTargetRegions($point);
        foreach ($regions as $id) {
            if (isset($this->zones[$id])) {
                $parent = $id;
                break;
            }
        }

        return $parent;
    }

    /**
     * Translate point (pyr) from src (fyros)
     * to dst (fyros in world map)
     *
     * All arrays are in [ [left,bottom], [right,top] ]
     *
     * @param Point $point
     * @param Bounds $src
     * @param Bounds $dst
     *
     * @return Point
     */
    protected function translate(Point $point, Bounds $src, Bounds $dst)
    {
        $px = ($point->x - $src->left) / $src->getWidth();
        $py = ($point->y - $src->top) / $src->getHeight();

        $left = $dst->left + $px * $dst->getWidth();
        $top = $dst->bottom - $py * $dst->getHeight();

        return new Point($left, $top);
    }

    /**
     * @param Bounds $zone
     * @param Bounds $src
     * @param Bounds $dst
     *
     * @return Bounds
     */
    protected function translateZone(Bounds $zone, Bounds $src, Bounds $dst)
    {
        $tl = $this->translate(new Point($zone->left, $zone->top), $src, $dst);
        $br = $this->translate(new Point($zone->right, $zone->bottom), $src, $dst);
        return new Bounds($tl->x, $br->y, $br->x, $tl->y);
    }
}
