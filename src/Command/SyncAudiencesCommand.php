<?php

namespace DMT\AbMiddlewareBundle\Command;

use DMT\AbMiddleware\GaAudienceHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ab:sync-ga4-audiences',
    description: 'Create/archive GA4 audiences based on configured experiments',
)]
class SyncAudiencesCommand extends Command
{
    public function __construct(private readonly GaAudienceHelper $gaAudienceHelper)
    {
        parent::__construct();
    }
    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->gaAudienceHelper->synchronizeAudiences();
        return Command::SUCCESS;
    }
}
