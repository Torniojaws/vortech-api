<?php

namespace VortechAPI\Autoloader;

class Loader
{
    public static function load($namespace)
    {
        $filename = __DIR__.'/'.strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $namespace).'.php');

        // This is purely to get 100 % coverage
        $fileWasFound = file_exists($filename);
        if ($fileWasFound) {
            require_once($filename);
        }

        return $fileWasFound;
    }
}
