<?php
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
    /** @var string */
    protected $path;

    /** @var array */
    protected $js;

    /** @var int */
    protected $lastModified;

    /** @var \Bmsite\Maps\MinifyInterface */
    protected $minifier;

    /** @var string */
    protected $header;

    /** @var string */
    private $jscache;

    /** @var string */
    protected $mincache;

    public function __construct(?MinifyInterface $minify)
    {
        $this->minifier = $minify;
        $this->buildAssets();
    }

    /**
     * @return string
     */
    public function etag()
    {
        return "map.{$this->lastModified}.js";
    }

    /**
     * @return string
     */
    public function dump()
    {
        if (empty($this->jscache)) {
            $this->cachejs();
        }

        return $this->jscache;
    }

    /**
     * Return minified version from js
     *
     * @return string
     *
     * @throws \RuntimeException if minifier not set
     */
    public function minify()
    {
        if ($this->minifier === null) {
            throw new \RuntimeException("Minifier not set");
        }

        if (empty($this->jscache) || empty($this->mincache)) {
            $this->cachejs();
            $this->cachemin();
        }

        return $this->mincache;

    }

    /**
     * Return html script tag integrity hash
     *
     * @param bool $minified
     * @param string $type
     *
     * @return string
     */
    public function integrity($minified = false, $type = 'sha384')
    {
        if (empty($this->jscache)) {
            $this->cachejs();
        }
        if ($minified && empty($this->mincache)) {
            $this->cachemin();
        }

        $js = $minified ? $this->mincache : $this->jscache;
        $hash = base64_encode(hash($type, $js, true));
        return "{$type}-{$hash}";
    }

    /**
     * Rebuild js cache from $js files array
     */
    protected function cachejs()
    {
        $result = '';
        foreach ($this->js as $file) {
            $result .= file_get_contents($file);
        }

        $this->jscache = $result;
    }

    /**
     * Rebuild minified js cache with header
     */
    protected function cachemin()
    {
        if (!empty($this->jscache)) {
            $this->cachejs();
        }

        $this->mincache = $this->header;
        $this->mincache .= $this->minifier->minify($this->jscache);
    }

    /**
     * @return array
     */
    protected function buildAssets()
    {
        $jsFiles = array(
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
        );

        $this->js = array();
        $this->lastModified = 0;
        $this->jscache = '';
        $this->mincache = '';
        $this->header = '';

        foreach ($jsFiles as $k => $file) {
            $filename = __DIR__.'/'.$file;
            if ($k === '__header') {
                $this->header = file_get_contents($filename);
                continue;
            }

            $mtime = filemtime($filename);
            if ($mtime > $this->lastModified) {
                $this->lastModified = $mtime;
            }

            $this->js[] = $filename;
        }
    }
}

