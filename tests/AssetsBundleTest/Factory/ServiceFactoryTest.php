<?php

namespace AssetsBundleTest\Factory;

class ServiceFactoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var array
     */
    protected $configuration;

    protected $requestTimeFloat;

    /**
     * @var \AssetsBundle\Factory\ServiceFactory
     */
    protected $serviceFactory;

    /**
     * @see PHPUnit\Framework\TestCase::setUp()
     */
    public function setUp()
    {
        $this->requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->serviceFactory = new \AssetsBundle\Factory\ServiceFactory();
        $this->configuration = \AssetsBundleTest\Bootstrap::getServiceManager()->get('Config');
    }

    public function testCreateServiceWithoutBaseUrl()
    {
        $aConfiguration = $this->configuration;
        unset($aConfiguration['assets_bundle']['baseUrl']);

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $oServiceManager->setAllowOverride(true);
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride(false);

        $this->assertInstanceOf(
           '\AssetsBundle\Service\Service',
           $this->serviceFactory->__invoke(\AssetsBundleTest\Bootstrap::getServiceManager(), 'AssetsBundleService')
        );
    }

    public function testCreateServiceWithClassnameFilter()
    {
        $aConfiguration = $this->configuration;
        $aConfiguration['assets_bundle']['filters']['css'] = 'AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter\CssAssetFileFilter';

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $oServiceManager->setAllowOverride(true);
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride(false);

        $this->assertInstanceOf(
            '\AssetsBundle\Service\Service',
            $this->serviceFactory->__invoke(\AssetsBundleTest\Bootstrap::getServiceManager(), 'AssetsBundleService')
         );
    }

    public function testCreateServiceWithClassnameRendererToStrategy()
    {
        $aConfiguration = $this->configuration;
        $aConfiguration['assets_bundle']['rendererToStrategy']['zendviewrendererphprenderer'] = '\AssetsBundle\View\Strategy\ViewHelperStrategy';

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $oServiceManager->setAllowOverride(true);
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride(false);

        $this->assertInstanceOf(
           '\AssetsBundle\Service\Service',
           $this->serviceFactory->__invoke(\AssetsBundleTest\Bootstrap::getServiceManager(), 'AssetsBundleService')
        );
    }

    public function testCreateServiceWithoutAssetsPath()
    {
        $aConfiguration = $this->configuration;
        unset($aConfiguration['assets_bundle']['assetsPath']);

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $oServiceManager->setAllowOverride(true);
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride(false);
        
        $this->assertInstanceOf(
           '\AssetsBundle\Service\Service',
           $this->serviceFactory->__invoke(\AssetsBundleTest\Bootstrap::getServiceManager(), 'AssetsBundleService')
        );
    }

    public function tearDown()
    {
        $_SERVER['REQUEST_TIME_FLOAT'] = $this->requestTimeFloat;
        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $oServiceManager->setAllowOverride(true);
        $oServiceManager->setService('Config', $this->configuration);
        $oServiceManager->setAllowOverride(false);
    }
}
