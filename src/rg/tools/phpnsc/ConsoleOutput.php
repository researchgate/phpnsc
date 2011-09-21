<?php
namespace rg\tools\phpnsc;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput implements Output {
    /**
     * @var OutputInterface 
     */
    private $output;
    private $errors = array();

    public function __construct(OutputInterface $output, $parameter) {
        $this->output = $output;
    }
    
    public function write($text) {
        $this->output->write($text);
    }
    
    public function writeln($text) {
        $this->output->writeln($text);
    }
    
    public function addError($description, $file, $line) {
        $this->errors[] = array(
            'description' => $description,
            'file' => $file,
            'line' => $line,
        );
    }
    
    public function printAll() {
        $this->writeln('Errors found:' . PHP_EOL);
        foreach ($this->errors as $error) {
            $this->writeln($error['file'] . ' (' . $error['line'] . '): ' . $error['description']);
        }
    }
}

