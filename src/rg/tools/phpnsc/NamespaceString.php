<?php
namespace rg\tools\phpnsc;

class NamespaceString {
    private $namespaceVendor;
    private $root;
    private $fullFilePath;
    
    public function __construct($namespaceVendor, $root, $fullFilePath) {
        $this->namespaceVendor = $namespaceVendor;
        $this->root = $root;
        $this->fullFilePath = $fullFilePath;
    }
    
    public function getNamespace() {
        $namespace = $this->namespaceVendor;
        $relativePath = str_replace($this->root, '', $this->fullFilePath);
        $relativePathParts = explode('/', $relativePath);
        for ($i = 1; $i < count($relativePathParts) - 1; $i++) {
            $namespace .= '\\' . $relativePathParts[$i];
        }
        return $namespace;
    }
    
    public function __toString() {
        return $this->getNamespace();
    }
}
