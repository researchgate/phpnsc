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

class CheckstyleOutput implements Output {

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var string
     */
    private $file;

    /**
     * @param OutputInterface $output
     * @param string|null     $parameter
     *
     * @throws \LogicException
     */
    public function __construct(OutputInterface $output, $parameter = null) {
        $this->output = $output;
        $this->file = $parameter;
        if (!$this->file) {
            throw new \LogicException("\\rg\\tools\\phpnsc\\CheckstyleOutput needs parameter to be set to the destination filename.");
        }
    }

    public function addError($description, $file, $line) {
        if (!isset($this->errors[$file])) {
            $this->errors[$file] = [];
        }

        $this->errors[$file][] = [
            'line' => $line,
            'column' => 1,
            'severity' => 'error',
            'message' => $description,
            'source' => 'rg.tools.phpnsc',
        ];
    }
    public function printAll() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<checkstyle version="1.0.0">' . PHP_EOL;
        foreach ($this->errors as $file => $errors) {
            $xml .= '  <file name="' . $file . '">' . PHP_EOL;
            foreach ($errors as $error) {
                $xml .= '    <error line="' . $error['line'] . '" column="' . $error['column'] . '" severity="' . $error['severity'] . '" message="' . $error['message'] . '" source="' . $error['source'] . '" />' . PHP_EOL;
            }
            $xml .= '  </file>' . PHP_EOL;
        }
        $xml .= '</checkstyle>' . PHP_EOL;

        file_put_contents($this->file, $xml);
    }

    public function write($text) {
    }

    public function writeln($text) {
    }
}
