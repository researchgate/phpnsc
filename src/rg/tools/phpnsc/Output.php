<?php
namespace rg\tools\phpnsc;

use Symfony\Component\Console\Output\OutputInterface;

interface Output {
    public function __construct(OutputInterface $output, $parameter);
    public function addError($description, $file, $line);
    public function printAll();
    public function write($text);
    public function writeln($text);
}

