## ADDED Requirements

### Requirement: Magento 1 code is not analyzed
The folders `app/code/core`, `app/code/community` and `app/code/local`, and every folder `app/design/<area>/<package>/<theme>` that has neither `theme.xml` nor `registration.php`, SHALL be left out of the analysis, as Magento 2 does not run them. When such a folder lies in, or holds, an analyzed path, one `magento1-code` notice SHALL name it. A folder `app/code/<Vendor>/<Module>` without `etc/module.xml` SHALL still be analyzed.

#### Scenario: Magento 1 code pool
- **WHEN** `app/code/community/Acme/Old/Model/Foo.php` calls the object manager
- **THEN** nothing is reported for it and a `magento1-code` notice names `app/code/community`

#### Scenario: Magento 1 theme
- **WHEN** `app/design/frontend/base/default` has no `theme.xml` and no `registration.php`
- **THEN** its templates are not analyzed and a `magento1-code` notice names it

#### Scenario: Magento 2 theme
- **WHEN** `app/design/frontend/Acme/shop` has `theme.xml`
- **THEN** its templates are analyzed
