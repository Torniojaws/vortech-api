<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');

class AutoloadTest extends TestCase
{
    public function setUp()
    {
        $this->autoload = new \VortechAPI\Autoloader\Loader();
    }

    public function testLoadWithExistingFile()
    {
        $file = '\Apps\News\GetNews';
        $fileLoaded = $this->autoload->load($file);

        $this->assertTrue($fileLoaded);
    }

    public function testLoadWithMissingFile()
    {
        $file = 'Foo\Bar\Stuff';
        $fileLoaded = $this->autoload->load($file);

        $this->assertFalse($fileLoaded);
    }
}
