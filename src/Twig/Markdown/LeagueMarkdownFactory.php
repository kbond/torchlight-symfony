<?php

namespace Torchlight\Symfony\Twig\Markdown;

use League\CommonMark\CommonMarkConverter;
use Torchlight\Symfony\Markdown\CommonMark\TorchlightExtension;
use Twig\Extra\Markdown\LeagueMarkdown;

/**
 * Helper for creating {@see \Twig\Extra\Markdown\LeagueMarkdown} with
 * {@see TorchlightExtension} enabled.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LeagueMarkdownFactory
{
    private TorchlightExtension $extension;

    public function __construct(TorchlightExtension $extension)
    {
        $this->extension = $extension;
    }

    public function __invoke(): LeagueMarkdown
    {
        $converter = new CommonMarkConverter();
        $converter->getEnvironment()->addExtension($this->extension);

        return new LeagueMarkdown($converter);
    }
}
