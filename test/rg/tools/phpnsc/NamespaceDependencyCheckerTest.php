<?php

class NamespaceDependencyCheckerTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var ClassModifierFilesystemMock 
     */
    private $filesystem;
    /**
     *
     * @var rg\tools\phpnsc\ClassScanner 
     */
    private $classScanner;
    
    /**
     *
     * @var rg\tools\phpnsc\ClassModifier 
     */
    private $dependencyChecker;
    
    /**
     *
     * @var DependencyCheckerOutputMock 
     */
    private $outputClass;
    
    protected function setUp() {
        parent::setUp();
        $output = new Symfony\Component\Console\Output\NullOutput();
        $this->outputClass = new DependencyCheckerOutputMock($output);
        $this->filesystem = new ClassModifierFilesystemMock('/root/folder');
        $this->classScanner = new rg\tools\phpnsc\ClassScanner($this->filesystem, '/root/folder', 
                'vendor', $this->outputClass);
        $this->dependencyChecker = new rg\tools\phpnsc\NamespaceDependencyChecker($this->filesystem, $this->classScanner, 
                'vendor', '/root/folder', $this->outputClass);
    }
    
    public function testModifyFiles() {
        $files = array_keys($this->filesystem->filesystem);
        $this->dependencyChecker->analyze($files);
        $expected = array (
          0 => 
          array (
            0 => 'Class TestException was referenced relatively but not defined',
            1 => '/root/folder/namespace/ClassOne.php',
            2 => 12,
          ),
          1 => 
          array (
            0 => 'Class InterfaceA (fully qualified: vendor\namespaceTwo\InterfaceA) was referenced relatively but has no matching use statement',
            1 => '/root/folder/namespace/ClassTwo.php',
            2 => 5,
          ),
          2 => 
          array (
            0 => 'Class ClassOne (fully qualified: vendor\namespace\ClassOne) was referenced relatively but has no matching use statement',
            1 => '/root/folder/namespaceTwo/OtherNamespace.php',
            2 => 14,
          ),
        );
        $this->assertEquals($expected, $this->outputClass->errors);
    }
}

use Symfony\Component\Console\Output\OutputInterface;

class DependencyCheckerOutputMock implements rg\tools\phpnsc\Output
{
    public $errors = array();
    
    public function __construct(OutputInterface $output, $parameter = null) {

    }
    public function addError($description, $file, $line) {
        $this->errors[] = array(
            $description, $file, $line,
        );
    }
    public function printAll() {

    }
    public function write($text) {

    }
    public function writeln($text) {

    }
}

class ClassModifierFilesystemMock extends \rg\tools\phpnsc\FilesystemAccess
{
    public $filesystem = array(
'/root/folder/namespace/ClassOne.php' =>
'<?php
namespace vendor\namespace;
use vendor\namespaceTwo\OtherNamespace;

class ClassOne extends ClassTwo {
    public function test() {
        parent::test();
        
        $b = new ClassTwo(ClassOne::CONSTANT, ClassTwo::CONSTANT, \OutOfNamespace $foo, OtherNamespace $bar, \OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
        try {
        } catch(TestException $e) {
        }
    }
}
',
'/root/folder/namespace/ClassTwo.php' =>
'<?php
namespace vendor\namespace;
use vendor\namespaceTwo\OtherNamespace;

   abstract   class ClassTwo extends \OutOfNamespace implements InterfaceA {
    public function test() {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, \OutOfNamespace $foo, OtherNamespace $bar, \OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
    }
}
',
'/root/folder/namespaceTwo/OtherNamespace.php' =>
'<?php
namespace vendor\namespaceTwo;
use vendor\namespace\ClassTwo;
use vendor\models\foo\Model;
use vendor\namespaceThree\InterfaceB;

class OtherNamespace extends \OutOfNamespace implements InterfaceA , InterfaceB {
    public function test() {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, \OutOfNamespace $foo, OtherNamespace $bar, \OutOfNamespace::FOO, InterfaceA $abc);
        $c = new \OutOfNamespace;
        $c = new \OutOf\Namespace;
        if($i instanceof \OutOfNamespace) {}
        if($i instanceof ClassOne) {}
    }
}
',
'/root/folder/namespaceTwo/InterfaceA.php' =>
'<?php
namespace vendor\namespaceTwo;

interface InterfaceA{
    public function test();
}
',
'/root/folder/namespaceThree/InterfaceB.php' =>
'<?php
namespace vendor\namespaceThree;

interface InterfaceB {
    public function test();
}
',    
    );
    public function getFile($filename) {
        return $this->filesystem[$filename];
    }
}