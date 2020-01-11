<?php

namespace AssetsBundle\Factory;

class ServiceFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{

    /**
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     * @param \Interop\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @throws \UnexpectedValueException
     * @return \AssetsBundle\Service\Service
     */
    public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null)
    {
        $aConfiguration = $oServiceLocator->get('Config');
        if (!isset($aConfiguration['assets_bundle'])) {
            throw new \UnexpectedValueException('AssetsBundle configuration is undefined');
        }

        // Initialize AssetsBundle service with options
        $oAssetsBundleService = new \AssetsBundle\Service\Service($oServiceLocator->get('AssetsBundleServiceOptions'));

        // Retrieve filters
        if (isset($aConfiguration['assets_bundle']['filters'])) {
            $aFilters = $aConfiguration['assets_bundle']['filters'];
            if ($aFilters instanceof \Traversable) {
                $aFilters = \Laminas\Stdlib\ArrayUtils::iteratorToArray($aFilters);
            } elseif (!is_array($aFilters)) {
                throw new \InvalidArgumentException('Assets bundle "filters" option expects an array or Traversable object; received "' . (is_object($aFilters) ? get_class($aFilters) : gettype($aFilters)) . '"');
            }
            
            $oAssetFilesManager = $oAssetsBundleService->getAssetFilesManager();

            if (!$oAssetFilesManager->hasAssetFileFiltersManager()) {
                $oAssetFileFiltersManager = new \AssetsBundle\AssetFile\AssetFileFiltersManager($oServiceLocator);
                $oAssetFilesManager->setAssetFileFiltersManager($oAssetFileFiltersManager);
            }


            $oAssetFileFiltersManager = $oAssetFilesManager->getAssetFileFiltersManager();
            foreach ($aFilters as $sFilterAliasName => $oFilter) {
                if ($oFilter === null) {
                    continue;
                }
                if ($oFilter instanceof \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface) {
                    $oAssetFileFiltersManager->setService($oFilter->getFilterName(), $oFilter);
                    continue;
                }
                if (is_string($oFilter)) {
                    $sFilterName = $oFilter;
                    $oFilter = array();
                } else {
                    if ($oFilter instanceof \Traversable) {
                        $oFilter = \Laminas\Stdlib\ArrayUtils::iteratorToArray($oFilter);
                    }
                    if (is_array($oFilter)) {
                        if (isset($oFilter['filter_name'])) {
                            $sFilterName = $oFilter['filter_name'];
                            unset($oFilter['filter_name']);
                        }
                    } elseif (!is_array($oFilter)) {
                        throw new \InvalidArgumentException('Filter expect expects a string, an array or Traversable object; received "' . (is_object($oFilter) ? get_class($oFilter) : gettype($oFilter)) . '"');
                    }
                }

                //Retrieve filter
                if ($oServiceLocator->has($sFilterName)) {
                    $oFilter = $oServiceLocator->get($sFilterName);
                } elseif (class_exists($sFilterName)) {
                    $oFilter = new $sFilterName($oFilter);
                } else {
                    throw new \InvalidArgumentException('Filter "' . $sFilterName . '" is not an available service or an existing class');
                }

                if ($oFilter instanceof \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface) {
                    $oAssetFileFiltersManager->setService($sAssetFileFilterName = $oFilter->getAssetFileFilterName(), $oFilter);
                    if (!$oAssetFileFiltersManager->has($sFilterAliasName)) {
                        $oAssetFileFiltersManager->setAlias($sFilterAliasName, $sAssetFileFilterName);
                    }
                } else {
                    throw new \InvalidArgumentException('Filter expects an instance of \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface, "' . get_class($oFilter) . '" given');
                }
            }
        }
        return $oAssetsBundleService;
    }

}
