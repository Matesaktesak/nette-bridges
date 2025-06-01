<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Node;
use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class DynamicNode extends StatementNode {

    public const string DynamicClassName = 'nittro-snippet-container';

    public ExpressionNode $argumentExpression;

    /**
     * @throws CompileException
     */
    public static function create(Tag $tag): self {
        $tag->expectArguments();
        if(!$tag->isNAttribute() || $tag->prefix && $tag->prefix != Tag::PrefixNone)
            throw new CompileException('Unknown macro ' . $tag->getNotation() . ', did you mean n:' . $tag->name . '?');

        $node = $tag->node = new self;
        $node->argumentExpression = $tag->parser->parseUnquotedStringOrExpression();

        $classAttr = $tag->htmlElement->getAttribute("class");
        $newClassAttr = new AttributeNode(new TextNode("class"), new TextNode(self::DynamicClassName));
        if($classAttr){
            $oldClassText = $classAttr;
            if($classAttr instanceof AttributeNode) $oldClassText = $classAttr->value->print(new PrintContext());

            // Class is a string without 'nitro-snippet-container'
            if(!preg_match('/(?:^|\s)' . self::DynamicClassName . '(?:\s|$)/', $oldClassText)){
                throw new CompileException("Dynamic container specifying the 'class' attribute must include the '" . self::DynamicClassName ."' class");
            }
        } else $tag->htmlElement->attributes->append($newClassAttr);

        return $node;
    }

    public function print(PrintContext $context): string {
        return $context->format(
            <<< PHP
                echo ' data-dynamic-mask="', LR\Filters::escapeHtmlAttribute($this->global->snippetDriver->getHtmlId(%node)), '"' %line
            PHP,
            $this->argumentExpression,
            $this->position
        );
    }

    public function &getIterator(): \Generator {
        yield $this->argumentExpression;
    }
}
