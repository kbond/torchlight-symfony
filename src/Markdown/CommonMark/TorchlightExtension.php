<?php

namespace Torchlight\Symfony\Markdown\CommonMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;

if (\interface_exists(EnvironmentBuilderInterface::class)) {
    /**
     * @author Kevin Bond <kevinbond@gmail.com>
     */
    final class TorchlightExtension extends V2TorchlightExtension
    {
    }
} else {
    /**
     * @author Kevin Bond <kevinbond@gmail.com>
     */
    final class TorchlightExtension extends V1TorchlightExtension
    {
    }
}
