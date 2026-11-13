<?php

namespace AssetsBundle\Factory\AssetFileFilter;

class ScssAssetFileFilterFactory implements \Laminas\ServiceManager\Factory\FactoryInterface {

    /**
     * @param \Psr\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @return \AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter\ScssAssetFileFilter
     */
    public function __invoke(\Psr\Container\ContainerInterface $oServiceLocator, $sRequestedName, ?array $aOptions = null) {
        return new \AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter\ScssAssetFileFilter();
    }

}
