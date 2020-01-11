<?php

namespace AssetsBundle\AssetFile\AssetFileFilter;

abstract class AbstractAssetFileFilter extends \Laminas\Stdlib\AbstractOptions implements \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface
{
    
    /**
     * @var string
     */
    const EXEC_TIME_PER_CHAR = 7E-5;

    /**
     * @var string
     */
    protected $assetFileFilterName;

    /**
     * @var \AssetsBundle\Service\ServiceOptions
     */
    protected $options;

    /**
     * @var string
     */
    protected $assetFileFilterProcessedDirPath;

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return string
     */
    public function filterAssetFile(\AssetsBundle\AssetFile\AssetFile $oAssetFile) : string
    {
        // Try to retrieve cached filter rendering
        if ($sCachedFilterRendering = $this->getCachedFilteredContent($oAssetFile)) {
            return $sCachedFilterRendering;
        }

        $iExecTime = strlen($sContent = $oAssetFile->getAssetFileContents()) * self::EXEC_TIME_PER_CHAR;
        $iMaxExecutionTime = ini_get('max_execution_time');
        set_time_limit(0);
        try {
            \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
            $sFilteredContent = $this->filterContent($sContent, $oAssetFile->getAssetFilePath());
            \Laminas\Stdlib\ErrorHandler::stop(true);
        } catch (\Throwable $oException) {
            throw new \RuntimeException('An error occured while running filter "'.$this->getAssetFileFilterName().'" on file\'s content "'.$oAssetFile->getAssetFilePath().'"', $oException->getCode(), $oException);
        }
        $sFilteredContent = trim($sFilteredContent);
        $this->cacheFilteredAssetFileContent($oAssetFile, $sFilteredContent);
        set_time_limit($iMaxExecutionTime);
        return $sFilteredContent;
    }
    
    /**
     * @param string $sContent
     * @param string $sFilePath
     * @return string
     */
    abstract protected function filterContent(string $sContent, string $sFilePath) : string;

    /**
     * @param string $sAssetFileFilterName
     * @return \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
     * @throws \InvalidArgumentException
     */
    public function setAssetFileFilterName(string $sAssetFileFilterName) : \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
    {
        if (empty($sAssetFileFilterName)) {
            throw new \InvalidArgumentException('Filter name is empty');
        }

        if (!is_string($sAssetFileFilterName)) {
            throw new \InvalidArgumentException('Filter name expects string, "' . gettype($sAssetFileFilterName) . '" given');
        }

        $this->assetFileFilterName = $sAssetFileFilterName;

        return $this;
    }

    /**
     * @return string
     * @throws \LogicException
     */
    public function getAssetFileFilterName() : string
    {
        if (is_string($this->assetFileFilterName) && !empty($this->assetFileFilterName)) {
            return $this->assetFileFilterName;
        }
        throw new \LogicException('Filter name is undefined');
    }

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions) : \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface
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

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return string
     */
    public function getCachedFilteredContentFilePath(\AssetsBundle\AssetFile\AssetFile $oAssetFile) : string
    {
        return $this->getAssetFileFilterProcessedDirPath() . DIRECTORY_SEPARATOR . md5($sAssetFilePath = $oAssetFile->getAssetFilePath());
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return bool|string
     */
    public function getCachedFilteredContent(\AssetsBundle\AssetFile\AssetFile $oAssetFile)
    {
        $sCachedFilteredContentFilePath = $this->getCachedFilteredContentFilePath($oAssetFile);
        if (!file_exists($sCachedFilteredContentFilePath)) {
            return false;
        }

        $oFilteredAssetFile = new \AssetsBundle\AssetFile\AssetFile(array(
            'assetFilePath' => $sCachedFilteredContentFilePath,
            'assetFileType' => $oAssetFile->getAssetFileType()
        ));

        if (!$oAssetFile->assetFileExists()) {
            return false;
        }

        // Retrieve cached filtered asset file last modified timestamp
        $iFilteredAssetFileLastModified = $oFilteredAssetFile->getAssetFileLastModified();
        if (!$iFilteredAssetFileLastModified) {
            return false;
        }

        // Retrieve asset file last modified timestamp
        $iAssetFileLastModified = $oAssetFile->getAssetFileLastModified();
        if (!$iAssetFileLastModified) {
            return false;
        }

        // If cached filtered asset file is outdated
        if ($iFilteredAssetFileLastModified < $iAssetFileLastModified) {
            return false;
        }

        return $oFilteredAssetFile->getAssetFileContents();
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @param string $sFilteredContent
     * @return \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
     * @throws \InvalidArgumentException
     */
    public function cacheFilteredAssetFileContent(\AssetsBundle\AssetFile\AssetFile $oAssetFile, string $sFilteredContent) : \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
    {
        if (is_string($sFilteredContent)) {
            $sCachedFilteredContentFilePath = $this->getCachedFilteredContentFilePath($oAssetFile);
            $bFileExists = file_exists($sCachedFilteredContentFilePath);

            \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
            file_put_contents($sCachedFilteredContentFilePath, $sFilteredContent);
            \Laminas\Stdlib\ErrorHandler::stop(true);

            if (!$bFileExists) {
                \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
                chmod($sCachedFilteredContentFilePath, $this->getOptions()->getFilesPermissions());
                \Laminas\Stdlib\ErrorHandler::stop(true);
            }
            return $this;
        }
        throw new \InvalidArgumentException('Filtered content expects string, "' . gettype($sFilteredContent) . '" given');
    }

    /**
     * @return string
     */
    public function getAssetFileFilterProcessedDirPath() : string
    {
        if (!is_dir($this->assetFileFilterProcessedDirPath)) {
            $this->assetFileFilterProcessedDirPath = $this->getOptions()->getProcessedDirPath() . DIRECTORY_SEPARATOR . strtolower(str_replace(
                                    array('/', '<', '>', '?', '*', '"', '|'),
                '_',
                $this->getAssetFileFilterName()
            ));
            if (!is_dir($this->assetFileFilterProcessedDirPath)) {
                \Laminas\Stdlib\ErrorHandler::start(\E_ALL);
                mkdir($this->assetFileFilterProcessedDirPath, $this->getOptions()->getDirectoriesPermissions());
                \Laminas\Stdlib\ErrorHandler::stop(true);
            }
        }
        return $this->assetFileFilterProcessedDirPath;
    }
}
