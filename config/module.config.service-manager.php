<?php

//Service manager module config
return [
    'factories' => [
        'ScssAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\ScssAssetFileFilterFactory::class,
        'LesscAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\LesscAssetFileFilterFactory::class,
        'LessphpAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\LessphpAssetFileFilterFactory::class,
        'CssAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\CssAssetFileFilterFactory::class,
        'JsMinAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\JsMinAssetFileFilterFactory::class,
        'JShrinkAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\JShrinkAssetFileFilterFactory::class,
        'PngAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\PngAssetFileFilterFactory::class,
        'JpegAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\JpegAssetFileFilterFactory::class,
        'GifAssetFileFilter' => \AssetsBundle\Factory\AssetFileFilter\GifAssetFileFilterFactory::class,
        'AssetsBundleService' => \AssetsBundle\Factory\ServiceFactory::class,
        'AssetsBundleServiceOptions' => \AssetsBundle\Factory\ServiceOptionsFactory::class,
        'AssetsBundleToolsService' => \AssetsBundle\Factory\ToolsServiceFactory::class,
        'JsCustomStrategy' => \AssetsBundle\Factory\JsCustomStrategyFactory::class,
        'JsCustomRenderer' =>  \AssetsBundle\Factory\JsCustomRendererFactory::class,
    ],
];
