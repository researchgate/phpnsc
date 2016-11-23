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

class FilesystemAccess
{
    private $root;

    public function __construct($root)
    {
        $this->root = $root;
    }

    public function setRoot($root)
    {
        $this->root = $root;
    }

    public function getFile($filename)
    {
        if ($this->root && strpos($filename, $this->root) !== 0) {
            throw new \Exception('can not read file '.$filename.' because it is not in configured root '.$this->root);
        }

        return file_get_contents($filename);
    }

    public function openDirectory($directory)
    {
        return opendir($directory);
    }

    public function closeDirectory($handle)
    {
        return closedir($handle);
    }

    public function readdir($handle)
    {
        return readdir($handle);
    }

    public function realpath($path)
    {
        return realpath($path);
    }

    public function isDir($path)
    {
        return is_dir($path);
    }
}
