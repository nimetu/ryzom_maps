<?php

namespace Bmsite\Maps\JavascriptApi;

use JavaScriptPacker;

/**
 * Simple wrapper around JavascriptPacker
 */
class JsMinifier implements MinifyInterface
{
    public function minify(string $str): string
    {
        /** @var string $min */
        $min = (new JavaScriptPacker($str))->pack();
        return $min;
    }
}
