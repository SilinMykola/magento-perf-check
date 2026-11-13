## ADDED Requirements

### Requirement: Calibration summary without project data
`resources/calibration-summary.php` SHALL turn one or more JSON reports into a file to share and a local file. The file to share SHALL hold only the tool version, the number of reports, the run time, the findings per rule, the notices per source and a verdict table of row numbers and rule IDs, with up to 30 findings per rule drawn with a fixed seed. The local file SHALL map each row to its report, file, line, message and rule page.

#### Scenario: Several projects
- **WHEN** the script gets the reports of two projects
- **THEN** the file to share has their summed counts and does not name either report

### Requirement: Nothing of the project in the shared file
The file to share SHALL NOT hold a path, a module, class, vendor or project name, a finding or notice message, or the name of a report file.

#### Scenario: Report with paths and names
- **WHEN** a report has findings in `app/code/ClientName/Secret/` and a notice that names a module
- **THEN** the file to share contains none of these, and the local file contains the paths
