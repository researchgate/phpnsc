<?php
namespace rg\tools\phpnsc;

use Symfony\Component\Console;

class Command extends Console\Command\Command
{
    public function __construct($name = null) {
        parent::__construct($name);
        
        $this->setDescription('namespacifys a project according to given config file');
        $this->setHelp('namespacifys a project according to given config file');
        $this->addArgument('config', Console\Input\InputOption::VALUE_REQUIRED, 'path to config file. see README.rst for details');
    }
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output) {
        $configFile = $input->getArgument('config');
        if (! $configFile) {
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
        $outputClass = $config->output->class;
        
        $consoleOutput = new $outputClass($output, $config->output->parameter);
        $classScanner = new ClassScanner($filesystem, $config->folders->root, $config->vendor, $consoleOutput);
        $classModifier = new NamespaceDependencyChecker($filesystem, $classScanner, $config->vendor, $config->folders->root, $consoleOutput);

        $classModifier->analyze($files);
        
        $templateDirectoryScanner = new DirectoryScanner($filesystem, $config->folders->root);
        foreach($config->templateFolders->include as $include) {
            $templateDirectoryScanner->includeDirectory($include);
        }
        foreach($config->templateFolders->exclude as $exclude) {
            $templateDirectoryScanner->excludeDirectory($exclude);
        }
        foreach($config->templateFiletypes->include as $include) {
            $templateDirectoryScanner->includeFiletype($include);
        }
        foreach($config->templateFiletypes->exclude as $exclude) {
            $templateDirectoryScanner->excludeFiletype($exclude);
        }
        $templateFiles = $templateDirectoryScanner->getFiles();
        
        $classModifier->analyze($templateFiles);
        
        $consoleOutput->printAll();
    }
}