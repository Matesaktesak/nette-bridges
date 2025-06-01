<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\ContentType;
use Latte\Essential\Nodes\ExtendsNode;

class InputIdNode extends StatementNode {

    public const string Input = 'input', Form = 'form';
    public string $type;

    public ?ExpressionNode $controlNameExpression = null;
    public ?ExpressionNode $controlPartExpression = null;

    public static function create(Tag $tag): self {
        $node = $tag->node = new self;
        $node->type = match($tag->name) {
            'form.id' => self::Form,
            'input.id' => self::Input,
        };

        if(!$tag->parser->isEnd()) $node->controlNameExpression = $tag->parser->parseUnquotedStringOrExpression();
        if(!$tag->parser->isEnd()) $node->controlPartExpression = $tag->parser->parseUnquotedStringOrExpression();

        return $node;
    }

    public function print(PrintContext $context): string {
        // form.id tag
        if($this->type === self::Form) {
            // When no argument is specified
            if($this->controlNameExpression === null) return $context->format(
                <<< PHP
                    echo %escape(end(\$this->global->formsStack)->getElementPrototype()->id) %line;
                PHP,
                $this->position
            );

            // With an argument
            if($this->controlNameExpression instanceof StringNode){
                // The argument is a string
                return $context->format(
                    <<< PHP
                        echo %escape(\$this->global->uiControl->getComponent(%dump)->getElementPrototype()->id) %line;
                    PHP,
                    $this->controlNameExpression,
                    $this->position,
                );
            }
            // The argument is some expression
            return $context->format(
                <<< PHP
                    echo %escape((%node)->getElementPrototype()->id) %line;
                PHP,
                $this->controlNameExpression,
                $this->position,
            );
        // input.id tag
        } else {
            $prefixVar = '$__prefix_'.$context->generateId();
            return $context->format(
                <<< PHP
                    %2.raw = str_contains(%0.dump, '-')) %1.line
                        ? \$this->global->uiControl->getComponent(%0.node)
                        : ( is_object(%0.node)
                            ? %0.node
                            : end(\$this->global->formsStack)[%0.dump]
                        );
                    if(%3.node){
                        echo %escape(%2.raw->getControlPart(%3.node)->getAttribute('id'))
                    } else echo %escape(%2.raw->getHtmlId());
                PHP,
                $this->controlNameExpression,
                $this->position,
                $prefixVar,
                $this->controlPartExpression
            );
        }
    }

    public function &getIterator(): \Generator {
        if($this->controlNameExpression) yield $this->controlNameExpression;
        if($this->controlPartExpression) yield $this->controlPartExpression;
    }
}
