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

    /**
     * @param string $version
     */
    public function __construct($version = 'leaflet')
    {
        $this->buildAssets($version);
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
        // TODO: cache
        $result = '';
        foreach ($this->js as $file) {
            $result .= file_get_contents($file);
        }
        return $result;
    }

    /**
     * @param string $version
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function buildAssets($version)
    {
        $jsFiles = array(
            /** @deprecated GoogleMaps v2 */
            'gmap2' => array(
                'deprecated/GoogleMaps2/map.js',
                'deprecated/GoogleMaps2/map-tooltip.js',
                'deprecated/GoogleMaps2/map-labels.js',
                'deprecated/GoogleMaps2/map-labels-control.js',
                'deprecated/GoogleMaps2/map-labels-overlay.js',
                //'map-overlay.js',
                'deprecated/GoogleMaps2/elabel.js',
                'deprecated/GoogleMaps2/markerclusterer.js',
                'deprecated/GoogleMaps2/markermanager.js',
            ),
            /** @deprecated GoogleMaps v3 */
            'gmap3' => array(
                'deprecated/GoogleMaps3/ryzom.maps.js',
                'deprecated/GoogleMaps3/ryzom.maps.icon.js',
                'deprecated/GoogleMaps3/ryzom.maps.convert.js',
                'deprecated/GoogleMaps3/tooltip.js',
                //'map-labels.js',
                //'map-labels-control.js', // - to select language
                //'map-labels-overlay.js', // - to display text on map - needs ELabel
            ),
            /** @deprecated  http://openlayers.org/ */
            'openlayers' => array(
                'deprecated/OpenLayers/Ryzom.js',
                'deprecated/OpenLayers/Ryzom.XY.js',
                'deprecated/OpenLayers/Cookie.js',
                'deprecated/OpenLayers/src/Icon.js',
                'deprecated/OpenLayers/src/Map.js',
                'deprecated/OpenLayers/Ryzom.Locations.js',
                //
                'deprecated/OpenLayers/src/Control.LanguageSwitcher.js',
                'deprecated/OpenLayers/src/Control.MousePosition.js',
                'deprecated/OpenLayers/src/Control.Tooltip.js',
                'deprecated/OpenLayers/src/Layer.Labels.js',
                'deprecated/OpenLayers/src/Layer.WorldTiles.js',
                'deprecated/OpenLayers/src/Style.Label.js',
            ),
            // http://leafletjs.com/
            'leaflet' => array(
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
            ),
        );

        if (!isset($jsFiles[$version])) {
            throw new \InvalidArgumentException(
                'Unsupported version ('.htmlspecialchars($version, ENT_QUOTES, 'UTF-8').')'
            );
        }

        $this->js = array();
        $this->lastModified = 0;
        foreach ($jsFiles[$version] as $file) {
            $filename = __DIR__.'/'.$file;

            $mtime = filemtime($filename);
            if ($mtime > $this->lastModified) {
                $this->lastModified = $mtime;
            }

            $this->js[] = $filename;
        }
    }
}
