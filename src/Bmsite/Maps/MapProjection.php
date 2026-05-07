<?php

declare(strict_types=1);

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
use Bmsite\Maps\BaseTypes\Polygon;

class MapProjection
{
    /** @var array<string, Bounds> */
    protected array $zones = [];

    /** @var array<string, Bounds> */
    protected array $serverZones = [];

    /**
     * @var array<array{
     *   name: string,
     *   order: int,
     *   polygon: Polygon,
     *   areas?: array<array{
     *     name: string,
     *     order: int,
     *     polygon: Polygon
     *   }>
     * }>
     */
    protected array $serverAreas = [];

    protected int $baseZoom = 10;

    protected float $baseScale = 1024;

    /**
     * Set world map continent coordinates.
     *
     * BaseZoom determines which zoom level should match server coordinates in 1:1 ratio.
     *
     * @param array<string, array{
     *   array{float,float},
     *   array{float,float}
     * }> $zones
     */
    public function setWorldZones(array $zones, int $baseZoom = 10)
    {
        $this->baseZoom = $baseZoom;
        $this->baseScale = (int) pow(2, $this->baseZoom);

        $this->zones = [];
        foreach ($zones as $key => $pos) {
            $this->zones[$key] = new Bounds($pos[0][0], $pos[0][1], $pos[1][0], $pos[1][1]);
        }
    }

    /**
     * @param array<string, array{
     *   array{float,float},
     *   array{float,float}
     * }> $zones
     */
    public function setServerZones(array $zones)
    {
        $this->serverZones = [];
        foreach ($zones as $key => $pos) {
            $this->serverZones[$key] = new Bounds($pos[0][0], $pos[0][1], $pos[1][0], $pos[1][1]);
        }
    }

    /**
     * @param array<string, array{
     *   order: int,
     *   points: float[],
     *   areas?: array<string, array{
     *     order: int,
     *     points: float[],
     *   }>,
     * }> $areas
     */
    public function setServerAreas(array $areas)
    {
        $this->serverAreas = [];
        foreach ($areas as $key => $area) {
            $this->serverAreas[$key] = [
                'name' => $key,
                'order' => (int) $area['order'],
                'polygon' => new Polygon($area['points']),
            ];
            if (!empty($area['areas'])) {
                foreach ($area['areas'] as $subkey => $subarea) {
                    $this->serverAreas[$key]['areas'][$subkey] = [
                        'name' => $subkey,
                        'order' => (int) $subarea['order'],
                        'polygon' => new Polygon($subarea['points']),
                    ];
                }
            }
        }
    }

    public function scale(int $zoom): float
    {
        return intval(pow(2, $zoom)) / $this->baseScale;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getZoneBounds(string $id): Bounds
    {
        if (isset($this->zones[$id])) {
            return $this->zones[$id];
        }

        return $this->projectZone($id);
    }

    /**
     * Project server zone into world map
     *
     * @throws \InvalidArgumentException
     */
    public function projectZone(string $zoneName): Bounds
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
     * Project latLng (server coords) into world coords
     *
     * @throws \InvalidArgumentException
     */
    public function project(Point $latlng, ?int $zoom = null): Point
    {
        $parent = $this->findParentZone($latlng);
        if (!$parent) {
            throw new \InvalidArgumentException("Coordinates outside known server zone ({$latlng})");
        }

        $bb = $this->translate($latlng, $this->serverZones[$parent], $this->zones[$parent]);
        if ($zoom !== null) {
            $scale = $this->scale($zoom);
            $bb->x = $bb->x * $scale;
            $bb->y = $bb->y * $scale;
        }
        return $bb;
    }

    /**
     * Convert world coords at zoom level into server coords.
     *
     * For overlaping zones (ie nexus/matis), zone with smaller area is returned.
     *
     * @throws \InvalidArgumentException
     */
    public function unproject(Point $p, ?int $zoom = null): Point|false
    {
        if ($zoom !== null) {
            $scale = $this->scale($zoom);
            $p->x /= $scale;
            $p->y /= $scale;
        }

        $zone = false;
        $minsize = false;
        foreach ($this->zones as $k => $v) {
            if ($v->contains($p->x, $p->y)) {
                $size = $v->getArea();
                if ($minsize === false || $size < $minsize) {
                    $minsize = $size;
                    $zone = $k;
                }
            }
        }
        if ($zone === false || !array_key_exists($zone, $this->serverZones)) {
            // TODO: try to map into closest zone
            return false;
        }

        return $this->translate($p, $this->zones[$zone], $this->serverZones[$zone]);
    }

    /**
     * Return all regions where point lands, sorted by smallest
     *
     * @return string[]
     */
    public function getTargetRegions(Point $p, int $distanceToClosest = 0): array
    {
        $result = [];
        foreach ($this->serverZones as $id => $zone) {
            if ($zone->contains($p->x, $p->y)) {
                $size = $zone->getSize();
                $result[$id] = $size[0] * $size[1];
            }
        }
        asort($result);

        if (empty($result) && $distanceToClosest > 0) {
            // TODO: find closest zone
        }

        return array_keys($result);
    }

    /**
     * Finds matching areas using server coords.
     * Uses polygons from areas.json
     *
     * Returns sorted array of [key, order] where key is area name and order is type.
     * continent=0, region=1, capital=2, village=3, stable=4, place=5, street=6, outpost=7, unknown = 8
     *
     * @template T of array{
     *   key: string,
     *   order: int,
     * }
     *
     * @return T[]
     */
    public function getTargetAreas(Point $point): array
    {
        $match = [];
        $sk = [];
        foreach ($this->serverAreas as $area) {
            if ($area['polygon']->contains($point->x, $point->y)) {
                $match[] = ['key' => $area['name'], 'order' => $area['order']];
                $sk[] = $area['order'];
                if (!empty($area['areas'])) {
                    foreach ($area['areas'] as $subarea) {
                        if ($subarea['polygon']->contains($point->x, $point->y)) {
                            $match[] = ['key' => $subarea['name'], 'order' => $subarea['order']];
                            $sk[] = $subarea['order'];
                        }
                    }
                }
            }
        }

        /**
         * mago analyze gives mixed-assignment warning without var types
         * @var array $sk
         * @var array $match
         */
        array_multisort($sk, SORT_NUMERIC, SORT_DESC, $match);

        /** @var T[] $match */
        return $match;
    }

    /**
     * Find parent zone that is present in world map using server coords
     */
    protected function findParentZone(Point $point): ?string
    {
        $regions = $this->getTargetRegions($point);
        foreach ($regions as $id) {
            if (isset($this->zones[$id])) {
                return $id;
            }
        }

        return null;
    }

    /**
     * Translate point (pyr) from src (fyros) to dst (fyros in world map)
     *
     * All arrays are in [ [left, bottom], [right, top] ]
     */
    protected function translate(Point $point, Bounds $src, Bounds $dst): Point
    {
        $px = ($point->x - $src->left) / $src->getWidth();
        $py = ($point->y - $src->top) / $src->getHeight();

        $left = $dst->left + ($px * $dst->getWidth());
        $top = $dst->bottom - ($py * $dst->getHeight());

        return new Point($left, $top);
    }

    protected function translateZone(Bounds $zone, Bounds $src, Bounds $dst): Bounds
    {
        $tl = $this->translate(new Point($zone->left, $zone->top), $src, $dst);
        $br = $this->translate(new Point($zone->right, $zone->bottom), $src, $dst);
        return new Bounds($tl->x, $br->y, $br->x, $tl->y);
    }
}
