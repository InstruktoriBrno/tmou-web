<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Commands;

use InstruktoriBrno\TMOU\Facades\Teams\CleanSSOSessions;
use Nette\DI\Attributes\Inject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanSSOSessionsCommand extends Command
{
    public static $defaultName = 'clean-sso-sessions';

    #[Inject]
    public CleanSSOSessions $cleanSSOSessions;

    public function __construct(CleanSSOSessions $cleanSSOSessions)
    {
        parent::__construct();
        $this->cleanSSOSessions = $cleanSSOSessions;
    }

    protected function configure(): void
    {
        $this->setName('clean-sso-sessions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->cleanSSOSessions)();
        return 0; // successful exit
    }
}
