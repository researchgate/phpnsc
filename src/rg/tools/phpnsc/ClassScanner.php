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
            $originalFileContent = $this->filesystem->getFile($file);
            $fileContent = $this->cleanContent($originalFileContent);
            $this->parseDefinedEntities($file, $namespace, $fileContent, $originalFileContent);
            $this->parseUsedEntities($file, $namespace, $fileContent, $originalFileContent);
            $this->parseUseStatements($file, $namespace, $fileContent, $originalFileContent);
            $progressbar->step();
        }
    }

    /**
     *
     * @param string $fileContent 
     * @return string
     */
    private function cleanContent($fileContent) {
        $fileContent = str_replace('\\\'', '  ', $fileContent);
        $fileContent = str_replace('\\"', '  ', $fileContent);
        
        $cleanWithWhitespaces = function($pattern, $fileContent) {
            $getWhitespaces = function($count) {
                $s = '';
                for ($i = 0; $i < $count; $i++) {
                    $s .= ' ';
                }
                return $s;
            };
            $matches = array();
            preg_match_all($pattern, $fileContent, $matches, PREG_OFFSET_CAPTURE);
            if (isset($matches[1])) {
                foreach ($matches[1] as $match) {
                    $fileContent =
                        substr($fileContent, 0, $match[1]) .
                        $getWhitespaces(strlen($match[0])) . 
                        substr($fileContent, $match[1] + strlen($match[0]));
                }
            }
            return $fileContent;
        };
        $fileContent = $cleanWithWhitespaces("/(\/\*.*\*\/)/sU", $fileContent);
        $fileContent = $cleanWithWhitespaces("/(\?>.*<\?)/sU", $fileContent);
        $fileContent = $cleanWithWhitespaces("/(\?>.*$)/sU", $fileContent);
        $fileContent = $cleanWithWhitespaces("/(\'.*\')/sU", $fileContent);
        $fileContent = $cleanWithWhitespaces("/(\".*\")/sU", $fileContent);
        $fileContent = $cleanWithWhitespaces("/(\/\/.*)/", $fileContent);
        
        if (false) {
            $fileContent = preg_replace("/(\/\*.*\*\/)/sU", '', $fileContent);
            $fileContent = preg_replace("/(\?>.*<\?)/sU", "", $fileContent);
            $fileContent = preg_replace("/(\?>.*$)/sU", "", $fileContent);
            $fileContent = preg_replace("/(\'.*\')/sU", "", $fileContent);
            $fileContent = preg_replace("/(\".*\")/sU", "", $fileContent);
            $fileContent = preg_replace("/(\/\/.*)/", "", $fileContent);
        }
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



    public function parseUsedEntities($file, $namespace, $fileContent, $originalFileContent) {
        $reservedClassKeywords = array(
            'parent', 'self', '__class__', 'static', 'array', 'new'
        );
        // new operator
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, '/\Wnew\s+([a-zA-Z0-9_\\\]+)/i', $this->usedEntities, $reservedClassKeywords);
        // Extends
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, '/\sextends\s+([a-zA-Z0-9_\\\]+)\W/i', $this->usedEntities, $reservedClassKeywords);
        // static call
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, '/\W([\$a-zA-Z0-9_\\\]+)::/i', $this->usedEntities, $reservedClassKeywords);
        // Typehints
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, '/[\,\(]\s*([a-zA-Z0-9_\\\]+)\s+\$[a-zA-Z0-9_]+/i', $this->usedEntities, $reservedClassKeywords);
        // implements
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, '/\simplements\s+([\s,a-zA-Z0-9_\\\]+)\W/i', $this->usedEntities, array(), 1, ',');
        // Instanceof
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, '/\sinstanceof\s+([a-zA-Z0-9_\\\]+)\W/i', $this->usedEntities);
    }

    private function parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, $regex, &$targetArray, array $reservedKeywords = array(), $matchIndex = 1, $split = null) {
        if (! isset($targetArray[$file])) {
            $targetArray[$file] = array();
        }
        $matches = array();
        preg_match_all($regex, $fileContent, $matches, PREG_OFFSET_CAPTURE);
        $checkAndAddMatch = function($match, $line) use(&$targetArray, $file, $reservedKeywords) {
            if (! in_array(strtolower($match), $reservedKeywords)) {
                if (! isset($targetArray[$file][$match])) {
                    $targetArray[$file][$match] = array();
                }
                if (! in_array($line, $targetArray[$file][$match])) {
                    $targetArray[$file][$match][] = $line;
                }
            }
        };

        if (isset($matches[$matchIndex]) && $matches[$matchIndex]) {
            foreach ($matches[$matchIndex] as $matchArray) {
                $match = $matchArray[0];
                $matchOffset = $matchArray[1];

                $subContent = substr($originalFileContent, 0, $matchOffset);
                $lines = explode("\n", $subContent);
                $matchLine = count($lines);

                if ($split !== null) {
                    $matchParts = explode($split, $match);
                    foreach ($matchParts as $matchPart) {
                        $matchPartParts = explode(' ', trim($matchPart));
                        $checkAndAddMatch(($matchPartParts[0]), $matchLine);
                    }
                } else {
                    $checkAndAddMatch($match, $matchLine);
                }

            }
        }
    }

    public function parseDefinedEntities($file, $namespace, $fileContent, $originalFileContent) {
        $this->parseFileWithRegexForDefinedEntities($file, $namespace, $fileContent, $originalFileContent, '/^\s*(abstract\s+)?class\s+([a-zA-Z0-9_]+)\W/mi', $this->definedEntities, 2);
        $this->parseFileWithRegexForDefinedEntities($file, $namespace, $fileContent, $originalFileContent, '/^\s*interface\s+([a-zA-Z0-9_]+)\W/mi', $this->definedEntities);
    }

    public function parseUseStatements($file, $namespace, $fileContent, $originalFileContent) {
        // TODO analyze use x as y;
        // TODO analyye use concatenation
        $this->parseFileWithRegexForUsedEntities($file, $namespace, $fileContent, $originalFileContent, '/\Wuse\s([a-zA-Z0-9_\\\]+)\W/i', $this->useStatements);
    }

    private function parseFileWithRegexForDefinedEntities($file, $namespace, $fileContent, $originalFileContent, $regex, &$targetArray, $matchIndex = 1) {
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
