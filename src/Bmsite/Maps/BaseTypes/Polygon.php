<?php

declare(strict_types=1);

/**
 * Date: 1/1/20
 * Time: 2:54 PM
 */

namespace Bmsite\Maps\BaseTypes;

class Polygon
{
    /** @var float[] */
    public array $points;

    /**
     * @param float[] $points
     */
    public function __construct(array $points)
    {
        $this->points = $points;
    }

    /**
     * @return float[]
     */
    public function asArray(): array
    {
        return $this->points;
    }

    public function __toString(): string
    {
        $ret = [];
        foreach ($this->points as $p) {
            $ret[] = sprintf('%.2f', $p);
        }
        return 'Polygon{' . join(',', $ret) . '}';
    }

    /**
     * Test if x,y is inside polygon.
     *
     * https://wrf.ecse.rpi.edu//Research/Short_Notes/pnpoly.html
     *
     * @param float $x
     * @param float $y
     *
     * @return bool
     */
    public function contains(float $x, float $y): bool
    {
        $nbPoints = count($this->points);
        if ($nbPoints < 6) {
            return false;
        }
        $success = false;
        for ($i = 0, $j = $nbPoints - 2; $i < $nbPoints; $j = $i, $i += 2) {
            $iX = $this->points[$i];
            $iY = $this->points[$i + 1];
            $jX = $this->points[$j];
            $jY = $this->points[$j + 1];
            if ($iY > $y !== $jY > $y && $x < (((($jX - $iX) * ($y - $iY)) / ($jY - $iY)) + $iX)) {
                $success = !$success;
            }
        }
        return $success;
    }
}
