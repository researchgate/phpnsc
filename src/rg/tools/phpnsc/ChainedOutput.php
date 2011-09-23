<?php
/*
 * This file is part of phpnsc.
 *
 * (c) Bastian Hofmann <bastian.hofmann@researchgate.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace rg\tools\phpnsc;

use Symfony\Component\Console\Output\OutputInterface;

class ChainedOutput implements Output {
    /**
     * @var OutputInterface 
     */
    private $output;
    private $outputClasses = array();
    
    public function __construct(OutputInterface $output, $parameter = null) {
        $this->output = $output;
    }
    
    public function addOutputClass($className, $parameter) {
        $class = new $className($this->output, $parameter);
        $this->outputClasses[] = $class;
    }
    
    public function addError($description, $file, $line) {
        foreach ($this->outputClasses as $outputClass) {
            $outputClass->addError($description, $file, $line);
        }
    }
    
    public function printAll() {
        foreach ($this->outputClasses as $outputClass) {
            $outputClass->printAll();
        }
    }
    
    public function write($text) {
        foreach ($this->outputClasses as $outputClass) {
            $outputClass->write($text);
        }
    }
    
    public function writeln($text) {
        foreach ($this->outputClasses as $outputClass) {
            $outputClass->writeln($text);
        }
    }
    
}
