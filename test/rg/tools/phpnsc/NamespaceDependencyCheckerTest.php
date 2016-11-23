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
     * @var rg\tools\phpnsc\NamespaceDependencyChecker
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
            1 => '/root/folder/testnamespace/ClassOne.php',
            2 => 12,
          ),
          1 =>
          array (
            0 => 'Class InterfaceA (fully qualified: vendor\testnamespaceTwo\InterfaceA) was referenced relatively but has no matching use statement',
            1 => '/root/folder/testnamespace/ClassTwo.php',
            2 => 5,
          ),
          2 =>
          array (
            0 => 'Class ClassOne (fully qualified: vendor\testnamespace\ClassOne) was referenced relatively but has no matching use statement',
            1 => '/root/folder/testnamespaceTwo/OtherNamespace.php',
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
'/root/folder/testnamespace/ClassOne.php' =>
'<?php
namespace vendor\testnamespace;
use vendor\testnamespaceTwo\OtherNamespace;

class ClassOne extends ClassTwo {
    public function test(\\OutOfNamespace $foo, OtherNamespace $bar) {
        parent::test();

        $b = new ClassTwo(ClassOne::CONSTANT, ClassTwo::CONSTANT, \\OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
        try {
        } catch(TestException $e) {
        }
    }
}
',
'/root/folder/testnamespace/ClassTwo.php' =>
'<?php
namespace vendor\testnamespace;
use vendor\testnamespaceTwo\OtherNamespace;

   abstract   class ClassTwo extends \OutOfNamespace implements InterfaceA {
    public function test(\OutOfNamespace $foo, OtherNamespace $bar) {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, \OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
    }
}
',
'/root/folder/testnamespaceTwo/OtherNamespace.php' =>
'<?php
namespace vendor\testnamespaceTwo;
use vendor\testnamespace\ClassTwo;
use vendor\models\foo\Model;
use vendor\testnamespaceThree\InterfaceB;

class OtherNamespace extends \OutOfNamespace implements InterfaceA , InterfaceB {
    public function test(\OutOfNamespace $foo, OtherNamespace $bar, InterfaceA $abc) {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, \OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
        $c = new \OutOf\TestNamespace;
        if($i instanceof \OutOfNamespace) {}
        if($i instanceof ClassOne) {}
    }
}
',
'/root/folder/testnamespaceTwo/InterfaceA.php' =>
'<?php
namespace vendor\testnamespaceTwo;

interface InterfaceA{
    public function test();
}
',
'/root/folder/testnamespaceThree/InterfaceB.php' =>
'<?php
namespace vendor\testnamespaceThree;

interface InterfaceB {
    public function test();
}
',
    );
    public function getFile($filename) {
        return $this->filesystem[$filename];
    }
}
