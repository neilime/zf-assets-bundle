<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter;

class PngAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\AbstractImageAssetFileFilter
{

    /**
     * @var string
     */
    protected $assetFileFilterName = 'Png';

    /**
     * Compression level: from 0 (no compression) to 9.
     * @var int
     */
    protected $imageQuality = 9;

    /**
     * @param int $iImageQuality
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\PngAssetFileFilter
     */
    public function setImageQuality(int $iImageQuality) : \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\PngAssetFileFilter
    {
        if (!is_int($iImageQuality) || $iImageQuality < 0 || $iImageQuality > 9) {
            throw new \InvalidArgumentException(sprintf(
                    '$iImageQuality expects int from 0 to 9 "%s" given',
                is_scalar($iImageQuality) ? $iImageQuality : (is_object($iImageQuality) ? get_class($iImageQuality) : gettype($iImageQuality))
            ));
        }
        $this->imageQuality = (int) $iImageQuality;
        return $this;
    }

    /**
     * @return int
     */
    public function getImageQuality() : int
    {
        return $this->imageQuality;
    }

    /**
     * @param resource $rImage
     * @return string
     * @throws \InvalidArgumentException
     */
    public function optimizeImage(resource $rImage) : string
    {
        if (is_resource($rImage)) {
            ob_start();
            imagepng($rImage, null, $this->getImageQuality());
            return ob_get_clean();
        }
        throw new \InvalidArgumentException('Image expects a ressource, "' . gettype($rImage) . '" given');
    }
}
