<?php

namespace AssetsBundle\Factory\AssetFileFilter;

class JsMinAssetFileFilterFactory implements \Zend\ServiceManager\Factory\FactoryInterface
{

    /**
     * @param \Interop\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @return \AssetsBundle\AssetFile\AssetFileFilter\JsAssetFileFilter\JsMinAssetFileFilter
     */
    public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null)
    {
        return new \AssetsBundle\AssetFile\AssetFileFilter\JsAssetFileFilter\JsMinAssetFileFilter();
    }

}
