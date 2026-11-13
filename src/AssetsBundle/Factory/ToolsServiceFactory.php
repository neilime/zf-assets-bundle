<?php

namespace AssetsBundle\Factory;

class ToolsServiceFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{

    /**
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     * @param \Psr\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @return \AssetsBundle\Service\ToolsService
     */
    public function __invoke(\Psr\Container\ContainerInterface $oServiceLocator, $sRequestedName, ?array $aOptions = null)
    {
        $oToolsService = new \AssetsBundle\Service\ToolsService();
        $oToolsService
                ->setAssetsBundleService($oServiceLocator->get('AssetsBundleService'))
                ->setMvcEvent(($oMvcEvent = $oServiceLocator->get('Application')->getMvcEvent()) ? clone $oMvcEvent : new \Laminas\Mvc\MvcEvent());

        return $oToolsService;
    }
}
