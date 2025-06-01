<?php

declare(strict_types=1);

namespace Nittro\Bridges\NittroLatte;

use Latte\Runtime\Template;
use Nette\Application\UI\Component;
use Nittro\Bridges\NittroUI\Helpers;


class NittroRuntime {
    public Component $control;

    public function getDialogId(string $name) : string {
        return Helpers::formatDialogId($name, $this->control);
    }

}
