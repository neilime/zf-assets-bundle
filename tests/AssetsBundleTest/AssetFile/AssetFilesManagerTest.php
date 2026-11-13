<?php

namespace AssetsBundleTest\AssetFile;

class AssetFilesManagerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \AssetsBundle\AssetFile\AssetFilesManager
     */
    protected $assetFilesManager;

    /**
     * @see PHPUnit\Framework\TestCase::setUp()
     */
    public function setUp(): void
    {
        $this->assetFilesManager = new \AssetsBundle\AssetFile\AssetFilesManager();
    }

    public function testRewriteDataUrl()
    {
        $aMatches = array(
            'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAyCAYAAACd+7GKAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAClJREFUeNpi/v//vwMTAwPDfzjBgMpFI/7hFSOT9Y8qRuF3JLoHAQIMAHYtMmRA+CugAAAAAElFTkSuQmCC)',
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAyCAYAAACd+7GKAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAClJREFUeNpi/v//vwMTAwPDfzjBgMpFI/7hFSOT9Y8qRuF3JLoHAQIMAHYtMmRA+CugAAAAAElFTkSuQmCC'
        );

        $this->assertSame($aMatches[0], $this->assetFilesManager->rewriteUrl($aMatches, new \AssetsBundle\AssetFile\AssetFile()));
    }

    public function testInvalidImageFailsWithoutLeakingTheErrorHandler(): void
    {
        $filter = new \AssetsBundle\AssetFile\AssetFileFilter\ImageAssetFileFilter\PngAssetFileFilter();
        $filter->setOptions(\AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleServiceOptions'));
        $asset = new \AssetsBundle\AssetFile\AssetFile([
            'asset_file_type' => \AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA,
            'asset_file_path' => __DIR__ . '/../../_files/assets/css/test.css',
        ]);

        $this->expectException(\RuntimeException::class);
        $filter->filterAssetFile($asset);
    }

    public function tearDown(): void
    {
        \AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleToolsService')->emptyCache(false);
        parent::tearDown();
    }
}
