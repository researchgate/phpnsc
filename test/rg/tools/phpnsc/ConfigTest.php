<?php


class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var FilesystemMock 
     */
    private $filesystem;
    /**
     *
     * @var rg\tools\phpnsc\Config 
     */
    private $config;
    
    protected function setUp() {
        parent::setUp();
        $this->filesystem = new FilesystemMock('/root/folder');
        $this->config = new rg\tools\phpnsc\Config($this->filesystem);
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

class FilesystemMock extends \rg\tools\phpnsc\FilesystemAccess
{
    public function getFile($filename) {
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