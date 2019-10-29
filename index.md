---
layout: default
title: Home
---
# AssetsBundle - Zend Framework module

[![Build Status](https://travis-ci.org/neilime/zf-assets-bundle.svg?branch=master)](https://travis-ci.org/neilime/zf-assets-bundle)
[![Coverage Status](https://coveralls.io/repos/github/neilime/zf-assets-bundle/badge.svg)](https://coveralls.io/github/neilime/zf-assets-bundle)
[![Latest Stable Version](https://poser.pugx.org/neilime/zf-assets-bundle/v/stable)](https://packagist.org/packages/neilime/zf-assets-bundle)
[![Total Downloads](https://poser.pugx.org/neilime/zf-assets-bundle/downloads)](https://packagist.org/packages/neilime/zf-assets-bundle)
[![License](https://poser.pugx.org/neilime/zf-assets-bundle/license)](https://packagist.org/packages/neilime/zf-assets-bundle)
[![Sponsor](https://img.shields.io/badge/%E2%9D%A4-Sponsor-ff69b4)](https://github.com/sponsors/neilime) 

📢 __AssetsBundle__ is a module for Zend Framework 3+ providing assets management (minifier, bundler & cache) like Css, Js, Less and Scss, dedicated to current module, controller and action.

This module is "development / production" environment aware.

🔧 In development:
 - Css & Js files are not bundled for easier debugging.
 - Less & Scss files are compiled when updated or if an "@import" file is updated

🚀 In production:
 - All asset files (Css, Js, medias) are __minified__, __bundled__ and __cached__ only if once. 
 - Assets path are encrypted to mask file tree (with the exception of files in the "assets" public directory)

# Helping Project

❤️ If this project helps you reduce time to develop and/or you want to help the maintainer of this project. You can [sponsor](https://github.com/sponsors/neilime) him. Thank you !

# Contributing

👍 If you wish to contribute to this project, please read the [CONTRIBUTING.md](CONTRIBUTING.md) file. Note: If you want to contribute don't hesitate, I'll review any PR.

# Documentation

1. [Installation](https://github.com/neilime/zf-assets-bundle/wiki/Installation)
2. [Use with Zend Skeleton Application](https://github.com/neilime/zf-assets-bundle/wiki/Use-with-Zend-Skeleton-Application)
3. [Configuration](https://github.com/neilime/zf-assets-bundle/wiki/Configuration)
4. [Custom Js](https://github.com/neilime/zf-assets-bundle/wiki/Custom-Js)
5. [Console tools](https://github.com/neilime/zf-assets-bundle/wiki/Console-tools)
6. [FAQ](https://github.com/neilime/zf-assets-bundle/wiki/FAQ)
8. [Code Coverage](https://coveralls.io/github/neilime/zf-assets-bundle)
9. [PHP Doc](https://neilime.github.io/zf-assets-bundle/phpdoc)
