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

class NamespaceDependencyChecker
{
    /**
     * @var FilesystemAccess
     */
    private $filesystem;
    /**
     * @var ClassScanner
     */
    private $classScanner;
    private $root;
    private $namespaceVendor;
    private $definedEntities;

    /**
     * @var Output
     */
    private $output;

    /**
     * @var bool
     */
    public $foundError;

    /**
     * @param FilesystemAccess $filesystem
     * @param ClassScanner     $classScanner
     * @param string           $namespaceVendor
     * @param string           $root
     * @param Output           $output
     */
    public function __construct(FilesystemAccess $filesystem, ClassScanner $classScanner, $namespaceVendor, $root,
            Output $output)
    {
        $this->filesystem = $filesystem;
        $this->classScanner = $classScanner;
        $this->root = $root;
        $this->namespaceVendor = $namespaceVendor;
        $this->output = $output;
        $this->foundError = false;
    }

    /**
     * @param array $files
     */
    public function analyze(array $files)
    {
        $this->output->writeln('Got '.count($files).' files');
        $this->output->writeln('Collect entities...');
        $this->definedEntities = $this->classScanner->getDefinedEntities();
        $this->output->writeln('Got '.count($this->definedEntities).' defined entities');
        $this->output->writeln('Check namespaces...');
        $progressbar = new Progressbar($this->output, count($files));
        foreach ($files as $file) {
            $this->analyzeFile($file);
            $progressbar->step();
        }
    }

    /**
     * @param string $file full path to file
     */
    private function analyzeFile($file)
    {
        $fileContent = $this->filesystem->getFile($file);
        $entitiesUsedInFile = $this->classScanner->getUsedEntities($file);

        $fileNamespace = (string) new NamespaceString($this->namespaceVendor, $this->root, $file);
        $fileNamespaceLength = strlen($fileNamespace) + 1;
        foreach ($entitiesUsedInFile as $usedEntity => $lines) {
            // we have a fully qualified name, so we do not need any use statements
            if (substr($usedEntity, 0, 1) === '\\') {
                continue;
            }
            if (substr($usedEntity, 0, 1) === '$') {
                continue;
            }
            $simpleName = $aliasName = $usedEntity;
            if (strpos($usedEntity, '\\') > 0) {
                $parts = explode('\\', $usedEntity);
                $simpleName = $parts[count($parts) - 1];
                $aliasName = $parts[0];
            }
            if (isset($this->definedEntities[$simpleName])) {
                foreach ($this->definedEntities[$simpleName]['namespaces'] as $usedEntityNamespace) {
                    if ($usedEntityNamespace === $fileNamespace) {
                        continue 2;
                    }
                    $usedEntityNamespaceT = $usedEntityNamespace.'\\'.$simpleName;
                    if (preg_match('/\Wuse\s+\\\?'.str_replace('\\', '\\\\', $usedEntityNamespaceT).';/', $fileContent)) {
                        continue 2;
                    }
                    if (strpos($usedEntityNamespaceT, $fileNamespace) === 0) {
                        $usedEntityNamespaceT = substr($usedEntityNamespaceT, $fileNamespaceLength);
                        if (preg_match('/\Wuse\s+\\\?'.str_replace('\\', '\\\\', $usedEntityNamespaceT).';/', $fileContent)) {
                            continue 2;
                        }
                    }

                    $parts = explode('\\', $usedEntityNamespace);
                    $usedEntityNamespaceT = '';
                    foreach ($parts as $part) {
                        if ($part === $aliasName) {
                            break;
                        }
                        $usedEntityNamespaceT .= $part.'\\';
                    }
                    if ($usedEntityNamespaceT === $fileNamespace.'\\') {
                        continue 2;
                    }
                    $usedEntityNamespaceT .= $aliasName;
                    if (preg_match('/\Wuse\s+\\\?'.str_replace('\\', '\\\\', $usedEntityNamespaceT).';/', $fileContent)) {
                        continue 2;
                    }

                }
                $errorMessage = 'Class '.$usedEntity.' (fully qualified: '.$usedEntityNamespace.'\\'.$simpleName.') was referenced relatively but has no matching use statement';
            } else {
                $errorMessage = 'Class '.$usedEntity.' was referenced relatively but not defined';
            }

            $regexUsedEntity = str_replace('\\', '\\\\', $usedEntity);
            if (preg_match('/\Wuse\s+(?:\\\?|[a-zA-Z0-9_\\\]+\\\\)'.$regexUsedEntity.';/', $fileContent)) {
                continue;
            }

            if (preg_match('/\Wuse\s+\\\?[a-zA-Z0-9_\\\]+\sas\s'.$aliasName.';/', $fileContent)) {
                continue;
            }

            $this->addMultipleErrors($errorMessage, $file, $lines);
        }
    }

    private function addMultipleErrors($description, $file, array $lines)
    {
        $this->foundError = true;
        foreach ($lines as $line) {
            $this->output->addError($description, $file, $line);
        }
    }
}
