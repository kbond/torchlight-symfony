<?php

namespace Torchlight\Symfony\Markdown\CommonMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;

if (\interface_exists(EnvironmentBuilderInterface::class)) {
    /**
     * @author Aaron Francis <aaron@hammerstone.dev>
     * @author Kevin Bond <kevinbond@gmail.com>
     */
    final class TorchlightExtension extends V2TorchlightExtension
    {
    }
} else {
    /**
     * @author Aaron Francis <aarondfrancis@gmail.com>
     * @author Kevin Bond <kevinbond@gmail.com>
     */
    final class TorchlightExtension extends V1TorchlightExtension
    {
    }
}
