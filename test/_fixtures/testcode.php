<?php
/** @noinspection ALL */
namespace Foo;

/**
 * To cover for class imports:
 * - FQN (e.g. \Foo\Bar\ReferenceClass)
 * - Aliases for class or namespace
 * - Multiple use statements concatenated
 * - The class definitions itself within the namespace
 *
 * Import statements are handled by PHP-Parser. Testing only resolution of aliases, just to be sure.
 */

use Foo\Bar as NamespaceAlias,
    Foo\Bar\AliasedClass as ClassAlias;

/**
 * To cover for class references:
 * - FQN (e.g. \Foo\Bar\ReferenceClass)
 * - Only the class name without namespace (e.g. ReferenceClass)
 * - Relative reference with partial namespace (e.g. Bar\ReferenceClass)
 *
 * Name resolution is handled by PHP-Parser. Testing only name resolution with a partial namespace.
 */

/**
 * Usage types to cover:
 * - extends / implememts
 * - Trait use, including conflict resolution with "insteadof"
 * - Function/method type hints, including T_ELLIPSIS (...) arguments and return types
 * - new statements
 * - Static class (constants, static properties, static calls)
 * - catch statements
 *
 * Not extracted:
 * - Classes referenced in PHPDoc
 * - PHP annotations
 */

/**
 * @property Bar\PhpDocProperty
 */
class Test extends Bar\Extended implements Bar\Implemented // Error
{
    use Bar\Trait1, Bar\Trait2 {
        Bar\Trait3::foo insteadof Bar\Trait4;
    }

    /**
     * @var Bar\PropertyType $property
     */
    private $property;

    /**
     * @Bar\Annotation
     *
     * @param Bar\PhpDocArg
     * @return Bar\PhpDocReturn
     * @throws Bar\PhpDocThrows
     */
    public function checkUsages(Bar\MethodArg $foo, Bar\MethodEllipsisArg ... $array) : ?Bar\MethodReturn
    {
        new Bar\NewInstance;
        echo $foo instanceof Bar\InstanceOfClass;

        Bar\StaticMethods::staticCall();
        Bar\StaticProperties::$staticProperty;
        echo Bar\Constants::CONSTANT;

        try {} catch (Bar\Exception $e) {}

        return null;
    }

    public function checkAliasResolution()
    {
        new NamespaceAlias\ClassInBarNamespace;
        new ClassAlias;
        new Test(); // This must be the class itself
    }
}

function (Bar\FunctionArg $foo, ?Bar\FunctionNullableArg $bar, Bar\FunctionEllipsisArg ...$array) : ?Bar\FunctionReturn {};
