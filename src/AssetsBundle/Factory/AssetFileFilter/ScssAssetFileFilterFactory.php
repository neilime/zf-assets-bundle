<?php

namespace AssetsBundle\Factory\AssetFileFilter;

class ScssAssetFileFilterFactory implements \Laminas\ServiceManager\Factory\FactoryInterface {

    /**
     * @param \Interop\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @return \AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter\ScssAssetFileFilter
     */
    public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null) {
        return new \AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter\ScssAssetFileFilter();
    }

}
