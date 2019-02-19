<?php

namespace rg\tools\phpnsc\Parser;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class ClassReferencesAggregator
{
    public function collectClassReferences(string $filePath): ClassReferencesVisitor
    {
        $visitor = new ClassReferencesVisitor();
        $traverser = new NodeTraverser();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, new Lexer());

        $code = file_get_contents($filePath);
        $stmts = $parser->parse($code);

        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

        return $visitor;
    }
}
