<?php

namespace Torchlight\Symfony;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Configuration
{
    public const ENDPOINT_URL = 'https://api.torchlight.dev/highlight';

    private string $apiKey;
    private string $defaultTheme;
    private array $globalOptions;

    public function __construct(string $apiKey, array $globalOptions = [])
    {
        $this->apiKey = $apiKey;
        $this->defaultTheme = $globalOptions['theme'] ?? 'github-light';

        unset($globalOptions['theme']);

        $this->globalOptions = \array_filter($globalOptions, static fn($option) => null !== $option);
    }

    public function apiKey(): string
    {
        return $this->apiKey;
    }

    public function defaultTheme(): string
    {
        return $this->defaultTheme;
    }

    public function globalOptions(): array
    {
        return $this->globalOptions;
    }

    /**
     * @return array The request body formatted for the Torchlight API
     */
    public function requestBodyFor(BlockCollection $blocks): array
    {
        return [
            'blocks' => \array_values(
                \array_map(
                    function(Block $block) {
                        return \array_filter([
                            'id' => $block->id(),
                            'code' => $block->code(),
                            'language' => $block->language(),
                            'theme' => $block->theme() ?? $this->defaultTheme,
                        ]);
                    },
                    $blocks->all()
                )
            ),
            'options' => $this->globalOptions,
        ];
    }

    /**
     * @param array           $response The response from the Torchlight API
     * @param BlockCollection $blocks   The blocks used for the request
     *
     * @return BlockCollection The rendered blocks
     */
    public function renderBlocksFromResponse(array $response, BlockCollection $blocks): BlockCollection
    {
        foreach ($response['blocks'] as $block) {
            $blocks->get($block['id'])
                ->render($block['wrapped'], $block['highlighted'], $block['classes'], $block['styles'])
            ;
        }

        return $blocks;
    }
}
