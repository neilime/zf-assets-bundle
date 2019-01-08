<?php

namespace AssetsBundle\AssetFile;

class AssetFilesConfiguration
{

    /**
     * @var array
     */
    protected $assetFiles = array();

    /**
     * @return string
     */
    public function getConfigurationKey() : string
    {
        return $this->getOptions()->getModuleName() . '-' . $this->getOptions()->getControllerName() . '-' . $this->getOptions()->getActionName();
    }

    /**
     * @param string $sAssetFileType : (optionnal)
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getAssetFiles($sAssetFileType = null) : array
    {
        if ($sAssetFileType && !\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type "' . $sAssetFileType . '" is not valid');
        }

        //Check if assets configuration is already set
        $sConfigurationKey = $this->getConfigurationKey();
        if (isset($this->assetFiles[$sConfigurationKey])) {
            if ($sAssetFileType) {
                return $this->assetFiles[$sConfigurationKey][$sAssetFileType];
            }
            return $this->assetFiles[$sConfigurationKey];
        }

        //Define default assets
        $aAssets = $this->assetFiles[$sConfigurationKey] = array_fill_keys(
            \AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES,
            array()
        );

        // Common configuration
        $aCommonConfiguration = $this->getOptions()->getAssets();
        foreach (\AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES as $sAssetType) {
            if (!empty($aCommonConfiguration[$sAssetType]) && is_array($aCommonConfiguration[$sAssetType])) {
                $aAssets[$sAssetType] = array_merge($aAssets[$sAssetType], $aCommonConfiguration[$sAssetType]);
            }
        }

        // Module configuration
        if (isset($aCommonConfiguration[$sModuleName = $this->getOptions()->getModuleName()])) {
            $aModuleConfiguration = $aCommonConfiguration[$sModuleName];
            foreach (\AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES as $sAssetType) {
                if (!empty($aModuleConfiguration[$sAssetType]) && is_array($aModuleConfiguration[$sAssetType])) {
                    $aAssets[$sAssetType] = array_merge($aAssets[$sAssetType], $aModuleConfiguration[$sAssetType]);
                }
            }

            // Controller configuration
            if (isset($aModuleConfiguration[$sControllerName = $this->getOptions()->getControllerName()])) {
                $aControllerConfiguration = $aModuleConfiguration[$sControllerName];
                foreach (\AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES as $sAssetType) {
                    if (!empty($aControllerConfiguration[$sAssetType]) && is_array($aControllerConfiguration[$sAssetType])) {
                        $aAssets[$sAssetType] = array_merge($aAssets[$sAssetType], $aControllerConfiguration[$sAssetType]);
                    }
                }

                // Action configuration
                if (isset($aControllerConfiguration[$sActionName = $this->getOptions()->getActionName()])) {
                    $aActionConfiguration = $aControllerConfiguration[$sActionName];
                    foreach (\AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES as $sAssetType) {
                        if (!empty($aActionConfiguration[$sAssetType]) && is_array($aActionConfiguration[$sAssetType])) {
                            $aAssets[$sAssetType] = array_merge($aAssets[$sAssetType], $aActionConfiguration[$sAssetType]);
                        }
                    }
                }
            }
        }

        // bRetrieve asset files from configuration
        foreach ($aAssets as $sAssetFileTypeKey => $aAssetFiles) {
            foreach (array_unique($aAssetFiles) as $sAssetFilePath) {
                $this->addAssetFileFromOptions(is_array($sAssetFilePath) ? array_merge(array('asset_file_type' => $sAssetFileTypeKey, $sAssetFilePath)) : array('asset_file_path' => $sAssetFilePath, 'asset_file_type' => $sAssetFileTypeKey));
            }
        }

        if ($sAssetFileType) {
            return $this->assetFiles[$sConfigurationKey][$sAssetFileType];
        }
        return $this->assetFiles[$sConfigurationKey];
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return \AssetsBundle\AssetFile\AssetFilesConfiguration
     */
    public function addAssetFile(\AssetsBundle\AssetFile\AssetFile $oAssetFile) : \AssetsBundle\AssetFile\AssetFilesConfiguration
    {
        $this->assetFiles[$this->getConfigurationKey()][$oAssetFile->getAssetFileType()][$oAssetFile->getAssetFilePath()] = $oAssetFile;
        return $this;
    }

    
    /**
     * @param array $aAssetFileOptions
     * @return \AssetsBundle\AssetFile\AssetFilesConfiguration
     * @throws \InvalidArgumentException
     */
    public function addAssetFileFromOptions(array $aAssetFileOptions) : \AssetsBundle\AssetFile\AssetFilesConfiguration
    {
        if (empty($aAssetFileOptions['asset_file_type'])) {
            throw new \InvalidArgumentException('Asset file type is empty');
        }

        // Initialize asset file
        $oAssetFile = new \AssetsBundle\AssetFile\AssetFile();
        $oAssetFile->setAssetFileType($aAssetFileOptions['asset_file_type']);
        unset($aAssetFileOptions['asset_file_type']);

        // Retrieve asset file path
        if (empty($aAssetFileOptions['asset_file_path'])) {
            throw new \InvalidArgumentException('Asset file path is empty');
        }

        if (!is_string($aAssetFileOptions['asset_file_path'])) {
            throw new \InvalidArgumentException('Asset file path expects string, "' . gettype($aAssetFileOptions['asset_file_path']) . '" given');
        }

        // Retrieve asset file realpath
        $sAssetRealPath = $this->getOptions()->getRealPath($aAssetFileOptions['asset_file_path'])? : $aAssetFileOptions['asset_file_path'];

        return $this->getAssetFileFromFilePath($oAssetFile, $sAssetRealPath);
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @param string $sAssetRealPath
     * @return \AssetsBundle\AssetFile\AssetFilesConfiguration
     */
    public function getAssetFileFromFilePath(\AssetsBundle\AssetFile\AssetFile $oAssetFile, string $sAssetRealPath) : \AssetsBundle\AssetFile\AssetFilesConfiguration
    {
        if (is_dir($sAssetRealPath)) {
            foreach ($this->getAssetFilesPathFromDirectory($sAssetRealPath, $oAssetFile->getAssetFileType()) as $sChildAssetRealPath) {
                $this->getAssetFileFromFilePath($oAssetFile, $sChildAssetRealPath);
            }
            return $this;
        }
        // Handle path with wildcard
        elseif (strpos($sAssetRealPath, '*') !== false) {
            $oGlobIterator = new \GlobIterator($sAssetRealPath);
            foreach ($oGlobIterator as $oItem) {
                $this->getAssetFileFromFilePath($oAssetFile, $oItem->getRealPath());
            }
            return $this;
        }

        $oNewAssetFile = clone $oAssetFile;
        return $this->addAssetFile($oNewAssetFile->setAssetFilePath($sAssetRealPath));
    }

    /**
     * Retrieve assets from a directory
     *
     * @param  string $sDirPath
     * @param  string $sAssetType
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function getAssetFilesPathFromDirectory(string $sDirPath, string $sAssetType) : array
    {
        if (!is_string($sDirPath) || !($sDirPath = $this->getOptions()->getRealPath($sDirPath)) && !is_dir($sDirPath)) {
            throw new \InvalidArgumentException('Directory not found : ' . $sDirPath);
        }
        if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetType)) {
            throw new \InvalidArgumentException('Asset\'s type is undefined : ' . $sAssetType);
        }

        $oDirIterator = new \DirectoryIterator($sDirPath);
        $aAssets = array();
        $bRecursiveSearch = $this->getOptions()->allowsRecursiveSearch();

        // Defined expected extensions for the given type
        if ($sAssetType === \AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA) {
            $aExpectedExtensions = $this->getOptions()->getMediaExt();
        } else {
            $aExpectedExtensions = array(\AssetsBundle\AssetFile\AssetFile::getAssetFileDefaultExtension($sAssetType));
        }

        foreach ($oDirIterator as $oFile) {
            if ($oFile->isFile()) {
                if (in_array(strtolower(pathinfo($oFile->getFilename(), PATHINFO_EXTENSION)), $aExpectedExtensions, true)) {
                    $aAssets[] = $oFile->getPathname();
                }
            } elseif ($oFile->isDir() && !$oFile->isDot() && $bRecursiveSearch) {
                $aAssets = array_merge(
                        $aAssets,
                    $this->getAssetFilesPathFromDirectory($oFile->getPathname(), $sAssetType)
                );
            }
        }
        return $aAssets;
    }

    /**
     * Retrieve asset relative path
     *
     * @param string $sAssetPath
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getAssetRelativePath(string $sAssetPath) : string
    {
        if (!($sAssetRealPath = $this->getOptions()->getRealPath($sAssetPath))) {
            throw new \InvalidArgumentException('File "' . $sAssetPath . '" does not exist');
        }

        // If asset is already a cache file
        $sCachePath = $this->getOptions()->getCachePath();
        return strpos($sAssetRealPath, $sCachePath) !== false ? str_ireplace(
                        array($sCachePath, '.less'),
            array('', '.css'),
            $sAssetRealPath
                ) : (
                $this->getOptions()->hasAssetsPath() ? str_ireplace(
                                array($this->getOptions()->getAssetsPath(), getcwd(), DIRECTORY_SEPARATOR),
                    array('', '', '_'),
                    $sAssetRealPath
                        ) : str_ireplace(
                                array(getcwd(), DIRECTORY_SEPARATOR),
                            array('', '_'),
                            $sAssetRealPath
                        )
                );
    }

    /**
     * Check if assets configuration is the same as last saved configuration
     *
     * @param array $aAssetsType
     * @return bool
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function assetsConfigurationHasChanged(array $aAssetsType = null) : bool
    {
        $aAssetsType = $aAssetsType ? array_unique($aAssetsType) : \AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES;

        // Retrieve saved onfiguration file
        if (file_exists($sConfigFilePath = $this->getConfigurationFilePath())) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            $aConfig = include $sConfigFilePath;
            \Zend\Stdlib\ErrorHandler::stop(true);

            if ($aConfig === false || !is_array($aConfig)) {
                throw new \RuntimeException('Unable to get file content from file "' . $sConfigFilePath . '"');
            }

            // Get assets configuration
            $aAssets = $this->getOptions()->getAssets();

            // Check if configuration has changed for each type of asset
            foreach ($aAssetsType as $sAssetType) {
                if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetType)) {
                    throw new \LogicException('Asset type "' . $sAssetType . '" does not exist');
                }
                if (empty($aAssets[$sAssetType]) && !empty($aConfig[$sAssetType])) {
                    return true;
                }
                if (!empty($aAssets[$sAssetType])) {
                    if (empty($aConfig[$sAssetType])) {
                        return true;
                    }
                    if (
                        array_diff($aAssets[$sAssetType], $aConfig[$sAssetType])
                        || array_diff($aConfig[$sAssetType], $aAssets[$sAssetType])
                    ) {
                        return true;
                    }
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Retrieve configuration file name for the current request
     *
     * @return string
     */
    public function getConfigurationFilePath() : string
    {
        return $this->getOptions()->getProcessedDirPath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->getOptions()->getCacheFileName() . '.conf';
    }

    /**
     * Save current asset configuration into conf file
     *
     * @return \AssetsBundle\AssetFile\AssetFilesConfiguration
     */
    public function saveAssetFilesConfiguration() : \AssetsBundle\AssetFile\AssetFilesConfiguration
    {

        // Retrieve configuration file path
        $sConfigurationFilePath = $this->getConfigurationFilePath();
        $bFileExists = file_exists($sConfigurationFilePath);

        // Create dir if needed
        if (!($bFileExists = file_exists($sConfigurationFilePath)) && !is_dir($sConfigurationFileDirPath = dirname($sConfigurationFilePath))) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            mkdir($sConfigurationFileDirPath, $this->getOptions()->getDirectoriesPermissions());
            \Zend\Stdlib\ErrorHandler::stop(true);
        }

        \Zend\Stdlib\ErrorHandler::start(\E_ALL);
        file_put_contents($sConfigurationFilePath, '<?php' . PHP_EOL . 'return ' . var_export($this->getOptions()->getAssets(), 1) . ';');
        \Zend\Stdlib\ErrorHandler::stop(true);
        if (!$bFileExists) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            chmod($sConfigurationFilePath, $this->getOptions()->getFilesPermissions());
            \Zend\Stdlib\ErrorHandler::stop(true);
        }
        return $this;
    }

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\AssetFile\AssetFilesConfiguration
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions) : \AssetsBundle\AssetFile\AssetFilesConfiguration
    {
        $this->options = $oOptions;
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
