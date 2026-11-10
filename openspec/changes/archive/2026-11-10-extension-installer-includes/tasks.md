# Tasks

- [x] Read the extension installer's list the way PHPStan finds it, and leave out the files it includes
- [x] Cover a project whose installer includes this package and bitexpert/phpstan-magento, with the real PHPStan
- [x] Check the fix in a Composer project with the installer, on PHPStan 1.12 and 2.x; this check alone covers leaving out bitexpert/phpstan-magento's file, which is not installed next to the tool in its tests
- [x] Update the installation docs and the changelog
