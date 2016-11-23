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

use Symfony\Component\Console;

class Command extends Console\Command\Command
{
    public function __construct($name = null) {
        parent::__construct($name);

        $this->setDescription('run the script');
        $this->setHelp('phpnsc run config_file.json');
        $this->addArgument('config', Console\Input\InputOption::VALUE_REQUIRED, 'path to config file. see README.rst for details');
    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output) {
        $configFile = $input->getArgument('config');
        if (!$configFile) {
            throw new \Exception('Config argument needed');
        }
        $filesystem = new FilesystemAccess(null);
        $config = new Config($filesystem);
        $config->loadConfig($configFile);
        $config = $config->getConfig();

        $config->folders->root = realpath($config->folders->root);

        $filesystem->setRoot($config->folders->root);

        $directoryScanner = new DirectoryScanner($filesystem, $config->folders->root);
        foreach($config->folders->include as $include) {
            $directoryScanner->includeDirectory($include);
        }
        foreach($config->folders->exclude as $exclude) {
            $directoryScanner->excludeDirectory($exclude);
        }
        foreach($config->filetypes->include as $include) {
            $directoryScanner->includeFiletype($include);
        }
        foreach($config->filetypes->exclude as $exclude) {
            $directoryScanner->excludeFiletype($exclude);
        }
        $files = $directoryScanner->getFiles();

        $outputClass = new ChainedOutput($output);
        foreach ($config->output as $outputConfiguration) {
            $outputClass->addOutputClass($outputConfiguration->class, $outputConfiguration->parameter);
        }

        $classScanner = new ClassScanner($filesystem, $config->folders->root, $config->vendor, $outputClass);
        $classModifier = new NamespaceDependencyChecker($filesystem, $classScanner, $config->vendor, $config->folders->root, $outputClass);

        $classModifier->analyze($files);

        $outputClass->printAll();

        $outputClass->writeln(\PHP_Timer::resourceUsage());

        if ($classScanner->foundError || $classModifier->foundError) {
            return 1;
        } else {
            return 0;
        }
    }
}
