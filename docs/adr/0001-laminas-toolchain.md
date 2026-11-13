# Maintain the module on Laminas MVC

Status: proposed, 2026-11-13.

The module's Zend Framework dependencies, PHP 7 runtime, retired Console
component and PHPUnit 7 setup prevent validation with supported tooling.

Migrate to Laminas MVC and laminas-cli, with PHP 8.4, ServiceManager 3, View 2 and
PHPUnit 12. These are the compatible stable lines accepted for this migration.
The current MVC and test packages exclude the newer runtime and component
majors. MVC remains in security-only maintenance; moving to a different web
framework is a separate application architecture decision.

Keep the package name, asset grouping, cache behavior and existing path
placeholders. Replace Zend namespaces and the console controller with registered
`render` and `empty` Symfony commands. Use maintained Less and SCSS compilers and
PHP's `GdImage` API. Remove the separate retired Lessc adapter. This is a breaking
release for Zend applications and callers of the old console controller.

Replace Travis with pinned shared HoverKraft PHP CI. Keep dependency and workflow
updates in Dependabot, and manual/scheduled publication separate from CI. Reuse
existing fixture assertions for compiled assets and CLI behavior; only SCSS
quote formatting changes in the expected CSS. A failing image filter must also
restore error handlers and execution limits.

References:

- [Laminas migration](https://docs.laminas.dev/migration/)
- [Laminas CLI](https://docs.laminas.dev/laminas-cli/intro/)
- [Laminas MVC maintenance](https://docs.laminas.dev/laminas-mvc/)
- [PHPUnit tests](https://docs.phpunit.de/en/12.5/writing-tests-for-phpunit.html)
