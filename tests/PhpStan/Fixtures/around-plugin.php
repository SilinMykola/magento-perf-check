<?php

declare(strict_types=1);

namespace Acme\Promo\Plugin {
    final class PricePlugin
    {
        private bool $enabled = true;
        private ?int $cached = null;

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

        public function aroundEarlyReturn(object $subject, callable $proceed, int $qty): int
        {
            if (!$this->enabled) {
                return 0;
            }
            $result = $proceed($qty);

            return $result;
        }

        public function aroundTernary(object $subject, callable $proceed, int $qty): int
        {
            return $this->enabled ? $proceed($qty) : 0;
        }

        public function aroundShortCircuit(object $subject, callable $proceed, int $qty): bool
        {
            return $this->enabled && $proceed($qty);
        }

        public function aroundCoalesce(object $subject, callable $proceed, int $qty): mixed
        {
            return $this->cached ?? $proceed($qty);
        }

        public function aroundInCondition(object $subject, callable $proceed, int $qty): int
        {
            return $proceed($qty) > 0 ? 1 : 0;
        }

        public function aroundReassigned(object $subject, callable $proceed, int $qty): int
        {
            $qty = max($qty, 1);

            return $proceed($qty);
        }

        public function aroundResultIntoArgument(object $subject, callable $proceed, int $qty): int
        {
            $qty = $proceed($qty);

            return $qty * 2;
        }

        public function aroundElementChanged(object $subject, callable $proceed, array $parts): mixed
        {
            $parts[0] = 'other';

            return $proceed($parts);
        }

        public function aroundStateReadBefore(object $subject, callable $proceed): mixed
        {
            $isNew = $subject->isObjectNew();
            $result = $proceed();

            return $isNew ? $result : null;
        }

        public function aroundListBuiltBefore(object $subject, callable $proceed): mixed
        {
            $ids[] = $subject->getId();
            $proceed();

            return $ids;
        }

        public function aroundVariableUnusedAfter(object $subject, callable $proceed): mixed
        {
            $id = $subject->getId();
            $subject->log($id);

            return $proceed();
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
