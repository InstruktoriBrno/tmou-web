<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Commands;

use InstruktoriBrno\TMOU\Facades\System\CreateTestDataFacade;
use Nette\DI\Attributes\Inject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestDataCommand extends Command
{
    public static $defaultName = 'create-test-data';

    #[Inject]
    public CreateTestDataFacade $createTestDataFacade;

    public function __construct(CreateTestDataFacade $createTestDataFacade)
    {
        parent::__construct();
        $this->createTestDataFacade = $createTestDataFacade;
    }

    protected function configure(): void
    {
        $this->setName('create-test-data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->createTestDataFacade)();
        return 0; // successful exit
    }
}
