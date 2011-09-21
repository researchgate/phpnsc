<?php
namespace rg\tools\phpnsc;

class ClassScanner {
    /**
     * @var FilesysyemAccess
     */
    private $filesystem;
    private $definedEntities;
    private $usedEntities;
    private $root;
    private $namespaceVendor;
    private $useStatements;
    
    /**
     *
     * @var Output
     */
    private $output;
    
    /**
     * @param FilesystemAccess $filesystem
     * @param string $root
     * @param string $namespaceVendor
     * @param Output $output
     */
    public function __construct(FilesystemAccess $filesystem, $root, $namespaceVendor, Output $output) {
        $this->filesystem = $filesystem;
        $this->root = $root;
        $this->namespaceVendor = $namespaceVendor;
        $this->output = $output;
    }
    
    /**
     * parses all given files for classes and interfaces that are defined or used in this
     * files
     * 
     * @param array $files 
     */
    public function parseFilesForClassesAndInterfaces($files) {
        $progressbar = new Progressbar($this->output, count($files));
        foreach ($files as $file) {
            $namespace = (string)new NamespaceString($this->namespaceVendor, $this->root, $file);
            //$file = '/Users/bastian/Checkouts/researchgate/webroot/modules/jobboardadmin/actions/JobboardAdminBatchImport.class.php';
            $fileContent = $this->filesystem->getFile($file);
            $fileContent = $this->cleanContent($fileContent);
            $this->parseDefinedEntities($file, $namespace, $fileContent);
            $this->parseUsedEntities($file, $namespace, $fileContent);
            $this->parseUseStatements($file, $namespace, $fileContent);
            $progressbar->step();
        }
    }
    
    /**
     *
     * @param string $fileContent 
     * @return string
     */
    private function cleanContent($fileContent) {
        $fileContent = str_replace('\\\'', '', $fileContent);
        $fileContent = str_replace('\\"', '', $fileContent);
        $fileContent = preg_replace("/(\/\*.*\*\/)/sU", "", $fileContent);
        $fileContent = preg_replace("/(\'.*\')/sU", "", $fileContent);
        $fileContent = preg_replace("/(\".*\")/sU", "", $fileContent);
        $fileContent = preg_replace("/(\/\/.*)/", "", $fileContent);
        //echo $fileContent;die;
        return $fileContent;
    }
    
    /**
     * @return array
     */
    public function getDefinedEntities() {
        return $this->definedEntities;
    }
    
    /**
     * @param string $file
     * @return array
     */
    public function getUsedEntities($file) {
        if (! isset($this->usedEntities[$file])) {
            return array();
        }
        return $this->usedEntities[$file];
    }
    
    /**
     * @param string $file
     * @return array
     */
    public function getUseStatements($file) {
        if (! isset($this->useStatements[$file])) {
            return array();
        }
        return $this->useStatements[$file];
    }


    
    public function parseUsedEntities($file, $namespace, $fileContent) {
        $reservedClassKeywords = array(
            'parent', 'self', '__class__', 'static', 'array',
        );
        // new operator
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, '/\Wnew\s+([a-zA-Z0-9_\\\]+)/i', $this->usedEntities, $reservedClassKeywords);
        // Extends
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, '/\sextends\s+([a-zA-Z0-9_\\\]+)\W/i', $this->usedEntities, $reservedClassKeywords);
        // static call
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, '/\W([\$a-zA-Z0-9_\\\]+)::/i', $this->usedEntities, $reservedClassKeywords);
        // Typehints
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, '/[\,\(]\s*([a-zA-Z0-9_\\\]+)\s+\$[a-zA-Z0-9_]+/i', $this->usedEntities, $reservedClassKeywords);
        // implements
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, '/\simplements\s+([\s,a-zA-Z0-9_\\\]+)\W/i', $this->usedEntities, array(), 1, ',');
        // Instanceof
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, '/\sinstanceof\s+([a-zA-Z0-9_\\\]+)\W/i', $this->usedEntities);
    }
    
    private function parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $regex, &$targetArray, array $reservedKeywords = array(), $matchIndex = 1, $split = null) {
        if (! isset($targetArray[$file])) {
            $targetArray[$file] = array();
        }
        $matches = array();
        preg_match_all($regex, $fileContent, $matches);
        $checkAndAddMatch = function($match) use(&$targetArray, $file, $reservedKeywords) {
            if (! in_array($match, $targetArray[$file]) && ! in_array(strtolower($match), $reservedKeywords)) {
                $targetArray[$file][] = $match;
            }
        };
        if (isset($matches[$matchIndex]) && $matches[$matchIndex]) {
            foreach ($matches[$matchIndex] as $match) {
                if ($split !== null) {
                    $matchParts = explode($split, $match);
                    foreach ($matchParts as $matchPart) {
                        $matchPartParts = explode(' ', trim($matchPart));
                        $checkAndAddMatch(($matchPartParts[0]));
                    }
                } else {
                    $checkAndAddMatch($match);
                }
                
            }
        }
    }
    
    public function parseDefinedEntities($file, $namespace, $fileContent) {
        $this->parseFileWithRegexForDefinedEntities($file, $namespace, $fileContent, '/^\s*(abstract\s+)?class\s+([a-zA-Z0-9_]+)\W/mi', $this->definedEntities, 2);
        $this->parseFileWithRegexForDefinedEntities($file, $namespace, $fileContent, '/^\s*interface\s+([a-zA-Z0-9_]+)\W/mi', $this->definedEntities);
    }
    
    public function parseUseStatements($file, $namespace, $fileContent) {
        // TODO analyze use x as y;
        // TODO analyye use concatenation
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, '/\Wuse\s([a-zA-Z0-9_\\\]+)\W/i', $this->useStatements);
    }
        
    private function parseFileWithRegexForDefinedEntities($file, $namespace, $fileContent, $regex, &$targetArray, $matchIndex = 1) {
        $matches = array();
        preg_match_all($regex, $fileContent, $matches);
        if (isset($matches[$matchIndex]) && $matches[$matchIndex]) {
            foreach ($matches[$matchIndex] as $match) {
                if (isset($targetArray[$match])) {
                    $targetArray[$match]['namespaces'][] = $namespace;
                    continue;
                }
                $targetArray[$match] = array(
                    'namespaces' => array($namespace),
                );
            }
        }
    }
}
