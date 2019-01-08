# AssetsBundle - Zend Framework module

[![Build Status](https://travis-ci.org/neilime/zf-assets-bundle.svg?branch=master)](https://travis-ci.org/neilime/zf-assets-bundle)
[![Latest Stable Version](https://poser.pugx.org/neilime/zf-assets-bundle/v/stable.svg)](https://packagist.org/packages/neilime/zf-assets-bundle)
[![Total Downloads](https://poser.pugx.org/neilime/zf-assets-bundle/downloads.svg)](https://packagist.org/packages/neilime/zf-assets-bundle)
[![Coverage Status](https://coveralls.io/repos/github/neilime/zf-assets-bundle/badge.svg?branch=master)](https://coveralls.io/github/neilime/zf-assets-bundle?branch=master)
[![Beerpay](https://beerpay.io/neilime/zf-assets-bundle/badge.svg)](https://beerpay.io/neilime/zf-assets-bundle)

📢 _AssetsBundle_ is a module for Zend Framework 3+ providing assets management (minifier, bundler & cache) like Css, Js, Less and Scss, dedicated to current module, controller and action.

This module is "development / production" environment aware.

🔧 In development :
 - Css & Js files are not bundled for easier debugging.
 - Less & Scss files are compiled when updated or if an "@import" file is updated

🚀 In production :
 - All asset files (Css, Js, medias) are __minified__, __bundled__ and __cached__ only if once. 
 - Assets path are encrypted to mask file tree (with the exception of files in the "assets" public directory)

# Helping Project

❤️ If this project helps you reduce time to develop and/or you want to help the maintainer of this project, you can support him on [![Beerpay](https://beerpay.io/neilime/zf-assets-bundle/badge.svg)](https://beerpay.io/neilime/zf-assets-bundle) Thank you !


# Contributing

👍 If you wish to contribute to this project, please read the [CONTRIBUTING.md](CONTRIBUTING.md) file.
NOTE : If you want to contribute don't hesitate, I'll review any PR.

# Requirements

## Mandatory

Name | Version
-----|--------
[php](https://secure.php.net/) | >=7.1
[zendframework/zend-config](https://github.com/zendframework/zend-config) | ^3.2.0
[zendframework/zend-console](https://github.com/zendframework/zend-console) | ^2.7.0
[zendframework/zend-eventmanager](https://github.com/zendframework/zend-eventmanager) | ^3.2.1
[zendframework/zend-http](https://github.com/zendframework/zend-http) | ^2.8.2
[zendframework/zend-modulemanager](https://github.com/zendframework/zend-modulemanager) | ^2.8.2
[zendframework/zend-mvc](https://github.com/zendframework/zend-mvc) | ^3.1.1
[zendframework/zend-mvc-console](https://github.com/zendframework/zend-mvc-console) | ^1.2.0
[zendframework/zend-servicemanager](https://github.com/zendframework/zend-servicemanager) | ^3.4.0
[zendframework/zend-view](https://github.com/zendframework/zend-view) | ^2.11.1

## Optionnal

Name | Version | What for 
-----|---------|----
[tubalmartin/cssmin](https://github.com/tubalmartin/cssmin) | ^4.1.1 | PHP port of the YUI CSS compressor
[neilime/lessphp](https://github.com/neilime/lessphp) | ^0.5 | Lessphp compliant fork
[oyejorge/less.php](https://github.com/oyejorge/less.php) | ^1.7.0.14 | Less parser
[leafo/scssphp](https://github.com/leafo/scssphp) | ^0.7.7 | SCSS compiler
[tedivm/jshrink](https://github.com/tedivm/jshrink) | ^1.3.1 | Javascript Minifier
[mrclay/jsmin-php](https://github.com/mrclay/jsmin-php) | ^2.4 | Port of Douglas Crockford's jsmin.c

# Pages

1. [Installation](https://github.com/neilime/zf-assets-bundle/wiki/Installation)
2. [Use with Zend Skeleton Application](https://github.com/neilime/zf-assets-bundle/wiki/Use-with-Zend-Skeleton-Application)
3. [Configuration](https://github.com/neilime/zf-assets-bundle/wiki/Configuration)
4. [Custom Js](https://github.com/neilime/zf-assets-bundle/wiki/Custom-Js)
5. [Console tools](https://github.com/neilime/zf-assets-bundle/wiki/Console-tools)
6. [FAQ](https://github.com/neilime/zf-assets-bundle/wiki/FAQ)
7. [PHP Doc](http://neilime.github.io/zf-assets-bundle/phpdoc/)
8. [Code Coverage](http://neilime.github.io/zf-assets-bundle/coverage/)