<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\Tools\Console\Helper;

use Nel\Misc\BnpFile;
use Ryzom\Translation\Loader\WordsLoader;
use Ryzom\Translation\StringsManager;
use Symfony\Component\Console\Helper\Helper;

/**
 * Class TranslateHelper
 */
class TranslateHelper extends Helper
{

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'translator';
    }

    /**
     * @param string $ryzomDataPath
     *
     * @return \Ryzom\Translation\StringsManager
     */
    public function load($ryzomDataPath)
    {
        $bnp = new BnpFile($ryzomDataPath.'/gamedev.bnp');

        $sm = new StringsManager();
        $sm->register(new WordsLoader());

        $sm->load('place', $bnp->readFile('place_words_en.txt'), 'en');
        $sm->load('place', $bnp->readFile('place_words_fr.txt'), 'fr');
        $sm->load('place', $bnp->readFile('place_words_de.txt'), 'de');
        $sm->load('place', $bnp->readFile('place_words_es.txt'), 'es');
        $sm->load('place', $bnp->readFile('place_words_ru.txt'), 'ru');

        return $sm;
    }

}
