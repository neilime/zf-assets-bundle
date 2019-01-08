<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\JsAssetFileFilter;

class JShrinkAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\JsAssetFileFilter\AbstractJsAssetFileFilter
{
        
    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     * @throws \LogicException
     */
    protected function filterContent(string $sContent, string $sFilePath) : string
    {
        if (!class_exists('JShrink\Minifier')) {
            throw new \LogicException('"JShrink\Minifier" class does not exist');
        }
        return \JShrink\Minifier::minify($sContent);
    }
}
