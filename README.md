# Symfony Torchlight Bundle

A [Torchlight](https://torchlight.dev/) syntax highlighting extension for the [Symfony](http://symfony.com/) framework.

Torchlight is a VS Code-compatible syntax highlighter that requires no JavaScript, supports every language,
every VS Code theme, line highlighting, git diffing, and more.

## Installation

1. Install the bundle:
    ```bash
    $ composer require torchlight/torchlight-symfony
    ```
2. Add `TORCHLIGHT_API_KEY` to your `.env` file.
3. If not using Flex, enable `TorchlightSymfonyBundle`

## General Usage

`Torchlight\Symfony\Renderer` is automatically wired up and available for
autoconfiguration. Inject it in any service/controller:

```php
use Torchlight\Symfony\Renderer;

public function someController(Renderer $torchlight)
{
    $block = $torchlight->render('some php code...', 'php'); // instance of \Torchlight\Symfony\Block
    
    $block->wrapped(); // highlighted code wrapped in code/pre tags
    $block->highlighted(); // highlighted code not wrapped in code/pre tags
    
    // customize the theme
    $block = $torchlight->render('some php code...', 'php', 'github-dark');
}
```

## Twig Extension

If [Twig](https://twig.symfony.com/) is installed in your app, a twig extension is automatically provided with
a `torchlight` filter:

```twig
{% apply torchlight('php') %}
some php code...
{% endapply %}

{% apply torchlight('php') %}
some more php code...
{% endapply %}
```

The above will highlight the two code blocks. Within the context of a request, for performance, the blocks are all
gathered and sent to the Torchlight API once before sending the response to the browser. If not in the context
of a request (ie console command), they are rendered inline.

If for some reason, you need to force rendering inline within the context of a request, use the `torchlight_inline`
filter:

```twig
{% apply torchlight_inline('php') %}
some php code...
{% endapply %}
```

## Cache

If caching is enabled in your app, for performance, rendered blocks are cached. They are tagged with `torchlight` to
ease clearing just these blocks. See [the configuration section](#configuration) for customizing the TTL and pool.

### CommonMark Extension

If using `league/commonmark`, `twig/markdown-extra` and `twig/extra-bundle` you can enable the Torchlight CommonMark
extension:

```yaml
# config/packages/torchlight.yaml
torchlight:
   twig:
      markdown: commonmark
```

When using the `markdown_to_html` filter provided by `twig/markdown-extra`, code blocks will be highlighted by
Torchlight:

```twig
{% apply markdown_to_html %}
# This is markdown

~~~php
this php code will be highlighted (replace ~~~ with ```)
~~~
{% endapply %}
```

## Configuration

Full bundle configuration:

```yaml
torchlight:
    api_key:              '%env(TORCHLIGHT_API_KEY)%'

    # Torchlight default options
    global_options:

        # The default VS Code theme to use (see https://torchlight.dev/docs/themes)
        theme:                github-light

        # Turn line numbers on or off (see https://torchlight.dev/docs/options/line-numbers)
        lineNumbers:          null

        # The CSS style to apply to line numbers (see https://torchlight.dev/docs/options/line-numbers#changing-the-style)
        lineNumbersStyle:     null

        # Turn on diff indicators (see https://torchlight.dev/docs/options/diffs)
        diffIndicators:       null

        # Turn on diff indicators (see https://torchlight.dev/docs/options/diffs#without-line-numbers)
        diffIndicatorsInPlaceOfLineNumbers: null

        # The text to show when a range is collapsed (see https://torchlight.dev/docs/options/summaries)
        summaryCollapsedIndicator: null

        # Disable Torchlight annotation processing altogether (see https://torchlight.dev/docs/options/annotations)
        torchlightAnnotations: null

    # HttpClient service to use
    http_client:          http_client

    twig:
        enabled:              true
        markdown:             null # One of "commonmark"
    cache:
        enabled:              true

        # Cache pool service to use
        service:              cache.app

        # Time to live in seconds (null for pool default)
        ttl:                  null
```

## Standalone Usage

```php
use Torchlight\Symfony\Block;
use Torchlight\Symfony\BlockCollection;
use Torchlight\Symfony\Renderer as Torchlight;

// auto-detect http client to use based on your current dependencies (symfony/http-client only right now but guzzle to come?)
$torchlight = Torchlight::create('your-api-key');

// customize default options
$torchlight = Torchlight::create('your-api-key', ['theme' => 'github-dark', 'lineNumbers' => false]);

// simple render
$block = $torchlight->render('some php code', 'php'); // see \Torchlight\Symfony\Block for all methods
$block->highlighted(); // highlighted code not wrapped in code/pre tags
$block->wrapped(); // highlighted code wrapped in code/pre tags
(string) $block; // equivalent to above

// deferred render
$collection = new BlockCollection();
$collection->add(new Block('some php code', 'php'));
$collection->add(new Block('some more php code', 'php'));

// just one API call
$rendered = $torchlight->renderBlocks($collection); // BlockCollection with all blocks rendered

foreach ($rendered as $block) {
    $block->wrapped(); // highlighted code wrapped in code/pre tags
}
```
