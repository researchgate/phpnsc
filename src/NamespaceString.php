<?php
/*
 * This file is part of phpnsc.
 *
 * (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rg\tools\phpnsc;

class NamespaceString
{
    private $namespaceVendor;
    private $root;
    private $fullFilePath;

    public function __construct($namespaceVendor, $root, $fullFilePath)
    {
        $this->namespaceVendor = $namespaceVendor;
        $this->root = $root;
        $this->fullFilePath = $fullFilePath;
    }

    public function getNamespace()
    {
        $namespace = '';
        $relativePath = str_replace($this->root, '', $this->fullFilePath);
        $relativePathParts = explode('/', $relativePath);
        for ($i = 1; $i < count($relativePathParts) - 1; ++$i) {
            $namespace .= '\\'.$relativePathParts[$i];
        }

        if ($this->namespaceVendor) {
            return $this->namespaceVendor.$namespace;
        }

        return trim($namespace, '\\');
    }

    public function __toString()
    {
        return $this->getNamespace();
    }
}
