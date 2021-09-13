<?php

namespace Torchlight\Symfony\DependencyInjection;

use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Torchlight\Symfony\BlockCollection;
use Torchlight\Symfony\Client\Psr6CacheClient;
use Torchlight\Symfony\Client\SymfonyClient;
use Torchlight\Symfony\Configuration;
use Torchlight\Symfony\EventSubscriber\RenderTorchlightBlocksSubscriber;
use Torchlight\Symfony\Markdown\CommonMark\TorchlightExtension as CommonMarkTorchlightExtension;
use Torchlight\Symfony\Renderer;
use Torchlight\Symfony\Twig\Markdown\LeagueMarkdownFactory;
use Torchlight\Symfony\Twig\TorchlightExtension as TorchlightTwigExtension;
use Torchlight\Symfony\Twig\TorchlightRuntime as TorchlightTwigRuntime;
use Twig\Extra\Markdown\LeagueMarkdown;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TorchlightSymfonyExtension extends ConfigurableExtension implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('torchlight');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_key')
                    ->defaultValue('%env(TORCHLIGHT_API_KEY)%')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('global_options')
                    ->addDefaultsIfNotSet()
                    ->info('Torchlight default options')
                    ->children()
                        ->scalarNode('theme')
                            ->defaultValue('github-light')
                            ->cannotBeEmpty()
                            ->info('The default VS Code theme to use (see https://torchlight.dev/docs/themes)')
                        ->end()
                        ->booleanNode('lineNumbers')
                            ->defaultNull()
                            ->info('Turn line numbers on or off (see https://torchlight.dev/docs/options/line-numbers)')
                        ->end()
                        ->scalarNode('lineNumbersStyle')
                            ->defaultNull()
                            ->info('The CSS style to apply to line numbers (see https://torchlight.dev/docs/options/line-numbers#changing-the-style)')
                        ->end()
                        ->booleanNode('diffIndicators')
                            ->defaultNull()
                            ->info('Turn on diff indicators (see https://torchlight.dev/docs/options/diffs)')
                        ->end()
                        ->booleanNode('diffIndicatorsInPlaceOfLineNumbers')
                            ->defaultNull()
                            ->info('Turn on diff indicators (see https://torchlight.dev/docs/options/diffs#without-line-numbers)')
                        ->end()
                        ->scalarNode('summaryCollapsedIndicator')
                            ->defaultNull()
                            ->info('The text to show when a range is collapsed (see https://torchlight.dev/docs/options/summaries)')
                        ->end()
                        ->booleanNode('torchlightAnnotations')
                            ->defaultNull()
                            ->info('Disable Torchlight annotation processing altogether (see https://torchlight.dev/docs/options/annotations)')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('http_client')
                    ->defaultValue('http_client')
                    ->info('HttpClient service to use')
                ->end()
                ->arrayNode('twig')
                    ->{\class_exists(TwigBundle::class) ? 'canBeDisabled' : 'canBeEnabled'}()
                    ->children()
                        ->enumNode('markdown')
                            ->values(['commonmark'])
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->{\interface_exists(AdapterInterface::class) ? 'canBeDisabled' : 'canBeEnabled'}()
                    ->children()
                        ->scalarNode('service')
                            ->cannotBeEmpty()
                            ->defaultValue('cache.app')
                            ->info('Cache pool service to use')
                        ->end()
                        ->integerNode('ttl')
                            ->defaultNull()
                            ->info('Time to live in seconds (null for pool default)')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }

    public function getAlias(): string
    {
        return 'torchlight';
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->register('torchlight.configuration', Configuration::class)
            ->setArguments([
                $mergedConfig['api_key'],
                $mergedConfig['global_options'],
            ])
        ;

        $container->register('torchlight.client', SymfonyClient::class)
            ->addArgument($mergedConfig['http_client'] ? new Reference('http_client') : null)
        ;

        $container->register(Renderer::class)
            ->setArguments([new Reference('torchlight.client'), new Reference('torchlight.configuration')])
        ;

        if ($mergedConfig['cache']['enabled']) {
            $container->register('torchlight.cache_client', Psr6CacheClient::class)
                ->setArguments([
                    new Reference('torchlight.cache_client.inner'),
                    new Reference($mergedConfig['cache']['service']),
                    $mergedConfig['cache']['ttl'],
                ])
                ->setDecoratedService('torchlight.client')
            ;
        }

        if ($mergedConfig['twig']['enabled']) {
            $container->register('torchlight.event_subscriber', RenderTorchlightBlocksSubscriber::class)
                ->addTag('kernel.event_subscriber')
                ->addTag('container.service_subscriber')
                ->addTag('container.service_subscriber', ['key' => BlockCollection::class, 'id' => 'torchlight.pending_blocks'])
            ;

            $container->register('torchlight.pending_blocks', BlockCollection::class)
                ->addTag('kernel.reset', ['method' => 'flush'])
            ;

            $container->register('torchlight.twig_extension', TorchlightTwigExtension::class)
                ->addTag('twig.extension')
            ;

            $container->register('torchlight.twig_runtime', TorchlightTwigRuntime::class)
                ->setArguments([new Reference(Renderer::class), new Reference('torchlight.pending_blocks')])
                ->addTag('twig.runtime')
            ;

            if ('commonmark' === $mergedConfig['twig']['markdown']) {
                // todo error checking if classes don't exist...
                $container->register('torchlight.commonmark_extension', CommonMarkTorchlightExtension::class)
                    ->addArgument(new Reference(Renderer::class))
                ;

                $container->register('torchlight.commonmark_factory', LeagueMarkdownFactory::class)
                    ->addArgument(new Reference('torchlight.commonmark_extension'))
                ;

                $container->register('twig.markdown.default', LeagueMarkdown::class)
                    ->setFactory(new Reference('torchlight.commonmark_factory'))
                ;
            }
        }
    }
}
