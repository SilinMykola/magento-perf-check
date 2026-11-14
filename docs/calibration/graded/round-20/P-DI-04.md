# P-DI-04: calibration grades, round 20

10 of 10 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Baldwin/ImageCleanup/Console/Command/RemoveCorruptResizedFiles.php:24` | TP-low | UserInteraction, ProgressIndicator, MediaDeleter and CorruptResizedFilesFinder only store arguments such as Filesystem, the file driver and a logger whose handler opens no file until it writes. |
| 2 | `app/code/Baldwin/ImageCleanup/Console/Command/RemoveObsoleteDatabaseEntries.php:22` | TP-low | UserInteraction, DatabaseGalleryDeleter and ObsoleteDatabaseEntriesFinder only store ResourceConnection, the attribute repository and a logger, so they open no connection and run no query. |
| 3 | `app/code/Baldwin/ImageCleanup/Console/Command/RemoveUnusedCacheHashDirectories.php:27` | TP-low | The finder pulls in a theme collection, view config, ParamsBuilder and app emulation, a larger graph that still runs no query or file I/O in any constructor (unsure: the graph is bigger than the other Baldwin commands). |
| 4 | `app/code/Baldwin/ImageCleanup/Console/Command/RemoveUnusedImageFiles.php:24` | TP-low | UserInteraction, ProgressIndicator, MediaDeleter and UnusedFilesFinder only store Filesystem, ResourceConnection, File IO and a config reader. |
| 5 | `app/code/Divante/PimcoreIntegration/Console/Command/AssetsImportCommand.php:46` | TP-low | Registry has an empty constructor, so creating it costs nothing, and the processor is already taken through a factory. |
| 6 | `app/code/Divante/PimcoreIntegration/Console/Command/CategoryImportCommand.php:47` | TP-low | Registry has an empty constructor, so creating it costs nothing, and the processor is already taken through a factory. |
| 7 | `app/code/Divante/PimcoreIntegration/Console/Command/ProductImportCommand.php:46` | TP-low | Registry has an empty constructor, so creating it costs nothing, and the processor is already taken through a factory. |
| 8 | `app/code/Macopedia/CategoryImporter/Console/Command/CategoriesCommand.php:92` | TP-low | File\Csv only stores a file driver and DirectoryList is created at bootstrap anyway, although the constructor also calls setCurrentStore('admin') on every run, which is outside this rule. |
| 9 | `app/code/Siteation/DebugBar/Console/McpServerCommand.php:27` | TP-low | Server builds six MCP tools whose shared presenter, store, summary and comparer only store arguments. |
| 10 | `app/code/Siteation/DebugBar/Console/ReportCommand.php:30` | TP-low | ProfileStore and ProfileReport only store Filesystem, config, a logger and two analysis helpers. |
