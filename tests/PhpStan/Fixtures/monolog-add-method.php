<?php

declare(strict_types=1);

namespace MonologAddMethodFixture;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

class ShopLogger extends Logger
{
}

class ShopService
{
    public function __construct(private ShopLogger $logger, private ?ShopLogger $optional, private LoggerInterface $psr)
    {
    }

    public function run(): void
    {
        $this->logger->addError('failed');
        $this->logger->addWarning('missing in Monolog 2');
        $this->logger->err('failed');
        $this->optional?->addInfo('nullsafe');
        $this->optional->addInfo('nullable');
        // PSR-3 methods are fine.
        $this->logger->error('failed');
        $this->logger->warning('fine');
        // Psr\Log\LoggerInterface is LoggerProxy in Magento 2.4: the call failed before 2.4.4 already.
        $this->psr->addError('x');
    }
}

/** A logger that declares the method itself keeps it in Monolog 2. */
class OwnLogger extends Logger
{
    public function addError($message, array $context = []) { return parent::error($message, $context) ?? true; }
}

/** Not a Monolog logger at all, like the message manager. */
class Messages
{
    public function addError(string $message): void
    {
    }
}

class Other
{
    public function run(OwnLogger $own, Messages $messages, Logger $monolog): void
    {
        $own->addError('kept');
        $messages->addError('not a logger');
        $monolog->addInfo('reported');
    }
}

class ParentCaller extends Logger
{
    public function addError($message, array $context = []) { return true; }

    public function run(): void
    {
        parent::addError('the Monolog 1 method of the parent');
        self::addError('its own method');
        static::addInfo('the Monolog 1 method, through static');
        (function (): void { static::addInfo('in a closure'); })();
        parent::error('fine');
    }
}
