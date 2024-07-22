<?php

namespace Bmsite\Maps;

use Bmsite\Maps\Tiles\TileStorageInterface;

class MockTileStorage implements  TileStorageInterface
{
    private $ext;
    public function setMapMode($mode){}
    public function setMapName($name){}
    public function setImageExt($ext){ $this->ext = $ext; }
    public function set($z, $x, $y, $img){}
    public function get($z, $x, $y){
        $im = imagecreatetruecolor(self::TILE_SIZE, self::TILE_SIZE);
        $bg = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $bg);

        $hw = self::TILE_SIZE / 2;
        $hh = self::TILE_SIZE / 2;

        $cx = $hw;
        $cy = $hh;
        $text = sprintf("z=%d y=%d, x=%d", $z, $y, $x);
        $red = imagecolorallocate($im, 255, 0, 0);
        imagestring($im, 5, 2, 2, $text, $red);

        $green = imagecolorallocate($im, 0, 255, 0);
        imageline($im, 0, 0, $hw, 0, $red);
        imageline($im, 0, 0, 0, $hh, $green);
        imageline($im, self::TILE_SIZE, self::TILE_SIZE, $hw, 0, $red);
        imageline($im, self::TILE_SIZE, self::TILE_SIZE, 0, $hh, $green);

        return $im;
    }
    public function delete($z, $x, $y){}
}

