<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\StaticMap\Layer;

/**
 * Class TextLayer
 */
class LangTileLayer extends TileLayer
{

    /** @var string */
    protected $lang = '';

    /** @var string */
    protected $mapname = 'lang_en';

    /** @var string */
    protected $tileExtension = 'png';

    /**
     * @param string $lang
     */
    public function setLanguage($lang)
    {
        $this->lang = $lang;
        $this->mapname = 'lang_'.$lang;
    }

}
