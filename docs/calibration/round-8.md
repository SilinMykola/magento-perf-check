# Calibration, round 8

This round measures X-LAY-01 again after its allowlist took the personal pages of known modules (change allow-module-personal-handles). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** both parts of the calibration corpus ([`corpus.json`](corpus.json)) and the Magento 2.4.9 core, with every experimental rule enabled. Run on 2026-11-11 with the code of 0.5.0-dev.
- **Grading:** every remaining finding was graded; all five carry verdicts from earlier rounds: the two core findings from round 1 and three from round 6. The runs are not stored: `runs/` keeps those of rounds 1 and 6.
- **Other rules:** no finding of another rule changed. X-LAY-03 shares the allowlist, but none of the new prefixes starts with `default`, `catalog_` or `cms_`.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [X-LAY-01](../rules/X-LAY-01.md) | warning | 5 | 5 | 2 | 3 | 0 | 0 | 100 % | 40 % | experimental |

**X-LAY-01.** The extended part went from 47 findings to 3, the 25 modules of round 1 from 2 to 0, and the core stays at 2. The removed findings are the pages listed in the [design](../../openspec/changes/archive/2026-11-11-allow-module-personal-handles/design.md). Round 6 had sampled 30 of the 47: the 24 false positives are among them, and so are three TP-low verdicts on Dotdigital report endpoints whose content is shared, but which only Dotdigital's servers fetch with a passcode. Four of the unsampled findings were graded for this change, all FP, and are left out now too:
- `Clerk/Clerk/view/frontend/layout/clerk_clerk_powerstep_popup.xml:4`: the popup with recommendations for what was just added to the current cart;
- `Mageplaza/Blog/view/frontend/layout/mpblog_author_information.xml:32`: the author form of the logged-in customer, guests are redirected;
- `Mageplaza/ShareCart/view/frontend/layout/sharecart_index_download.xml:25`: a PDF of the current cart;
- `Taxjar/SalesTax/view/frontend/layout/onestepcheckout_index_index.xml:27`: a block on the checkout page of a one-step checkout module.

What remains:
- TP: the popular search terms page, in the core and in the Hyvä theme. It is the same for every visitor.
- TP-low: the contact page, in the core (captcha) and in the Hyvä theme (a form filled with the customer's name), and Smile's per-store contact page. They are public, but get little traffic.

## Decision

X-LAY-01 stays experimental: with 5 findings, the data is insufficient. Its precision on what remains is 100 %, against 20 % in round 6.
