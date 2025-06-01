<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\BooleanNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class ErrorsNode extends StatementNode {
    public ?ExpressionNode $fieldNameExpression = null;
    public ?ArrayNode $arguments = null;

    public bool $form = false;
    public bool $nAttr = false;

    /**
     * @throws CompileException
     */
    public static function create(Tag $tag): self {
        $node = $tag->node = new self;
        if($tag->name === 'errors.form') $node->form = true;

        if(!$node->form){
            $tag->expectArguments();
            $node->fieldNameExpression = $tag->parser->parseUnquotedStringOrExpression();
        }

        if(!$tag->parser->isEnd()) $node->arguments = $tag->parser->parseArguments();

        // Only expect content when being an n:attribute, we won't support pair-tag syntax
        if($tag->isNAttribute()) {
            $node->nAttr = true;
            $target = $tag->htmlElement;
            $tagType = strtolower($target->name);
            $childType = match ($tagType) {
                'ul', 'ol' => 'li',
                default => 'p'
            };

            if($target->content !== null) throw new CompileException("Element with {$tag->getNotation()} mustn't have children");

            $target->content = match($node->form) {
                true => new AuxiliaryNode(
                    fn(PrintContext $context, ...$args) => $context->format(
                        <<< PHP
                            \$__form = !%1.dump ? %2.node : \$this->global->uiControl[%2.node] ?? end(\$this->global->formsStack) %0.line;
                            foreach(\$__form->getOwnErrors() as \$__error) {
                                \$__element = Nette\Utils\Html::el(%3.dump)->setClass('error')->setText(\$__error);
                                echo \$__element->getHtml();
                            }
                        PHP
                        , ...$args
                    ),
                    [
                        $tag->node->position,
                        new BooleanNode($node->fieldNameExpression instanceof StringNode),
                        $node->fieldNameExpression,
                        new StringNode($childType)
                    ]
                ),
                false => new AuxiliaryNode(
                    fn(PrintContext $context, ...$args) => $context->format(
                        <<< PHP
                            \$__input = !%1.dump ? %2.node : end(\$this->global->formsStack)[%2.node] %0.line;
                            foreach(\$__input->getErrors() as \$__error) {
                                \$__element = Nette\Utils\Html::el(%3.dump)->setClass('error')->setText(\$__error);
                                echo \$__element->getHtml();
                            }
                        PHP
                        , ...$args
                    ),
                    [
                        $tag->node->position,
                        new BooleanNode($node->fieldNameExpression instanceof StringNode),
                        $node->fieldNameExpression,
                        new StringNode($childType)
                    ]
                )
            };
        }

        return $node;
    }

    public function print(PrintContext $context): string {
        // When operating as n:attribute, we only need to print the id here
        if($this->nAttr){
            if($this->form) return $context->format(
                <<< PHP
                        \$__form = !%1.dump ? %2.node : \$this->global->uiControl[%2.node] ?? end(\$this->global->formsStack) %0.line;
                        echo ' id="' . \$__form->getElementPrototype()->id . '-errors"';
                    PHP,
                $this->position,
                $this->fieldNameExpression instanceof StringNode,
                $this->fieldNameExpression
            );

            return $context->format(
                <<< PHP
                    \$__input = !%1.dump ? %2.node : end(\$this->global->formsStack)[%2.node] %0.line;
                    echo ' id="' . \$__input->getHtmlId() . '-errors"';
                PHP,
                $this->position,
                $this->fieldNameExpression instanceof StringNode,
                $this->fieldNameExpression
            );
        }

        throw new NotImplementedException('{errors} hasn\'t been implemented yet');
    }

    public function &getIterator(): \Generator {
        if($this->fieldNameExpression) yield $this->fieldNameExpression;
        if($this->arguments) yield $this->arguments;
    }
}
