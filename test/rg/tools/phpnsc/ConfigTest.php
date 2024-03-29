<?php
namespace rg\tools\phpnsc;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void {
        parent::setUp();
        $filesystem = new FilesystemMock('/root/folder');
        $this->config = new Config($filesystem);
    }

    public function testLoadConfig() {
        $this->config->loadConfig('foo');
        $config = $this->config->getConfig();
        $this->assertEquals('researchgate', $config->vendor);
        $this->assertEquals('/Users/bastian/Checkouts/researchgate/webroot', $config->folders->root);
        $this->assertEquals(array(
            'modules', 'core', 'models'
        ), $config->folders->include);
    }
}

class FilesystemMock extends FilesystemAccess
{
    public function getFile($filename): string {
        return '{
    "vendor" : "researchgate",
    "folders" : {
        "root"    : "/Users/bastian/Checkouts/researchgate/webroot",
        "include" : ["modules", "core", "models"],
        "exclude" : ["core/lib"]
    },
    "filetypes" : {
        "include" : [".php"],
        "exclude" : [".tpl.php"]
    },
    "modificators" : {
        "preg_replace" : [ { 
                "search" : "\\/Autoloader\\\\:\\\\:addPackage\\\\((\'|\\\\\\")[a-zA-Z0-9_\\\\\\/\\\\*]+(\'|\\\\\\")\\\\);\\/i",
                "replace" : ""
            },{ 
                "search" : "\\/Autoloader\\\\:\\\\:addClass((\'|\\\\\\")[a-zA-Z0-9_\\\\\\/\\\\*]+(\'|\\\\\\")\\\\);\\/i",
                "replace" : ""
            }
        ]
    }
}';
    }

    public function realpath($path) {
        return $path;
    }
}
