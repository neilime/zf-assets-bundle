<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter;

class GifAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\AbstractImageAssetFileFilter
{

    /**
     * @var string
     */
    protected $assetFileFilterName = 'Gif';

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return bool
     */
    protected function assetFileShouldBeOptimize(string $sContent) : bool
    {
        // Check if image is not an animated Gif
        return parent::assetFileShouldBeOptimize($sContent) && !preg_match('#(\x00\x21\xF9\x04.{4}\x00\x2C.*){2,}#s', $sContent);
    }

    /**
     * @param \GdImage $rImage
     * @return string
     * @throws \InvalidArgumentException
     */
    public function optimizeImage(\GdImage $rImage) : string
    {
        ob_start();
        imagegif($rImage);
        return ob_get_clean();
    }
}
