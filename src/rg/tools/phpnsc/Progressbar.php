<?php
namespace rg\tools\phpnsc;

class Progressbar {
    private $output;
    private $i = 0;
    private $count;
    private $linebreakAfter;
    
    public function __construct(Output $output, $count, $linebreakAfter = 60) {
        $this->output = $output;
        $this->count = $count;
        $this->linebreakAfter = $linebreakAfter;
    }
    
    public function step() {
        $this->i++;
        $this->output->write('.');
        if ($this->i % $this->linebreakAfter === 0 || $this->i == $this->count) {
            $this->output->writeln(' ' . $this->i . '/' . $this->count);
        }
    }
}

?>
