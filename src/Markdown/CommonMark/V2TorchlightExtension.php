<?php

namespace Torchlight\Symfony\Markdown\CommonMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentRenderedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Output\RenderedContent;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Torchlight\Symfony\Block;
use Torchlight\Symfony\BlockCollection;
use Torchlight\Symfony\Renderer;

/**
 * @author Aaron Francis <aaron@hammerstone.dev>
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class V2TorchlightExtension extends BaseTorchlightExtension implements ExtensionInterface, NodeRendererInterface
{
    private BlockCollection $pending;

    public function __construct(Renderer $renderer)
    {
        $this->pending = new BlockCollection();

        parent::__construct($renderer);
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addEventListener(DocumentRenderedEvent::class, [$this, 'onDocumentRendered'])
            ->addRenderer(FencedCode::class, $this, 10)
            ->addRenderer(IndentedCode::class, $this, 10)
        ;
    }

    /**
     * @param FencedCode|IndentedCode $node
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): ?string
    {
        $language = $node instanceof FencedCode ? $node->getInfo() : null;

        $this->pending->add($block = new Block($node->getLiteral(), $language));

        return $block;
    }

    public function onDocumentRendered(DocumentRenderedEvent $event): void
    {
        $content = $this->renderer
            ->renderBlocks($this->pending->flush())
            ->replacePendingBlocks($event->getOutput()->getContent())
        ;

        $event->replaceOutput(new RenderedContent($event->getOutput()->getDocument(), $content));
    }
}
