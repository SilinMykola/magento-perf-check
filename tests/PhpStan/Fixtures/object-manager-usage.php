<?php

declare(strict_types=1);

namespace ObjectManagerUsageFixture;

use Magento\Framework\App\ObjectManager;

final class Helper
{
    private object $logger;

    public function __construct(?object $logger = null)
    {
        $this->logger = $logger ?? ObjectManager::getInstance()->get(\stdClass::class);
    }

    public function run(): object
    {
        return ObjectManager::getInstance()->get(\stdClass::class);
    }

    public function __wakeup(): void
    {
        $this->logger = ObjectManager::getInstance()->get(\stdClass::class);
    }
}

final class WithoutFallback
{
    private object $logger;

    public function __construct()
    {
        $this->logger = ObjectManager::getInstance()->get(\stdClass::class);
    }
}

final class ItemFactory
{
    public function create(): object
    {
        return ObjectManager::getInstance()->get(\stdClass::class);
    }
}

final class IfFallbacks
{
    private object $a;
    private object $b;
    private array $processors;
    private ?object $cached = null;

    public function __construct(?object $a = null, ?object $b = null, array $processors = [], ?object $c = null, ?object $d = null)
    {
        if (!isset($a)) {
            $a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->a = $a;
        if ($b === null) {
            $b = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->b = $b;
        if (empty($processors)) {
            $this->processors = ['one' => ObjectManager::getInstance()->get(\stdClass::class)];
        }
        if (is_null($d)) {
            $d = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($c === null || !$d) {
            $c = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($this->cached === null) {
            $this->cached = ObjectManager::getInstance()->get(\stdClass::class);
        }
    }
}

final class LocalObjectManager
{
    private object $a;
    private object $b;

    public function __construct(?object $a = null, ?object $b = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager->get(\stdClass::class);
        $this->b = $b ?? $objectManager->get(\stdClass::class);
    }
}

final class LocalObjectManagerUsedElsewhere
{
    private object $a;
    private object $b;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager->get(\stdClass::class);
        $this->b = $objectManager->create(\stdClass::class);
    }
}

final class IfFallbackEdges
{
    private object $a;
    private object $b;
    private \Closure $later;

    public function __construct(?object $a = null, ?object $b = null, ?object $c = null)
    {
        if (null === $a) {
            $a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$b) {
            $b = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!isset($a, $c)) {
            $c = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$c) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
            $this->warmUp();
        }
        $this->later = function (?object $a = null): object {
            if (!$a) {
                $a = ObjectManager::getInstance()->get(\stdClass::class);
            }

            return $a;
        };
        $this->b = $b;
    }

    private function warmUp(): void
    {
    }
}

final class LocalObjectManagerHandedOn
{
    private object $a;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager;
    }
}

final class FallbackBlockWithWork
{
    private object $a;
    private object $b;
    private mixed $config;

    public function __construct(private object $scopeConfig, ?object $a = null, ?object $b = null)
    {
        $objectManager = ObjectManager::getInstance();
        if (!isset($a)) {
            $a = $objectManager->get(\stdClass::class);
        }
        $this->a = $a;
        if (!$b) {
            $this->config = $this->scopeConfig->getValue('x');
            $b = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->b = $b;
    }
}

final class LocalObjectManagerCaptured
{
    private object $a;
    private \Closure $make;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager->get(\stdClass::class);
        $this->make = fn (): object => $objectManager->create(\stdClass::class);
    }
}

final class LocalObjectManagerKeptInBlock
{
    private object $a;
    private object $b;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        if (!isset($a)) {
            $this->b = $objectManager;
        }
        $this->a = $a ?? $objectManager->get(\stdClass::class);
    }
}

final class FallbackBlockWithHiddenWork
{
    private object $a;
    private object $b;
    private mixed $config;
    private array $registry = [];

    public function __construct(?object $a = null, ?object $b = null)
    {
        if (!$a) {
            $this->config = ObjectManager::getInstance()->get(\stdClass::class)->getValue('x');
            $a = ObjectManager::getInstance()->create(\stdClass::class);
        }
        $this->a = $a;
        if (!$b) {
            $this->registry[$this->key()] = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->b = $b;
    }

    private function key(): string
    {
        return 'b';
    }
}

final class LazyGetters
{
    private ?object $a = null;
    private ?object $b = null;
    private ?object $c = null;
    private ?object $d = null;
    private ?object $other = null;

    /** @var list<object> */
    private array $list = [];

    public function allowed(): void
    {
        if ($this->a === null) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$this->b) {
            $this->b = ObjectManager::getInstance()->create(\stdClass::class);
            // phpcs:enable
        }
        if (!$this->c instanceof \stdClass) {
            $this->c = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!is_object($this->d)) {
            $this->d = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (empty($this->list)) {
            $this->list = [ObjectManager::getInstance()->get(\stdClass::class)];
        }
    }

    public function reported(?object $local): ?object
    {
        if ($this->a === null) {
            $this->other = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($this->a === null) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class)->get('x');
        }
        if ($this->a === null) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
            $this->warmUp();
        }
        if ($local === null) {
            $local = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$this->b instanceof \stdClass) {
            return ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!is_object($this->a, $this->b)) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!is_string($this->a)) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($this->list[0]->a === null) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($this->a === null) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($this->b === null) {
            $this->b = ObjectManager::getInstance()->get(\stdClass::class, [
                'inner' => ObjectManager::getInstance()->get(\stdClass::class),
            ]);
        }

        return $local;
    }

    private function warmUp(): void
    {
    }
}

final class FallbackOnLocal
{
    public function __construct()
    {
        $local = null;
        if ($local === null) {
            $local = ObjectManager::getInstance()->get(\stdClass::class);
        }
    }
}

final class FallbackNewForms
{
    public function __construct(?object $a = null, ?object $b = null, ?object $c = null)
    {
        if (!is_object($a)) {
            $a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$b instanceof \stdClass) {
            $b = ObjectManager::getInstance()->get(\stdClass::class);
            // phpcs:enable
        }
        if ($c === null) {
            $c = ObjectManager::getInstance()->get(\stdClass::class, ['inner' => ObjectManager::getInstance()->get(\stdClass::class)]);
        }
    }
}

final class DataLazyGetters extends \Magento\Framework\DataObject
{
    public function allowed(): object
    {
        $model = $this->getData('style_view_model');
        if (!$model) {
            $model = ObjectManager::getInstance()->get(\stdClass::class);
            $this->setData('style_view_model', $model);
        }
        if (!$this->getData('other')) {
            $this->setData('other', ObjectManager::getInstance()->get(\stdClass::class));
        }

        return $model;
    }

    public function reported(string $key): ?object
    {
        $model = $this->getData('first');
        if (!$model) {
            $model = ObjectManager::getInstance()->get(\stdClass::class);
            $this->setData('second', $model);
        }
        if (!$this->getData('third')) {
            $model = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$this->getData($key)) {
            $this->setData($key, ObjectManager::getInstance()->get(\stdClass::class));
        }
        if (!$this->getData('fourth')) {
            $this->setData('fourth', ObjectManager::getInstance()->get(\stdClass::class));
            $this->warmUp();
        }
        if (!$model) {
            $other = ObjectManager::getInstance()->get(\stdClass::class);
            $this->setData('first', $model);
        }
        if (!$this->other('fifth')) {
            $this->setData('fifth', ObjectManager::getInstance()->get(\stdClass::class));
        }

        return $model;
    }

    public function alsoReported(\Magento\Framework\DataObject $row): ?object
    {
        $twoKeys = $this->getData('a');
        $twoKeys = $this->getData('b');
        if (!$twoKeys) {
            $twoKeys = ObjectManager::getInstance()->get(\stdClass::class);
            $this->setData('a', $twoKeys);
        }
        if (!$this->getData('list')) {
            $this->setData('list', [ObjectManager::getInstance()->get(\stdClass::class), $this->other('x')]);
        }
        $list = $this->getData('list2');
        if (!$list) {
            $list = [ObjectManager::getInstance()->get(\stdClass::class), $this->other('x')];
            $this->setData('list2', $list);
        }
        $kept = $this->getData('kept');
        if (!$kept) {
            $kept = ObjectManager::getInstance()->get(\stdClass::class);
            $this->setData('kept', $twoKeys);
        }
        $busy = $this->getData('busy');
        if (!$busy) {
            $busy = ObjectManager::getInstance()->get(\stdClass::class);
            $this->warmUp();
            $this->setData('busy', $busy);
        }
        $item = $this->getData('items', 0);
        if (!$item) {
            $item = ObjectManager::getInstance()->get(\stdClass::class);
            $this->setData('items', $item);
        }
        $foreign = $row->getData('foreign');
        if (!$foreign) {
            $foreign = ObjectManager::getInstance()->get(\stdClass::class);
            $row->setData('foreign', $foreign);
        }

        return $item;
    }

    private function other(string $key): ?object
    {
        return null;
    }

    private function warmUp(): void
    {
    }
}

final class TernaryFallbacks
{
    private object $config;
    private object $other;
    private object $third;
    private object $fourth;
    private object $wrong;
    private object $fifth;

    public function __construct(?object $config = null, ?object $other = null, ?object $third = null, ?object $fourth = null, ?object $wrong = null, ?object $fifth = null)
    {
        $this->config = $config !== null ? $config : ObjectManager::getInstance()->get(\stdClass::class);
        $this->other = $other === null ? ObjectManager::getInstance()->get(\stdClass::class) : $other;
        $this->third = isset($third) ? $third : ObjectManager::getInstance()->get(\stdClass::class);
        $this->fourth = $fourth ? $fourth : ObjectManager::getInstance()->get(\stdClass::class);
        $this->fifth = !empty($fifth) ? $fifth : ObjectManager::getInstance()->get(\stdClass::class);
        $this->wrong = $wrong !== null ? $config : ObjectManager::getInstance()->get(\stdClass::class); // reported: tests another value
        $this->wrong = $wrong !== null ? ObjectManager::getInstance()->get(\stdClass::class) : $wrong; // reported: runs when the value is there
    }

    public function getter(?object $given): object
    {
        return $given !== null ? $given : ObjectManager::getInstance()->get(\stdClass::class); // reported: outside the constructor
    }
}

final class LazyGetterThroughLocal
{
    private ?object $config = null;
    private ?object $other = null;

    public function allowed(): object
    {
        if ($this->config === null) {
            /** @var \stdClass $config */
            $config = ObjectManager::getInstance()->get(\stdClass::class);

            $this->config = $config;
        }

        return $this->config;
    }

    public function reported(): ?object
    {
        if ($this->other === null) {
            $other = ObjectManager::getInstance()->get(\stdClass::class); // reported: stored in another property
            $this->config = $other;
        }
        if ($this->other === null) {
            $other = ObjectManager::getInstance()->get(\stdClass::class); // reported: the block does more
            $other->ready = true;
            $this->other = $other;
        }
        if ($this->other === null) {
            $other = ObjectManager::getInstance()->get(\stdClass::class); // reported: another variable is stored
            $this->other = $this->config;
        }

        return $this->other;
    }
}

final class FallbackScope
{
    private ?object $a = null;
    private ?object $b = null;
    private ?object $c = null;
    /** @var ?list<mixed> */
    private ?array $list = null;
    /** @var callable */
    private $later;

    public function __construct(?object $param = null)
    {
        $param ??= ObjectManager::getInstance()->get(\stdClass::class);
        $this->later = static fn (?object $x): object => $x ?: ObjectManager::getInstance()->get(\stdClass::class); // reported: a closure runs later
    }

    public function keptGetters(): void
    {
        $this->a = $this->a ?? ObjectManager::getInstance()->get(\stdClass::class);
        $this->b = $this->b ?: ObjectManager::getInstance()->get(\stdClass::class);
        $this->c ??= ObjectManager::getInstance()->get(\stdClass::class);
    }

    public function notKept(?object $given): object
    {
        $this->a = $this->b ?? ObjectManager::getInstance()->get(\stdClass::class); // reported: another property is tested
        $local = $given ?: ObjectManager::getInstance()->get(\stdClass::class); // reported: not kept
        $this->c ??= ObjectManager::getInstance()->get(\stdClass::class)->create(); // reported: more than the dependency
        $this->list ??= [ObjectManager::getInstance()->get(\stdClass::class), $given]; // reported: more than the dependency

        return $this->a ?? ObjectManager::getInstance()->get(\stdClass::class); // reported: not kept
    }
}

final class FallbackScopeEdges
{
    private static ?object $shared = null;
    private static ?object $other = null;
    private static ?object $third = null;
    private ?object $a = null;
    /** @var ?list<object> */
    private ?array $list = null;
    /** @var array<string, object> */
    private array $instances = [];

    public function __construct(?object $x = null, ?object $y = null)
    {
        $objectManager = ObjectManager::getInstance();
        $y ??= $objectManager->get(\stdClass::class);
        $this->instances['x'] = new \ArrayObject([$x ?: ObjectManager::getInstance()->get(\stdClass::class), $y]);
    }

    public function allowed(): object
    {
        self::$shared = self::$shared ?? ObjectManager::getInstance()->get(\stdClass::class);
        static::$other ??= ObjectManager::getInstance()->get(\stdClass::class);
        if (self::$third === null) {
            self::$third = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->list ??= [ObjectManager::getInstance()->get(\stdClass::class), ObjectManager::getInstance()->get(\stdClass::class)];

        return $this->a ??= ObjectManager::getInstance()->get(\stdClass::class);
    }

    public function reported(?object $local, string $type): void
    {
        $local = $local ?? ObjectManager::getInstance()->get(\stdClass::class); // reported: a local variable is not kept
        $this->a = $this->a ? $this->a : ObjectManager::getInstance()->get(\stdClass::class); // reported: full ternary outside the constructor
        $this->instances[$type] ??= ObjectManager::getInstance()->get($type); // reported: a service locator by key
        $later = function (): void {
            $this->a = $this->a ?: ObjectManager::getInstance()->get(\stdClass::class); // reported: in a closure
        };
    }
}
