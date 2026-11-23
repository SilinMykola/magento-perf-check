# Proposal

## Why

[Round 22](../../../docs/calibration/round-22.md) graded the 16 X-GQL-02 findings of the Magento 2.4.9 core, which no round had graded before. One false positive was `BundlePriceDetails`, whose resolver calls `$this->cartRepository->get($cartItem->getQuoteId())` for each cart item: every item of the list has the same quote id, the cart query loaded that quote already, and `QuoteRepository::get()` returns it from `$quotesById` ([QuoteRepository.php#L155-L162](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Quote/Model/QuoteRepository.php#L155-L162)).

## What Changes

- X-GQL-02 leaves out `get()` on `Magento\Quote\Api\CartRepositoryInterface` when its argument is the quote id of a cart item.
- Round 22 records the grades of the experimental rules on the `github2` part and of X-GQL-02 on the core, and corrects round 15's X-GQL-02 verdicts after a re-check. No rule changes status.
