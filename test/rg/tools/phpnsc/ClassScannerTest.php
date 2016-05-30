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
$bar = "new Foo"; $bar = \'new Foo\';
$bar = <<<INDEXEXCHANGE
function cygnus_index_parse_res(){}function cygnus_index_start(){function e(e){var t=a[e];return"string"==typeof t?t:"\\\\u"+("0000"+e.charCodeAt(0).toString(16)).slice(-4)}function t(t){return o.lastIndex=0,o.test(t)?t.replace(o,e):t}function i(e,t,i){if(this.initialized=!1,"number"!=typeof e||e%1!==0||0>e)throw"Invalid Site ID";if("number"==typeof i&&i%1==0&&i>=0&&(this.timeoutDelay=i),this.siteID=e,this.impressions=[],this._parseFnName=void 0,top===self?(this.sitePage=location.href,this.topframe=1):(this.sitePage=document.referrer,this.topframe=0),"undefined"!=typeof t){if("function"!=typeof t)throw"Invalid jsonp target function";this._parseFnName="cygnus_index_args.parseFn"}"undefined"==typeof _IndexRequestData.requestCounter?_IndexRequestData.requestCounter=Math.floor(256*Math.random()):_IndexRequestData.requestCounter=(_IndexRequestData.requestCounter+1)%256,this.requestID=String((new Date).getTime()%2592e3*256+_IndexRequestData.requestCounter+256),this.initialized=!0}if(cygnus_index_primary_request){for(var s=[],n=0;n<cygnus_index_args.slots.length;n++){var r=cygnus_index_args.slots[n],u={id:"T1_"+r.id,width:r.width,height:r.height,siteID:169418};({id:"T2_"+r.id,width:r.width,height:r.height,siteID:444444});s.push(u)}for(var n=0;n<s.length;n++)cygnus_index_args.slots.push(s[n]);cygnus_index_primary_request=!1}cygnus_index_args.parseFn=cygnus_index_parse_res;var o=/[\\\\\\"\\x00-\\x1f\\x7f-\\x9f\\u00ad\\u0600-\\u0604\\u070f\\u17b4\\u17b5\\u200c-\\u200f\\u2028-\\u202f\\u2060-\\u206f\\ufeff\\ufff0-\\uffff]/g,a={"\\b":"\\\\b","	":"\\\\t","\\n":"\\\\n","\\f":"\\\\f","\\r":"\\\\r",\'"\':\'\\\\"\',"\\\\":"\\\\\\\\"};i.prototype.serialize=function(){var e=\'{"id":\'+this.requestID+\',"site":{"page":"\'+t(this.sitePage)+\'"\';"string"==typeof document.referrer&&(e+=\',"ref":"\'+t(document.referrer)+\'"\'),e+=\'},"imp":[\';for(var i=0;i<this.impressions.length;i++){var s=this.impressions[i],n=[];e+=\'{"id":"\'+s.id+\'", "banner":{"w":\'+s.w+\',"h":\'+s.h+\',"topframe":\'+String(this.topframe)+"}","number"==typeof s.bidfloor&&(e+=\',"bidfloor":\'+s.bidfloor,"string"==typeof s.bidfloorcur&&(e+=\',"bidfloorcur":"\'+t(s.bidfloorcur)+\'"\')),"string"!=typeof s.slotID||s.slotID.match(/^\\s*$/)||n.push(\'"sid":"\'+t(s.slotID)+\'"\'),"number"==typeof s.siteID&&n.push(\'"siteID":\'+s.siteID),n.length>0&&(e+=\',"ext": {\'+n.join()+"}"),e+=i+1==this.impressions.length?"}":"},"}return e+="]}"},i.prototype.setPageOverride=function(e){return"string"!=typeof e||e.match(/^\\s*$/)?!1:(this.sitePage=e,!0)},i.prototype.addImpression=function(e,t,i,s,n,r){var u={id:String(this.impressions.length+1)};if("number"!=typeof e||1>=e)return null;if("number"!=typeof t||1>=t)return null;if(("string"==typeof n||"number"==typeof n)&&String(n).length<=50&&(u.slotID=String(n)),u.w=e,u.h=t,void 0!=i&&"number"!=typeof i)return null;if("number"==typeof i){if(0>i)return null;if(u.bidfloor=i,void 0!=s&&"string"!=typeof s)return null;u.bidfloorcur=s}if("undefined"!=typeof r){if(!("number"==typeof r&&r%1===0&&r>=0))return null;u.siteID=r}return this.impressions.push(u),u.id},i.prototype.buildRequest=function(){if(0!=this.impressions.length&&this.initialized===!0){var e=encodeURIComponent(this.serialize()),t="https:"===window.location.protocol?"https://as.casalemedia.com":"http://as.casalemedia.com";return t+="/headertag?v=9&x3=1&fn=cygnus_index_parse_res&s="+this.siteID+"&r="+e,"number"==typeof this.timeoutDelay&&this.timeoutDelay%1==0&&this.timeoutDelay>=0&&(t+="&t="+this.timeoutDelay),t}};try{if("undefined"==typeof cygnus_index_args||"undefined"==typeof cygnus_index_args.siteID||"undefined"==typeof cygnus_index_args.slots)return;"undefined"==typeof _IndexRequestData&&(_IndexRequestData={},_IndexRequestData.impIDToSlotID={},_IndexRequestData.reqOptions={});var d=new i(cygnus_index_args.siteID,cygnus_index_args.parseFn,cygnus_index_args.timeout);cygnus_index_args.url&&"string"==typeof cygnus_index_args.url&&d.setPageOverride(cygnus_index_args.url),_IndexRequestData.impIDToSlotID[d.requestID]={},_IndexRequestData.reqOptions[d.requestID]={};for(var f,g,s=0;s<cygnus_index_args.slots.length;s++)f=cygnus_index_args.slots[s],g=d.addImpression(f.width,f.height,f.bidfloor,f.bidfloorcur,f.id,f.siteID),g&&(_IndexRequestData.impIDToSlotID[d.requestID][g]=String(f.id));return"number"==typeof cygnus_index_args.targetMode&&(_IndexRequestData.reqOptions[d.requestID].targetMode=cygnus_index_args.targetMode),"function"==typeof cygnus_index_args.callback&&(_IndexRequestData.reqOptions[d.requestID].callback=cygnus_index_args.callback),d.buildRequest()}catch(h){}}cygnus_index_args={timeout:300,siteID:$indexExchangeSiteId,slots:[{id:"1",width:728,height:90,siteID:$indexExchangeSiteId},{id:"2",width:300,height:250,siteID:$indexExchangeSiteId},{id:"3",width:320,height:50,siteID:$indexExchangeSiteId}]};var cygnus_index_primary_request=!0;
window.load_index = function(){
    var indexScript = document.createElement(\'script\');
    indexScript.type = \'text/javascript\';
    indexScript.src = cygnus_index_start();
    var node = document.getElementsByTagName(\'script\')[0];
    node.parentNode.insertBefore(indexScript,node);
}

load_index();
cygnus_index_ready_state=function(){
INDEXEXCHANGE;
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
                'Foo' => array(3),
                'Bar' => array(3),
            ),
            '/root/folder/namespace/ClassTwo.php' => array(
                'ClassOne' => array(18),
                'ClassTwo' => array(19,22),
                'ClassThree' => array(18),
                'OutOfNamespace' => array(22),
                'TypeHintClass' => array(18),
                'OtherNamespace' => array(22),
                'Bar' => array(3),
            ),
        );

        foreach ($files as $file) {
            $this->assertEquals($expectedEntities[$file], $this->classScanner->getUsedEntities($file));
        }
    }

    public function testParseUsedEntitiesOtherHeredoc() {
        $this->filesystem->filesystem = [
            '/root/folder/namespace/ClassOne.php' => '
<?php
class ClassOne  implements Bar  extends Foo
',
            '/root/folder/namespace/ClassTwo.php' => '
<?php
abstract    class      ClassTwo   implements Bar
$bar = "new Foo"; $bar = \'new Foo\';
$bar = <<<_TEXT
        new Foo();
_TEXT;
 $foo = new ClassOne(ClassThree::CONSTANT, TypeHintClass  $variable);
$bar = new ClassTwo;
$foo = new __CLASS__;
$xyz = new $variable(NotClassCONSTANT, parent::FOO, self::FOO, static::FOO, array $bar, string $blub, int $bat, $float $baz, callable $bar);
$b = new ClassTwo(ClassTwo::CONSTANT, OutOfNamespace $foo, OtherNamespace $bar, OutOfNamespace::FOO);
interface   InterfaceTwo
function foo(ClassFour $bar) : ClassFive {}
',
        ];
        $files = array_keys($this->filesystem->filesystem);

        $this->classScanner->parseFilesForClassesAndInterfaces($files);

        $expectedEntities = [
            '/root/folder/namespace/ClassOne.php' => [
                'Foo' => [3],
                'Bar' => [3],
            ],
            '/root/folder/namespace/ClassTwo.php' => [
                'ClassOne' => [8],
                'ClassTwo' => [9, 12],
                'ClassThree' => [8],
                'OutOfNamespace' => [12],
                'TypeHintClass' => [8],
                'OtherNamespace' => [12],
                'Bar' => [3],
                'ClassFour' => [14],
                'ClassFive' => [14],
            ],
        ];

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
