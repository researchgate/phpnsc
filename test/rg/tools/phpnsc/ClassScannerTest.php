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
        $this->filesystem = new ClassScannerFilesystemMock('/root/folder');
        $this->classScanner = new rg\tools\phpnsc\ClassScanner($this->filesystem, '/root/folder', 
                'vendor', $output);
    }
    
    public function testParseDefinedEntities() {
        $this->filesystem->filesystem = array(
'/root/folder/namespace/ClassOne.php' => '
<?php
class ClassOne extends Foo

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
                'file' => '/root/folder/namespace/ClassOne.php',
                'namespace' => 'vendor\namespace',
            ),
            'ClassTwo' => array(
                'file' => '/root/folder/namespace/ClassTwo.php',
                'namespace' => 'vendor\namespace',
            ),
            'InterfaceOne' => array(
                'file' => '/root/folder/namespace/InterfaceOne.php',
                'namespace' => 'vendor\namespace',
            ),
            'InterfaceTwo' => array(
                'file' => '/root/folder/namespace/ClassTwo.php',
                'namespace' => 'vendor\namespace',
            ),
        );
        
        $this->assertEquals($expectedEntities, $this->classScanner->getDefinedEntities());
    }
    
    public function testParseDefinedEntitiesWithDoubleEntityRaisesException() {
        $this->setExpectedException('Exception', 
                'double entity nameClassOne in file /root/folder/namespace/ClassTwo.php. Already defined in /root/folder/namespace/ClassOne.php');
        $this->filesystem->filesystem = array(
'/root/folder/namespace/ClassOne.php' => '
<?php
class ClassOne extends Foo

foo class Bar

class %%^daga

',
'/root/folder/namespace/ClassTwo.php' => '
<?php
abstract    class      ClassOne

interface   InterfaceTwo
',      
        );
        $files = array_keys($this->filesystem->filesystem);
        
        $this->classScanner->parseFilesForClassesAndInterfaces($files);
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
                'Foo', 'Bar',
            ),
            '/root/folder/namespace/ClassTwo.php' => array(
                'ClassOne', 'ClassTwo', 'ClassThree', 'OutOfNamespace', 'TypeHintClass', 
                'OtherNamespace', 'Bar',
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