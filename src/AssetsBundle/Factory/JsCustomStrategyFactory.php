<?php
namespace AssetsBundle\Factory;
class JsCustomStrategyFactory implements \Zend\ServiceManager\Factory\FactoryInterface{

	/**
	 * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
	 * @param \Interop\Container\ContainerInterface $oServiceLocator
	 * @return \AssetsBundle\View\Strategy\JsCustomStrategy
	 */
	public function __invoke(\Interop\Container\ContainerInterface $oServiceLocator, $sRequestedName, array $aOptions = null){
		$oJsCustomStrategy = new \AssetsBundle\View\Strategy\JsCustomStrategy();
		return $oJsCustomStrategy->setRouter($oServiceLocator->get('router'))->setRenderer($oServiceLocator->get('JsCustomRenderer'));
	}
}