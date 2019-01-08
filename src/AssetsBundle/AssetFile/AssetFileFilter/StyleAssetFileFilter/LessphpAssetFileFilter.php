<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter;

class LessphpAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
{

    /**
     * @var string
     */
    protected $assetFileFilterName = 'Lessphp';

    /**
     * @var \Less_Parser
     */
    protected $engine;

    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     */
    protected function filterContent(string $sContent, string $sFilePath) : string
    {
        // Prevents undefined $_SERVER['DOCUMENT_ROOT']
        $bSetDocumentRoot = false;
        if(!array_key_exists('DOCUMENT_ROOT', $_SERVER)){
            $_SERVER['DOCUMENT_ROOT'] = getcwd();
            $bSetDocumentRoot = true;
        }
        $sFilteredContent = $this->getEngine()->parseFile($sFilePath)->getCss();

        if($bSetDocumentRoot){
            unset($_SERVER['DOCUMENT_ROOT']);
        }

        return $sFilteredContent;
    }

    /**
     * @return \Less_Parser
     */
    protected function getEngine() : \Less_Parser
    {
        $sClassName = '\\Less_Parser';
        if (!class_exists($sClassName)) {
            throw new \LogicException('"'. $sClassName. '" class does not exist');
        }

        if (is_a($this->engine, $sClassName)) {
            $this->engine->Reset();
            return $this->engine;
        }
        
        $oEngine = new $sClassName();       
        $oEngine->SetImportDirs(array(getcwd() => getcwd()));
        return $this->engine = $oEngine;
    }
}
