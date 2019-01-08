<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter;

class LesscAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
{

    /**
     * @var string
     */
    protected $assetFileFilterName = 'Lessc';

    /**
     * Filter engine
     * @var \lessc
     */
    protected $engine;
    
    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     */
    protected function filterContent(string $sContent, string $sFilePath) : string
    {
        return $this->getEngine()->compile($sContent);
    }
    
    /**
     * @return \lessc
     */
    protected function getEngine() : \lessc
    {
        $sClassName = '\\lessc';
        if (!class_exists($sClassName)) {
            throw new \LogicException('"'. $sClassName. '" class does not exist');
        }

        if (is_a($this->engine, $sClassName)) {
            return $this->engine;
        }
        
        $oEngine = new $sClassName();
        $oEngine->addImportDir(getcwd());
        $oEngine->setAllowUrlRewrite(true);

        return $this->engine = $oEngine;
    }
}
