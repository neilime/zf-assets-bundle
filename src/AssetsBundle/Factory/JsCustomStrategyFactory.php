<?php
namespace AssetsBundle\Factory;
class JsCustomStrategyFactory implements \Laminas\ServiceManager\Factory\FactoryInterface{

	/**
	 * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
	 * @param \Psr\Container\ContainerInterface $oServiceLocator
	 * @return \AssetsBundle\View\Strategy\JsCustomStrategy
	 */
	public function __invoke(\Psr\Container\ContainerInterface $oServiceLocator, $sRequestedName, ?array $aOptions = null){
		$oJsCustomStrategy = new \AssetsBundle\View\Strategy\JsCustomStrategy();
		return $oJsCustomStrategy->setRouter($oServiceLocator->get('router'))->setRenderer($oServiceLocator->get('JsCustomRenderer'));
	}
}