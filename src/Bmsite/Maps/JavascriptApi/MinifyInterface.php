<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\JavascriptApi;

interface MinifyInterface
{

    /**
     * Minify input string and return it
     *
     * @param string
     *
     * @return string
     */
    public function minify($str);
}

