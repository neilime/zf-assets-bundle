<?php

return array(
    'router' => include __DIR__ . '/module.config.router.php',
    'laminas-cli' => [
        'commands' => [
            'render' => \AssetsBundle\Command\RenderAssetsCommand::class,
            'empty' => \AssetsBundle\Command\EmptyCacheCommand::class,
        ],
    ],
    'service_manager' => include __DIR__ . '/module.config.service-manager.php',
    'assets_bundle' => include __DIR__ . '/module.config.assets-bundle.php',
    'view_manager' => include __DIR__ . '/module.config.view-manager.php'
);
