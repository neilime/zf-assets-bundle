<?php

namespace AssetsBundle\AssetFile\AssetFileFilter\StyleAssetFileFilter;

class CssAssetFileFilter extends \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
{

    /**
     * @var string
     */
    protected $assetFileFilterName = \AssetsBundle\AssetFile\AssetFile::ASSET_CSS;

    /**
     * @var \tubalmartin\CssMin\Minifier
     */
    protected $engine;

    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     */
    protected function filterContent(string $sContent, string $sFilePath) : string
    {
        return $this->getEngine()->run($sContent);
    }

    /**
     * @return \tubalmartin\CssMin\Minifier
     * @throws \LogicException
     */
    protected function getEngine() : \tubalmartin\CssMin\Minifier
    {
        $sClassName = '\\tubalmartin\\CssMin\\Minifier';
        if (!class_exists($sClassName)) {
            throw new \LogicException('"'. $sClassName. '" class does not exist');
        }

        if (is_a($this->engine, $sClassName)) {
            return $this->engine;
        }
       
        return $this->engine = new $sClassName();
    }
}
