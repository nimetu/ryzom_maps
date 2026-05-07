<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Layer;

use Bmsite\Maps\StaticMap\StaticMapGenerator;

class LangTileLayer extends TileLayer
{
    protected string $lang;

    protected string $mapname;

    protected string $tileExtension;

    public function __construct(StaticMapGenerator $map, int $minZoom, int $maxZoom)
    {
        parent::__construct($map, $minZoom, $maxZoom);

        $this->lang = 'en';
        $this->mapname = 'lang_en';
        $this->tileExtension = 'png';
    }

    public function setLanguage(string $lang)
    {
        $this->lang = $lang;
        $this->mapname = 'lang_' . $lang;
    }
}
