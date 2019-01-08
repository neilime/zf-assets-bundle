<?php

namespace AssetsBundle\AssetFile;

class AssetFileFiltersManager extends \Zend\ServiceManager\AbstractPluginManager
{

    /**
     * Whether or not changes may be made to this instance.
     *
     * @param bool
     */
    protected $allowOverride = true;

    /**
     * @var \AssetsBundle\Service\ServiceOptions
     */
    protected $options;

    public function getRegisteredAssetFileFilters() : array
    {
        return array_keys($this->services);
    }

    /**
     * Validate the plugin. Checks that the filter loaded is an instance of \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface
     * @param mixed $oAssetsFilter
     * @throws \RuntimeException
     */
    public function validate($oAssetFileFilter)
    {
        if ($oAssetFileFilter instanceof \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface) {
            return;
        }
        throw new \RuntimeException(sprintf(
                'Assets Filter expects an instance of \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface, "%s" given',
            is_object($oAssetFileFilter) ? get_class($oAssetFileFilter) : (is_scalar($oAssetFileFilter) ? $oAssetFileFilter : gettype($oAssetFileFilter))
        ));
    }

    /**
     * @param string $sName
     * @param mixed $oAssetFileFilter
     * @param bool $bShared
     * @return \AssetsBundle\AssetFile\AssetFileFiltersManager
     */
    public function setService($sName, $oAssetFileFilter, $bShared = true)
    {
        if ($oAssetFileFilter) {
            $this->validate($oAssetFileFilter);
            $oAssetFileFilter->setOptions($this->getOptions());
        }
        parent::setService($sName, $oAssetFileFilter, $bShared);
        return $this;
    }

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\AssetFile\AssetFileFiltersManager
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions) : \AssetsBundle\AssetFile\AssetFileFiltersManager
    {
        $this->options = $oOptions;
        foreach ($this->services as $oAssetFileFilter) {
            $oAssetFileFilter->setOptions($oOptions);
        }
        return $this;
    }

    /**
     * @return \AssetsBundle\Service\ServiceOptions
     * @throws \LogicException
     */
    public function getOptions() : \AssetsBundle\Service\ServiceOptions
    {
        if ($this->options instanceof \AssetsBundle\Service\ServiceOptions) {
            return $this->options;
        }
        throw new \LogicException(
            'Property "options" expects an instance of "\AssetsBundle\Service\ServiceOptions", "'.(
                is_object($this->options)
                ? get_class($this->options)
                : gettype($this->options)
            ).'" defined'
        );
    }
}
