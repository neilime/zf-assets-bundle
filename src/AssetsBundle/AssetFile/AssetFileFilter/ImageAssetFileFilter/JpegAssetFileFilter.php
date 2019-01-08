<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter;

class JpegAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\AbstractImageAssetFileFilter
{

    /**
     * @var string
     */
    protected $assetFileFilterName = 'Jpeg';

    /**
     * Compression level: from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
     * @var int
     */
    protected $imageQuality = 30;

    /**
     * @param int $iImageQuality
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\JpegAssetFileFilter
     */
    public function setImageQuality(int $iImageQuality) : \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\JpegAssetFileFilter
    {
        if (!is_int($iImageQuality) || $iImageQuality < 0 || $iImageQuality > 100) {
            throw new \InvalidArgumentException(sprintf(
                    '$iImageQuality expects int from 0 to 100 "%s" given',
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
            imagejpeg($rImage, null, $this->getImageQuality());
            return ob_get_clean();
        }
        throw new \InvalidArgumentException('Image expects a ressource, "' . gettype($rImage) . '" given');
    }
}
