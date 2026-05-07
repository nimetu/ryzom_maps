<?php

declare(strict_types=1);

/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps;

class ResourceLoader
{
    public function __construct(
        private string $path = __DIR__ . '/Resources',
    ) {}

    /**
     * Return full path + filename for requested file
     */
    public function getFilePath(string $filename): string
    {
        return $this->path . '/' . $filename;
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $file): bool
    {
        return file_exists($this->getFilePath($file));
    }

    /**
     * Return file content or boolean false if file does not exist
     */
    public function getContents(string $name): string|bool
    {
        if ($this->fileExists($name)) {
            $path = $this->getFilePath($name);
            return file_get_contents($path);
        }
        return false;
    }

    /**
     * Load file content and decode it as json string
     */
    public function loadJson(string $filename, bool $assoc = true): ?array
    {
        return json_decode($this->getContents($filename), $assoc);
    }
}
