# Design

## D1. Collectors and a rule on CollectedDataNode

A rule on `MethodCall` sees only the file it is in, and the called method is usually in another file. PHPStan collectors run on every file, and a `CollectedDataNode` rule sees all their data once per analysis. P-API-01 already works this way, and its result-cache test shows that a change in the called file reaches the report.

- `QueryCallCollector` (`MethodCall`). It runs when the call is a query under the predicates of P-LOOP-01, P-LOOP-09 or P-LOOP-10 and the scope is a class method. It records:
  - the declaring class and the method;
  - the query (`Class::method()`), its line and its file (for a trait, the trait's file);
  - whether the query is a `getList()`.

  The predicates are the same code that those rules use, moved into a shared class so the two cannot drift. A query in a closure counts only when the closure runs in the method's own loop: a closure the method returns or stores runs nothing when the method is called.
- `MemoizingMethodCollector` (`InClassMethodNode`). It records the lines of a method whose result the method keeps for later calls:
  - the value stored in an element of a property, `$this->cache[$id] = ...` or `self::$cache[$id] ??= ...`;
  - the value of `$this->product ??= ...`;
  - the body of an `if` that reads a property and assigns it;
  - the value assigned to a property after an `if` on that property that returns early.

  An append (`$this->log[] = ...`) keeps nothing. Only queries on those lines count as kept, so a method that caches one value and queries another still counts.
- `LoopCallCollector` (`CallLike`). It records a `->` or `::` call in loop context (`IN_LOOP`, not `LEAVES_LOOP`) whose receiver PHPStan resolves to one class:
  - the declaring class and method of the resolved method;
  - the line;
  - whether the loop runs once per item (`IN_ITEM_LOOP`);
  - whether the receiver may be a new object in each iteration: the foreach value, something reached from it, `new` or a `create()` result.

  PHPStan hands a `?->` call to collectors as a `->` call as well. A `->` call that is itself a query is left out, because P-LOOP-01, P-LOOP-09 or P-LOOP-10 report it already.

## D2. Matching

The rule reports a loop call when its declaring class and method have a query call. A kept query does not count, unless the receiver may be a new object in each iteration: a lazy getter on a new item each time, such as `$item->getOrderItem()`, still queries once per item. Both names come from declarations or reflection, so they are compared exactly. A `getList()` counts only for a call in a loop over items, as P-LOOP-09 leaves paging loops out. Each loop call is reported once, naming the first query of the method by line that counts.

A call through an interface or to an abstract method has no body, so it is not matched. The same holds for a callee outside the analyzed paths, such as core code, because collectors only run on the analyzed files. Both choices keep the rule to what can be shown from the code.

## D3. Severity and status

The rule is experimental at `warning`. Whether the callee runs for every item depends on branches that the rule does not follow, so the false-positive risk is medium until calibration says otherwise.
