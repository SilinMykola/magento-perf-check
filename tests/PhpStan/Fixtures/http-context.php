<?php

declare(strict_types=1);

namespace Acme\Promo\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Quote\Api\Data\CartInterface;

final class HttpContextValues
{
    private ?int $customerId = null;

    public function __construct(private readonly Context $context, private readonly Session $session)
    {
    }

    public function reported(CustomerInterface $customer, CartInterface $cart, ?int $customerId, string $email): void
    {
        $this->context->setValue('a', $this->session->getCustomerId(), 0);
        $this->context->setValue('b', md5($customer->getEmail()), '');
        $this->context->setValue('c', $customer->getId(), 0);
        $this->context->setValue('d', $cart->getId(), 0);
        $this->context->setValue('e', $this->session->getId(), '');
        $this->context->setValue('f', time(), 0);
        $this->context->setValue('g', $customerId, 0);
        $this->context->setValue('h', $this->customerId, 0);
        $this->context->setValue('i', 'x' . $email, '');
        $this->context->setValue('j', (string) $this->session->getCustomerId(), '');
        $this->context->setValue('k', $this->session->getCustomerId() ?? 0, 0);
        $this->context->setValue('l', $this->session->getCustomerId() ?: 0, 0);
        $this->context->setValue('m', $customerId !== null ? $customerId : 0, 0);
        $this->context->setValue('n', md5(implode('|', [$customer->getId(), 1])), '');
        $this->context->setValue('o', sprintf('%d', $customer->getId()), '');
    }

    public function notReported(CustomerInterface $customer, \Acme\Promo\Plugin\Other $other, int $groupId): void
    {
        $this->context->setValue('a', $this->session->getCustomerGroupId(), 0);
        $this->context->setValue('b', $customer->getGroupId(), 0);
        $this->context->setValue('c', $this->session->getCurrencyCode(), 'EUR');
        $this->context->setValue('d', (bool) $this->session->getCustomerId(), false);
        $this->context->setValue('e', $this->session->getCustomerId() ? 1 : 0, 0);
        $this->context->setValue('f', $this->session->getCustomerId() > 0, false);
        $this->context->setValue('g', $groupId, 0);
        $this->context->setValue('h', $other->getId(), 0);
        $this->context->setValue('i', strlen($customer->getEmail()), 0);
        $this->context->setValue('k', implode('|', [$groupId, 1]), '');
        $other->setValue('j', $this->session->getCustomerId(), 0);
    }
}

final class Other
{
    public function getId(): int
    {
        return 1;
    }

    public function setValue(string $name, ?int $value, int $default): void
    {
    }
}
