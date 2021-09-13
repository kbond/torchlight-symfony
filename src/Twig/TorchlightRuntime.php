<?php

namespace Torchlight\Symfony\Twig;

use Torchlight\Symfony\Block;
use Torchlight\Symfony\BlockCollection;
use Torchlight\Symfony\Renderer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TorchlightRuntime
{
    private Renderer $renderer;
    private BlockCollection $pending;

    public function __construct(Renderer $renderer, BlockCollection $pending)
    {
        $this->renderer = $renderer;
        $this->pending = $pending;
    }

    public function render(array $context, string $code, ?string $language = null, ?string $theme = null): string
    {
        if (isset($context['app']) && $context['app']->getRequest()) {
            // add to pending collection for render in response event
            $this->pending->add($block = new Block($code, $language, $theme));

            return $block;
        }

        return $this->renderInline($code, $language, $theme);
    }

    public function renderInline(string $code, ?string $language = null, ?string $theme = null): string
    {
        return $this->renderer->render($code, $language, $theme);
    }
}
