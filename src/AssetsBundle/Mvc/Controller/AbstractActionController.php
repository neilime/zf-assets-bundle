<?php

namespace AssetsBundle\Mvc\Controller;

abstract class AbstractActionController extends \Laminas\Mvc\Controller\AbstractActionController {

    /**
     * @var string
     */
    const JS_CUSTOM_ACTION = 'jscustom';

    /**
     * @param \Laminas\Mvc\MvcEvent $oEvent
     * @return mixed
     * @throws \LogicException
     */
    public function onDispatch(\Laminas\Mvc\MvcEvent $oEvent) {
        $oReturn = parent::onDispatch($oEvent);
        if ($this->params('action') === self::JS_CUSTOM_ACTION) {
            if (!is_array($oReturn)) {
                throw new \LogicException('jscustomAction return expects an array, "' . gettype($oReturn) . '" given');
            }

            /* @var $oAssetsBundleService \AssetsBundle\Service\Service */
            $oAssetsBundleService = $oEvent->getApplication()->getServiceManager()->get('AssetsBundleService');
            $oOptions = $oAssetsBundleService->getOptions();
            //Retrieve asset files manager
            $oAssetFilesCacheManager = $oAssetsBundleService->getAssetFilesManager()->getAssetFilesCacheManager();

            //Check js files
            foreach ($oReturn as &$sJsFilePath) {
                if ($sJsFilePath = $oOptions->getRealPath($sJsFilePath)) {
                    $oJsAssetFile = new \AssetsBundle\AssetFile\AssetFile(array(
                        'asset_file_type' => \AssetsBundle\AssetFile\AssetFile::ASSET_JS,
                        'asset_file_path' => $sJsFilePath
                    ));
                    // Copy js file into cache
                    $oAssetFilesCacheManager->cacheAssetFile($oJsAssetFile);
                    $sJsFilePath = $oJsAssetFile;
                } else {
                    throw new \LogicException('File "' . $sJsFilePath . '" does not exist');
                }
            }
            $oEvent->getViewModel()->setVariable('jsCustomFiles', $oReturn);
        } elseif (
                !$this->getRequest()->isXmlHttpRequest() && method_exists($this, 'jscustomAction')
        ) {
            /* @var $oAssetsBundleService \AssetsBundle\Service\Service */
            $oAssetsBundleService = $oEvent->getApplication()->getServiceManager()->get('AssetsBundleService');
            $oOptions = $oAssetsBundleService->getOptions();

            if ($oOptions->isProduction()) {
                $this->layout()->jsCustomUrl = $this->getEvent()->getRouter()->assemble(
                        array('controller' => $this->params('controller'), 'js_action' => $this->params('action')), array('name' => 'jscustom/definition')
                );
            } else {
                if ($aJsFilesPath = $this->jsCustomAction($this->params('action'))) {
                    if (!is_array($aJsFilesPath)) {
                        throw new \LogicException('Js files path expects an array, "' . gettype($aJsFilesPath) . '" given');
                    }

                    //Retrieve asset files manager
                    $oAssetFilesCacheManager = $oAssetsBundleService->getAssetFilesManager()->getAssetFilesCacheManager();

                    //Check js files
                    foreach ($aJsFilesPath as &$sJsFilePath) {
                        if ($sJsFilePath = $oOptions->getRealPath($sJsFilePath)) {
                            //Copy js file into cache
                            $oAssetFilesCacheManager->cacheAssetFile($oJsAssetFile = new \AssetsBundle\AssetFile\AssetFile(array(
                                'asset_file_type' => \AssetsBundle\AssetFile\AssetFile::ASSET_JS,
                                'asset_file_path' => $sJsFilePath
                            )));

                            $sJsFilePath = $oOptions->getAssetFileBaseUrl($oJsAssetFile, $oJsAssetFile->getAssetFileLastModified()? : time());
                        } else {
                            throw new \LogicException('File "' . $sJsFilePath . '" does not exist');
                        }
                    }
                }
                $this->layout()->jsCustomFiles = array_merge(is_array($this->layout()->jsCustomFiles) ? $this->layout()->jsCustomFiles : array(), $aJsFilesPath);
            }
        }
        $oEvent->setResult($oReturn);
        return $oReturn;
    }

}
