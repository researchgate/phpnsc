<?php

namespace rg\tools\phpnsc\Parser;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeVisitorAbstract;

class ClassReferencesVisitor extends NodeVisitorAbstract
{
    private $declaredNamespaces = [];
    private $declaredClasses = [];
    private $referencedClasses = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->declaredNamespaces[(string) $node->name] = $node;
        } else if ($node instanceof Node\Stmt\ClassLike) {
            $this->declaredClasses[(string) $node->namespacedName] = $node;
        } else if ($node instanceof FullyQualified) {
            $fullyQualifiedName = (string) $node;
            $this->referencedClasses[$fullyQualifiedName][] = $node;
        }
    }

    public function getDeclaredNamespaces(): array
    {
        return $this->declaredNamespaces;
    }

    public function getDeclaredClasses(): array
    {
        return $this->declaredClasses;
    }

    public function getReferencedClasses(): array
    {
        return $this->referencedClasses;
    }
}
