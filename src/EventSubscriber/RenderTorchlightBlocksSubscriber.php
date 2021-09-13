<?php

namespace Torchlight\Symfony\EventSubscriber;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Torchlight\Symfony\BlockCollection;
use Torchlight\Symfony\Renderer;

class RenderTorchlightBlocksSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ('html' !== $request->getPreferredFormat()) {
            return;
        }

        if (!($blocks = $this->container->get(BlockCollection::class))->count()) {
            return;
        }

        if (!\is_string($content = $response->getContent())) {
            return;
        }

        $response->setContent(
            $this->container->get(Renderer::class)->renderBlocks($blocks->flush())->replacePendingBlocks($content)
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => 'onKernelResponse',
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            Renderer::class,
            BlockCollection::class,
        ];
    }
}
