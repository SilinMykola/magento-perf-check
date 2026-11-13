## ADDED Requirements

### Requirement: Areas of the script rules
Each script rule SHALL name the areas whose scripts it reads. File discovery SHALL also find the scripts of a module's `view/adminhtml` and of themes under `app/design/adminhtml`, and the analyzer SHALL read a script only when an enabled script rule reads its area. J-DEP-01 and J-DEP-02 SHALL read the storefront areas only.

#### Scenario: Storefront rule and an admin script
- **WHEN** only J-DEP-01 is enabled and a module has a `view/adminhtml/web/js/form.js`
- **THEN** the analyzer does not read that file

### Requirement: Code of a script
On request, the scanner SHALL give the code of a script: the source with every comment, string, template literal and regular expression literal replaced by spaces and its line breaks kept, so an offset in it is an offset in the source. A blanked string SHALL start with a double quote, a blanked template with a backtick and a blanked regular expression with `/`. The code SHALL be built only when a rule asks for it.

#### Scenario: Call in a comment
- **WHEN** a script has `// $.ajax(url).success(fn)`
- **THEN** that line of the code is blank

### Requirement: Pattern limits of a script rule
When a script rule cannot match its patterns against a script because of a PCRE limit, the analyzer SHALL keep the findings the rule made and report a notice with the source `script` that names the rule and the file. The other rules SHALL still read the script.

#### Scenario: Backtrack limit
- **WHEN** a script has `$.get(u).success(f)` and then a call whose brackets nest 100000 deep
- **THEN** U-JS-01 reports the `success` call and the report has a `script` notice "U-JS-01 could not read all of this script" for that file
