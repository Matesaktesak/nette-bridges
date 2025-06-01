<?php

namespace Nittro\Bridges\NittroLatte;

use Latte\Extension;
use Latte\Runtime;
use Nette\Application\UI\Component;
use Nittro\Bridges\NittroLatte\Nodes\DialogNode;
use Nittro\Bridges\NittroLatte\Nodes\DynamicNode;
use Nittro\Bridges\NittroLatte\Nodes\ErrorsNode;
use Nittro\Bridges\NittroLatte\Nodes\FlashesNode;
use Nittro\Bridges\NittroLatte\Nodes\FlashTargetNode;
use Nittro\Bridges\NittroLatte\Nodes\InputIdNode;
use Nittro\Bridges\NittroLatte\Nodes\ParamNode;
use Nittro\Bridges\NittroLatte\Nodes\SnippetIdNode;
class NittroExtension extends Extension {
    public function getTags(): array {
        return [
            'snippet.id' => SnippetIdNode::create(...),
            'input.id' => InputIdNode::create(...),
            'form.id' => InputIdNode::create(...),
            'param' => ParamNode::create(...),
            'flashes' => FlashesNode::create(...),
            'flashes.target' => FlashTargetNode::create(...),
            'dynamic' => DynamicNode::create(...),
            'errors' => ErrorsNode::create(...),
            'n:errors' => ErrorsNode::create(...),
            'errors.form' => ErrorsNode::create(...),
            'n:errors.form' => ErrorsNode::create(...),
            'dialog' => DialogNode::create(...),
            'dialog.form' => DialogNode::create(...),
            'dialog.iframe' => DialogNode::create(...)
        ];
    }

    public function beforeRender(Runtime\Template $template): void {
        if (
            isset($template->global->uiControl)
            && isset($template->global->nittro)
            && $template->global->uiControl instanceof Component
            && $template->global->nittro instanceof NittroRuntime
        ) {
            $template->global->nittro->control = $template->global->uiControl;
        }
    }

    public static function deprecated(string $old, string $new) : void {
        $new = $old[0] === '{' ? "{{$new}}" : "n:$new";
        trigger_error("The {$old} macro is deprecated, please use {$new}", E_USER_DEPRECATED);
    }
}
