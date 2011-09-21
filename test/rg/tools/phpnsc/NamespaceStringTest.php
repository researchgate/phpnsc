<?php

class NamespaceStringTest extends PHPUnit_Framework_TestCase {
    public function testCreateNamespaceString() {
        $namespaceVendor = 'vendor';
        $root = '/root/folder';
        $fullFilePath = '/root/folder/namespace/File.php';
        
        $namespaceString = new rg\tools\phpnsc\NamespaceString($namespaceVendor, $root, $fullFilePath);
        
        $this->assertEquals('vendor\namespace', (string) $namespaceString);
        $this->assertEquals((string) $namespaceString, $namespaceString->getNamespace());
    }
}

