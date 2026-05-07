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

/**
 * Class FileTileStorage
 */
class FileTileStorage implements TileStorageInterface
{
    /** @var string */
    protected $tiledir;

    /** @var string */
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
    public function set(int $z, int $x, int $y, \GdImage $img)
    {
        $file = $this->getFilename($z, $x, $y);

        $dir = dirname($file);
        /** @mago-expect lint:no-error-control-operator */
        if (!file_exists($dir) && !@mkdir($dir, 0775, true)) {
            throw new \RuntimeException("Unable to create output directory '{$file}'");
        }

        match ($this->ext) {
            'png' => imagepng($img, $file, 9),
            'jpg' => imagejpeg($img, $file, 90),
            default => throw new \InvalidArgumentException('Unknown image type'),
        };
    }

    /** {@inheritdoc} */
    public function get(int $z, int $x, int $y): ?\GdImage
    {
        $file = $this->getFilename($z, $x, $y);
        if (!file_exists($file)) {
            return null;
        }

        return match ($this->ext) {
            'png' => imagecreatefrompng($file),
            'jpg' => imagecreatefromjpeg($file),
            default => null,
        };
    }

    /** {@inheritdoc} */
    public function delete($z, $x, $y)
    {
        $file = $this->getFilename($z, $x, $y);
        unlink($file);
    }

    protected function getFilename(int $z, int $x, int $y): string
    {
        $file = $this->tiledir . "/{$this->mapmode}/{$this->mapname}/{$z}/{$x}";
        return $file . "/{$y}.{$this->ext}";
    }
}
