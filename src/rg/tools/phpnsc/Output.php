<?php
namespace rg\tools\phpnsc;

interface Output {
    public function addError($description, $file, $line);
    public function printAll();
    public function write($text);
    public function writeln($text);
}

