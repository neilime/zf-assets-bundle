<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\JsAssetFileFilter;

class JsMinAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\JsAssetFileFilter\AbstractJsAssetFileFilter
{
        
    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     * @throws \LogicException
     */
    protected function filterContent(string $sContent, string $sFilePath) : string
    {
        if (!class_exists('JSMin')) {
            throw new \LogicException('"JSMin" class does not exist');
        }
        return \JSMin::minify($sContent);
    }
}
