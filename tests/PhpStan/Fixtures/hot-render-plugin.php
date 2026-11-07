<?php

declare(strict_types=1);

namespace Acme\Render;

// Configured on AbstractBlock in etc/frontend/di.xml.
final class OnBlock
{
    public function afterToHtml(object $subject, string $result): string
    {
        return $result;
    }

    public function beforeGetChildHtml(object $subject): void
    {
    }

    public function afterGetCacheKeyInfo(object $subject, array $result): array
    {
        return $result;
    }

    private function afterToHtmlHelper(): void
    {
    }
}

// Configured on Template in etc/di.xml.
final class OnTemplate
{
    /** @return mixed */
    public function aroundFetchView(object $subject, callable $proceed, string $file)
    {
        return $proceed($file);
    }

    public function aroundGetTemplate(object $subject, callable $proceed): string
    {
        return $proceed();
    }
}

// Configured on Layout in etc/di.xml.
final class OnLayout
{
    public function beforeRenderElement(object $subject, string $name): void
    {
    }

    public function afterGetOutput(object $subject, string $result): string
    {
        return $result;
    }
}

// Configured on a specific block.
final class OnSpecificBlock
{
    public function afterToHtml(object $subject, string $result): string
    {
        return $result;
    }
}

// Configured on AbstractBlock only in etc/adminhtml/di.xml.
final class InAdmin
{
    public function afterToHtml(object $subject, string $result): string
    {
        return $result;
    }
}

// Configured on AbstractBlock with disabled="true".
final class Disabled
{
    public function afterToHtml(object $subject, string $result): string
    {
        return $result;
    }
}

// Configured on AbstractBlock, but a private method is no plugin method.
final class PrivateMethod
{
    private function afterToHtml(string $result): string
    {
        return $result;
    }
}

// Configured on AbstractBlock, and disabled by name in another module.
final class DisabledByName
{
    public function afterToHtml(object $subject, string $result): string
    {
        return $result;
    }
}

// Not a plugin.
final class NotConfigured
{
    public function afterToHtml(string $result): string
    {
        return $result;
    }
}
