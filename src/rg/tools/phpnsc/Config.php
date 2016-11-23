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

class Config
{
    /**
     * @var FilesysyemAccess
     */
    private $filesystem;

    /**
     * @var object
     */
    private $config;

    /**
     * @param FilesystemAccess $filesystem
     */
    public function __construct(FilesystemAccess $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $file full path to config file
     */
    public function loadConfig($file)
    {
        $fileContent = $this->filesystem->getFile($this->filesystem->realpath($file));
        $this->config = json_decode($fileContent);
    }

    /**
     * @return object
     */
    public function getConfig()
    {
        return $this->config;
    }
}
