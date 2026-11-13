# Development

Use the PHP and Composer dependencies declared in
[composer.json](../../composer.json). Tests also require GD with JPEG support.

```sh
composer install --no-scripts --no-plugins
composer lint:ci
composer test
```

For CI reports, enable Xdebug coverage or PCOV and run `composer test:ci`.
Reports are written to `build/`. Tests operate on local fixtures and clean their
own generated asset directories.

GitHub CI uses the shared HoverKraft PHP workflow. It discovers PHP versions
from Composer, validates syntax and package metadata, runs PHPUnit and checks a
source archive in an isolated Composer consumer. Dependabot maintains Composer
dependencies and GitHub Actions. Pull request titles use Conventional Commits;
only prose body and footer line lengths are exempted in the root configuration.

## Releases

Run the Release workflow manually, or let its weekly schedule check for changes.
The shared preparation workflow derives the version from conventional changes;
no relevant change skips publication unless a manual run requests `force`.
Publication requires successful validation and creates the version tag and
GitHub release. Composer package versions come from tags.

Packagist must be connected to this repository to import new tags. The optional
`CHANGELOG_SUMMARY` secret configures release summaries through the shared
HoverKraft publishing actions. Release preparation on pull requests only
maintains release metadata; it does not publish packages.
