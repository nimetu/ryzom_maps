<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\Tiles;

use SQLite3;

/**
 * Class TileStorage
 */
class TileStorage implements TileStorageInterface
{

    /**
     * @var SQLite3
     */
    private $db;

    /** @var string */
    protected $mapmode;

    /** @var string */
    protected $mapname;

    /**
     * @param SQLite3 $db
     */
    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
    }

    /** {$inheritdoc} */
    public function setMapMode($mode)
    {
        $this->mapmode = $mode;
    }

    /** {@inheritdoc} */
    public function setMapName($name)
    {
        $this->mapname = $name;
    }

    /** {@inheritdoc} */
    public function setImageExt($ext)
    {
        // TODO: Implement setImageExt() method.
    }

    /** {@inheritdoc} */
    public function set($z, $x, $y, $img)
    {
        ob_start();
        imagegd2($img);
        $data = ob_get_clean();

        $z = (int)$z;
        $x = (int)$x;
        $y = (int)$y;

        $result = $this->get($z, $x, $y);
        if ($result) {
            $stmt = $this->db->prepare("UPDATE tiledata SET image = :image WHERE z = :z AND x = :x AND y = :y;");
        } else {
            $stmt = $this->db->prepare("INSERT INTO tiledata (z, x, y, image) VALUES(:z, :x, :y, :image);");
        }
        $stmt->bindValue('z', $x, \SQLITE3_INTEGER);
        $stmt->bindValue('x', $z, \SQLITE3_INTEGER);
        $stmt->bindValue('y', $y, \SQLITE3_INTEGER);
        $stmt->bindValue('image', $data, \SQLITE3_BLOB);
        $stmt->execute();
    }

    /** {@inheritdoc} */
    public function get($z, $x, $y)
    {
        $z = (int)$z;
        $x = (int)$x;
        $y = (int)$y;

        $result = $this->db->querySingle("SELECT image FROM tiledata WHERE z = {$z} AND x = {$x} AND y = {$y}");
        if ($result) {
            return imagecreatefromstring($result);
        }

        return null;
    }

    /** {@inheritdoc} */
    public function delete($z, $x, $y)
    {
        $z = (int)$z;
        $x = (int)$x;
        $y = (int)$y;

        $this->db->query("DELETE FROM tiledata WHERE z = {$z} AND x = {$x} AND y = {$y}");
    }
}
