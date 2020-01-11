<?php

namespace AssetsBundleTest;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

// Composer autoloading
if (! file_exists($sComposerAutoloadPath = __DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Composer autoload file "' . $sComposerAutoloadPath . '" does not exist');
}
if (false === (include $sComposerAutoloadPath)) {
    throw new \RuntimeException('An error occured while including composer autoload file "' . $sComposerAutoloadPath . '"');
}

class Bootstrap {

    /**
     * @var \Laminas\ServiceManager\ServiceManager
     */
    protected static $serviceManager;

    /**
     * @var array
     */
    protected static $config;

    /**
     * Initialize bootstrap
     */
    public static function init() {
        // Load the user-defined test configuration file, if it exists;
        $aTestConfig = include  __DIR__ . '/application.config.php';
        $aModulePaths = array();
        if (isset($aTestConfig['module_listener_options']['module_paths'])) {
            foreach ($aTestConfig['module_listener_options']['module_paths'] as $sModulePath) {
                if (($sPath = static::findParentPath($sModulePath))) {
                    $aModulePaths[] = $sPath;
                }
            }
        }
        

        // Use ModuleManager to load this module and it's dependencies
        static::$config = \Laminas\Stdlib\ArrayUtils::merge(
            array(
                'module_listener_options' => array(
                    'module_paths' => $aModulePaths
                ),
            ), 
            $aTestConfig
        );

        static::$serviceManager = new \Laminas\ServiceManager\ServiceManager();
        $oConfig = new \Laminas\Mvc\Service\ServiceManagerConfig();
        $oConfig->configureServiceManager(static::$serviceManager);
        static::$serviceManager->setService('ApplicationConfig', static::$config);
        static::$serviceManager->get('ModuleManager')->loadModules();
    }

    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public static function getServiceManager() {
        return static::$serviceManager;
    }

    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public static function setServiceManager(\Laminas\ServiceManager\ServiceManager $oServiceManager) {
        static::$serviceManager = $oServiceManager;
    }

    /**
     * @return array
     */
    public static function getConfig() {
        return static::$config;
    }

    /**
     * Retrieve parent for a given path
     * @param string $sPath
     * @return boolean|string
     */
    protected static function findParentPath($sPath) {
        $sCurrentDir = __DIR__;
        $sPreviousDir = '.';
        while (!is_dir($sPreviousDir . '/' . $sPath)) {
            $sCurrentDir = dirname($sCurrentDir);
            if ($sPreviousDir === $sCurrentDir) {
                return false;
            }
            $sPreviousDir = $sCurrentDir;
        }
        return $sCurrentDir . '/' . $sPath;
    }

}

\AssetsBundleTest\Bootstrap::init();
