<?php

namespace AssetsBundleTest\View\Renderer;

class JsCustomRendererTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var \AssetsBundle\View\Renderer\JsCustomRenderer
     */
    protected $jsCustomRenderer;

    /**
     * @see PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void {
        $this->jsCustomRenderer = new \AssetsBundle\View\Renderer\JsCustomRenderer();
    }

    public function testGetEngine() {
        $this->assertEquals($this->jsCustomRenderer, $this->jsCustomRenderer->getEngine());
    }

    public function testSetResolver() {
        $this->assertEquals($this->jsCustomRenderer, $this->jsCustomRenderer->setResolver(\AssetsBundleTest\Bootstrap::getServiceManager()->get('ViewResolver')));
    }

    public function testRenderWithWrongViewModel() {
        $this->expectException(\InvalidArgumentException::class);
        $this->jsCustomRenderer->render('wrong');
    }

}
