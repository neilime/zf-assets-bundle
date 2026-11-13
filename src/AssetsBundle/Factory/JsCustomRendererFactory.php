<?php
namespace AssetsBundle\Factory;

class JsCustomRendererFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{

    /**
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     * @param \Psr\Container\ContainerInterface $oServiceLocator
     * @return \AssetsBundle\View\Renderer\JsCustomRenderer
     */
    public function __invoke(\Psr\Container\ContainerInterface $oServiceLocator, $sRequestedName, ?array $aOptions = null)
    {
        return new \AssetsBundle\View\Renderer\JsCustomRenderer();
    }
}
