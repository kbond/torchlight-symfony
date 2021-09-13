<?php

namespace Torchlight\Symfony\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TorchlightExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('torchlight', [TorchlightRuntime::class, 'render'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFilter('torchlight_inline', [TorchlightRuntime::class, 'renderInline'], ['is_safe' => ['html']]),
        ];
    }
}
