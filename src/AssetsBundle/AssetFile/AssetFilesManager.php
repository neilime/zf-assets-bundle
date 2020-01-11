<?php

namespace AssetsBundle\AssetFile;

class AssetFilesManager
{

    /**
     * @var \AssetsBundle\Service\ServiceOptions
     */
    protected $options;

    /**
     * @var \AssetsBundle\AssetFile\AssetFilesConfiguration
     */
    protected $assetFilesConfiguration;

    /**
     * @var \AssetsBundle\AssetFile\AssetFileFiltersManager
     */
    protected $assetFileFiltersManager;

    /**
     * @var \AssetsBundle\AssetFile\AssetFilesCacheManager
     */
    protected $assetFilesCacheManager;

    /**
     * @var array
     */
    protected $tmpAssetFilesPathes = array();

    /**
     * Constructor
     *
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     */
    public function __construct(\AssetsBundle\Service\ServiceOptions $oOptions = null)
    {
        if ($oOptions) {
            $this->setOptions($oOptions);
        }
    }

    /**
     * On destruction, delete all existing tmp asset files
     */
    public function __destruct()
    {
        foreach ($this->tmpAssetFilesPathes as $sTmpAssetFilePath) {
            if (file_exists($sTmpAssetFilePath)) {
                \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
                unlink($sTmpAssetFilePath);
                \Laminas\Stdlib\ErrorHandler::stop(true);
            }
        }
    }

    /**
     * @param string $sAssetFileType
     * @return array
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function getCachedAssetsFiles(string $sAssetFileType): array
    {
        if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type "' . $sAssetFileType . '" is not valid');
        }

        // Production
        if ($this->getOptions()->isProduction()) {
            $oAssetFilesCacheManager = $this->getAssetFilesCacheManager();

            // Production cached asset files do not exist
            if (!$oAssetFilesCacheManager->hasProductionCachedAssetFiles($sAssetFileType)) {
                switch ($sAssetFileType) {
                    case \AssetsBundle\AssetFile\AssetFile::ASSET_JS:
                        $this->cacheJsAssetFiles();
                        break;
                    case \AssetsBundle\AssetFile\AssetFile::ASSET_CSS:
                        $this->cacheCssAssetFiles();
                        break;
                    default:
                        throw new \DomainException('Only "' . \AssetsBundle\AssetFile\AssetFile::ASSET_JS . '" & "' . \AssetsBundle\AssetFile\AssetFile::ASSET_CSS . '" assets file type can be retrieved');
                }
            }
            return $oAssetFilesCacheManager->getProductionCachedAssetFiles($sAssetFileType);
        }

        // Development
        switch ($sAssetFileType) {
            case \AssetsBundle\AssetFile\AssetFile::ASSET_JS:
                return $this->cacheJsAssetFiles();
            case \AssetsBundle\AssetFile\AssetFile::ASSET_CSS:
                return $this->cacheCssAssetFiles();
            default:
                throw new \DomainException('Only "' . \AssetsBundle\AssetFile\AssetFile::ASSET_JS . '" & "' . \AssetsBundle\AssetFile\AssetFile::ASSET_CSS . '" assets file type can be retrieved');
        }
    }

    /**
     * Cache Css asset files and retrieve cached asset files
     *
     * @return array
     */
    protected function cacheCssAssetFiles() : array
    {

        // Cache media asset files
        $this->cacheMediaAssetFiles();

        if ($this->getOptions()->isProduction()) {
            // Retrieve asset file filters manager
            $oAssetFileFiltersManager = $this->getAssetFileFiltersManager();

            // Retrieve Css file filter if available
            $oCssFileFilter = $oAssetFileFiltersManager->has(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS) ? $oAssetFileFiltersManager->get(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS) : null;

            // Create tmp asset file
            $oTmpAssetFile = $this->createTmpAssetFile(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS);

            // Merge less asset files
            foreach ($this->cacheCompiledCssAssetFiles() as $oAssetFile) {
                $oTmpAssetFile->setAssetFileContents($this->rewriteAssetFileUrls(
                    // File content
                    $oCssFileFilter ? $oCssFileFilter->filterAssetFile($oAssetFile) : $oAssetFile->getAssetFileContents(),
                    // Current asset file
                    $oAssetFile
                ). PHP_EOL);

                // Remove temp less asset file
                \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
                unlink($oAssetFile->getAssetFilePath());
                \Laminas\Stdlib\ErrorHandler::stop(true);
            }

            // Merge css asset files
            foreach ($this->getAssetFilesConfiguration()->getAssetFiles(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS) as $oAssetFile) {
                $oTmpAssetFile->setAssetFileContents($this->rewriteAssetFileUrls(
                    // File content
                    $oCssFileFilter ? $oCssFileFilter->filterAssetFile($oAssetFile) : $oAssetFile->getAssetFileContents(),
                    // Current asset file
                    $oAssetFile
                ) . PHP_EOL);
            }

            // Cache asset file if not empty
            if ($oTmpAssetFile->getAssetFileSize()) {
                return array($this->getAssetFilesCacheManager()->cacheAssetFile($oTmpAssetFile));
            }
            return array();
        }

        // Cache less asset files
        $aAssetFiles = $this->cacheCompiledCssAssetFiles();

        // Retrieve asset files cache manager
        $oAssetFilesCacheManager = $this->getAssetFilesCacheManager();
        foreach ($this->getAssetFilesConfiguration()->getAssetFiles(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS) as $oAssetFile) {
            // Cache asset file if not empty
            if ($oAssetFile->getAssetFileSize()) {
                $aAssetFiles[] = $oAssetFilesCacheManager->cacheAssetFile($oAssetFile);
            }
        }
        return $aAssetFiles;
    }

    /**
     * Cache Less asset files and retrieve cached asset files
     *
     * @return array
     */
    protected function cacheCompiledCssAssetFiles() : array
    {
        $aCompiledAssetFiles = array();
        $bIsProduction = $this->getOptions()->isProduction();
        foreach (\AssetsBundle\AssetFile\AssetFile::COMPILED_CSS_TYPES as $sAssetType) {
            $sCompiledExtension = \AssetsBundle\AssetFile\AssetFile::getAssetFileCompiledExtension($sAssetType);

            $oTmpAssetFile = $this->createTmpAssetFile($sAssetType);
            if ($bIsProduction) {
                $oTmpAssetFile->setAssetFileType($sCompiledExtension);
            }
            
            // Retrieve Asset file cache manager;
            $oAssetFilesCacheManager = $this->getAssetFilesCacheManager();

            // Retrieve asset file cached if exists
            if (file_exists($sAssetFileCachedPath = $oAssetFilesCacheManager->getAssetFileCachePath($oTmpAssetFile))) {
                \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
                $iAssetFileCachedFilemtime = filemtime($sAssetFileCachedPath);
                \Laminas\Stdlib\ErrorHandler::stop(true);
            } else {
                $iAssetFileCachedFilemtime = null;
            }

            // Build import file
            \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
            $bIsUptoDate = !$this->getAssetFilesConfiguration()->assetsConfigurationHasChanged(array($sAssetType));

            $bHasContent = false;
            foreach ($this->getAssetFilesConfiguration()->getAssetFiles($sAssetType) as $oAssetFile) {
                if ($iAssetFileCachedFilemtime && $bIsUptoDate) {
                    $bIsUptoDate = $iAssetFileCachedFilemtime >= $oAssetFile->getAssetFileLastModified();
                }
                // If asset file is a php file, retrieve its content and add it as final file content
                if ($oAssetFile->getAssetFileExtension() === 'php') {
                    $sTmpContent = $oAssetFile->getAssetFileContents();
                }
                // Else import it in final file
                else {
                    $sTmpContent = '@import "' . str_replace(array(getcwd(), DIRECTORY_SEPARATOR), array('', '/'), $oAssetFile->getAssetFilePath()) . '";';
                }

                if ($sTmpContent) {
                    $bHasContent = true;
                    $oTmpAssetFile->setAssetFileContents($sTmpContent . PHP_EOL);
                }
            }
            \Laminas\Stdlib\ErrorHandler::stop(true);


            // If file is up to date return cached asset file
            if ($iAssetFileCachedFilemtime && $bIsUptoDate) {
                continue;
            }

            // If file is empty no need to handle it
            if (!$bHasContent) {
                continue;
            }

            // Run asset file filter
            $sAssetFileFilteredContent = $this->getAssetFileFiltersManager()->get($sAssetType)->filterAssetFile($oTmpAssetFile);
            
            // If filtered content is empty no need to handle it
            if (! $sAssetFileFilteredContent) {
                continue;
            }

            // Create compiled asset file
            if (isset($aCompiledAssetFiles[$sCompiledExtension])) {
                $oCompiledAssetFile = $aCompiledAssetFiles[$sCompiledExtension];
            } else {
                $oCompiledAssetFile = $aCompiledAssetFiles[$sCompiledExtension] = $this->createTmpAssetFile($sAssetType);
                $oCompiledAssetFile->setAssetFileType($oTmpAssetFile->getAssetFileType());
            }

            // Set new content to tmp asset file
            $oCompiledAssetFile->setAssetFileContents($sAssetFileFilteredContent, true);
        }

        $aReturn = array();
        foreach ($aCompiledAssetFiles as $sCompiledExtension => $oCompiledAssetFile) {
            $oCachedAssetFile = $this->getAssetFilesCacheManager()->cacheAssetFile($oCompiledAssetFile);
            $aReturn[] = $oCachedAssetFile->setAssetFileType($sCompiledExtension);
        }

        return $aReturn;
    }

    /**
     * Cache Js asset files and retrieve cached asset files
     *
     * @return array
     */
    protected function cacheJsAssetFiles() : array
    {
        if ($this->getOptions()->isProduction()) {
            // Retrieve asset file filters manager
            $oAssetFileFiltersManager = $this->getAssetFileFiltersManager();

            // Retrieve Js asset file filter if available
            $oJsFileFilter = $oAssetFileFiltersManager->has(\AssetsBundle\AssetFile\AssetFile::ASSET_JS) ? $oAssetFileFiltersManager->get(\AssetsBundle\AssetFile\AssetFile::ASSET_JS) : null;

            // Create tmp asset file
            $oTmpAssetFile = $this->createTmpAssetFile(\AssetsBundle\AssetFile\AssetFile::ASSET_JS);

            foreach ($this->getAssetFilesConfiguration()->getAssetFiles(\AssetsBundle\AssetFile\AssetFile::ASSET_JS) as $oAssetFile) {
                $sAssetFileContent = $oJsFileFilter ? $oJsFileFilter->filterAssetFile($oAssetFile) : $oAssetFile->getAssetFileContents();
                $oTmpAssetFile->setAssetFileContents($sAssetFileContent . PHP_EOL);
            }
            if ($oTmpAssetFile->getAssetFileSize()) {
                return array($this->getAssetFilesCacheManager()->cacheAssetFile($oTmpAssetFile));
            }
            return array();
        }

        // Retrieve asset files cache manager
        $oAssetFilesCacheManager = $this->getAssetFilesCacheManager();
        $aAssetFiles = array();
        foreach ($this->getAssetFilesConfiguration()->getAssetFiles(\AssetsBundle\AssetFile\AssetFile::ASSET_JS) as $oAssetFile) {
            // Cache asset file if not empty
            if ($oAssetFile->getAssetFileSize()) {
                $aAssetFiles[] = $oAssetFilesCacheManager->cacheAssetFile($oAssetFile);
            }
        }
        return $aAssetFiles;
    }

    /**
     * Cache media asset files and retrieve cached asset files
     *
     * @return array
     */
    protected function cacheMediaAssetFiles() : array
    {
        $aAssetFileFilters = array();
        $aAssetFiles = array();

        //Retrieve asset files cache manager
        $oAssetFilesCacheManager = $this->getAssetFilesCacheManager();

        // Retrieve asset file filters manager
        $oAssetFileFiltersManager = $this->getAssetFileFiltersManager();
        $bIsProduction = $this->getOptions()->isProduction();

        foreach ($this->getAssetFilesConfiguration()->getAssetFiles(\AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA) as $oAssetFile) {
            if ($oAssetFilesCacheManager->isAssetFileCached($oAssetFile)) {
                $aAssetFiles[] = $oAssetFilesCacheManager->getAssetFileCachePath($oAssetFile);
                continue;
            }
            if ($bIsProduction) {
                if (array_key_exists($sAssetFileExtension = $oAssetFile->getAssetFileExtension(), $aAssetFileFilters)) {
                    $oMediaFileFilter = $aAssetFileFilters[$sAssetFileExtension];
                } else {
                    $oMediaFileFilter = $aAssetFileFilters[$sAssetFileExtension] = $oAssetFileFiltersManager->has($sAssetFileExtension) ? $oAssetFileFiltersManager->get($sAssetFileExtension) : null;
                }
            } else {
                $oMediaFileFilter = null;
            }

            if ($oMediaFileFilter) {
                $oTmpAssetFile = $this->createTmpAssetFile(\AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA);
                $oTmpAssetFile->setAssetFileContents($oMediaFileFilter->filterAssetFile($oAssetFile), false);

                // Cache asset file
                $aAssetFiles[] = $oAssetFilesCacheManager->cacheAssetFile($oTmpAssetFile, $oAssetFile);
            } else {
                // Cache asset file
                $aAssetFiles[] = $oAssetFilesCacheManager->cacheAssetFile($oAssetFile);
            }
        }

        return $aAssetFiles;
    }

    /**
     * Rewrite url of an asset file content to match with cache path if needed
     * @param string $sAssetFileContent
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return string
     */
    public function rewriteAssetFileUrls(string $sAssetFileContent, \AssetsBundle\AssetFile\AssetFile $oAssetFile) : string
    {
        // Callback for url rewriting
        $oRewriteUrlCallback = array($this, 'rewriteUrl');
        return preg_replace_callback('/url\(([^\)]+)\)/', function ($aMatches) use ($oAssetFile, $oRewriteUrlCallback) {
            return call_user_func($oRewriteUrlCallback, $aMatches, $oAssetFile);
        }, $sAssetFileContent);
    }

    /**
     * Rewrite url to match with cache path if needed
     *
     * @param array $aMatches
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return string
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function rewriteUrl(array $aMatches, \AssetsBundle\AssetFile\AssetFile $oAssetFile): string
    {
        if (!isset($aMatches[1])) {
            throw new \InvalidArgumentException('Url match is not valid');
        }

        // Remove quotes & double quotes from url
        $aFirstCharMatches = null;
        $sFirstChar = preg_match('/^("|\'){1}/', $sUrl = trim($aMatches[1]), $aFirstCharMatches) ? $aFirstCharMatches[1] : '';
        $sUrl = str_ireplace(array('"', '\''), '', $sUrl);

        // Data url
        if (strpos($sUrl, 'data:') === 0) {
            return $aMatches[0];
        }

        // Remote absolute url
        if (preg_match('/^http/', $sUrl)) {
            return $aMatches[0];
        }

        // Split arguments
        if (strpos($sUrl, '?') !== false) {
            list($sUrl, $sArguments) = explode('?', $sUrl);
        }

        // Split anchor
        if (strpos($sUrl, '#') !== false) {
            list($sUrl, $sAnchor) = explode('#', $sUrl);
        }

        // Absolute url
        if (($sUrlRealpath = $this->getOptions()->getRealPath($sUrl, $oAssetFile))) {
            // Initialize asset file from url
            $oUrlAssetFile = new \AssetsBundle\AssetFile\AssetFile(array(
                'asset_file_type' => \AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA,
                'asset_file_path' => $sUrlRealpath
            ));

            $sAssetFileCachePath = $this->getAssetFilesCacheManager()->getAssetFileCachePath($oUrlAssetFile);
            if (!file_exists($sAssetFileCachePath)) {
                throw new \LogicException('Media file "' . $oUrlAssetFile->getAssetFilePath() . '" used by "' . $oAssetFile->getAssetFilePath() . '" does not have been cached. Please add it into ["assets_bundle"]["assets"]["media"] configuration array');
            }

            // Define cached file path
            $oUrlAssetFile->setAssetFilePath($sAssetFileCachePath);

            // Retrieve asset file base url
            $sAssetFileBaseUrl = $this->getOptions()->getAssetFileBaseUrl($oUrlAssetFile);

            // Add argument and / or anchor to asset file base url
            $sAssetFileRealBaseUrl = $sFirstChar . $sAssetFileBaseUrl . (empty($sArguments) ? '' : '?' . $sArguments) . (empty($sAnchor) ? '' : '#' . $sAnchor) . $sFirstChar;

            // Return asset file base url
            return str_replace($aMatches[1], $sAssetFileRealBaseUrl, $aMatches[0]);
        } // Remote relative url
        elseif ($oAssetFile->isAssetFilePathUrl()) {
            return str_replace($aMatches[1], $sFirstChar . dirname($oAssetFile->getAssetFilePath()) . '/' . ltrim($sUrl, '/') . $sFirstChar, $aMatches[0]);
        } // Url is not an exising file
        else {
            throw new \LogicException('Url file "' . $sUrl . '" does not exist even relative with "' . $oAssetFile->getAssetFilePath() . '"');
        }
    }

    /**
     * @param string $sAssetFileType
     * @return \AssetsBundle\AssetFile\AssetFile
     * @throws \InvalidArgumentException
     */
    protected function createTmpAssetFile(string $sAssetFileType) : \AssetsBundle\AssetFile\AssetFile
    {
        if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type "' . $sAssetFileType . '" is not valid');
        }
        // Create tmp asset file
        \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
        $sTmpAssetFilePath = tempnam(
            $this->getOptions()->getTmpDirPath(),
            $sAssetFileType . '_' . uniqid()
        );
        \Laminas\Stdlib\ErrorHandler::stop(true);
        $this->tmpAssetFilesPathes[] = $sTmpAssetFilePath;
        return new \AssetsBundle\AssetFile\AssetFile([
            'asset_file_type' => $sAssetFileType,
            'asset_file_path' => $sTmpAssetFilePath,
        ]);
    }

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\AssetFile\AssetFilesManager
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions) : \AssetsBundle\AssetFile\AssetFilesManager
    {
        $this->options = $oOptions;
        if (isset($this->assetFilesConfiguration)) {
            $this->getAssetFilesConfiguration()->setOptions($this->options);
        }
        if (isset($this->assetFileFiltersManager)) {
            $this->getAssetFileFiltersManager()->setOptions($this->options);
        }
        if (isset($this->assetFilesCacheManager)) {
            $this->getAssetFilesCacheManager()->setOptions($this->options);
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

    /**
     * Set the asset files configuration
     *
     * @param  \AssetsBundle\AssetFile\AssetFilesConfiguration $oAssetFilesConfiguration
     * @return \AssetsBundle\AssetFile\AssetFilesManager
     */
    public function setAssetFilesConfiguration(\AssetsBundle\AssetFile\AssetFilesConfiguration $oAssetFilesConfiguration) : \AssetsBundle\AssetFile\AssetFilesManager
    {
        $this->assetFilesConfiguration = $oAssetFilesConfiguration->setOptions($this->getOptions());
        return $this;
    }

    /**
     * Retrieve the asset files configuration. Lazy loads an instance if none currently set.
     *
     * @return \AssetsBundle\AssetFile\AssetFilesConfiguration
     */
    public function getAssetFilesConfiguration() : \AssetsBundle\AssetFile\AssetFilesConfiguration
    {
        if (!$this->assetFilesConfiguration instanceof \AssetsBundle\AssetFile\AssetFilesConfiguration) {
            $this->setAssetFilesConfiguration(new \AssetsBundle\AssetFile\AssetFilesConfiguration());
        }
        return $this->assetFilesConfiguration;
    }

    /**
     * Check if AssetFileFiltersManager is defined
     *
     * @return bool
     */
    public function hasAssetFileFiltersManager() : bool
    {
        return $this->assetFileFiltersManager instanceof \AssetsBundle\AssetFile\AssetFileFiltersManager;
    }
    
    /**
     * Retrieve the asset file filters manager. Lazy loads an instance if none currently set.
     *
     * @return \AssetsBundle\AssetFile\AssetFileFiltersManager
     * @throws \LogicException
     */
    public function getAssetFileFiltersManager() : \AssetsBundle\AssetFile\AssetFileFiltersManager
    {
        if (!$this->assetFileFiltersManager instanceof \AssetsBundle\AssetFile\AssetFileFiltersManager) {
            throw new \LogicException(
                'Property "assetFileFiltersManager" expects an instance of "\AssetsBundle\AssetFile\AssetFileFiltersManager", "'.(
                    is_object($this->assetFileFiltersManager)
                    ? get_class($this->assetFileFiltersManager)
                    : gettype($this->assetFileFiltersManager)
                ).'" defined'
            );
        }
        return $this->assetFileFiltersManager;
    }

    /**
     * Set the asset file filters manager
     *
     * @param \AssetsBundle\AssetFile\AssetFileFiltersManager $oAssetFileFiltersManager
     * @return \AssetsBundle\AssetFile\AssetFilesManager
     */
    public function setAssetFileFiltersManager(\AssetsBundle\AssetFile\AssetFileFiltersManager $oAssetFileFiltersManager) : \AssetsBundle\AssetFile\AssetFilesManager
    {
        $this->assetFileFiltersManager = $oAssetFileFiltersManager->setOptions($this->getOptions());
        return $this;
    }

    /**
     * Retrieve the asset files cache manager. Lazy loads an instance if none currently set.
     *
     * @return \AssetsBundle\AssetFile\AssetFilesCacheManager
     */
    public function getAssetFilesCacheManager() : \AssetsBundle\AssetFile\AssetFilesCacheManager
    {
        if (!$this->assetFilesCacheManager instanceof \AssetsBundle\AssetFile\AssetFilesCacheManager) {
            $this->setAssetFilesCacheManager(new \AssetsBundle\AssetFile\AssetFilesCacheManager());
        }
        return $this->assetFilesCacheManager;
    }

    /**
     * Set the asset files cache manager
     *
     * @param \AssetsBundle\AssetFile\AssetFilesCacheManager $oAssetFilesCacheManager
     * @return \AssetsBundle\AssetFile\AssetFilesManager
     */
    public function setAssetFilesCacheManager(\AssetsBundle\AssetFile\AssetFilesCacheManager $oAssetFilesCacheManager)
    {
        $this->assetFilesCacheManager = $oAssetFilesCacheManager->setOptions($this->getOptions());
        return $this;
    }
}
