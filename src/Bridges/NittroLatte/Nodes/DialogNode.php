<?php

namespace Nittro\Bridges\NittroLatte\Nodes;

use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class DialogNode extends StatementNode {

    public function create(Tag $tag): self {
        $node = $tag->node = new self;
        return $node;
    }

    public function print(PrintContext $context): string {
        return $context->format();
    }

    public function &getIterator(): \Generator {
        false && yield;
    }
}
