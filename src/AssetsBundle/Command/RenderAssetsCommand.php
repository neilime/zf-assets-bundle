<?php

namespace AssetsBundle\Command;

use AssetsBundle\Service\ToolsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'render', description: 'Render all configured assets')]
final class RenderAssetsCommand extends Command
{
    public function __construct(private readonly ToolsService $tools)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->tools->setConsole($output)->renderAllAssets();
        return Command::SUCCESS;
    }
}
