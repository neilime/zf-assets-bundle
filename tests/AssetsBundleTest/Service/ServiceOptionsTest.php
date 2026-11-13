<?php
namespace AssetsBundleTest\Service;

class ServiceOptionsTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \AssetsBundle\Service\ServiceOptions
     */
    protected $serviceOptions;
    
    protected $requestTimeFloat;
    
    /**
     * @see PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->serviceOptions = new \AssetsBundle\Service\ServiceOptions();
    }

    public function testIsProductionUndefined()
    {
        $this->expectException(\LogicException::class);
        $this->serviceOptions->isProduction();
    }

    public function testSetProductionWithWrongValue()
    {
        $this->expectException(\TypeError::class);
        $this->serviceOptions->setProduction(array());
    }

    public function testGetLastModifiedTimeUndefined()
    {
        $this->expectException(\LogicException::class);
        // Override lastModifiedTime
        $oReflectionClass = new \ReflectionClass('\AssetsBundle\Service\ServiceOptions');
        $oLastModifiedTimeProp = $oReflectionClass->getProperty('lastModifiedTime');
        $oLastModifiedTimeProp->setAccessible(true);
        $oLastModifiedTimeProp->setValue($this->serviceOptions, array());
        $this->serviceOptions->getLastModifiedTime();
    }

    public function testSetLastModifiedTimeWithWrongValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceOptions->setLastModifiedTime(array());
    }

    public function testGetCachePathUndefined()
    {
        $this->expectException(\LogicException::class);
        $this->serviceOptions->getCachePath();
    }

    public function testSetCachePathWithWrongValue()
    {
        $this->expectException(\TypeError::class);
        $this->serviceOptions->setCachePath(array());
    }

    public function testGetAssetsPathUndefined()
    {
        $this->expectException(\LogicException::class);
        $this->serviceOptions->getAssetsPath();
    }

    public function testGetBaseUrlUndefined()
    {
        $this->expectException(\LogicException::class);
        //Override baseUrl
        $oReflectionClass = new \ReflectionClass('\AssetsBundle\Service\ServiceOptions');
        $oBaseUrlProp = $oReflectionClass->getProperty('baseUrl');
        $oBaseUrlProp->setAccessible(true);
        $oBaseUrlProp->setValue($this->serviceOptions, array());
        $this->serviceOptions->getBaseUrl();
    }

    public function testSetBaseUrlWithWrongValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceOptions->setBaseUrl(array());
    }

    public function testGetCacheUrlUndefined()
    {
        $this->expectException(\LogicException::class);
        $this->serviceOptions->getCacheUrl();
    }

    public function testSetCacheUrlWithWrongValue()
    {
        $this->expectException(\TypeError::class);
        $this->serviceOptions->setCacheUrl(array());
    }

    public function testGetMediaExtUndefined()
    {
        $this->expectException(\LogicException::class);
        $this->serviceOptions->getMediaExt();
    }

    public function testSetMediaExtWithWrongValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceOptions->setMediaExt(array(array('wrong')));
    }

    public function testAllowsRecursiveSearchUndefined()
    {
        $this->expectException(\LogicException::class);
        $this->serviceOptions->allowsRecursiveSearch();
    }

    public function testSetRecursiveSearchWithWrongValue()
    {
        $this->expectException(\TypeError::class);
        $this->serviceOptions->setRecursiveSearch(array('wrong'));
    }

    public function testGetAssetsUndefined()
    {
        $this->expectException(\LogicException::class);
        // Override assets
        $oReflectionClass = new \ReflectionClass('\AssetsBundle\Service\ServiceOptions');
        $oAssetsProp = $oReflectionClass->getProperty('assets');
        $oAssetsProp->setAccessible(true);
        $oAssetsProp->setValue($this->serviceOptions, null);
        $this->serviceOptions->getAssets();
    }

    public function testGetRendererUndefined()
    {
        $this->expectException(\LogicException::class);
        $this->serviceOptions->getRenderer();
    }

    public function testGetModuleNameUndefined()
    {
        $this->expectException(\LogicException::class);
        // Override moduleName
        $oReflectionClass = new \ReflectionClass('\AssetsBundle\Service\ServiceOptions');
        $oModuleNameProp = $oReflectionClass->getProperty('moduleName');
        $oModuleNameProp->setAccessible(true);
        $oModuleNameProp->setValue($this->serviceOptions, null);
        $this->serviceOptions->getModuleName();
    }

    public function testGetControllerNameUndefined()
    {
        $this->expectException(\LogicException::class);
        // Override controllerName
        $oReflectionClass = new \ReflectionClass('\AssetsBundle\Service\ServiceOptions');
        $oControllerNameProp = $oReflectionClass->getProperty('controllerName');
        $oControllerNameProp->setAccessible(true);
        $oControllerNameProp->setValue($this->serviceOptions, null);
        $this->serviceOptions->getControllerName();
    }

    public function testGetActionNameUndefined()
    {
        $this->expectException(\LogicException::class);
        // Override actionName
        $oReflectionClass = new \ReflectionClass('\AssetsBundle\Service\ServiceOptions');
        $oActionNameProp = $oReflectionClass->getProperty('actionName');
        $oActionNameProp->setAccessible(true);
        $oActionNameProp->setValue($this->serviceOptions, null);
        $this->serviceOptions->getActionName();
    }
    
    public function tearDown(): void
    {
        $_SERVER['REQUEST_TIME_FLOAT'] = $this->requestTimeFloat;
    }
}
