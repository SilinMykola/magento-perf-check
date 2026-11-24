<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\PhpStan;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\PhpStan\CollectionStateVisitor;

/**
 * The filter values the visitor hands to getFirstItem(). The rule tests cannot show these cases: PHPStan types a
 * property fetch stored from the filter call as it was there, not as it is where getFirstItem() is called.
 */
final class CollectionStateVisitorTest extends TestCase
{
    /**
     * @return array<string, array{string, string, int}>
     */
    public static function writes(): array
    {
        return [
            'property of another object' => ['$o->ids', '$o->ids = 5;', 0],
            'element of a property of another object' => ['$o->ids', '$o->ids[0] = 5;', 0],
            'property of another object in destructuring' => ['$o->ids', '[$o->ids] = [5];', 0],
            'property of another object as a foreach value' => ['$o->ids', 'foreach ([5] as $o->ids) {}', 0],
            'element keyed by the object' => ['$o->ids', '$map[$o->key] = 5;', 1],
            'element keyed by the object, through a property' => ['$o->ids', '$map->items[$o->key] = 5;', 1],
            'property of a property of $this' => ['$this->ids', '$this->other->ids = 5;', 1],
            'element of $this' => ['$this->ids', '$this[0] = 5;', 1],
        ];
    }

    #[DataProvider('writes')]
    public function testWriteThroughTheValueDropsTheFilter(string $value, string $write, int $values): void
    {
        $code = '<?php class C { function f($c, $o, $map) { $c->addIdFilter(' . $value . '); ' . $write . ' $c->getFirstItem(); } }';
        $statements = (new ParserFactory())->createForNewestSupportedVersion()->parse($code) ?? [];
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CollectionStateVisitor());
        $traverser->traverse($statements);

        $getFirstItem = (new NodeFinder())->findFirst($statements, static fn (Node $node): bool => $node instanceof Node\Expr\MethodCall
            && $node->name instanceof Node\Identifier && $node->name->toString() === 'getFirstItem');
        self::assertNotNull($getFirstItem);
        self::assertCount($values, (array) $getFirstItem->getAttribute(CollectionStateVisitor::UNIQUE_FILTER_VALUES));
    }
}
