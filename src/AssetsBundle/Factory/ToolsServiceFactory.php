<?php

namespace AssetsBundle\Factory;

class ToolsServiceFactory implements \Zend\ServiceManager\Factory\FactoryInterface
{

    /**
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     * @param \Interop\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @return \AssetsBundle\Service\ToolsService
     */
    public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null)
    {
        $oToolsService = new \AssetsBundle\Service\ToolsService();
        $oToolsService
                ->setAssetsBundleService($oServiceLocator->get('AssetsBundleService'))
                ->setMvcEvent(($oMvcEvent = $oServiceLocator->get('Application')->getMvcEvent()) ? clone $oMvcEvent : new \Zend\Mvc\MvcEvent());

        if ($oServiceLocator->has('console') && ($oConsole = $oServiceLocator->get('console')) instanceof \Zend\Console\Adapter\AdapterInterface) {
            $oToolsService->setConsole($oConsole);
        }
        return $oToolsService;
    }
}
