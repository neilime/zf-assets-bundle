<?php
declare(strict_types = 1);

namespace AssetsBundle\Service;

class ServiceOptions extends \Laminas\Stdlib\AbstractOptions
{
    const NO_MODULE = 'no_module';
    const NO_ACTION = 'no_action';
    const NO_CONTROLLER = 'no_controller';

    /**
     * Application environment (Developpement => false)
     * @var boolean
     */
    protected $production;

    /**
     * Arbitrary last modified time in production
     * @var scalable|null
     */
    protected $lastModifiedTime;

    /**
     * Cache directory absolute path
     * @var string
     */
    protected $cachePath;

    /**
     * Assets directory absolute path (allows you to define relative path for assets config)
     * @var string
     */
    protected $assetsPath;

    /**
     * Temp directory absolute path
     * @var string
     */
    protected $tmpDirPath;

    /**
     * Processed files directory absolute path
     * @var string
     */
    protected $processedDirPath;

    /**
     * Base URL of the application
     * @var string
     */
    protected $baseUrl = '';

    /**
     * Cache directory base url
     * @var string
     */
    protected $cacheUrl;

    /**
     * Media extensions to be cached
     * @var array
     */
    protected $mediaExt;

    /**
     * Allows search for matching assets in required folder and its subfolders
     * @var boolean
     */
    protected $recursiveSearch;

    /**
     * Permissions for created files
     * @var integer
     */
    protected $filesPermissions;

    /**
     * Permissions for created directories
     * @var integer
     */
    protected $directoriesPermissions;

    /**
     * Required assets
     * @var array
     */
    protected $assets = array();

    /**
     * Assets renderer
     * @var \Laminas\View\Renderer\RendererInterface
     */
    protected $renderer;

    /**
     * Current module name
     * @var string
     */
    protected $moduleName = self::NO_MODULE;

    /**
     * Current controller name
     * @var string
     */
    protected $controllerName = self::NO_CONTROLLER;

    /**
     * Current action name
     * @var string
     */
    protected $actionName = self::NO_ACTION;

    /**
     * Disabled contexts
     * @var array
     */
    protected $disabledContexts = array();

    /**
     * View helper plugins by asset file types
     * @var array
     */
    protected $view_helper_plugins = array();

    /**
     * Store resolved real paths
     * @var array
     */
    protected $resolvedPaths = array();

    /**
     * Store open basedir allowed paths
     * @var array
     */
    protected $openBaseDirPaths = null;

    /**
     * @param bool $bProduction
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setProduction(bool $bProduction) : \AssetsBundle\Service\ServiceOptions
    {
        if (is_bool($bProduction)) {
            $this->production = $bProduction;
            return $this;
        }
        throw new \InvalidArgumentException('Argument "$bProduction" option expects a bool, "' . gettype($bProduction) . '" given');
    }

    /**
     * @throws \LogicException
     * @return bool
     */
    public function isProduction() : bool
    {
        if (is_bool($this->production)) {
            return $this->production;
        }
        throw new \LogicException('"Production" option is undefined');
    }

    /**
     * @param scalar|null $sLastModifiedTime
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setLastModifiedTime($sLastModifiedTime = null)
    {
        if (is_scalar($sLastModifiedTime) || is_null($sLastModifiedTime)) {
            $this->lastModifiedTime = $sLastModifiedTime;
            return $this;
        }
        throw new \InvalidArgumentException('"Last modified time" option expects a scalable value, "' . gettype($sLastModifiedTime) . '" given');
    }

    /**
     * @throws \LogicException
     * @return scalar|null
     */
    public function getLastModifiedTime()
    {
        if (is_scalar($this->lastModifiedTime) || is_null($this->lastModifiedTime)) {
            return $this->lastModifiedTime;
        }
        throw new \LogicException('"Last modified time" option is undefined');
    }

    /**
     * @param string $sCachePath
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setCachePath(string $sCachePath) : \AssetsBundle\Service\ServiceOptions
    {
        if (!is_string($sCachePath)) {
            throw new \InvalidArgumentException('Argument "$sCachePath" option expects a bool, "' . gettype($sCachePath) . '" given');
        }

        if (!($sRealCachePath = $this->getRealPath($sCachePath))  || !is_dir($sRealCachePath)) {
            throw new \InvalidArgumentException('Argument "$sCachePath" expects a valid directory path, "' . $sCachePath . '" given');
        }

        if (is_writable($sRealCachePath)) {
            $this->cachePath = $sRealCachePath;
            return $this;
        }
        throw new \InvalidArgumentException('Cache path directory "' . $sRealCachePath . '" is not writable');
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function getCachePath() : string
    {
        if (is_string($this->cachePath)) {
            return $this->cachePath;
        }
        throw new \LogicException(
            'Property "cachePath" expects a string, "'.(
                is_object($this->cachePath)
                ? get_class($this->cachePath)
                : gettype($this->cachePath)
        ).'" defined'
        );
    }

    /**
     * @param string|null $sAssetsPath
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setAssetsPath(string $sAssetsPath = null) : \AssetsBundle\Service\ServiceOptions
    {
        if (is_null($sAssetsPath)) {
            $this->assetsPath = null;
            return $this;
        }
        
        if (is_dir($sAssetsRealPath = $this->getRealPath($sAssetsPath))) {
            $this->assetsPath = $sAssetsRealPath;
            return $this;
        }
        throw new \InvalidArgumentException('"assetsPath" config expects a valid directory path, "' . $sAssetsRealPath . '" given');
    }

    /**
     * @return bool
     */
    public function hasAssetsPath(): bool
    {
        return is_string($this->assetsPath);
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function getAssetsPath() : string
    {
        if ($this->hasAssetsPath()) {
            return $this->assetsPath;
        }
        throw new \LogicException('"Assets path" option is undefined');
    }

    /**
     * @param string $sTmpDirPath
     * @return \AssetsBundle\Service\ServiceOptions
     * @throws \InvalidArgumentException
     */
    public function setTmpDirPath(string $sTmpDirPath) : \AssetsBundle\Service\ServiceOptions
    {
        if (($sTmpDirRealPath = $this->getRealPath($sTmpDirPath)) && is_dir($sTmpDirRealPath) && is_writable($sTmpDirRealPath)) {
            $this->tmpDirPath = $sTmpDirRealPath;
            return $this;
        }
        throw new \InvalidArgumentException('"Temp dir path" option "' . $sTmpDirPath . '" is not writable directory path');
    }

    /**
     * @return string
     * @throws \LogicException
     */
    public function getTmpDirPath() : string
    {
        if (is_dir($this->tmpDirPath) && is_writable($this->tmpDirPath)) {
            return $this->tmpDirPath;
        }
        throw new \LogicException('"Temp dir path" option is undefined');
    }

    /**
     * @param string $sProcessedDirPath
     * @return \AssetsBundle\Service\ServiceOptions
     * @throws \InvalidArgumentException
     */
    public function setProcessedDirPath(string $sProcessedDirPath) : \AssetsBundle\Service\ServiceOptions
    {
        if (($sProcessedDirRealPath = $this->getRealPath($sProcessedDirPath)) && is_dir($sProcessedDirRealPath) && is_writable($sProcessedDirRealPath)) {
            $this->processedDirPath = $sProcessedDirRealPath;
            return $this;
        }
        throw new \InvalidArgumentException('"Processed dir path" option "' . $sProcessedDirPath . '" is not writable directory path');
    }

    /**
     * @return string
     * @throws \LogicException
     */
    public function getProcessedDirPath() : string
    {
        if (is_dir($this->processedDirPath) && is_writable($this->processedDirPath)) {
            return $this->processedDirPath;
        }
        throw new \LogicException('"Processed dir path" option is undefined');
    }

    /**
     * @param string $sBaseUrl
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setBaseUrl($sBaseUrl)
    {
        if (is_string($sBaseUrl)) {
            $this->baseUrl = rtrim($sBaseUrl, '/');
            return $this;
        }
        throw new \InvalidArgumentException('"Base url" option expects a string, "' . gettype($sBaseUrl) . '" given');
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function getBaseUrl() : string
    {
        if (is_string($this->baseUrl)) {
            return $this->baseUrl;
        }
        throw new \LogicException('"Base url" option is undefined');
    }

    /**
     * @param string $sCacheUrl
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setCacheUrl(string $sCacheUrl) : \AssetsBundle\Service\ServiceOptions
    {
        if (strpos($sCacheUrl, '@zfBaseUrl') !== false) {
            $sCacheUrl = $this->getBaseUrl() . '/' . ltrim(str_ireplace('@zfBaseUrl', '', $sCacheUrl), '/');
        }
        $this->cacheUrl = $sCacheUrl;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function getCacheUrl() : string
    {
        if (is_string($this->cacheUrl)) {
            return $this->cacheUrl;
        }
        throw new \LogicException('"Cache url" option is undefined');
    }

    /**
     * @param array $aMediaExt
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setMediaExt(array $aMediaExt) : \AssetsBundle\Service\ServiceOptions
    {
        $this->mediaExt = array();
        foreach (array_unique($aMediaExt) as $sMediaExt) {
            if (empty($sMediaExt)) {
                throw new \InvalidArgumentException('Media extension is empty');
            }
            if (is_string($sMediaExt)) {
                $this->mediaExt[] = $sMediaExt;
            } else {
                throw new \InvalidArgumentException('"Media extension" expects a string, "' . gettype($sMediaExt) . '" given');
            }
        }
        return $this;
    }

    /**
     * @throws \LogicException
     * @return array
     */
    public function getMediaExt() : array
    {
        if (is_array($this->mediaExt)) {
            return $this->mediaExt;
        }
        throw new \LogicException('"Media extensions" option is undefined');
    }

    /**
     * @param bool $bRecursiveSearch
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setRecursiveSearch(bool $bRecursiveSearch) : \AssetsBundle\Service\ServiceOptions
    {
        if (is_bool($bRecursiveSearch)) {
            $this->recursiveSearch = $bRecursiveSearch;
            return $this;
        }
        throw new \InvalidArgumentException('"Recursive search" option expects a boolean, "' . gettype($bRecursiveSearch) . '" given');
    }

    /**
     * @throws \LogicException
     * @return bool
     */
    public function allowsRecursiveSearch() : bool
    {
        if (is_bool($this->recursiveSearch)) {
            return $this->recursiveSearch;
        }
        throw new \LogicException('"Recursive search" option is undefined');
    }

    /**
     * @return int
     * @throws \LogicException
     */
    public function getFilesPermissions() : int
    {
        if (is_integer($this->filesPermissions)) {
            return $this->filesPermissions;
        }
        throw new \LogicException('Property "filesPermissions" expects an integer, "' . (is_object($this->filesPermissions) ? get_class($this->filesPermissions) : gettype($this->filesPermissions)) . '" defined');
    }

    /**
     * @param int $iFilesPermissions
     * @return \AssetsBundle\Service\ServiceOptions
     * @throws \InvalidArgumentException
     */
    public function setFilesPermissions(int $iFilesPermissions) : \AssetsBundle\Service\ServiceOptions
    {
        if (is_integer($iFilesPermissions)) {
            $this->filesPermissions = $iFilesPermissions;
            return $this;
        }
        throw new \InvalidArgumentException('Argument "$iFilesPermissions" expects an integer, "' . (is_object($iFilesPermissions) ? get_class($iFilesPermissions) : gettype($iFilesPermissions)) . '" given');
    }

    /**
     * @return int
     * @throws \LogicException
     */
    public function getDirectoriesPermissions() : int
    {
        if (is_integer($this->directoriesPermissions)) {
            return $this->directoriesPermissions;
        }
        throw new \LogicException('Property "directoriesPermissions" expects an integer, "' . (is_object($this->directoriesPermissions) ? get_class($this->directoriesPermissions) : gettype($this->directoriesPermissions)) . '" defined');
    }

    /**
     * @param int $iDirectoriesPermissions
     * @return \AssetsBundle\Service\ServiceOptions
     * @throws \InvalidArgumentException
     */
    public function setDirectoriesPermissions(int $iDirectoriesPermissions) : \AssetsBundle\Service\ServiceOptions
    {
        if (is_integer($iDirectoriesPermissions)) {
            $this->directoriesPermissions = $iDirectoriesPermissions;
            return $this;
        }
        throw new \InvalidArgumentException('Argument "$iDirectoriesPermissions" expects an integer, "' . (is_object($iDirectoriesPermissions) ? get_class($iDirectoriesPermissions) : gettype($iDirectoriesPermissions)) . '" given');
    }

    /**
     * @param array $aAssets
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setAssets(array $aAssets) : \AssetsBundle\Service\ServiceOptions
    {
        $this->assets = $aAssets;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return array
     */
    public function getAssets() : array
    {
        if (is_array($this->assets)) {
            return $this->assets;
        }
        throw new \LogicException('"Assets" option is undefined');
    }

    /**
     * @param \Laminas\View\Renderer\RendererInterface $oRenderer
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setRenderer(\Laminas\View\Renderer\RendererInterface $oRenderer) : \AssetsBundle\Service\ServiceOptions
    {
        $this->renderer = $oRenderer;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return \Laminas\View\Renderer\RendererInterface
     */
    public function getRenderer() : \Laminas\View\Renderer\RendererInterface
    {
        if ($this->renderer instanceof \Laminas\View\Renderer\RendererInterface) {
            return $this->renderer;
        }
        throw new \LogicException('"Renderer" option is undefined');
    }

    /**
     * @param string $sModuleName
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setModuleName(string $sModuleName) : \AssetsBundle\Service\ServiceOptions
    {
        if (empty($sModuleName)) {
            throw new \InvalidArgumentException('"Module name" option is empty');
        }
        if (!is_string($sModuleName)) {
            throw new \InvalidArgumentException('"Module name" option expects a string, "' . gettype($sModuleName) . '" given');
        }
        $this->moduleName = $sModuleName;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function getModuleName() : string
    {
        if (is_string($this->moduleName)) {
            return $this->moduleName;
        }
        throw new \LogicException('"Module name" option is undefined');
    }

    /**
     * @param string $sControllerName
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setControllerName(string $sControllerName) : \AssetsBundle\Service\ServiceOptions
    {
        if (empty($sControllerName)) {
            throw new \InvalidArgumentException('"Controller name" option is empty');
        }
        if (!is_string($sControllerName)) {
            throw new \InvalidArgumentException('"Controller name" option expects a string, "' . gettype($sControllerName) . '" given');
        }
        $this->controllerName = $sControllerName;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function getControllerName() : string
    {
        if (is_string($this->controllerName)) {
            return $this->controllerName;
        }
        throw new \LogicException('"Controller name" option is undefined');
    }

    /**
     * @param string $sActionName
     * @throws \InvalidArgumentException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setActionName(string $sActionName) : \AssetsBundle\Service\ServiceOptions
    {
        if (empty($sActionName)) {
            throw new \InvalidArgumentException('"Action name" option is empty');
        }
        if (!is_string($sActionName)) {
            throw new \InvalidArgumentException('"Action name" option expects a string, "' . gettype($sActionName) . '" given');
        }
        $this->actionName = $sActionName;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function getActionName() : string
    {
        if (is_string($this->actionName)) {
            return $this->actionName;
        }
        throw new \LogicException('"Action name" option is undefined');
    }

    /**
     * @param array $aDisabledContexts
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setDisabledContexts(array $aDisabledContexts)
    {
        $this->disabledContexts = $aDisabledContexts;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAssetsBundleDisabled() : bool
    {
        if (isset($this->disabledContexts[$sModuleName = $this->getModuleName()])) {
            if (is_bool($this->disabledContexts[$sModuleName])) {
                return $this->disabledContexts[$sModuleName];
            }
            if (isset($this->disabledContexts[$sModuleName][$sControllerName = $this->getControllerName()])) {
                if (is_bool($this->disabledContexts[$sModuleName][$sControllerName])) {
                    return $this->disabledContexts[$sModuleName][$sControllerName];
                }
                if (isset($this->disabledContexts[$sModuleName][$sControllerName][$sActionName = $this->getActionName()])) {
                    if (is_bool($this->disabledContexts[$sModuleName][$sControllerName][$sActionName])) {
                        return $this->disabledContexts[$sModuleName][$sControllerName][$sActionName];
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param array $aViewHelperPlugins
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function setViewHelperPlugins(array $aViewHelperPlugins) : \AssetsBundle\Service\ServiceOptions
    {
        $this->view_helper_plugins = $aViewHelperPlugins;
        return $this;
    }

    /**
     * @return array
     * @throws \LogicException
     */
    public function getViewHelperPlugins() : array
    {
        if (is_array($this->view_helper_plugins)) {
            return $this->view_helper_plugins;
        }
        throw new \LogicException('View helper plugins are undefined');
    }

    /**
     * @param string $sAssetFileType
     * @return \Laminas\View\Helper\HelperInterface
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function getViewHelperPluginForAssetFileType(string $sAssetFileType) : \Laminas\View\Helper\HelperInterface
    {
        if (\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
            if (!is_array($this->view_helper_plugins)) {
                throw new \LogicException('View helper plugins are undefined');
            }

            if (isset($this->view_helper_plugins[$sAssetFileType]) && $this->view_helper_plugins[$sAssetFileType] instanceof \Laminas\View\Helper\HelperInterface) {
                return $this->view_helper_plugins[$sAssetFileType];
            }
            throw new \InvalidArgumentException('View helper plugin for asset file type "' . $sAssetFileType . '" is undefined');
        }
    }

    /**
     * Try to retrieve realpath for a given path (manage @zfRootPath)
     * @param string $sPathToResolve
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function getRealPath(string $sPathToResolve, \AssetsBundle\AssetFile\AssetFile $oAssetFile = null) : ?string
    {
        if (!is_string($sPathToResolve)) {
            throw new \InvalidArgumentException('Argument "$sPathToResolve" expects a string, "' . (is_object($sPathToResolve) ? get_class($sPathToResolve) : gettype($sPathToResolve)) . '" given');
        }
        if (!$sPathToResolve) {
            throw new \InvalidArgumentException('Argument "$sPathToResolve" is empty');
        }

        // Define resolved paths key
        $sResolvedPathsKey = ($oAssetFile ? $oAssetFile->getAssetFilePath() . '_' : '') . $sPathToResolve;

        if (isset($this->resolvedPaths[$sResolvedPathsKey])) {
            return $this->resolvedPaths[$sResolvedPathsKey];
        }
        // If path is "/", assets path is prefered
        if ($sPathToResolve === DIRECTORY_SEPARATOR && $this->hasAssetsPath()) {
            return $this->resolvedPaths[$sResolvedPathsKey] = $this->getAssetsPath();
        }

        // Path is absolute
        if (strpos($sPathToResolve, '@zfRootPath') !== false) {
            $sPathToResolve = str_ireplace('@zfRootPath', getcwd(), $sPathToResolve);
        }
        if (strpos($sPathToResolve, '@zfAssetsPath') !== false) {
            $sPathToResolve = str_ireplace('@zfAssetsPath', $this->getAssetsPath(), $sPathToResolve);
        }

        if (($sRealPath = realpath($sPathToResolve)) !== false) {
            return $this->resolvedPaths[$sResolvedPathsKey] = $sRealPath;
        }

        // Try to define real path with given asset file path
        if ($oAssetFile && $this->safeFileExists($sRealPath = dirname($oAssetFile->getAssetFilePath()) . DIRECTORY_SEPARATOR . $sPathToResolve)) {
            return $this->resolvedPaths[$sResolvedPathsKey] = realpath($sRealPath);
        }

        // Try to guess real path with root path or asset path (if defined)
        if ($this->hasAssetsPath() && $this->safeFileExists($sRealPath = $this->getAssetsPath() . DIRECTORY_SEPARATOR . $sPathToResolve)) {
            return $this->resolvedPaths[$sResolvedPathsKey] = realpath($sRealPath);
        }

        if ($this->safeFileExists($sRealPath = getcwd() . DIRECTORY_SEPARATOR . $sPathToResolve)) {
            return $this->resolvedPaths[$sResolvedPathsKey] = realpath($sRealPath);
        }
        return null;
    }

    /**
     * Check if file exists, only search in "open_basedir" path if defined
     * @param string $sFilePath
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function safeFileExists(string $sFilePath) : bool
    {
        if (!is_string($sFilePath)) {
            throw new \InvalidArgumentException('Argument "$sFilePath" expects a string, "' . (is_object($sFilePath) ? get_class($sFilePath) : gettype($sFilePath)) . '" given');
        }

        // Retrieve "open_basedir" restriction
        if ($this->openBaseDirPaths === null) {
            if ($sOpenBaseDir = ini_get('open_basedir')) {
                $this->openBaseDirPaths = explode(PATH_SEPARATOR, $sOpenBaseDir);
            } else {
                $this->openBaseDirPaths = array();
            }
        }

        if (!$this->openBaseDirPaths) {
            return file_exists($sFilePath);
        }
        foreach ($this->openBaseDirPaths as $sAllowedPath) {
            if (strpos($sFilePath, $sAllowedPath)) {
                return file_exists($sFilePath);
            }
        }
        return false;
    }

    /**
     * Retrieve cache file name for given module name, controller name and action name
     * @return string
     */
    public function getCacheFileName() : string
    {
        $aAssets = $this->getAssets();

        $sCacheFileName = isset($aAssets[$sModuleName = $this->getModuleName()]) ? $sModuleName : \AssetsBundle\Service\ServiceOptions::NO_MODULE;

        $aUnwantedKeys = array_fill_keys(\AssetsBundle\AssetFile\AssetFile::ALL_ASSET_TYPES, true);
        $aAvailableModuleAssets = array_diff_key($aAssets, $aUnwantedKeys);
        $bControllerNameFound = false;
        $sControllerName = $this->getControllerName();
        foreach ($aAvailableModuleAssets as $aModuleConfig) {
            if (isset($aModuleConfig[$sControllerName])) {
                $bControllerNameFound = true;
                break;
            }
        }
        $sCacheFileName .= '_' . ($bControllerNameFound ? $sControllerName : \AssetsBundle\Service\ServiceOptions::NO_CONTROLLER);

        $bActionNameFound = false;
        $sActionName = $this->getActionName();
        reset($aAvailableModuleAssets);
        foreach ($aAvailableModuleAssets as $aModuleConfig) {
            foreach (array_diff_key($aModuleConfig, $aUnwantedKeys) as $aControllerConfig) {
                if (isset($aControllerConfig[$sActionName])) {
                    $bActionNameFound = true;
                }
            }
        }
        $sCacheFileName .= '_' . ($bActionNameFound ? $sActionName : \AssetsBundle\Service\ServiceOptions::NO_ACTION);
        return md5($sCacheFileName);
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @param scalar $iLastModifiedTime
     * @return string
     */
    public function getAssetFileBaseUrl(\AssetsBundle\AssetFile\AssetFile $oAssetFile, $iLastModifiedTime = null) : string
    {
        if ($oAssetFile->isAssetFilePathUrl()) {
            return $oAssetFile->getAssetFilePath();
        }
        $sAssetPath = str_replace(array($this->getCachePath(), DIRECTORY_SEPARATOR), array('', '/'), $oAssetFile->getAssetFilePath());

        if ($oAssetFile->getAssetFileType() === \AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA) {
            return $this->getCacheUrl() . ltrim($sAssetPath, '/');
        }

        if ($iLastModifiedTime === null) {
            $iLastModifiedTime = $oAssetFile->getAssetFileLastModified();
        }
        return $this->getCacheUrl() . ltrim($sAssetPath, '/') . ($iLastModifiedTime ? (strpos($sAssetPath, '?') === false ? '?' : '&') . $iLastModifiedTime : '');
    }
}
