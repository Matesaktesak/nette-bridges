<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\NullNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class DialogNode extends StatementNode {
    public ExpressionNode $target;
    public ?ExpressionNode $targetValue;
    public ?ArrayNode $args = null;

    public string $type;

    /**
     * @throws CompileException
     */
    public static function create(Tag $tag): self {
        $tag->expectArguments();
        if(!$tag->isNAttribute()) throw new CompileException("Tag {$tag->name} can only be used as n:{$tag->name}");

        $node = $tag->node = new self;

        $node->type = preg_replace('/^dialog\.?/', '', $tag->name) ?: null;

        $node->target = $tag->parser->parseExpression();
        $node->targetValue = !$tag->parser->isEnd()
            ? $tag->parser->parseExpression()
            : new NullNode()
        ;

        $node->args = !$tag->parser->isEnd()
            ? $tag->parser->parseArguments()
            : new ArrayNode();
        ;

        return $node;
    }

    public function print(PrintContext $context): string {
        return $context->format(
            <<< PHP
                \$__dialogSpec = [ %line
                    'current' => %1.node === '@current',
                    'self' => %1.node === '@self',
                    'name' => %1.node,
                    'source' => %2.node,
                    'options' => %3.node,
                    'type' => %4.dump,
                ];

                if((\$__dialogSpec['current'] || \$__dialogSpec['self']) && \$__dialogSpec['source'] === 'keep') {
                    \$__dialogSpec['name'] = array_shift(\$__dialogSpec['options']);
                    \$__dialogSpec['source'] = array_shift(\$__dialogSpec['options']);
                }

                if(!\$__dialogSpec['source'] && \$__dialogSpec['type'] !== 'iframe') \$__dialogSpec['source'] = ltrim(\$__dialogSpec['name'], '@');

                echo ' data-dialog="' . %escape(json_encode([
                    'current' => \$__dialogSpec['current'], // true/false
                    'self' => \$__dialogSpec['self'],       // true/false
                    \$this->global->nittro->getDialogId(\$__dialogSpec['name']) => [
                        'type' => \$__dialogSpec['type'] ?? null,
                        'source' => \$__dialogSpec['source'] ? \$this->global->snippetDriver->getHtmlId(\$__dialogSpec['source']) : null,
                        'options' => \$__dialogSpec['options'],
                    ]
                ])) . '"';
            PHP,
            $this->position,
            $this->target,
            $this->targetValue,
            $this->args,
            $this->type,
        );
    }

    public function &getIterator(): \Generator {
        yield $this->target;
        if($this->targetValue) yield $this->targetValue;
        if($this->args) yield $this->args;
    }
}
