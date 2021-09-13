<?php

namespace Torchlight\Symfony\Markdown\CommonMark;

use Torchlight\Symfony\Renderer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class BaseTorchlightExtension
{
    protected Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }
}
