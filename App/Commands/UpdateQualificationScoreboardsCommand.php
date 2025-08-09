<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Commands;

use InstruktoriBrno\TMOU\Facades\Qualification\UpdateScoreboardsFacade;
use Nette\DI\Attributes\Inject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateQualificationScoreboardsCommand extends Command
{
    public static $defaultName = 'update-qualification-scoreboards';

    #[Inject]
    public UpdateScoreboardsFacade $updateScoreboardsFacade;

    public function __construct(UpdateScoreboardsFacade $updateScoreboardsFacade)
    {
        parent::__construct();
        $this->updateScoreboardsFacade = $updateScoreboardsFacade;
    }

    protected function configure(): void
    {
        $this->setName('update-qualification-scoreboards');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->updateScoreboardsFacade)();
        return 0; // successful exit
    }
}
