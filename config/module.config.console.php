<?php

//Console module config
return array(
    'router' => array(
        'routes' => array(
            'render-assets' => array(
                'options' => array(
                    'route' => 'render',
                    'defaults' => array(
                        'controller' => \AssetsBundle\Controller\ToolsController::class,
                        'action' => 'renderAssets'
                    )
                )
            ),
            'empty-cache' => array(
                'options' => array(
                    'route' => 'empty',
                    'defaults' => array(
                        'controller' => \AssetsBundle\Controller\ToolsController::class,
                        'action' => 'emptyCache'
                    )
                )
            )
        )
    )
);
