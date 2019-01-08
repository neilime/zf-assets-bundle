<?php

namespace AssetsBundle\AssetFile\AssetFileFilter;

interface AssetFileFilterInterface extends \Zend\Stdlib\ParameterObjectInterface {

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return string
     */
    public function filterAssetFile(\AssetsBundle\AssetFile\AssetFile $oAssetFile) : string;

    /**
     * @return string
     */
    public function getAssetFileFilterName() : string;

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions) : \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface;

    /**
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function getOptions() : \AssetsBundle\Service\ServiceOptions;

    /**
     * @return string
     */
    public function getAssetFileFilterProcessedDirPath() : string;
}
