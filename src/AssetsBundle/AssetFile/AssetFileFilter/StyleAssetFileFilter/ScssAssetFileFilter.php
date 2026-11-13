<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter;

class ScssAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
{

    /**
     * @var string
     */
    protected $assetFileFilterName = 'Scss';

    /**
     * Filter engine
     * @var \ScssPhp\ScssPhp\Compiler
     */
    protected $engine;
    
    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     */
    protected function filterContent(string $sContent, string $sFilePath) : string
    {
        return $this->getEngine()->compileFile($sFilePath)->getCss();
    }
    
    /**
     * @return \ScssPhp\ScssPhp\Compiler
     */
    protected function getEngine() : \ScssPhp\ScssPhp\Compiler
    {
        $sClassName = '\\ScssPhp\\ScssPhp\\Compiler';
        if (!class_exists($sClassName)) {
            throw new \LogicException('"'. $sClassName. '" class does not exist');
        }

        if (is_a($this->engine, $sClassName)) {
            return $this->engine;
        }
        
        $oEngine = new $sClassName();
        $oEngine->setImportPaths([getcwd()]);

        return $this->engine = $oEngine;
    }
}
