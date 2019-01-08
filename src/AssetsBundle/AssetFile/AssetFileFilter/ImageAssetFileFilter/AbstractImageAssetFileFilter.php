<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter;

abstract class AbstractImageAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
{

    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     */
    protected function filterContent(string $sContent, string $sFilePath) : string
    {
        // If asset file should not be optimized, return raw content
        if (!$this->assetFileShouldBeOptimize($sContent)) {
            return $sContent;
        }

        // Optimize image
        \Zend\Stdlib\ErrorHandler::start(\E_ALL);
        $rImage = imagecreatefromstring($sContent);
        imagealphablending($rImage, false);
        imagesavealpha($rImage, true);
        \Zend\Stdlib\ErrorHandler::stop(true);
        return $this->optimizeImage($rImage);
    }


    /**
     * @param string $sContent
     * @return bool
     */
    protected function assetFileShouldBeOptimize(string $sContent) : bool
    {
        return $sContent && function_exists('imagecreatefromstring');
    }

    /**
     * @param resource $rImage
     * @return string
     */
    abstract protected function optimizeImage(resource $rImage) : string;
}
