<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Essential\Nodes\ExtendsNode;

class InputIdNode extends StatementNode {

    public const string Input = 'input', Form = 'form';
    public string $type;

    public ?ExpressionNode $argumentExpression;

    public function create(Tag $tag): self {
        $node = $tag->node = new self;
        $node->type = match($tag->name) {
            'form.id' => self::Form,
            'input.id' => self::Input,
        };

        if(!$tag->parser->isEnd()) $node->argumentExpression = $tag->parser->parseUnquotedStringOrExpression();

        return $node;
    }

    public function print(PrintContext $context): string {
        return $context->format(
            <<< PHP
                echo
            PHP

        );
    }

    public function &getIterator(): \Generator {
        yield $this->argumentExpression;
    }
}
