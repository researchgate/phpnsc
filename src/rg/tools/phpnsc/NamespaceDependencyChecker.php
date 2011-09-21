<?php
namespace rg\tools\phpnsc;

class NamespaceDependencyChecker {
    /**
     * @var FilesysyemAccess
     */
    private $filesystem;
    /**
     *
     * @var ClassScanner
     */
    private $classScanner;
    private $fileModifiers;
    private $root;
    private $namespaceVendor;
    private $definedEntities;
    
    /**
     *
     * @var Output
     */
    private $output;
    
    /**
     * @param FilesystemAccess $filesystem
     * @param ClassScanner $classScanner
     * @param string $namespaceVendor
     * @param string $root
     * @param Output $output
     */
    public function __construct(FilesystemAccess $filesystem, ClassScanner $classScanner, $namespaceVendor, $root, 
            Output $output) {
        $this->filesystem = $filesystem;
        $this->classScanner = $classScanner;
        $this->root = $root;
        $this->namespaceVendor = $namespaceVendor;
        $this->output = $output;
    }
    
    /**
     *
     * @param array $files 
     */
    public function analyze(array $files) {
        $this->output->writeln('Got ' . count($files) . ' files');
        $this->output->writeln('Start analyzing...');
        $this->classScanner->parseFilesForClassesAndInterfaces($files);
        $this->definedEntities = $this->classScanner->getDefinedEntities();
        $this->output->writeln('Got ' . count($this->definedEntities) . ' defined entities');
        $this->output->writeln('Start modifying...');
        $progressbar = new Progressbar($this->output, count($files));
        foreach ($files as $file) {
            $this->analyzeFile($file);
            $progressbar->step();
        }
    }
    
    /**
     *
     * @param string $file full path to file
     */
    private function analyzeFile($file) {
        $fileContent = $this->filesystem->getFile($file);
        $entitiesUsedInFile = $this->classScanner->getUsedEntities($file);

        $fileNamespace = (string) new NamespaceString($this->namespaceVendor, $this->root, $file);
        foreach ($entitiesUsedInFile as $i => $usedEntity) {
            // we have a fully qualified name, so we do not need any use statements
            if (substr($usedEntity, 0, 1) === '\\') {
                continue;
            }
            if (substr($usedEntity, 0, 1) === '$') {
                continue;
            }
            $simpleName = $usedEntity;
            $aliasName = '';
            if (strpos($usedEntity, '\\') > 0 ) {
                $parts = explode('\\', $usedEntity);
                $simpleName = $parts[count($parts) - 1];
                $aliasName = $parts[count($parts) - 2];
            }
            if (isset($this->definedEntities[$simpleName])) {
                $foundMatchingUseStatement = false;
                foreach ($this->definedEntities[$simpleName]['namespaces'] as $usedEntityNamespace ) {
                    if ($usedEntityNamespace === $fileNamespace) {
                        $foundMatchingUseStatement = true;
                    }
                    $usedEntityNamespaceT = $usedEntityNamespace . '\\' . $simpleName;
                    if (preg_match('/\Wuse\s+\\\?' . str_replace('\\', '\\\\', $usedEntityNamespaceT) . '\W/', $fileContent)) {
                        $foundMatchingUseStatement = true;
                    }
                    if (strpos($usedEntityNamespaceT, $fileNamespace) === 0) {
                        $usedEntityNamespaceT = substr($usedEntityNamespaceT, strlen($fileNamespace) + 1);
                        if (preg_match('/\Wuse\s+\\\?' . str_replace('\\', '\\\\', $usedEntityNamespaceT) . '\W/', $fileContent)) {
                            $foundMatchingUseStatement = true;
                        }
                    }
                    if ($aliasName) {
                        $parts = explode('\\', $usedEntityNamespace);
                        $usedEntityNamespaceT = '';
                        foreach ($parts as $part) {
                            if ($part === $aliasName) {
                                break;
                            }
                            $usedEntityNamespaceT .= $part . '\\';
                        }
                        $usedEntityNamespaceT .= $aliasName;
                        if (preg_match('/\Wuse\s+\\\?' . str_replace('\\', '\\\\', $usedEntityNamespaceT) . '\W/', $fileContent)) {
                            $foundMatchingUseStatement = true;
                        }
                    }
                }
                if (! $foundMatchingUseStatement) {
                    $this->output->addError('Class ' . $usedEntity . ' (fully qualified:' . $usedEntityNamespace . '\\' . $simpleName . ') was referenced relatively but has no matching use statement', $file, 0);
                }
            } else {
                $foundMatchingUseStatement = false;
                
                if (preg_match('/\Wuse\s+\\\?' . str_replace('\\', '\\\\', $usedEntity) . '\W/', $fileContent)) {
                    $foundMatchingUseStatement = true;
                } 
                if (preg_match('/\Wuse\s+[a-zA-Z\\\]+\\' . str_replace('\\', '\\\\', $usedEntity) . '\W/', $fileContent)) {
                    $foundMatchingUseStatement = true;
                } 
                
                if (! $foundMatchingUseStatement) {
                    $this->output->addError('Class ' . $usedEntity . ' was referenced relatively but not defined', $file, 0);
                }
            }
        }
    }
      

}

