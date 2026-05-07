<?php

declare(strict_types=1);

/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/24/13
 * Time: 11:00 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Bmsite\Maps\BaseTypes;

class Label
{
    public Point $point;

    public string $text;

    public Color $color;

    // TODO: use enum
    public int $type;
}
