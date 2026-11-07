## ADDED Requirements

### Requirement: Symlinked directories
XML discovery SHALL follow symlinked directories under the analyzed paths, except a link that leads back to a directory on the path taken or to a parent of one, so that a link loop ends.

#### Scenario: Module symlinked into app/code
- **WHEN** `app/code/Ext/Mod` is a symlink to a module with an `etc/events.xml`, and the analyzed path is `app/code`
- **THEN** that file is analyzed

#### Scenario: Link loop
- **WHEN** a directory under the analyzed path links back to its parent
- **THEN** discovery ends and each file is analyzed once
