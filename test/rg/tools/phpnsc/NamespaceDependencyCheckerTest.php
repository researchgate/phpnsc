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
    private $classModifier;
    
    protected function setUp() {
        parent::setUp();
        $output = new Symfony\Component\Console\Output\NullOutput();
        $this->filesystem = new ClassModifierFilesystemMock('/root/folder');
        $this->classScanner = new rg\tools\phpnsc\ClassScanner($this->filesystem, '/root/folder', 
                'vendor', $output);
        $this->classModifier = new rg\tools\phpnsc\ClassModifier($this->filesystem, $this->classScanner, 
                'vendor', '/root/folder', $output);
    }
    
    public function testModifyFiles() {
        $files = array_keys($this->filesystem->filesystem);
        $this->classModifier->addFileModifier(rg\tools\phpnsc\FileModifier::getModifier(
            '\\researchgate\\tools\\PHPNamespacify\\PregReplaceFileModifier', array(
            'search' => "/Autoloader::addPackage\(('|\")[a-zA-Z0-9_\/\*]+('|\")(\s*,\s*[a-z]+)?\);\s*/i", 'replace' => ''
        )));
        $this->classModifier->addFileModifier(rg\tools\phpnsc\FileModifier::getModifier(
            '\\researchgate\\tools\\PHPNamespacify\\PregReplaceFileModifier', array(
            'search' => "/Autoloader::addClass\(('|\")[a-zA-Z0-9_\/\*]+('|\")(\s*,\s*[a-z]+)?\);\s*/i", 'replace' => ''
        )));
        $this->classModifier->modifyFiles($files);
        $expected = array('/root/folder/namespace/ClassOne.php' =>
'<?php
namespace vendor\namespace;
use vendor\namespaceTwo\OtherNamespace;

class ClassOne extends ClassTwo {
    public function test() {
        parent::test();
        
        $b = new ClassTwo(ClassOne::CONSTANT, ClassTwo::CONSTANT, \OutOfNamespace $foo, OtherNamespace $bar, \OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
        try {
        } catch(\cassandra_TestException $e) {
        }
    }
}
',
'/root/folder/namespace/ClassTwo.php' =>
'<?php
namespace vendor\namespace;
use vendor\namespaceTwo\OtherNamespace;
use vendor\namespaceTwo\InterfaceA;

   abstract   class ClassTwo extends \OutOfNamespace implements InterfaceA {
    public function test() {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, \OutOfNamespace $foo, OtherNamespace $bar, \OutOfNamespace::FOO);
        $c = new \OutOfNamespace;
        if(class_exists(\'vendor\namespace\ClassTwo\')){}
    }
}
',
'/root/folder/namespaceTwo/OtherNamespace.php' =>
'<?php
namespace vendor\namespaceTwo;
use vendor\namespace\ClassTwo;
use vendor\models\foo\Model;
use vendor\namespaceThree\InterfaceB;
use vendor\namespace\ClassOne;

class OtherNamespace extends \OutOfNamespace implements InterfaceA , InterfaceB {
    public function test() {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, \OutOfNamespace $foo, OtherNamespace $bar, \OutOfNamespace::FOO, InterfaceA $abc);
        $c = new \OutOfNamespace;
        $d = new \Mandango\Exception();
        $e = new \cassandra_Class();
        if($i instanceof \OutOfNamespace) {}
        if($i instanceof ClassOne) {}
        new \Mandango\Mandango(new Model(), new \Mandango\Cache\CappedArrayCache(), $loggerCallable);
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
use vendor\models\foo\ModelA;

interface InterfaceB {
    public function test();
    get_class($foo) == \'vendor\models\foo\ModelA\';
    get_class($foo) != \'vendor\models\foo\ModelA\';
    is_subclass_of($foo, \'vendor\models\foo\ModelA\');
}
',            
'/root/folder/namespaceTwo/DoctrineTest.php' =>
'<?php
namespace vendor\namespaceTwo;
use vendor\models\foo\ModelA;
use vendor\models\foo\ModelC;
use vendor\models\foo\ModelD;
use vendor\models\foo\ModelH;
use vendor\models\foo\ModelI;
use vendor\models\foo\ModelK;
use vendor\models\foo\Model;
use vendor\models\foo\ModelB;
use vendor\models\foo\ModelJ;
use vendor\models\foo\ModelL;

class DoctrineTest{
    protected $documentClass = \'vendor\models\foo\ModelJ\';
    public function test() {
        \Doctrine::getTable(\'vendor\models\foo\Model\');
        $s->hasMany(\'vendor\models\foo\ModelA\', array(
           "refClass"=> "vendor\models\foo\ModelB"
          ))
          ->hasOne(\'vendor\models\foo\ModelC\')
          ->from(\'vendor\models\foo\ModelD e\')
          ->innerJoin(\'a.ModelE\')
          ->rightJoin(\'table.ModelF\')
          ->leftJoin(\'a.ModelG\');
          ->leftJoin(\'vendor\models\foo\ModelK\');
        $s->delete(\'vendor\models\foo\ModelH\');
        $s->update(
            \'vendor\models\foo\ModelI\'
        );
        $s->update(
            \'NotDefined\'
        );
        $q = \Doctrine_Query::create()
        new \Doctrine_Collection(\'vendor\models\foo\ModelL\');
    }
}
',
'/root/folder/models/foo/Model.php' =>
'<?php
namespace vendor\models\foo;

class Model{}
class ModelA{}
class ModelB{}
class ModelC{}
class ModelD{}
class ModelE{}
class ModelF{}
class ModelG{}
class ModelH{}
class ModelI{}
class ModelJ{}
class ModelK{}
class ModelL{}
',
'/root/folder/models/foo/ModelTwo.php' =>
'<?php
namespace vendor\models\foo;
use vendor\namespace\ClassTwo;

class ModelTwo extends ClassTwo
{
    new \Mandango\Group\EmbeddedGroup(\'\rg\models\mongodb\TopicBean\');
    new \Mandango\Group\ReferenceGroup(\'\rg\models\mongodb\TopicBean\');
}
' );
        $this->assertEquals($expected, $this->filesystem->savedFiles);
    }
    
    public function testModifyingTemplatesBeforeFilesThrowsException() {
        $this->setExpectedException('Exception', 'You have to call modify files prior to modifying templates');
        $files = array_keys($this->filesystem->filesystem);
        $this->classModifier->addFileModifier(rg\tools\phpnsc\FileModifier::getModifier(
            '\\researchgate\\tools\\PHPNamespacify\\PregReplaceFileModifier', array(
            'search' => "/Autoloader::addPackage\(('|\")[a-zA-Z0-9_\/\*]+('|\")\);\s*/i", 'replace' => ''
        )));
        $this->classModifier->modifyTemplates($files);
    }
    
    public function testModifyTemplates() { return;
        $files = array_keys($this->filesystem->filesystem);
        $this->classModifier->addFileModifier(rg\tools\phpnsc\FileModifier::getModifier(
            '\\researchgate\\tools\\PHPNamespacify\\PregReplaceFileModifier', array(
            'search' => "/Autoloader::addPackage\(('|\")[a-zA-Z0-9_\/\*]+('|\")\);\s*/i", 'replace' => ''
        )));
        $this->classModifier->modifyFiles($files);
        $expected = array();
        $this->filesystem->savedFiles = array();
        
        $this->classModifier->modifyTemplates($files);
        
        $this->assertEquals($expected, $this->filesystem->savedFiles);
    }
}

class ClassModifierFilesystemMock extends \rg\tools\phpnsc\FilesystemAccess
{
    public $filesystem = array(
'/root/folder/namespace/ClassOne.php' =>
'<?php
class ClassOne extends ClassTwo {
    public function test() {
        parent::test();
        
        Autoloader::addPackage(\'foo/bar/*\');
        Autoloader::addPackage("foo/bar/*");
        Autoloader::addClass("foo/bar/*");
        Autoloader::addClass("foo/bar/*", true);
        Autoloader::addClass("foo/bar/*",true);
        AutoLoader::addPackage("foo/bar/*");
        AutoLoader::addPackage("foo/bar/*", false);
                
        $b = new ClassTwo(ClassOne::CONSTANT, ClassTwo::CONSTANT, OutOfNamespace $foo, OtherNamespace $bar, OutOfNamespace::FOO);
        $c = new OutOfNamespace;
        try {
        } catch(cassandra_TestException   $e) {
        }
    }
}
',
'/root/folder/namespace/ClassTwo.php' =>
'<?php
   abstract   class ClassTwo extends OutOfNamespace implements InterfaceA {
    public function test() {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, OutOfNamespace $foo, OtherNamespace $bar, OutOfNamespace::FOO);
        $c = new OutOfNamespace;
        if(class_exists(\'ClassTwo\')){}
    }
}
',
'/root/folder/namespaceTwo/OtherNamespace.php' =>
'<?php
class OtherNamespace extends OutOfNamespace implements InterfaceA , InterfaceB {
    public function test() {
        parent::test();
        $b = new ClassTwo(ClassTwo::CONSTANT, OutOfNamespace $foo, OtherNamespace $bar, OutOfNamespace::FOO, InterfaceA $abc);
        $c = new OutOfNamespace;
        $d = new Mandango\Exception();
        $e = new cassandra_Class();
        if($i instanceof OutOfNamespace) {}
        if($i instanceof ClassOne) {}
        new Mandango\Mandango(new Model(), new \Mandango\Cache\CappedArrayCache(), $loggerCallable);
    }
}
',
'/root/folder/namespaceTwo/InterfaceA.php' =>
'<?php
interface InterfaceA{
    public function test();
}
',
'/root/folder/namespaceThree/InterfaceB.php' =>
'<?php
interface InterfaceB {
    public function test();
    get_class($foo) == \'ModelA\';
    get_class($foo) != \'ModelA\';
    is_subclass_of($foo, \'ModelA\');
}
',
'/root/folder/namespaceTwo/DoctrineTest.php' =>
'<?php
class DoctrineTest{
    protected $documentClass = \'ModelJ\';
    public function test() {
        Doctrine::getTable(\'Model\');
        $s->hasMany(\'ModelA\', array(
           "refClass"=> "ModelB"
          ))
          ->hasOne(\'ModelC\')
          ->from(\'ModelD e\')
          ->innerJoin(\'a.ModelE\')
          ->rightJoin(\'table.ModelF\')
          ->leftJoin(\'a.ModelG\');
          ->leftJoin(\'ModelK\');
        $s->delete(\'ModelH\');
        $s->update(
            \'ModelI\'
        );
        $s->update(
            \'NotDefined\'
        );
        $q = Doctrine_Query::create()
        new Doctrine_Collection(\'ModelL\');
    }
}
',
'/root/folder/models/foo/Model.php' =>
'<?php
class Model{}
class ModelA{}
class ModelB{}
class ModelC{}
class ModelD{}
class ModelE{}
class ModelF{}
class ModelG{}
class ModelH{}
class ModelI{}
class ModelJ{}
class ModelK{}
class ModelL{}
',
'/root/folder/models/foo/ModelTwo.php' =>
'<?php
class ModelTwo extends \ClassTwo
{
    new Mandango\Group\EmbeddedGroup(\'TopicBean\');
    new Mandango\Group\ReferenceGroup(\'TopicBean\');
}
'        
    );
    public $savedFiles = array();
    
    public function getFile($filename) {
        return $this->filesystem[$filename];
    }
    
    public function saveFile($filename, $data) {
        $this->savedFiles[$filename] = $data;
    }
}