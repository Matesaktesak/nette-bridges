<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class SnippetIdNode extends StatementNode {

    public ExpressionNode $argumentExpression;

    /**
     * @throws CompileException
     */
    public static function create(Tag $tag): self {
        $tag->expectArguments();
        $node = $tag->node = new self;
        $node->argumentExpression = $tag->parser->parseUnquotedStringOrExpression();
        return $node;
    }

    public function print(PrintContext $context): string {
        return $context->format(
            <<< PHP
                echo %escape($this->global->snippetDriver->getHtmlId(%node)) %line
            PHP,
            $this->argumentExpression,
            $this->position
        );
    }

    public function &getIterator(): \Generator {
        yield $this->argumentExpression;
    }
}
