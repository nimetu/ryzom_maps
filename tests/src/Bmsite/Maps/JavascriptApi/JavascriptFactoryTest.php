<?php

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2026 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */
namespace Bmsite\Maps\JavascriptApi;

class JavascriptFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testDefault()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Minifier not set');

        $js = new JavascriptFactory();
        $this->assertNotEmpty($js->dump());
        $this->assertSame("map.{$js->lastModified()}.js", $js->etag());

        $js->minify();
    }

    public function testIntegrity()
    {
        $len = $this->getBase64EncodedHashLength();

        $minifier = new JsMinifier();
        $js = new JavascriptFactory($minifier);

        $jshash = $js->integrity();
        $this->assertTrue(str_starts_with($jshash, 'sha384-'));
        $this->assertSame($len, strlen($jshash));

        $minhash = $js->integrity(true);
        $this->assertTrue(str_starts_with($minhash, 'sha384-'));
        $this->assertSame($len, strlen($minhash));

        $this->assertNotSame($jshash, $minhash);
    }

    public function testIntegrityWithoutMinifierForJs()
    {
        $len = $this->getBase64EncodedHashLength();

        $js = new JavascriptFactory();

        $jshash = $js->integrity();
        $this->assertTrue(str_starts_with($jshash, 'sha384-'));
        $this->assertSame($len, strlen($jshash));
    }

    public function testIntegrityWithoutMinifierForJsmin()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Minifier not set');

        $js = new JavascriptFactory();
        $js->integrity(true);
    }

    private function getBase64EncodedHashLength(): int
    {
        $b64 = base64_encode(hash('sha384', '*', true));
        return strlen('sha364-' . $b64);
    }
}
