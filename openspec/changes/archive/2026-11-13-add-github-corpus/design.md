# Design

## D1. A hard-linked copy of `extended`

Many GitHub modules need another module that the corpus already has, such as `mageplaza/module-core` or the Hyvä theme module. A copy of `extended`, plus the `app/code` modules of the `packagist` part, which `extended` leaves out, provides them. `rsync --link-dest` links every file to its source instead of copying it, so the part costs the new modules plus the directory entries. Nothing writes into the corpus: the tool reads the project, and its cache lives in the system temp directory. The run passes only the new modules in `--paths`, so the findings of `extended` are not counted twice.

## D2. Selection

The GitHub search API, `topic:magento2-module archived:false fork:false`, sorted by stars, read in pages of 100. Stars change, so the manifest records the date and the stars of each repository. A repository must hold a single module at its root or in `src/`: a repository of many modules has no single place to copy from. Its `composer.json` must be of type `magento2-module`, and every requirement other than `php`, `ext-*` and `lib-*` must be installed in the copy at a version that fits, or be the Composer name of a module in the `packagist` or `extended` part. PHP must accept 8.4.0, as for the other parts. The repository's default branch is taken, not a tag: many of these repositories have no tags.

## D3. Clones

Each repository is cloned to `var/corpus/github-clones/<owner>/<repo>`, beside the other corpus parts, so the corpus stays in one git-ignored place; `build-corpus --clean` removes the clones too. Rebuilding the part keeps them, and a clone that exists is reused without a fetch. The manifest records the commit of each one, so a finding can be traced to its code. The copy into the corpus leaves out `.git`.

## D4. Safety

The registration check of the `extended` part applies unchanged. Nothing of a clone runs before that check. `git clone` runs no hooks of the repository. A repository with a symbolic link is skipped: a link could bring a file from outside the clone into the analyzed tree and from there into a report that is committed. A constraint that Composer cannot parse, and a clone that fails, skip the repository instead of stopping the build.
