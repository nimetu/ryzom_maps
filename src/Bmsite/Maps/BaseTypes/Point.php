<?php

declare(strict_types=1);

/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/18/13
 * Time: 2:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Bmsite\Maps\BaseTypes;

class Point
{
    public float $x;

    public float $y;

    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getDistance(float $x, float $y): float
    {
        /** @var float $dx */
        $dx = pow(abs($this->x - $x), 2);
        /** @var float $dy */
        $dy = pow(abs($this->y - $y), 2);

        return sqrt($dx + $dy);
    }

    /**
     * @return array{float,float} [x, y]
     */
    public function asArray(): array
    {
        return [$this->x, $this->y];
    }

    public function __toString(): string
    {
        return sprintf('Point{%.2f,%.2f}', $this->x, $this->y);
    }
}
