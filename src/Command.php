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

use SebastianBergmann\Timer\ResourceUsageFormatter;
use Symfony\Component\Console;
use Symfony\Component\Console\Application;

class Command extends Console\Command\Command
{
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->setDescription('run the script');
        $this->setHelp('phpnsc run config_file.json');
        $this->addArgument('config', Console\Input\InputOption::VALUE_REQUIRED, 'path to config file. see README.rst for details');
    }

    public static function main() {
        $application = new Application('phpnsc');
        $application->add(new self('run'));
        $application->run();
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $configFile = $input->getArgument('config');
        if (!$configFile) {
            throw new \Exception('Config argument needed');
        }

        $filesystem = new FilesystemAccess(null);
        $config = new Config($filesystem);
        $config->loadConfig($configFile);
        $config = $config->getConfig();

        $outputClass = new ChainedOutput($output);
        foreach ($config->output ?? [] as $outputConfiguration) {
            $outputClass->addOutputClass($outputConfiguration->class, $outputConfiguration->parameter ?? "");
        }

        $filesPerRoot = [];
        $classScanner = new ClassScanner($filesystem, $outputClass);
        foreach ($config->sources ?? [] as $sourceConfig) {
            if (!isset($sourceConfig->folders->root)) {
                throw new \Exception('Config error: "sources[].folders.root" must be defined');
            }
            if (!isset($sourceConfig->vendor)) {
                throw new \Exception('Config error: "sources[].vendor" must be defined');
            }

            $sourceConfig->folders->root = realpath($sourceConfig->folders->root);
            $filesystem->setRoot($sourceConfig->folders->root);

            $outputClass->writeln('Scan ' . $sourceConfig->folders->root);

            $directoryScanner = new DirectoryScanner($filesystem, $sourceConfig->folders->root);
            foreach ($sourceConfig->folders->include ?? [] as $include) {
                $directoryScanner->includeDirectory($include);
            }
            foreach ($sourceConfig->folders->exclude ?? [] as $exclude) {
                $directoryScanner->excludeDirectory($exclude);
            }
            foreach ($sourceConfig->filetypes->include ?? [] as $include) {
                $directoryScanner->includeFiletype($include);
            }
            foreach ($sourceConfig->filetypes->exclude ?? [] as $exclude) {
                $directoryScanner->excludeFiletype($exclude);
            }

            $files = $directoryScanner->getFiles();
            $classScanner->parseFilesForClassesAndInterfaces($files, $sourceConfig->folders->root, $sourceConfig->vendor);

            $filesPerRoot[$sourceConfig->folders->root] = $files;
            $outputClass->writeln('');
        }

        $foundError = false;

        foreach ($config->sources ?? [] as $sourceConfig) {
            $filesystem->setRoot($sourceConfig->folders->root);
            $outputClass->writeln('Analyze ' . $sourceConfig->folders->root);

            $files = $filesPerRoot[$sourceConfig->folders->root];

            $dependencyChecker = new NamespaceDependencyChecker($filesystem, $classScanner, $sourceConfig->vendor, $sourceConfig->folders->root, $outputClass);
            $dependencyChecker->analyze($files);
            $foundError |= $classScanner->foundError | $dependencyChecker->foundError;
            $outputClass->writeln('');
        }

        $outputClass->printAll();
        $outputClass->writeln((new ResourceUsageFormatter())->resourceUsageSinceStartOfRequest());

        return $foundError ? 1 : 0;
    }
}
