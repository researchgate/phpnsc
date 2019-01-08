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

class DirectoryScanner
{
    /**
     * @var FilesystemAccess
     */
    private $filesystem;

    private $root;

    private $directoryIncludes = [];
    private $directoryExcludes = [];
    private $filetypeIncludes = [];
    private $filetypeExcludes = [];
    private $directorySeparator;

    /**
     * @param FilesystemAccess $filesystem
     * @param string $root
     * @param string $directorySeparator
     */
    public function __construct(FilesystemAccess $filesystem, $root, $directorySeparator = DIRECTORY_SEPARATOR)
    {
        $this->filesystem = $filesystem;
        $this->root = $root;
        $this->directorySeparator = $directorySeparator;
    }

    /**
     * @param string $directory
     */
    public function includeDirectory($directory)
    {
        $this->directoryIncludes[] = $directory;
    }

    /**
     * @param string $directory
     */
    public function excludeDirectory($directory)
    {
        $this->directoryExcludes[] = $directory;
    }

    /**
     * @param string $filetype
     */
    public function includeFiletype($filetype)
    {
        $this->filetypeIncludes[] = $filetype;
    }

    /**
     * @param string $filetype
     */
    public function excludeFiletype($filetype)
    {
        $this->filetypeExcludes[] = $filetype;
    }

    /**
     * get all files that should be analyzed and modified.
     *
     * @return array
     */
    public function getFiles()
    {
        $files = [];
        foreach ($this->directoryIncludes as $directory) {
            $files = array_merge($files, $this->getFilesFromDir($this->root.$this->directorySeparator.$directory));
        }

        return $files;
    }

    /**
     * get all files with included filetypes from given directory and all subdirectories
     * that are not in a subdirectory from the excluded directory list.
     *
     * @param string $dir
     *
     * @return array
     */
    private function getFilesFromDir($dir)
    {
        $files = [];
        if ($handle = $this->filesystem->openDirectory($dir)) {
            while (false !== ($file = $this->filesystem->readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $fullPath = $dir.$this->directorySeparator.$file;
                    if ($this->filesystem->isDir($fullPath)) {
                        if (!$this->isDirectoryExcluded($fullPath)) {
                            $files[] = $this->getFilesFromDir($fullPath);
                        }
                    } elseif ($this->isFiletypeIncluded($file)) {
                        $files[] = $this->filesystem->realpath($fullPath);
                    }
                }
            }
            $this->filesystem->closeDirectory($handle);
        }

        return $this->array_flat($files);
    }

    /**
     * checks if current filetype is included in the filetype list and not excluded
     * from it.
     *
     * @param string $file
     *
     * @return bool
     */
    private function isFiletypeIncluded($file)
    {
        foreach ($this->filetypeIncludes as $included) {
            if (substr($file, strlen($included) * -1) === $included) {
                foreach ($this->filetypeExcludes as $excluded) {
                    if (substr($file, strlen($excluded) * -1) === $excluded) {
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * checks if given directory is part of the excluded directory list.
     *
     * @param string $dir
     *
     * @return bool
     */
    private function isDirectoryExcluded($dir)
    {
        foreach ($this->directoryExcludes as $excluded) {
            if ($this->filesystem->realpath($this->root.$this->directorySeparator.$excluded) === $this->filesystem->realpath($dir)) {
                return true;
            }
        }

        return false;
    }

    /**
     * small helper function to flatten a multi dimensional array.
     *
     * @param array $array
     *
     * @return array
     */
    private function array_flat($array)
    {
        $tmp = [];
        foreach ($array as $a) {
            if (is_array($a)) {
                $tmp = array_merge($tmp, $this->array_flat($a));
            } else {
                $tmp[] = $a;
            }
        }

        return $tmp;
    }
}
