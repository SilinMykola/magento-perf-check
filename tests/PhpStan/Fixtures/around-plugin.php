<?php

declare(strict_types=1);

namespace Acme\Promo\Plugin {
    final class PricePlugin
    {
        private bool $enabled = true;

        public function aroundGetPrice(object $subject, callable $proceed, int $qty): int
        {
            $result = $proceed($qty);

            return $result * 2;
        }

        public function aroundGetFinalPrice(object $subject, callable $proceed, ...$args): mixed
        {
            return $proceed(...$args);
        }

        public function aroundConditional(object $subject, callable $proceed, int $qty): int
        {
            if ($this->enabled) {
                return $proceed($qty);
            }

            return 0;
        }

        public function aroundChangedArgument(object $subject, callable $proceed, int $qty): int
        {
            return $proceed($qty + 1);
        }

        public function aroundGuarded(object $subject, callable $proceed, int $qty): int
        {
            try {
                return $proceed($qty);
            } catch (\RuntimeException $e) {
                return 0;
            }
        }

        public function aroundTwice(object $subject, callable $proceed, int $qty): int
        {
            $proceed($qty);

            return $proceed($qty);
        }

        public function aroundInClosure(object $subject, callable $proceed, int $qty): int
        {
            $call = fn () => $proceed($qty);

            return $call();
        }

        private function aroundPrivate(object $subject, callable $proceed): mixed
        {
            return $proceed();
        }
    }
}

namespace Acme\Promo\Model {
    final class PriceModifier
    {
        public function aroundGetPrice(object $subject, callable $proceed): mixed
        {
            return $proceed();
        }
    }

    final class NotAPluginAtAll
    {
        public function aroundSomething(object $subject, callable $proceed): mixed
        {
            return $proceed();
        }
    }
}
