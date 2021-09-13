<?php

namespace Torchlight\Symfony;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Block
{
    private string $id;
    private string $code;
    private ?string $language;
    private ?string $theme;
    private ?string $wrapped;
    private ?string $highlighted;
    private ?string $classes;
    private ?string $styles;

    /**
     * @param string      $code     The code to highlight
     * @param string|null $language The code language
     * @param string|null $theme    The VS Code theme
     */
    public function __construct(string $code, ?string $language = null, ?string $theme = null)
    {
        $this->code = $code;
        $this->language = $language;
        $this->theme = $theme;
        $this->id = \sprintf('<torchlight:%s/>', \md5($code.$language.$theme));
    }

    /**
     * @return string If un-rendered: a unique html placeholder
     *                If rendered: the rendered html (wrapped in pre/code tags)
     */
    public function __toString(): string
    {
        return $this->isRendered() ? $this->wrapped : $this->id;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function language(): ?string
    {
        return $this->language;
    }

    public function theme(): ?string
    {
        return $this->theme;
    }

    /**
     * @return string The rendered html (wrapped in pre/code tags)
     */
    public function wrapped(): string
    {
        return $this->ensureRendered()->wrapped;
    }

    /**
     * @return string The rendered html (not wrapped in pre/code tags)
     */
    public function highlighted(): string
    {
        return $this->ensureRendered()->highlighted;
    }

    /**
     * @return string Classes that should be applied to the code tag
     */
    public function classes(): string
    {
        return $this->ensureRendered()->classes;
    }

    /**
     * @return string Styles that should be applied to the code tag
     */
    public function styles(): string
    {
        return $this->ensureRendered()->styles;
    }

    /**
     * @return bool Whether this block has been rendered
     */
    public function isRendered(): bool
    {
        return isset($this->wrapped);
    }

    public function render(string $html, string $highlighted, string $classes, string $styles): self
    {
        $this->wrapped = $html;
        $this->highlighted = $highlighted;
        $this->classes = $classes;
        $this->styles = $styles;

        return $this;
    }

    private function ensureRendered(): self
    {
        if (!$this->isRendered()) {
            throw new \LogicException('This block is not yet rendered.');
        }

        return $this;
    }
}
