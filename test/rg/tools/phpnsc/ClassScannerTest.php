<?php


class ClassScannerTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var ClassScannerFilesystemMock 
     */
    private $filesystem;
    /**
     *
     * @var rg\tools\phpnsc\ClassScanner 
     */
    private $classScanner;
    
    protected function setUp() {
        parent::setUp();
        $output = new Symfony\Component\Console\Output\NullOutput();
        $outputClass = new rg\tools\phpnsc\ConsoleOutput($output);
        $this->filesystem = new ClassScannerFilesystemMock('/root/folder');
        $this->classScanner = new rg\tools\phpnsc\ClassScanner($this->filesystem, '/root/folder', 
                'vendor', $outputClass);
    }
    
    public function testParseDefinedEntities() {
        $this->filesystem->filesystem = array(
'/root/folder/namespace/ClassOne.php' => '
<?php
class ClassOne extends Foo

foo class Bar

class %%^daga

',
'/root/folder/namespace/FinalClass.php' => '
<?php
final class FinalClass extends Foo

foo class Bar

class %%^daga

',
'/root/folder/namespace/ClassTwo.php' => '
<?php
abstract    class      ClassTwo

interface   InterfaceTwo
',        
'/root/folder/namespace/InterfaceOne.php' => '
<?php
interface InterfaceOne
',        
        );
        $files = array_keys($this->filesystem->filesystem);
        
        $this->classScanner->parseFilesForClassesAndInterfaces($files);
        
        $expectedEntities = array(
            'ClassOne' => array(
                'namespaces' => array('vendor\namespace'),
            ),
            'FinalClass' => array(
                'namespaces' => array('vendor\namespace'),
            ),
            'ClassTwo' => array(
                'namespaces' => array('vendor\namespace'),
            ),
            'InterfaceOne' => array(
                'namespaces' => array('vendor\namespace'),
            ),
            'InterfaceTwo' => array(
                'namespaces' => array('vendor\namespace'),
            ),
        );
        
        $this->assertEquals($expectedEntities, $this->classScanner->getDefinedEntities());
    }
    
    public function testParseDefinedEntitiesWithDoubleEntityRaisesException() {
        $this->filesystem->filesystem = array(
'/root/folder/namespace/ClassOne.php' => '
<?php
class ClassOne extends Foo

foo class Bar

class %%^daga

',
'/root/folder/namespaceTwo/ClassOne.php' => '
<?php
abstract    class      ClassOne

interface   InterfaceTwo
',      
        );
        $files = array_keys($this->filesystem->filesystem);
        
        $this->classScanner->parseFilesForClassesAndInterfaces($files);
        
        $expectedEntities = array(
            'ClassOne' => array(
                'namespaces' => array('vendor\namespace', 'vendor\namespaceTwo'),
            ),
            'InterfaceTwo' => array(
                'namespaces' => array('vendor\namespaceTwo'),
            ),
        );
        
        $this->assertEquals($expectedEntities, $this->classScanner->getDefinedEntities());
    }
    
    public function testParseUsedEntities() {
        $this->filesystem->filesystem = array(
'/root/folder/namespace/ClassOne.php' => '
<?php
class ClassOne  implements Bar  extends Foo
',
'/root/folder/namespace/ClassTwo.php' => '
<?php
abstract    class      ClassTwo   implements Bar 

 $foo = new ClassOne(ClassThree::CONSTANT, TypeHintClass  $variable);
$bar = new ClassTwo;
$foo = new __CLASS__;
$xyz = new $variable(NotClassCONSTANT, parent::FOO, self::FOO, static::FOO, array $bar);
$b = new ClassTwo(ClassTwo::CONSTANT, OutOfNamespace $foo, OtherNamespace $bar, OutOfNamespace::FOO);
interface   InterfaceTwo
',      
        );
        $files = array_keys($this->filesystem->filesystem);
        
        $this->classScanner->parseFilesForClassesAndInterfaces($files);
        
        $expectedEntities = array(
            '/root/folder/namespace/ClassOne.php' => array(
                'Foo' => array(3), 'Bar' => array(3),
            ),
            '/root/folder/namespace/ClassTwo.php' => array(
                'ClassOne' => array(5), 'ClassTwo' => array(6,9), 'ClassThree' => array(5), 'OutOfNamespace' => array(9), 'TypeHintClass' => array(5), 
                'OtherNamespace' => array(9), 'Bar' => array(3),
            ),
        );
        
        foreach ($files as $file) {
            $this->assertEquals($expectedEntities[$file], $this->classScanner->getUsedEntities($file));
        }
    }
}

class ClassScannerFilesystemMock extends \rg\tools\phpnsc\FilesystemAccess
{
    public $filesystem = array();
    
    public function getFile($filename) {
        return $this->filesystem[$filename];
    }
}