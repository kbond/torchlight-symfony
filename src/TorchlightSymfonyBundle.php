<?php

namespace Torchlight\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Torchlight\Symfony\DependencyInjection\TorchlightSymfonyExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TorchlightSymfonyBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new TorchlightSymfonyExtension();
    }
}
