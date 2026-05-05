<?php

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\Tiles;

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
    public function setMapMode($mode);

    /**
     * @param string $name atys|atys_sp|lang_en
     */
    public function setMapName($name);

    /**
     * @param string $ext png|jpg
     */
    public function setImageExt($ext);

    /**
     * @param int $z
     * @param int $x
     * @param int $y
     * @param resource $img
     */
    public function set($z, $x, $y, $img);

    /**
     * @param int $z
     * @param int $x
     * @param int $y
     */
    public function get($z, $x, $y);

    /**
     * @param int $z
     * @param int $x
     * @param int $y
     */
    public function delete($z, $x, $y);
}
