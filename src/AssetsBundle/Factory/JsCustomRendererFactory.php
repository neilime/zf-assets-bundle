<?php
namespace AssetsBundle\Factory;

class JsCustomRendererFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{

    /**
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     * @param \Interop\Container\ContainerInterface $oServiceLocator
     * @return \AssetsBundle\View\Renderer\JsCustomRenderer
     */
    public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null)
    {
        return new \AssetsBundle\View\Renderer\JsCustomRenderer();
    }
}
