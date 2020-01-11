<?php

namespace AssetsBundle\Factory;

class ServiceOptionsFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{

    /**
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     * @param \Interop\Container\ContainerInterface $oServiceLocator
     * @param string $sRequestedName
     * @param array $aOptions
     * @throws \UnexpectedValueException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null)
    {
        $aConfiguration = $oServiceLocator->get('Config');
        if (!isset($aConfiguration['assets_bundle'])) {
            throw new \UnexpectedValueException('AssetsBundle configuration is undefined');
        }

        $aOptions = $aConfiguration['assets_bundle'];
        if ($aOptions instanceof \Traversable) {
            $aOptions = \Laminas\Stdlib\ArrayUtils::iteratorToArray($aOptions);
        } elseif (!is_array($aOptions)) {
            throw new \InvalidArgumentException('"assets_bundle" configuration expects an array or Traversable object; received "' . (is_object($aOptions) ? get_class($aOptions) : gettype($aOptions)) . '"');
        }

        //Define base URL of the application
        if (!isset($aOptions['baseUrl'])) {
            if (($oRequest = $oServiceLocator->get('request')) instanceof \Laminas\Http\PhpEnvironment\Request) {
                $aOptions['baseUrl'] = $oRequest->getBaseUrl();
            } else {
                $oRequest = new \Laminas\Http\PhpEnvironment\Request();
                $aOptions['baseUrl'] = $oRequest->getBaseUrl();
            }
        }

        //Retrieve filters
        if (isset($aOptions['view_helper_plugins'])) {
            $aViewHelperPlugins = $aOptions['view_helper_plugins'];
            if ($aViewHelperPlugins instanceof \Traversable) {
                $aViewHelperPlugins = \Laminas\Stdlib\ArrayUtils::iteratorToArray($aOptions);
            } elseif (!is_array($aViewHelperPlugins)) {
                throw new \InvalidArgumentException('Assets bundle "filters" option expects an array or Traversable object; received "' . (is_object($aViewHelperPlugins) ? get_class($aViewHelperPlugins) : gettype($aViewHelperPlugins)) . '"');
            }

            $oViewHelperPluginManager = $oServiceLocator->get('ViewHelperManager');

            foreach ($aViewHelperPlugins as $sAssetFileType => $oViewHelperPlugin) {
                if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
                    throw new \LogicException('Asset file type "' . $sAssetFileType . '" is not valid');
                }
                if (is_string($oViewHelperPlugin)) {
                    if ($oViewHelperPluginManager->has($oViewHelperPlugin)) {
                        $oViewHelperPlugin = $oViewHelperPluginManager->get($oViewHelperPlugin);
                    } elseif (class_exists($oViewHelperPlugin)) {
                        $oViewHelperPlugin = new $oViewHelperPlugin();
                    } else {
                        throw new \LogicException('View helper plugin "' . $oViewHelperPlugin . '" is not a registered service or an existing class');
                    }

                    if ($oViewHelperPlugin instanceof \Laminas\View\Helper\HelperInterface) {
                        $aViewHelperPlugins[$sAssetFileType] = $oViewHelperPlugin;
                    } else {
                        throw new \LogicException('View helper plugin expects an instance of "\Laminas\View\Helper\HelperInterface", "' . get_class($oViewHelperPlugin) . '" given');
                    }
                }
            }
            $aOptions['view_helper_plugins'] = $aViewHelperPlugins;
        }

        // Unset filters
        unset($aOptions['filters']);
        return new \AssetsBundle\Service\ServiceOptions($aOptions);
    }
}
