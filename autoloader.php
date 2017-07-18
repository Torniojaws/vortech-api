<?php

namespace VortechAPI\Autoloader;

class Loader
{
    public static function load($namespace)
    {
        $filename = __DIR__.'/'.strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $namespace).'.php');
        if (file_exists($filename)) {
            require_once($filename);
        }
    }
}
