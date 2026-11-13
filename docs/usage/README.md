# Installation and usage

Install the package in a Laminas MVC application:

```sh
composer require neilime/zf-assets-bundle
```

Add `AssetsBundle` to the application's `config/modules.config.php` module list.
Create writable cache and processed-file directories:

```sh
mkdir -p public/cache data/AssetsBundle/processed
```

## Configuration

Configure `assets_bundle` in an application config file, such as
`config/autoload/assets-bundle.global.php`:

```php
<?php
return [
    'assets_bundle' => [
        'production' => true,
        'assets' => [
            'css' => ['css/site.css'],
            'js' => ['js/site.js'],
        ],
    ],
];
```

Paths are relative to `public/` by default. `@zfRootPath` resolves to the
application working directory and `@zfAssetsPath` to the configured assets
path. These path placeholders remain supported. See
[the default configuration](../../config/module.config.assets-bundle.php) for
cache paths, permissions, filters and view helpers.

Use `production: false` in local application configuration to keep CSS and
JavaScript separate. Less and SCSS still compile. In production, the module
bundles assets and caches the result. Register fonts and images referenced by
styles in the `media` asset list so the module can copy them and rewrite URLs.

Render the configured `headLink` and `headScript` helpers from your layout.
Asset groups can be scoped to module, controller and action names through nested
entries in `assets`.

## Optional filters

Install the filters used by your asset configuration:

```sh
composer require tedivm/jshrink tubalmartin/cssmin
composer require wikimedia/less.php scssphp/scssphp
```

Enable the PHP GD extension for PNG, JPEG and GIF optimization. Animated GIFs
retain their animation. `mrclay/jsmin-php` is an alternative JavaScript filter;
select `JsMinAssetFileFilter` in the `filters` configuration to use it.

## Console commands

Run commands from the application's root:

```sh
vendor/bin/laminas render
vendor/bin/laminas empty
```

`render` prepares all configured assets for the selected environment. `empty`
removes generated files from the configured cache, temporary and processed
asset directories. Use directories dedicated to AssetsBundle; do not point
these settings at shared application data.

## Action-specific JavaScript

A controller extending `AssetsBundle\Mvc\Controller\AbstractActionController`
can implement `jscustomAction($action)` and return an array of JavaScript paths.
The layout receives `jsCustomFiles` in development and `jsCustomUrl` in
production. Render those values as script URLs in your layout.
