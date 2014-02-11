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
 * Class FileTileStorage
 */
class FileTileStorage implements TileStorageInterface
{

    /** @var string */
    protected $tiledir;

    protected $ext = 'jpg';

    /** @var string */
    protected $mapmode;

    /** @var string */
    protected $mapname;

    /**
     * @param string $tiledir
     */
    public function __construct($tiledir)
    {
        $this->tiledir = $tiledir;
        $this->mapmode = 'world';
        $this->mapname = 'atys';
    }

    /** {@inheritdoc} */
    public function setMapMode($mode)
    {
        $this->mapmode = $mode;
    }

    /** {@inheritdoc} */
    public function setMapName($name)
    {
        $this->mapname = $name;
    }

    /**
     * @param string $ext png|jpg
     */
    public function setImageExt($ext)
    {
        $this->ext = $ext;
    }

    /** {@inheritdoc} */
    public function set($z, $x, $y, $img)
    {
        $file = $this->getFilename($z, $x, $y, true);

        if (!$file) {
            throw new \RuntimeException("Unable to save image file ({$z}/{$x}/{$y})");
        }
        switch ($this->ext) {
            case 'png':
                imagepng($img, $file, 9);
                break;
            case 'jpg':
            default:
                imagejpeg($img, $file, 90);
                break;
        }
    }

    /** {@inheritdoc} */
    public function get($z, $x, $y)
    {
        $file = $this->getFilename($z, $x, $y);
        if (file_exists($file)) {
            switch ($this->ext) {
                case 'png':
                    $out = imagecreatefrompng($file);
                    break;
                case 'jpg':
                default:
                    $out = imagecreatefromjpeg($file);
                    break;
            }
            return $out;
        }
        return null;
    }

    /** {@inheritdoc} */
    public function delete($z, $x, $y)
    {
        $file = $this->getFilename($z, $x, $y);
        unlink($file);
    }

    /**
     * @param int $z
     * @param int $x
     * @param int $y
     * @param bool $mkdir
     *
     * @return null|string
     * @throws \RuntimeException
     */
    protected function getFilename($z, $x, $y, $mkdir = false)
    {
        $file = $this->tiledir."/{$this->mapmode}/{$this->mapname}/{$z}/{$x}";
        if (!file_exists($file)) {
            if ($mkdir && !mkdir($file, 0775, true)) {
                throw new \RuntimeException("Unable to create output directory {$file}");
            }
        }
        return $file."/{$y}.{$this->ext}";
    }
}
