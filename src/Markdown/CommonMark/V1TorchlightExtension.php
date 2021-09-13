<?php

namespace Torchlight\Symfony\Markdown\CommonMark;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use Torchlight\Symfony\Block;
use Torchlight\Symfony\BlockCollection;

/**
 * @author Aaron Francis <aaron@hammerstone.dev>
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class V1TorchlightExtension extends BaseTorchlightExtension implements ExtensionInterface, BlockRendererInterface
{
    private BlockCollection $renderedBlocks;

    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $environment
            ->addEventListener(DocumentParsedEvent::class, [$this, 'onDocumentParsed'])
            ->addBlockRenderer(FencedCode::class, $this, 10)
            ->addBlockRenderer(IndentedCode::class, $this, 10)
        ;
    }

    public function onDocumentParsed(DocumentParsedEvent $event): void
    {
        $walker = $event->getDocument()->walker();
        $blocksToRender = new BlockCollection();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if (($node instanceof FencedCode || $node instanceof IndentedCode) && $event->isEntering()) {
                $blocksToRender->add(self::createBlockFrom($node));
            }
        }

        $this->renderedBlocks = $this->renderer->renderBlocks($blocksToRender);
    }

    /**
     * @param FencedCode|IndentedCode $block
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, bool $inTightList = false): ?string
    {
        return $this->renderedBlocks->get(self::createBlockFrom($block)->id());
    }

    /**
     * @param FencedCode|IndentedCode $block
     */
    private static function createBlockFrom(AbstractBlock $block): Block
    {
        return new Block($block->getStringContent(), $block instanceof FencedCode ? $block->getInfo() : null);
    }
}
