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

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput implements Output
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $errors = [];

    public function __construct(OutputInterface $output, $parameter = null)
    {
        $this->output = $output;
    }

    public function write($text)
    {
        $this->output->write($text);
    }

    public function writeln($text)
    {
        $this->output->writeln($text);
    }

    public function addError($description, $file, $line)
    {
        $this->errors[] = [
            'description' => $description,
            'file' => $file,
            'line' => $line,
        ];
    }

    public function printAll()
    {
        $this->writeln('Errors found:'.PHP_EOL);
        foreach ($this->errors as $error) {
            $this->writeln($error['file'].' ('.$error['line'].'): '.$error['description']);
        }
    }
}
