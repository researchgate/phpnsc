<?php
namespace rg\tools\phpnsc;

use PHPUnit\Framework\TestCase;

class NamespaceStringTest extends TestCase {

    public function testCreateNamespaceString() {
        $namespaceVendor = 'vendor';
        $root = '/root/folder';
        $fullFilePath = '/root/folder/namespace/File.php';

        $namespaceString = new NamespaceString($namespaceVendor, $root, $fullFilePath);

        $this->assertEquals('vendor\namespace', (string) $namespaceString);
        $this->assertEquals((string) $namespaceString, $namespaceString->getNamespace());
    }

    public function testCreateNamespaceStringWithoutVendor() {
        $namespaceVendor = '';
        $root = '/root/folder';
        $fullFilePath = '/root/folder/namespace/File.php';

        $namespaceString = new NamespaceString($namespaceVendor, $root, $fullFilePath);

        $this->assertEquals('namespace', (string) $namespaceString);
        $this->assertEquals((string) $namespaceString, $namespaceString->getNamespace());
    }
}

