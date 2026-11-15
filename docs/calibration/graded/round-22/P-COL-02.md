# P-COL-02: calibration grades, round 22

9 of 9 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/MageOS/Blog/Model/AuthorRepository.php:65` | FP | The filter is on `slug`, which the resource model's save guard keeps unique, so at most one row matches. |
| 2 | `app/code/MageOS/Blog/Model/CategoryRepository.php:72` | FP | The filter is on `url_key` and store, kept unique by the same save guard. |
| 3 | `app/code/MageOS/Blog/Model/PostRepository.php:84` | FP | The filter is on `url_key` and store, kept unique by the same save guard. |
| 4 | `app/code/MageOS/Blog/Model/TagRepository.php:72` | FP | The filter is on `url_key` and store, kept unique by the same save guard. |
| 5 | `app/code/MageOS/NewsletterCoupon/Model/SubscriberInformationRepository.php:64` | FP | The filter is on `subscriber_id`; the only writer creates a row only when none exists for the subscriber. |
| 6 | `app/code/MageOS/NewsletterCoupon/Model/SubscriberInformationRepository.php:76` | TP-low | The filter is on `subscriber_email`, without a unique key: a few rows at most, and nothing calls the method. |
| 7 | `app/code/MageOS/NewsletterCoupon/Model/SubscriberInformationRepository.php:89` | FP | The filter is on `coupon_id`; each row gets a newly generated coupon. |
| 8 | `app/code/Opengento/CategoryImportExport/Model/Import/Categories.php:120` | FP | The filter is on `category_code`, which the module builds from the unique category path and treats as a key. |
| 9 | `app/code/Zwernemann/Withdrawal/Model/WithdrawalConfirmation.php:243` | TP-low | The filter is on the status, a primary key, with a join to its states: one row or a handful, once per confirmation. |
