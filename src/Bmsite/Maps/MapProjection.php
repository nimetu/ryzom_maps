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
use Bmsite\Maps\BaseTypes\Polygon;

/**
 * Class MapProjection
 */
class MapProjection
{
    /** @var Bounds[] */
    protected $zones = array();

    /** @var Bounds[] */
    protected $serverZones = array();

    /** @var array[] */
    protected $serverAreas = array();

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
     * @param array $areas
     */
    public function setServerAreas(array $areas)
    {
        $this->serverAreas = array();
        foreach($areas as $key => $area) {
            $this->serverAreas[$key] = array('name' => $key, 'order' => $area['order'], 'polygon' => new Polygon($area['points']));
            if (!empty($area['areas'])) {
                foreach($area['areas'] as $subkey => $subarea) {
                    $this->serverAreas[$key]['areas'][$subkey] = array('name' => $subkey, 'order' => $subarea['order'], 'polygon' => new Polygon($subarea['points']));
                }
            }
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
     * Convert image coords at given zoom level into server coords.
     *
     * Point in smaller area in overlaping zones
     * (ie nexus/matis) is returned.
     *
     * @param Point $p
     * @param int $zoom zoom level to use
     *
     * @throws \InvalidArgumentException
     * @return Point|bool
     */
    public function unproject(Point $p, $zoom = null)
    {
        if ($zoom !== null) {
            var_dump('convet to base zoom', $p, $zoom);
            $scale = $this->scale($zoom);
            $p->x = $p->x / $scale;
            $p->y = $p->y / $scale;
            var_dump('->', $p, $scale);
        }

        $zone = false;
        $minsize = false;
        foreach($this->zones as $k => $v) {
            if ($v->contains($p->x, $p->y)) {
                $size = $v->getWidth() * $v->getHeight();
                if ($minsize === false || $size < $minsize) {
                    $minsize = $size;
                    $zone = $k;
                }
            }
        }
        if ($zone === false || empty($this->serverZones[$zone])) {
            // TODO: try to map into closest zone
            return false;
        }

        return $this->translate($p, $this->zones[$zone], $this->serverZones[$zone]);
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
     * Finds matching areas using server coords.
     * Uses polygons from areas.json
     *
     * Returns sorted array of [key, order] where key is area name and order is type.
     * continent=0, region=1, capital=2, village=3, stable=4, place=5, street=6, outpost=7, unknown = 8
     *
     * @param Point $point
     *
     * @return array[]
     */
    public function getTargetAreas(Point $point)
    {
        $match = array();
        $sk = array();
        foreach($this->serverAreas as $area) {
            if ($area['polygon']->contains($point->x, $point->y)) {
                $match[] = array('key' => $area['name'], 'order' => $area['order']);
                $sk[] = $area['order'];
                if (!empty($area['areas'])) {
                    foreach($area['areas'] as $subarea) {
                        if ($subarea['polygon']->contains($point->x, $point->y)) {
                            $match[] = array('key' => $subarea['name'], 'order' => $subarea['order']);
                            $sk[] = $subarea['order'];
                        }
                    }
                }
            }
        }

        array_multisort($sk, SORT_NUMERIC, SORT_DESC, $match);
        // TODO: should return match = [ 0 => [zones...], 1 => [areas....] ] ? 'kitiniere' is in duplicate

        return $match;
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
