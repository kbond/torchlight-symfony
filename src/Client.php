<?php

namespace Torchlight\Symfony;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Client
{
    /**
     * @param BlockCollection $blocks The blocks to render
     *
     * @return BlockCollection The rendered blocks
     */
    public function render(BlockCollection $blocks, Configuration $configuration): BlockCollection;
}
