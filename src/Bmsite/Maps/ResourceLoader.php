<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps;

/**
 * Class ResourceLoader
 */
class ResourceLoader
{

    /**
     * Return resources directory path
     *
     * @return string
     */
    public function getPath()
    {
        return __DIR__.'/Resources';
    }

    /**
     * Return full path + filename for requested file
     *
     * @param string $filename
     *
     * @return string
     */
    public function getFilePath($filename)
    {
        return $this->getPath().'/'.$filename;
    }

    /**
     * Check if file exists
     *
     * @param string $file
     *
     * @return bool
     */
    public function fileExists($file)
    {
        $path = $this->getFilePath($file);
        return file_exists($path);
    }

    /**
     * Return file content or boolean false if file does not exist
     *
     * @param string $name
     *
     * @return string|bool
     */
    public function getContents($name)
    {
        if ($this->fileExists($name)) {
            $path = $this->getFilePath($name);
            return file_get_contents($path);
        }
        return false;
    }

    /**
     * Load file content and decode it as json string
     *
     * @param string $filename
     * @param bool $assoc
     *
     * @return mixed
     */
    public function loadJson($filename, $assoc = true)
    {
        return json_decode($this->getContents($filename), $assoc);
    }
}

