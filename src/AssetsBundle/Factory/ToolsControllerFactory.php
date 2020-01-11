<?php

namespace AssetsBundle\Factory;

class ToolsControllerFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{

    /**
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     * @param \Interop\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @return  \AssetsBundle\Controller\ToolsController
     */
    public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null)
    {
        $oToolsController = new \AssetsBundle\Controller\ToolsController();
        return $oToolsController->setAssetsBundleToolsService($oServiceLocator->get('AssetsBundleToolsService'));
    }
}
