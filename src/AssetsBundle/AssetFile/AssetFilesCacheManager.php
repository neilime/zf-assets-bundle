<?php

namespace AssetsBundle\AssetFile;

class AssetFilesCacheManager
{

    /**
     * @var \AssetsBundle\Service\ServiceOptions
     */
    protected $options;

    /**
     * List of unwanted file path characters
     * @var array
     */
    protected $unwantedFilePathChars = array('<', '>', '?', '*', '"', '|', ':');

    /**
     * Constructor
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     */
    public function __construct(\AssetsBundle\Service\ServiceOptions $oOptions = null)
    {
        if ($oOptions) {
            $this->setOptions($oOptions);
        }
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return string
     * @throws \DomainException
     */
    public function getAssetFileCachePath(\AssetsBundle\AssetFile\AssetFile $oAssetFile) : string
    {
        switch ($sAssetType = $oAssetFile->getAssetFileType()) {
            case \AssetsBundle\AssetFile\AssetFile::ASSET_CSS:
            case \AssetsBundle\AssetFile\AssetFile::ASSET_JS:
                // In production, css & js files have a unique name depending on current matching route
                if ($this->getOptions()->isProduction()) {
                    $sCacheFilePath = $this->getOptions()->getCacheFileName() . '.' . \AssetsBundle\AssetFile\AssetFile::getAssetFileDefaultExtension($sAssetType);
                }
                // In development, css & js files dirname are displayed for easy debugging
                else {
                    $sCacheFilePath = $this->sanitizeAssetFilePath($oAssetFile);
                }
                break;
            case \AssetsBundle\AssetFile\AssetFile::ASSET_LESS:
            case \AssetsBundle\AssetFile\AssetFile::ASSET_SCSS:
                // In production, css & js files have a unique name depending on current matching route
                if ($this->getOptions()->isProduction()) {
                    throw new \DomainException('Asset\'s type "' . $sAssetType . '" can not be cached in production');
                }
                // In development, compilded css files are displayed in a single file for easy debugging
                $sCacheFilePath = 'dev_' . $sAssetType . '_' . $this->getOptions()->getCacheFileName() . '.' . \AssetsBundle\AssetFile\AssetFile::getAssetFileCompiledExtension($sAssetType);
                break;
            case \AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA:
                // In production, media dirname is encrypted
                if ($this->getOptions()->isProduction()) {
                    $sCacheFilePath = md5(dirname($sAssetFilePath = $oAssetFile->getAssetFilePath())) . DIRECTORY_SEPARATOR . basename($sAssetFilePath);
                }
                // In development, media dirname is displayed for easy debugging
                else {
                    $sCacheFilePath = $this->sanitizeAssetFilePath($oAssetFile);
                }
                break;
            default:
                throw new \DomainException('Asset\'s type "' . $sAssetType . '" can not be cached');
        }
        return $this->getOptions()->getCachePath() . DIRECTORY_SEPARATOR . str_replace($this->unwantedFilePathChars, '_', ltrim(str_ireplace(getcwd(), '', $sCacheFilePath), DIRECTORY_SEPARATOR));
    }

    /**
     * @param string $sAssetFileType
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function hasProductionCachedAssetFiles(string $sAssetFileType) : bool
    {
        if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type "' . $sAssetFileType . '" is not valid');
        }

        if (in_array($sAssetFileType, array(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS, \AssetsBundle\AssetFile\AssetFile::ASSET_JS))) {
            return file_exists($this->getOptions()->getCachePath() . DIRECTORY_SEPARATOR . $this->getOptions()->getCacheFileName() . '.' . \AssetsBundle\AssetFile\AssetFile::getAssetFileDefaultExtension($sAssetFileType));
        }
        throw new \InvalidArgumentException(__METHOD__ . 'allows "' . \AssetsBundle\AssetFile\AssetFile::ASSET_CSS . '" & "' . \AssetsBundle\AssetFile\AssetFile::ASSET_JS . '" asset file type, "' . $sAssetFileType . '" given');
    }

    /**
     * @param string $sAssetFileType
     * @return array
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getProductionCachedAssetFiles(string $sAssetFileType) : array
    {
        if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type "' . $sAssetFileType . '" is not valid');
        }

        if (!in_array($sAssetFileType, array(\AssetsBundle\AssetFile\AssetFile::ASSET_CSS, \AssetsBundle\AssetFile\AssetFile::ASSET_JS))) {
            throw new \InvalidArgumentException(__METHOD__ . 'allows "' . \AssetsBundle\AssetFile\AssetFile::ASSET_CSS . '" & "' . \AssetsBundle\AssetFile\AssetFile::ASSET_JS . '" asset file type, "' . $sAssetFileType . '" given');
        }
        
        $sCacheFileName = $this->getOptions()->getCacheFileName();
        $sCacheFileExtension = \AssetsBundle\AssetFile\AssetFile::getAssetFileDefaultExtension($sAssetFileType);

        $aAssetFiles = array();
        foreach (glob($this->getOptions()->getCachePath() . DIRECTORY_SEPARATOR . $sCacheFileName . '*.' . $sCacheFileExtension) as $sAssetFilePath) {
            $aAssetFiles[] = new \AssetsBundle\AssetFile\AssetFile(array(
                'asset_file_path' => $sAssetFilePath,
                'asset_file_type' => $sAssetFileType
            ));
        }
        return $aAssetFiles;
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @param \AssetsBundle\AssetFile\AssetFile $oSourceAssetFile
     * @return \AssetsBundle\AssetFile\AssetFile
     * @throws \LogicException
     */
    public function cacheAssetFile(\AssetsBundle\AssetFile\AssetFile $oAssetFile, \AssetsBundle\AssetFile\AssetFile $oSourceAssetFile = null) : \AssetsBundle\AssetFile\AssetFile
    {

        // Define source asset file
        if (!$oSourceAssetFile) {
            $oSourceAssetFile = $oAssetFile;
        }
        

        // Check that file need to be cached
        if ($this->isAssetFileCached($oSourceAssetFile)) {
            return $oAssetFile->setAssetFilePath($this->getAssetFileCachePath($oSourceAssetFile));
        }

        // Retrieve asset file cache path
        $sCacheFilePath = $this->getAssetFileCachePath($oSourceAssetFile);

        // Retrieve cache file directory path
        $sCacheFileDirPath = dirname($sCacheFilePath);
        if ($sCacheFileDirPath === '.') {
            throw new \LogicException('Asset file cache path "' . $sCacheFilePath . '" does not provide a parent directory');
        }

        // Create directory if not exists
        if (!is_dir($sCacheFileDirPath)) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            if (!mkdir($sCacheFileDirPath, $this->getOptions()->getDirectoriesPermissions(), true)) {
                throw new \RuntimeException('Error occured while creating directory "' . $sCacheFileDirPath . '"');
            }
            if ($oException = \Zend\Stdlib\ErrorHandler::stop()) {
                throw new \RuntimeException('Error occured while creating directory "' . $sCacheFileDirPath . '"', $oException->getCode(), $oException);
            }
        } elseif (!is_writable($sCacheFileDirPath)) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            if (!chmod($sCacheFileDirPath, $this->getOptions()->getDirectoriesPermissions())) {
                throw new \RuntimeException('Error occured while changing mode on directory "' . $sCacheFileDirPath . '"');
            }
            \Zend\Stdlib\ErrorHandler::stop(true);
        }

        $bFileExists = file_exists($sCacheFilePath);

        // Cache remote asset file
        if ($oAssetFile->isAssetFilePathUrl()) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            $oAssetFileFileHandle = fopen($oAssetFile->getAssetFilePath(), 'rb');
            \Zend\Stdlib\ErrorHandler::stop(true);
            if (!$oAssetFileFileHandle) {
                throw new \LogicException('Unable to open asset file "' . $oAssetFile->getAssetFilePath() . '"');
            }

            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            file_put_contents($sCacheFilePath, stream_get_contents($oAssetFileFileHandle));
            \Zend\Stdlib\ErrorHandler::stop(true);

            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            fclose($oAssetFileFileHandle);
            \Zend\Stdlib\ErrorHandler::stop(true);
        }
        // Cache local asset file
        else {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            $sAssetFilePath = $oAssetFile->getAssetFilePath();
            if (!is_file($sAssetFilePath)) {
                throw new \LogicException('Asset file "' . $sAssetFilePath . '" does not exits');
            }
            copy($sAssetFilePath, $sCacheFilePath);
            \Zend\Stdlib\ErrorHandler::stop(true);
        }

        if (!$bFileExists) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            chmod($sCacheFilePath, $this->getOptions()->getFilesPermissions());
            \Zend\Stdlib\ErrorHandler::stop(true);
        }

        return $oAssetFile->setAssetFilePath($sCacheFilePath);
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return bool
     */
    public function isAssetFileCached(\AssetsBundle\AssetFile\AssetFile $oAssetFile) : bool
    {
        if (file_exists($sAssetFileCachedPath = $this->getAssetFileCachePath($oAssetFile))) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            // Can't retrieve last modified from url, don't reload it
            $bIsUpdated = (!($iLastModified = $oAssetFile->getAssetFileLastModified()) && $oAssetFile->isAssetFilePathUrl()) ? true : ($iLastModified && filemtime($sAssetFileCachedPath) >= $iLastModified);
            \Zend\Stdlib\ErrorHandler::stop(true);
            return $bIsUpdated;
        }
        return false;
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return string
     */
    public function sanitizeAssetFilePath(\AssetsBundle\AssetFile\AssetFile $oAssetFile) : string
    {
        return $oAssetFile->isAssetFilePathUrl() ? str_replace(
                        array_merge(array('/'), $this->unwantedFilePathChars),
            '_',
            implode('/', array_slice(explode('/', preg_replace('/http:\/\/|https:\/\/|www./', '', $oAssetFile->getAssetFilePath())), 0, 1))
                ) : $oAssetFile->getAssetFilePath();
    }

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\AssetFile\AssetFilesCacheManager
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions) : \AssetsBundle\AssetFile\AssetFilesCacheManager
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
