<?php

namespace Torchlight\Symfony\Client;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Torchlight\Symfony\BlockCollection;
use Torchlight\Symfony\Client;
use Torchlight\Symfony\Configuration;

/**
 * Torchlight client that uses symfony/http-client.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SymfonyClient implements Client
{
    private HttpClientInterface $httpClient;

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function render(BlockCollection $blocks, Configuration $configuration): BlockCollection
    {
        if (!\count($blocks)) {
            return $blocks;
        }

        $response = $this->httpClient->request('POST', Configuration::ENDPOINT_URL, [
            'auth_bearer' => $configuration->apiKey(),
            'json' => $configuration->requestBodyFor($blocks),
        ])->toArray();

        return $configuration->renderBlocksFromResponse($response, $blocks);
    }
}
