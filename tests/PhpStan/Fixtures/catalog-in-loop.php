<?php

declare(strict_types=1);

namespace Acme\CatalogLoops;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\Source\Table;

final class Urls
{
    /** @param list<Product> $products */
    public function reported(array $products, Collection $collection, Url $url): void
    {
        foreach ($products as $product) {
            $product->getProductUrl();
            $product->getUrlInStore();
            $product->getUrlModel()->getUrl($product);
            $url->getProductUrl($product);
            $url->getUrlInStore($product);
        }
        foreach ($collection as $item) {
            $item->getProductUrl();
        }
        foreach ($collection->getItems() as $item) {
            $item->getUrlInStore();
        }
    }

    public function rewritesAdded(Collection $collection): void
    {
        $collection->addUrlRewrite();
        foreach ($collection as $item) {
            $item->getProductUrl();
        }
    }

    /** @param list<Product> $products */
    public function notReported(array $products, Product $single, \Acme\CatalogLoops\Block $block): void
    {
        $single->getProductUrl();
        foreach ($products as $product) {
            $block->getProductUrl($product);
            $block->getUrl();
        }
    }
}

final class Block
{
    public function getProductUrl(Product $product): string
    {
        return (string) $product->getSku();
    }

    public function getUrl(): string
    {
        return '';
    }

    public function getAttributeText(string $code): string
    {
        return $code;
    }
}

final class Texts
{
    /** @param list<Product> $products */
    public function reported(array $products, Collection $collection, Table $table): void
    {
        foreach ($products as $product) {
            $product->getAttributeText('color');
            $table->getOptionText(1);
        }
        foreach ($collection as $item) {
            $item->getAttributeText('color');
        }
    }

    /** @param list<Product> $products */
    public function notReported(array $products, Product $single, Boolean $boolean, Block $block): void
    {
        $single->getAttributeText('color');
        foreach ($products as $product) {
            $boolean->getOptionText(1);
            $block->getAttributeText('color');
            unset($product);
        }
    }
}

final class Attributes
{
    public function select(Collection $collection, Other $other, string $name): void
    {
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect(['name', 'price']);
        $collection->addAttributeToSelect($name);
        $other->addAttributeToSelect('*');
    }
}

final class Other
{
    public function addAttributeToSelect(string $attribute): void
    {
    }
}

final class InClosure
{
    /** @return list<string> */
    public function rewritesAddedBeforeTheCallback(Collection $collection): array
    {
        $collection->addUrlRewrite();

        return array_map(function (Product $product): string {
            return $product->getProductUrl();
        }, $collection->getItems());
    }

    public function anotherTypeInTheLoopVariable(Collection $collection): void
    {
        foreach ($collection as $item) {
            $item = new Block();
            $item->getAttributeText('color');
            $item->getProductUrl(new Product());
        }
    }
}

final class ProductInterfaces
{
    /** @param list<\Magento\Catalog\Api\Data\ProductInterface> $products */
    public function reported(array $products): void
    {
        foreach ($products as $product) {
            $product->getProductUrl();
            $product->getAttributeText('color');
        }
    }
}
