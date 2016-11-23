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

interface Output
{
    public function __construct(OutputInterface $output, $parameter = null);
    public function addError($description, $file, $line);
    public function printAll();
    public function write($text);
    public function writeln($text);
}
