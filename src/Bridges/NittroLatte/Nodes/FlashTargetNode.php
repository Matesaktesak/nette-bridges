<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Nette\NotImplementedException;

class FlashTargetNode extends StatementNode {
    public ?ExpressionNode $placementExpression;
    public bool $isNAttribute;

    /**
     * @throws CompileException
     */
    public static function create(Tag $tag): self {
        $node = $tag->node = new self;
        if($tag->prefix && $tag->prefix !== Tag::PrefixNone) throw new CompileException("Unknown macro {$tag->getNotation()}, did you mean n:{$tag->name}?");
        if($tag->htmlElement->getAttribute("id") !== null) throw new CompileException("Cannot combine HTML attribute id with {$tag->getNotation()}");

        $node->isNAttribute = $tag->isNAttribute();
        if(!$tag->parser->isEnd()) $node->placementExpression = $tag->parser->parseUnquotedStringOrExpression();

        return $node;
    }

    public function print(PrintContext $context): string {
        if($this->isNAttribute){
            return $context->format(
                <<< PHP
                    echo ' id="', LR\Filters::escapeHtmlAttribute($this->global->uiControl->getParameterId('flashes')), '"' %line
                PHP . ($this->placementExpression ? <<< PHP
                    echo ' data-flash-placement="', %node, '"'
                PHP : ''),
                $this->position,
                $this->placementExpression
            );

        } else {
            // TODO
            throw new NotImplementedException();
        }
    }

    public function &getIterator(): \Generator {
        yield $this->placementExpression;
    }
}
