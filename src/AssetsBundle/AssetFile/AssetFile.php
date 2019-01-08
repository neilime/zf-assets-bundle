<?php

namespace AssetsBundle\AssetFile;

class AssetFile extends \Zend\Stdlib\AbstractOptions
{
    const ASSET_CSS = 'css';
    const ASSET_JS = 'js';
    const ASSET_LESS = 'less';
    const ASSET_SCSS = 'scss';
    const ASSET_MEDIA = 'media';
    
    const ALL_ASSET_TYPES = [
        self::ASSET_CSS,
        self::ASSET_JS,
        self::ASSET_LESS,
        self::ASSET_SCSS,
        self::ASSET_MEDIA,
    ];

    const COMPILED_CSS_TYPES = [
        self::ASSET_LESS,
        self::ASSET_SCSS,
    ];

    /**
     * @var string
     */
    protected $assetFileType;

    /**
     * @var string
     */
    protected $assetFilePath;

    /**
     * @var string
     */
    protected $assetFileContents;

    /**
     * @var string
     */
    protected $assetFileContentsLastRetrievedTime;

    /**
     * @var string
     */
    protected $assetFileExtension;

    /**
     * @return string
     * @throws \LogicException
     */
    public function getAssetFileType() : string
    {
        if (self::assetFileTypeExists($this->assetFileType)) {
            return $this->assetFileType;
        }
        throw new \LogicException('Asset file type is undefined');
    }

    /**
     * @param string $sAssetFileType
     * @return \AssetsBundle\AssetFile\AssetFile
     * @throws \InvalidArgumentException
     */
    public function setAssetFileType(string $sAssetFileType) : \AssetsBundle\AssetFile\AssetFile
    {
        if (self::assetFileTypeExists($sAssetFileType)) {
            $this->assetFileType = $sAssetFileType;
            return $this;
        }
        throw new \InvalidArgumentException('Asset file type "' . $sAssetFileType . '" does not exist');
    }

    /**
     * @return string
     * @throws \LogicException
     */
    public function getAssetFilePath() : string
    {
        if (is_string($this->assetFilePath)) {
            return $this->assetFilePath;
        }
        throw new \LogicException('Asset file path is undefined');
    }

    /**
     * @return bool
     */
    public function isAssetFilePathUrl() : bool
    {
        return filter_var($sAssetFilePath = $this->getAssetFilePath(), FILTER_VALIDATE_URL) && preg_match('/^\/|http/', $sAssetFilePath);
    }

    /**
     * @param string $sAssetFilePath
     * @return \AssetsBundle\AssetFile\AssetFile
     * @throws \InvalidArgumentException
     */
    public function setAssetFilePath(string $sAssetFilePath) : \AssetsBundle\AssetFile\AssetFile
    {
        if (!is_string($sAssetFilePath)) {
            throw new \InvalidArgumentException('Asset file path expects string, "' . gettype($sAssetFilePath) . '" given');
        }

        // Reset asset file contents
        $this->assetFileContents = null;

        if (is_readable($sAssetFilePath)) {
            $this->assetFilePath = $sAssetFilePath;
            return $this;
        }

        // Asset file path is an url
        if (strpos($sAssetFilePath, '://') === false) {
            throw new \InvalidArgumentException('Asset\'s file "' . $sAssetFilePath . '" does not exist');
        } elseif (in_array($this->getAssetFileType(), self::COMPILED_CSS_TYPES, true)) {
            throw new \InvalidArgumentException($this->getAssetFileType().' assets does not support urls, "' . $sAssetFilePath . '" given');
        }

        if (!($sFilteredAssetFilePath = filter_var($sAssetFilePath, FILTER_VALIDATE_URL))) {
            throw new \InvalidArgumentException('Asset\'s file path "' . $sAssetFilePath . '" is not a valid url');
        }

        \Zend\Stdlib\ErrorHandler::start(\E_ALL);
        $oFileHandle = fopen($sFilteredAssetFilePath, 'r');
        \Zend\Stdlib\ErrorHandler::stop(true);
        if (!$oFileHandle) {
            throw new \InvalidArgumentException('Unable to open asset file "' . $sFilteredAssetFilePath . '"');
        }

        \Zend\Stdlib\ErrorHandler::start(\E_ALL);
        $aMetaData = stream_get_meta_data($oFileHandle);
        \Zend\Stdlib\ErrorHandler::stop(true);
        if (empty($aMetaData['uri'])) {
            throw new \InvalidArgumentException('Unable to retreive uri metadata from file "' . $sFilteredAssetFilePath . '"');
        }
        $this->assetFilePath = $aMetaData['uri'];

        \Zend\Stdlib\ErrorHandler::start(\E_ALL);
        fclose($oFileHandle);
        \Zend\Stdlib\ErrorHandler::stop(true);

        return $this;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getAssetFileContents() : string
    {
        if (
                $this->assetFileContents &&
                (($iLastModified = $this->getAssetFileLastModified()) && $iLastModified < $this->assetFileContentsLastRetrievedTime)
        ) {
            return $this->assetFileContents;
        }

        if(!$this->assetFileExists()){
            if ($this->assetFileContents) {
                return $this->assetFileContents;
            }
            throw new \LogicException('Asset file "'.$this->getAssetFilePath().'" does not exist, unable to retrieve its contents');
        }

        $sAssetFilePath = $this->getAssetFilePath();

        if ($this->isAssetFilePathUrl()) {
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            $oFileHandle = fopen($sAssetFilePath, 'r');
            \Zend\Stdlib\ErrorHandler::stop(true);

            $this->assetFileContents = '';
            while (($sContent = fgets($oFileHandle)) !== false) {
                $this->assetFileContents .= $sContent . PHP_EOL;
            }
            if (!feof($oFileHandle)) {
                throw new \RuntimeException('Unable to retrieve asset contents from file "' . $sAssetFilePath . '"');
            }
            fclose($oFileHandle);
        } elseif (strtolower(pathinfo($sAssetFilePath, PATHINFO_EXTENSION)) === 'php') {
            ob_start();
            if (false === include $sAssetFilePath) {
                throw new \RuntimeException('Error appends while including asset file "' . $sAssetFilePath . '"');
            }
            $this->assetFileContents = ob_get_clean();
        } elseif (($this->assetFileContents = file_get_contents($sAssetFilePath)) === false) {
            throw new \RuntimeException('Unable to retrieve asset contents from file "' . $sAssetFilePath . '"');
        }

        // Update content last retrieved time
        $this->assetFileContentsLastRetrievedTime = time();

        return $this->assetFileContents;
    }

    /**
     * @param string $sAssetFileContents
     * @param bool $bFileAppend
     * @return \AssetsBundle\AssetFile\AssetFile
     * @throws \InvalidArgumentException
     */
    public function setAssetFileContents(string $sAssetFileContents, bool $bFileAppend = true) : \AssetsBundle\AssetFile\AssetFile
    {
        if (!is_string($sAssetFileContents)) {
            throw new \InvalidArgumentException('Asset file content expects string, "' . gettype($sAssetFileContents) . '" given');
        }

        $sAssetFilePath = $this->getAssetFilePath();
        if ($bFileAppend) {
            if ($this->assetFileContents) {
                $this->assetFileContents .= $sAssetFileContents;
            }
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            file_put_contents($sAssetFilePath, $sAssetFileContents, FILE_APPEND);
            \Zend\Stdlib\ErrorHandler::stop(true);
        } else {
            $this->assetFileContents = $sAssetFileContents;
            \Zend\Stdlib\ErrorHandler::start(\E_ALL);
            file_put_contents($sAssetFilePath, $sAssetFileContents);
            \Zend\Stdlib\ErrorHandler::stop(true);
        }
       
        // Update content last retrieved time
        $this->assetFileContentsLastRetrievedTime = time();

        return $this;
    }

    /**
     * @return string
     */
    public function getAssetFileExtension() : string
    {
        return $this->assetFileExtension ? : $this->assetFileExtension = strtolower(pathinfo($this->getAssetFilePath(), PATHINFO_EXTENSION));
    }

    /**
     * Retrieve asset file last modified timestamp
     * @return int|null
     */
    public function getAssetFileLastModified() : ?int
    {
        $sAssetFilePath = $this->getAssetFilePath();
        if ($this->isAssetFilePathUrl()) {
            if (
                    // Retrieve headers
                    ($aHeaders = get_headers($sAssetFilePath, 1))
                    // Assert return is OK
                    && strstr($aHeaders[0], '200') !== false
                    // Retrieve last modified as DateTime
                    && !empty($aHeaders['Last-Modified']) && $oLastModified = new \DateTime($aHeaders['Last-Modified'])
            ) {
                return $oLastModified->getTimestamp();
            }

            $oCurlHandle = curl_init($sAssetFilePath);
            curl_setopt($oCurlHandle, CURLOPT_NOBODY, true);
            curl_setopt($oCurlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($oCurlHandle, CURLOPT_FILETIME, true);
            if (curl_exec($oCurlHandle) === false) {
                return null;
            }
            return curl_getinfo($oCurlHandle, CURLINFO_FILETIME) ? : null;
        }

        if (!file_exists($sAssetFilePath)) {
            throw new \LogicException('Asset file "'.$sAssetFilePath.'" does not exist');
        }

        \Zend\Stdlib\ErrorHandler::start(\E_ALL);
        $iAssetFileFilemtime = filemtime($sAssetFilePath);
        \Zend\Stdlib\ErrorHandler::stop(true);
        return $iAssetFileFilemtime ? : null;
    }

    /**
     * Check if the asset file exists
     * @return bool
     */
    public function assetFileExists() : bool
    {
        // Remote file
        if ($this->isAssetFilePathUrl()) {
            // Retrieve headers & assert return is OK
            return ($aHeaders = get_headers($sAssetFilePath = $this->getAssetFilePath(), 1)) && strstr($aHeaders[0], '200') !== false;
        }
        return file_exists($this->getAssetFilePath());
    }
    
    /**
     * Retrieve asset file size
     * @return int|null
     */
    public function getAssetFileSize() : ?int
    {         
        // Remote file
        if ($this->isAssetFilePathUrl()) {
            if (
                    // Retrieve headers
                    ($aHeaders = get_headers($sAssetFilePath = $this->getAssetFilePath(), 1))
                    // Assert return is OK
                    && strstr($aHeaders[0], '200') !== false
                    // Retrieve content length
                    && !empty($aHeaders['Content-Length']) && $iAssetFileSize = $aHeaders['Content-Length']
            ) {
                return $iAssetFileSize;
            }
            $oCurlHandle = curl_init($sAssetFilePath);
            curl_setopt($oCurlHandle, CURLOPT_NOBODY, true);
            curl_setopt($oCurlHandle, CURLOPT_RETURNTRANSFER, true);
            if (curl_exec($oCurlHandle) === false) {
                return null;
            }
            return curl_getinfo($oCurlHandle, CURLINFO_CONTENT_LENGTH_DOWNLOAD) ? : null;
        }
        
        // Local file
        \Zend\Stdlib\ErrorHandler::start(\E_ALL);
        $iAssetFileSize = filesize($this->getAssetFilePath());
        \Zend\Stdlib\ErrorHandler::stop(true);
        return $iAssetFileSize ? : null;
    }

    public function assetFileContentEquals(\AssetsBundle\AssetFile\AssetFile $oAssetFile)
    {
        // If files exists both
        $bFileAExists = $this->assetFileExists();
        $bFileBExists = $oAssetFile->assetFileExists();

        $iFileASize = $bFileAExists ? $this->getAssetFileSize() : strlen($this->getAssetFileContent());
        $iFileBSize = $bFileBExists ? $oAssetFile->getAssetFileSize() : strlen($oAssetFile->getAssetFileContent());

        // Compare size first
        if ($this->getAssetFileSize() !==  $oAssetFile->getAssetFileSize()) {
            return false;
        }

        if ($bFileAExists && $bFileBExists) {

            // Then compare content
            $rFileHandleA = fopen($this->getAssetFilePath(), 'rb');
            $rFileHandleB = fopen($oAssetFile->getAssetFilePath(), 'rb');

            while (($sCharA = fread($rFileHandleA, 4096)) !== false) {
                $sCharB = fread($rFileHandleB, 4096);
                if ($sCharA !== $sCharB) {
                    fclose($rFileHandleA);
                    fclose($rFileHandleB);
                    return false;
                }
            }

            fclose($rFileHandleA);
            fclose($rFileHandleB);

            return true;
        }

        if (!$bFileAExists && !$bFileBExists) {
            return $this->getAssetFileContents() === $oAssetFile->getAssetFileContents();
        }

        $rFileHandle = fopen($bFileAExists ? $this->getAssetFilePath() : $oAssetFile->getAssetFilePath(), 'rb');
        $sFileContent = $bFileAExists ? $this->getAssetFileContents() : $oAssetFile->getAssetFileContents();

        $iCharLength = 4096;
        $iCharsChecked = 0;
        while (($sCharA = fread($rFileHandle, $iCharLength)) !== false) {
            $sCharB = substr($sFileContent, $iCharsChecked, $iCharLength);
            if ($sCharA !== $sCharB) {
                fclose($rFileHandle);
                return false;
            }
            $iCharsChecked += $iCharLength;
        }

        return true;
    }

    /**
     * Check if asset file's type is valid
     * @param string $sAssetFileType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public static function assetFileTypeExists(string $sAssetFileType) : bool
    {
        if (!is_string($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type expects string, "' . gettype($sAssetFileType) . '" given');
        }

        return in_array($sAssetFileType, self::ALL_ASSET_TYPES, true);
    }

    /**
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function getAssetFileDefaultExtension(string $sAssetFileType) : string
    {
        if (!is_string($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type expects string, "' . gettype($sAssetFileType) . '" given');
        }

        switch ($sAssetFileType) {
            case self::ASSET_CSS:
            case self::ASSET_LESS:
            case self::ASSET_SCSS:
            case self::ASSET_JS:
                return $sAssetFileType;
            default:
                throw new \DomainException('Asset file type "' . $sAssetFileType . '" has no default extension');
        }
    }

    /**
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @return string
     *
     */
    public static function getAssetFileCompiledExtension(string $sAssetFileType) : string
    {
        if (!is_string($sAssetFileType)) {
            throw new \InvalidArgumentException('Asset file type expects string, "' . gettype($sAssetFileType) . '" given');
        }
        switch ($sAssetFileType) {
            case self::ASSET_LESS:
            case self::ASSET_SCSS:
                return self::ASSET_CSS;
            default:
                throw new \DomainException('Asset file type "' . $sAssetFileType . '" has no compilded extension');
        }
    }
}
