<?php

namespace rg\tools\phpnsc\Parser;

use PHPUnit\Framework\TestCase;

class ClassReferencesAggregatorTest extends TestCase
{
    public function test()
    {
        $resolver = new ClassReferencesAggregator();
        $extraction = $resolver->collectClassReferences(__DIR__.'/../_fixtures/testcode.php');

        $referencedClasses = $extraction->getReferencedClasses();
        $declaredClasses = $extraction->getDeclaredClasses();
        $declaredNamespaces = $extraction->getDeclaredNamespaces();

        $expectedReferencedClasses = [
            'Foo\\Bar\\Extended',
            'Foo\\Bar\\Implemented',

            'Foo\\Bar\\Trait1',
            'Foo\\Bar\\Trait2',
            'Foo\\Bar\\Trait3',
            'Foo\\Bar\\Trait4',

            'Foo\\Bar\\MethodArg',
            'Foo\\Bar\\MethodEllipsisArg',
            'Foo\\Bar\\MethodReturn',

            'Foo\\Bar\\NewInstance',
            'Foo\\Bar\\InstanceOfClass',

            'Foo\\Bar\\StaticMethods',
            'Foo\\Bar\\StaticProperties',
            'Foo\\Bar\\Constants',
            'Foo\\Bar\\Exception',

            'Foo\\Bar\\ClassInBarNamespace',
            'Foo\\Bar\\AliasedClass',
            'Foo\\Test',

            'Foo\\Bar\\FunctionArg',
            'Foo\\Bar\\FunctionNullableArg',
            'Foo\\Bar\\FunctionEllipsisArg',
            'Foo\\Bar\\FunctionReturn',
        ];

        $this->assertEquals($expectedReferencedClasses, array_keys($referencedClasses));
        $this->assertEquals(['Foo\\Test'], array_keys($declaredClasses));
        $this->assertEquals(['Foo'], array_keys($declaredNamespaces));
    }
}
