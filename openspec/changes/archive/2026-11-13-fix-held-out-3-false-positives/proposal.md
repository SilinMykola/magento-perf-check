# Proposal

## Why

The third held-out check (`docs/calibration/held-out-3.md`, written in change `add-github-corpus`) graded 62 findings of the default set on 50 GitHub modules and found four false positives, of two kinds:

- P-LOOP-01 and P-LOOP-03 report a call in a `try` inside a loop although the loop is left right after it: `try { $product = $this->productRepository->getById($id); break; } catch (NoSuchEntityException $e) {}`, and a `delete()` in a `catch` that always ends with `throw`. The loop exit check does not walk a `try` inside a loop at all, because a `catch` can go on with the next iteration.
- P-DI-06 reports two allowed forms: the constructor fallback written as a full ternary, `$config !== null ? $config : ObjectManager::getInstance()->get(...)`, and a lazy getter that assigns the object to a local variable before storing it in the tested property.

## What Changes

- **Loop exit through a `try` in the loop.** A `catch` goes on with what follows the `try`, so a call in a `catch` that then leaves the loop with `throw`, `return` or `break` runs at most once. The `try` body is walked only when every `catch` leaves the loop as well; a `try` without `catch` is walked too. A `catch` that goes on with the next iteration keeps every call of the body in loop context: Magento's repositories throw `NoSuchEntityException` after the query, so `try { getById(); break; } catch (NoSuchEntityException $e) {}` runs one query per missing id. A `finally` goes on with what follows the `try` only when neither the `try` nor a `catch` holds `continue`, `break` or `goto`.

  This fixes the P-LOOP-03 false positive. The P-LOOP-01 one, the empty `catch` above, stays reported: the rule cannot know that only one option of the list has the code it looks for.
- **P-DI-06 full ternary.** In the constructor, a ternary whose condition tests that a value is there (`!== null`, `!= null`, `isset()`, the value itself, `is_object()`, `instanceof`) and returns that value, or tests that it is missing and returns the value in the other branch, counts as the fallback, like `?:` and `??`.
- **P-DI-06 lazy getter through a variable.** The getter block may assign the object manager result to a local variable and then store that variable in the tested property.
- Rule pages of P-LOOP-01 and P-DI-06 and the loop rule requirement are updated.

Measured on the whole corpus before and after; the counts go into `held-out-3.md`.
