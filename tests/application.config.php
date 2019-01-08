<?php
return array(
    'modules' => array(
        'Zend\Router',
        'Zend\Mvc\Console',
        'AssetsBundle',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
               __DIR__.'/configuration.php'
        ),
        'module_paths' => array(
            'module',
            'vendor'
        ),
    ),
);
