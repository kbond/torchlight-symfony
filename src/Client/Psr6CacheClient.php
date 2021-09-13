<?php

namespace Torchlight\Symfony\Client;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Torchlight\Symfony\Block;
use Torchlight\Symfony\BlockCollection;
use Torchlight\Symfony\Client;
use Torchlight\Symfony\Configuration;

/**
 * Torchlight client that caches blocks using a PSR-6 cache.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Psr6CacheClient implements Client
{
    private Client $inner;
    private CacheItemPoolInterface $cache;
    private ?int $ttl;

    public function __construct(Client $inner, CacheItemPoolInterface $cache, ?int $ttl = null)
    {
        $this->inner = $inner;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function render(BlockCollection $blocks, Configuration $configuration): BlockCollection
    {
        $cacheKeyTemplate = '%s%s'.\json_encode($configuration->globalOptions(), \JSON_THROW_ON_ERROR);
        $cacheItems = $this->fetchFromCache($blocks, $configuration->defaultTheme(), $cacheKeyTemplate);
        $blocksToRender = $blocks->all();
        $blocksFromCache = new BlockCollection();

        /** @var array<string, CacheItemInterface> $cacheItems */
        foreach ($cacheItems as $item) {
            if ($item->isHit() && ($block = $item->get()) instanceof Block) {
                $blocksFromCache->add($block);

                unset($blocksToRender[$block->id()]);
            }
        }

        if (!\count($blocksToRender)) {
            // all fetched from cache
            return $blocksFromCache;
        }

        $renderedBlocks = $this->inner->render(new BlockCollection(...\array_values($blocksToRender)), $configuration);

        foreach ($renderedBlocks as $block) {
            $cacheItem = $cacheItems[self::cacheKey($block, $configuration->defaultTheme(), $cacheKeyTemplate)];
            $cacheItem->set($block);
            $cacheItem->expiresAfter($this->ttl);

            if ($cacheItem instanceof CacheItem && $this->cache instanceof TagAwareAdapterInterface) {
                // utilize Symfony's cache tags if available
                $cacheItem->tag('torchlight');
            }

            $this->cache->saveDeferred($cacheItem);
        }

        $this->cache->commit();

        return $blocksFromCache->merge($renderedBlocks);
    }

    private static function cacheKey(Block $block, string $defaultTheme, string $template): string
    {
        return \sprintf($template, $block->id(), $block->theme() ?? $defaultTheme);
    }

    /**
     * @return array<string, CacheItemInterface>
     */
    private function fetchFromCache(BlockCollection $blocks, string $defaultTheme, string $template): array
    {
        $items = $this->cache->getItems(
            \array_values(
                \array_map(
                    static fn(Block $block) => self::cacheKey($block, $defaultTheme, $template),
                    $blocks->all()
                )
            )
        );

        if (!\is_array($items)) {
            $items = \iterator_to_array($items);
        }

        return $items;
    }
}
