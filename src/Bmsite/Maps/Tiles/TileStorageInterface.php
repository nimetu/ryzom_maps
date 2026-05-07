<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\Tiles;

use GdImage;

/**
 * Class TileStorageInterface
 */
interface TileStorageInterface
{
    /**
     * Tile size in pixels
     */
    const TILE_SIZE = 256;

    /**
     * @param string $mode world|server
     */
    public function setMapMode(string $mode);

    /**
     * @param string $name atys|atys_sp|lang_en
     */
    public function setMapName(string $name);

    /**
     * @param string $ext png|jpg
     */
    public function setImageExt(string $ext);

    /**
     * @throws \RuntimeException if output directory does not exist and cannot be created
     * @throws \InvalidArgumentException if image type is unknown
     */
    public function set(int $z, int $x, int $y, GdImage $img);

    public function get(int $z, int $x, int $y): ?GdImage;

    public function delete(int $z, int $x, int $y);
}
