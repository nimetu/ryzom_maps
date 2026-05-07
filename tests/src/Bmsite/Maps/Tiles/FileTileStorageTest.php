<?php

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\Tiles;

use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(FileTileStorage::class)]
class FileTileStorageTest extends \PHPUnit\Framework\TestCase
{
	public function	testGetJpegTile()
	{
		$path = __DIR__.'/_files/tiles';
		$ts = new FileTileStorage($path);
		$this->assertSame('world', $this->getProperty($ts, 'mapmode'));
		$this->assertSame('atys', $this->getProperty($ts, 'mapname'));
		$this->assertSame('jpg', $this->getProperty($ts, 'ext'));
		$this->assertInstanceOf(\GdImage::class, $ts->get(10, 20, 30));
	}

	public function testGetPngTile()
	{
		$path = __DIR__.'/_files/tiles';
		$ts = new FileTileStorage($path);
		$ts->setImageExt('png');
		$this->assertSame('world', $this->getProperty($ts, 'mapmode'));
		$this->assertSame('atys', $this->getProperty($ts, 'mapname'));
		$this->assertSame('png', $this->getProperty($ts, 'ext'));
		$this->assertInstanceOf(\GdImage::class, $ts->get(10, 20, 40));
	}

	public function testGetNonExistTile()
	{
		$path = __DIR__.'/_files/tiles';
		$ts = new FileTileStorage($path);
		$this->assertNull($ts->get(0, 0, 0));
	}

	public function testGetFilename()
	{
		$path = __DIR__.'/_files/tiles';
		$ts = new FileTileStorage($path);
		$expect = $path . '/world/atys/1/2/3.jpg';
		$this->assertSame($expect, $this->callGetFilename($ts, 1, 2, 3));
	}

	public function testSetDeleteTile()
	{
		$path = $this->createTempDirectory();

		$ts = new FileTileStorage($path);
		$tile = imagecreatetruecolor(1, 1);
		$ts->set(100, 200, 300, $tile);
		$file = $path . '/world/atys/100/200/300.jpg';
		$this->assertFileExists($file);

		$img = imagecreatefromjpeg($file);
		$this->assertInstanceOf(\GdImage::class, $img);

		$ts->delete(100, 200, 300);
		$this->assertFileDoesNotExist($file);

		rmdir($path . '/world/atys/100/200');
		rmdir($path . '/world/atys/100');
		rmdir($path . '/world/atys');
		rmdir($path . '/world');

		$ts->setMapMode('server');
		$ts->setMapName('atys_sp');
		$ts->setImageExt('png');
		$ts->set(101, 201, 301, $tile);
		$file = $path . '/server/atys_sp/101/201/301.png';
		$this->assertFileExists($file);

		$img = imagecreatefrompng($file);
		$this->assertInstanceOf(\GdImage::class, $img);

		$ts->delete(101, 201, 301);
		$this->assertFileDoesNotExist($file);

		rmdir($path . '/server/atys_sp/101/201');
		rmdir($path . '/server/atys_sp/101');
		rmdir($path . '/server/atys_sp');
		rmdir($path . '/server');

		rmdir($path);

		$this->assertDirectoryDoesNotExist($path);
	}

	public function testReadOnlyTileStorageOnSave()
	{
		$tmpdir = $this->createTempDirectory();
		$path = $tmpdir . '/read-only';
		mkdir($path, 0o500);
		$this->assertDirectoryExists($path);

		$ts = new FileTileStorage($path);
		$img = imagecreate(1,1);

		try {
			$ts->set(1, 1, 1, $img);
		} catch(\RuntimeException $ex) {
			$this->assertSame("Unable to create output directory '{$path}/world/atys/1/1/1.jpg'", $ex->getMessage());

			rmdir($path);
			rmdir($tmpdir);

			return;
		}

		$this->fail('FileTileStorage::set() mkdir should of failed and exception should of thrown');
	}

	private function callGetFilename(FileTileStorage $ts, ...$args)
	{
		$ref = new ReflectionClass(FileTileStorage::class);
		$method = $ref->getMethod('getFilename');
		$method->setAccessible(true);

		return $method->invoke($ts, ...$args);
	}

	private function getProperty(FileTileStorage $ts, string $prop)
	{
		$ref = new ReflectionClass(FileTileStorage::class);
		$prop = $ref->getProperty($prop);
		$prop->setAccessible(true);

		return $prop->getValue($ts);
	}

	private function createTempDirectory()
	{
		$tempfile = tempnam(sys_get_temp_dir(), '');
		$path = $tempfile . '.dir';
		mkdir($path, 0o0700, true);
		unlink($tempfile);

		$this->assertDirectoryExists($path, 'Creating temporary directory failed');
		$this->assertFileDoesNotExist($tempfile);

		return $path;
	}
}
