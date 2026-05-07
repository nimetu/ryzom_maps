<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\JavascriptApi;

/**
 * Class JavascriptBuilder
 */
class JavascriptFactory
{
    protected int $lastModified;

    protected ?MinifyInterface $minifier;

    protected string $header;

    private string $jscache;

    protected string $mincache;

    public function __construct(?MinifyInterface $minify = null)
    {
        $this->minifier = $minify;
        $this->buildAssets();
    }

    public function etag(): string
    {
        return "map.{$this->lastModified}.js";
    }

    public function lastModified(): int
    {
        return $this->lastModified;
    }

    public function dump(): string
    {
        return $this->jscache;
    }

    /**
     * Return minified version from js
     *
     * @throws \RuntimeException if minifier not set
     */
    public function minify(): string
    {
        if ($this->minifier === null) {
            throw new \RuntimeException('Minifier not set');
        }

        return $this->mincache;
    }

    /**
     * Return html script tag integrity hash
     *
     * @throws \RuntimeException if minifier not set
     */
    public function integrity(bool $minified = false, string $type = 'sha384'): string
    {
        if ($minified && !$this->minifier) {
            throw new \RuntimeException('Minifier not set');
        }

        $js = $minified ? $this->mincache : $this->jscache;

        $hash = base64_encode(hash($type, $js, true));
        return "{$type}-{$hash}";
    }

    /**
     * Concatenate all javascript files into single string
     */
    protected function buildAssets()
    {
        $this->lastModified = 0;
        $this->jscache = '';
        $this->mincache = '';
        $this->header = '';

        $jsFiles = [
            '__header' => 'Leaflet/header.txt',
            'Leaflet/OpenLayers.Geometry.js',
            'Leaflet/Ryzom.js',
            'Leaflet/Ryzom.XY.js',
            'Leaflet/Ryzom.Map.js',
            'Leaflet/Ryzom.Icon.js',
            'Leaflet/control/MousePosition.js',
            'Leaflet/geo/projection/RyzomServer.js',
            'Leaflet/geo/projection/RyzomWorld.js',
            'Leaflet/geo/crs/RyzomServer.js',
            'Leaflet/geo/crs/RyzomWorld.js',
        ];

        foreach ($jsFiles as $k => $file) {
            $filename = __DIR__ . '/' . $file;
            if ($k === '__header') {
                $this->header = file_get_contents($filename);
                continue;
            }

            $mtime = filemtime($filename);
            if ($mtime > $this->lastModified) {
                $this->lastModified = $mtime;
            }

            $this->jscache .= file_get_contents($filename);
        }

        if ($this->minifier) {
            $this->mincache = $this->header;
            $this->mincache .= $this->minifier->minify($this->jscache);
        }
    }
}
