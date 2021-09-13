<?php

namespace Torchlight\Symfony;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class BlockCollection implements \Countable, \IteratorAggregate
{
    /** @var array<string, Block> */
    private array $blocks = [];

    public function __construct(Block ...$blocks)
    {
        $this->add(...$blocks);
    }

    public function add(Block ...$blocks): self
    {
        foreach ($blocks as $block) {
            $this->blocks[$block->id()] = $block;
        }

        return $this;
    }

    public function get(string $id): Block
    {
        if (!isset($this->blocks[$id])) {
            throw new \InvalidArgumentException('Block not found.');
        }

        return $this->blocks[$id];
    }

    /**
     * @return array<string, Block>
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * @return \Traversable<string, Block>
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->blocks as $id => $block) {
            yield $id => $block;
        }
    }

    public function count(): int
    {
        return \count($this->blocks);
    }

    public function merge(self $other): self
    {
        $this->blocks = \array_merge($this->blocks, $other->blocks);

        return $this;
    }

    public function replacePendingBlocks(string $content): string
    {
        return \str_replace(
            \array_map(static fn(Block $block) => $block->id(), $this->blocks),
            \array_map(static fn(Block $block) => $block->wrapped(), $this->blocks),
            $content
        );
    }

    public function flush(): self
    {
        $clone = clone $this;

        $this->blocks = [];

        return $clone;
    }
}
