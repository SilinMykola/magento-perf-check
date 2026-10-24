# X-EVT-03: calibration sample

3 of 3 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Magewirephp/Magewire/etc/events.xml:14` (packagist)

[X-EVT-03] Observer "MagewirephpMagewireViewBlockAbstractToHtmlAfter" on "view_block_abstract_to_html_after" runs for every block or layout element rendered, hundreds of times per page. Observe a more specific event if one exists.

```
     8          />
     9      </event>
    10  
    11      <event name="view_block_abstract_to_html_after">
    12          <observer name="MagewirephpMagewireViewBlockAbstractToHtmlAfter"
    13                    instance="Magewirephp\Magewire\Observer\ViewBlockAbstractToHtmlAfter"
>   14          />
    15      </event>
    16  
    17      <event name="controller_action_predispatch">
    18          <observer name="MagewirephpMagewireControllerActionPredispatch"
    19                    instance="Magewirephp\Magewire\Observer\ControllerActionPredispatch"
    20          />
```

## 2. `app/code/Fastly/Cdn/etc/frontend/events.xml:5` (packagist)

[X-EVT-03] Observer "fastly_mark_esi_page" on "core_layout_render_element" runs for every block or layout element rendered, hundreds of times per page. Observe a more specific event if one exists.

```
     1  <?xml version="1.0"?>
     2  <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     3          xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
     4      <event name="core_layout_render_element">
>    5          <observer name="fastly_mark_esi_page" instance="Fastly\Cdn\Observer\MarkEsiPage" />
     6      </event>
     7  
     8      <event name="controller_action_postdispatch_magento_pagecache_block_esi">
     9          <observer name="fastly_mark_esi_block" instance="Fastly\Cdn\Observer\MarkEsiBlock" />
    10      </event>
    11  </config>
```

## 3. `app/code/Magewirephp/Magewire/etc/events.xml:8` (packagist)

[X-EVT-03] Observer "MagewirephpMagewireViewBlockAbstractToHtmlBefore" on "view_block_abstract_to_html_before" runs for every block or layout element rendered, hundreds of times per page. Observe a more specific event if one exists.

```
     2  <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     3          xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd"
     4  >
     5      <event name="view_block_abstract_to_html_before">
     6          <observer name="MagewirephpMagewireViewBlockAbstractToHtmlBefore"
     7                    instance="Magewirephp\Magewire\Observer\ViewBlockAbstractToHtmlBefore"
>    8          />
     9      </event>
    10  
    11      <event name="view_block_abstract_to_html_after">
    12          <observer name="MagewirephpMagewireViewBlockAbstractToHtmlAfter"
    13                    instance="Magewirephp\Magewire\Observer\ViewBlockAbstractToHtmlAfter"
    14          />
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Magewirephp/Magewire/etc/events.xml:14` | TP-low | Runs per block, but for non-Magewire blocks it only pops the lifecycle stack and fires an internal trigger; the framework needs every block and no narrower event exists. | re-check |  |
| 2 | `app/code/Fastly/Cdn/etc/frontend/events.xml:5` | FP | Fastly's ESI marker is the page-cache counterpart of the exempted Magento_PageCache observer; it returns early when Fastly is off and otherwise only reads the block TTL. | re-check |  |
| 3 | `app/code/Magewirephp/Magewire/etc/events.xml:8` | TP-low | Runs per block, but for non-Magewire blocks it only pushes the lifecycle stack and fires an internal trigger; the framework needs every block and no narrower event exists. | re-check |  |
