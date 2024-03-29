<?php
namespace rg\tools\phpnsc;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class NamespaceDependencyCheckerTest extends TestCase
{

    /**
     * @var array
     */
    private $files;

    /**
     * @var NamespaceDependencyChecker
     */
    private $dependencyChecker;

    /**
     * @var DependencyCheckerOutputMock
     */
    private $outputClass;

    protected function setUp(): void {
        parent::setUp();
        $output = new NullOutput();
        $this->outputClass = new DependencyCheckerOutputMock($output);
        $filesystem = new ClassModifierFilesystemMock('/root/folder');
        $this->files = array_keys($filesystem->filesystem);
        $classScanner = new ClassScanner($filesystem, $this->outputClass);
        $classScanner->parseFilesForClassesAndInterfaces($this->files, '/root/folder', 'vendor');
        $this->dependencyChecker = new NamespaceDependencyChecker($filesystem, $classScanner, 'vendor', '/root/folder', $this->outputClass);
    }

    public function testModifyFiles() {
        $this->dependencyChecker->analyze($this->files);
        $expected = [
            [
                'Namespace does not match folder structure, got vendor\testnamespaceTwo expected vendor\testnamespaceInvalid',
                '/root/folder/testnamespaceInvalid/ClassX.php',
                2,
            ],
            [
                'Namespace does not match folder structure, got vendor\testnamespaceTwo expected vendor\testnamespaceInvalid',
                '/root/folder/testnamespaceInvalid/ClassY.php',
                2,
            ],
            [
                'Namespace does not match folder structure, got vendor\testnamespaceTwo expected vendor\testnamespaceInvalid',
                '/root/folder/testnamespaceInvalid/ClassZ.php',
                2,
            ],
            [
                'Namespace does not match folder structure, got vendor\testnamespaceTwo expected vendor\testnamespaceInvalid',
                '/root/folder/testnamespaceInvalid/ClassZZ.php',
                2,
            ],
            [
                'Class TestException was referenced relatively but not defined',
                '/root/folder/testnamespace/ClassOne.php',
                15,
            ],
            [
                'Class void was referenced relatively but not defined',
                '/root/folder/testnamespace/ClassOne.php',
                23,
            ],
            [
                'Class InterfaceA (fully qualified: vendor\testnamespaceTwo\InterfaceA) was referenced relatively but has no matching use statement',
                '/root/folder/testnamespace/ClassTwo.php',
                5,
            ],
            [
                'Class ClassOne (fully qualified: vendor\testnamespace\ClassOne) was referenced relatively but has no matching use statement',
                '/root/folder/testnamespaceTwo/OtherNamespace.php',
                14,
            ],
        ];
        $this->assertEquals($expected, $this->outputClass->errors);
    }
}

class DependencyCheckerOutputMock implements Output
{
    public $errors = array();
    public $warnings = array();

    public function __construct(OutputInterface $output, $parameter = null) {

    }
    public function addError($description, $file, $line) {
        $this->errors[] = array(
            $description, $file, $line,
        );
    }
    public function addWarning($description, $file, $line) {
        $this->warnings[] = array(
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

class ClassModifierFilesystemMock extends FilesystemAccess
{
    public $filesystem = array(
'/root/folder/testnamespace/ClassOne.php' =>
'<?php
namespace vendor\testnamespace;
use vendor\testnamespaceTwo\OtherNamespace;
use vendor\testnamespaceWith_Underscore\UnderscoreNamespaceClass;
use GlobalClassWithoutUnderscore as GlobalAlias;
use GlobalClassWith_Underscore as UnderscoreAlias;

class ClassOne extends ClassTwo {
    public function test(\\OutOfNamespace $foo, OtherNamespace $bar, GlobalAlias $anotherFoo, UnderscoreAlias $anotherBar, UnderscoreNamespaceClass $underscoreNamespaceClass) {
        parent::test();

        $b = new ClassTwo(ClassOne::CONSTANT, ClassTwo::CONSTANT, \\OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
        try {
        } catch(TestException $e) {
        }
    }

    private function testVoidAsReturnType(): void {
        // this declaration should work fine
    }

    private function testVoidAsArgument(void $foo) {
        // this should not be allowed
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
'/root/folder/testnamespaceInvalid/ClassX.php' =>
'<?php
namespace vendor\testnamespaceTwo;

class ClassX {
    public function test() {}
}
',
'/root/folder/testnamespaceInvalid/ClassY.php' =>
'<?php
namespace vendor\testnamespaceTwo {

    class ClassY {
        public function test() {}
    }
}
',
'/root/folder/testnamespaceInvalid/ClassZ.php' =>
'<?php
namespace vendor\testnamespaceTwo
{
    class ClassZ {
        public function test() {}
    }
}
',
'/root/folder/testnamespaceInvalid/ClassZZ.php' =>
'<?php namespace vendor\testnamespaceTwo
{
    class ClassZZ {
        public function test() {}
    }
}
',
'/root/folder/testnamespaceValid/ClassX.php' =>
'<?php
namespace vendor\testnamespaceValid;

class ClassX {
    public function test() {}
}
',
'/root/folder/testnamespaceValid/ClassY.php' =>
'<?php
namespace vendor\testnamespaceValid {

    class ClassY {
        public function test() {}
    }
}
',
'/root/folder/testnamespaceValid/ClassZ.php' =>
'<?php
namespace vendor\testnamespaceValid
{
    class ClassZ {
        public function test() {}
    }
}
',
    );
    public function getFile($filename) {
        return $this->filesystem[$filename];
    }
}
