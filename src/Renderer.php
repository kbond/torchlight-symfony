<?php

namespace Torchlight\Symfony;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torchlight\Symfony\Client\SymfonyClient;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Renderer
{
    private Client $client;
    private Configuration $configuration;

    public function __construct(Client $client, Configuration $configuration)
    {
        $this->client = $client;
        $this->configuration = $configuration;
    }

    public static function create(string $apiKey, array $config = [], ?Client $client = null): self
    {
        return new self($client ?? self::autoDiscoverClient(), new Configuration($apiKey, $config));
    }

    /**
     * @see Block::__construct()
     *
     * @return Block The rendered block
     */
    public function render(string $code, ?string $language = null, ?string $theme = null): Block
    {
        foreach ($this->renderBlocks((new BlockCollection())->add(new Block($code, $language, $theme))) as $renderedBlock) {
            return $renderedBlock;
        }

        throw new \RuntimeException('No block rendered.');
    }

    /**
     * @return BlockCollection The rendered blocks
     */
    public function renderBlocks(BlockCollection $blocks): BlockCollection
    {
        return $this->client->render($blocks, $this->configuration);
    }

    private static function autoDiscoverClient(): Client
    {
        if (\interface_exists(HttpClientInterface::class)) {
            return new SymfonyClient();
        }

        // TODO: Guzzle, Laravel, etc

        throw new \LogicException('Could not auto-discover client, try running "composer require symfony/http-client".');
    }
}
