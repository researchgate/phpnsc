<?php
namespace rg\test\tools\phpnsc;

use PHPUnit\Framework\TestCase;
use rg\tools\phpnsc\NamespaceString;

class NamespaceStringTest extends TestCase {
    public function testCreateNamespaceString() {
        $namespaceVendor = 'vendor';
        $root = '/root/folder';
        $fullFilePath = '/root/folder/namespace/File.php';

        $namespaceString = new NamespaceString($namespaceVendor, $root, $fullFilePath);

        $this->assertEquals('vendor\namespace', (string) $namespaceString);
        $this->assertEquals((string) $namespaceString, $namespaceString->getNamespace());
    }
}

