<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Facades\Files\ChangeStorageFileFacade;
use InstruktoriBrno\TMOU\Facades\Files\DeleteStorageFileFacade;
use InstruktoriBrno\TMOU\Forms\ChangeFileFormFactory;
use InstruktoriBrno\TMOU\Forms\CreateNewDirectoryFormFactory;
use InstruktoriBrno\TMOU\Forms\UploadFileFormFactory;
use InstruktoriBrno\TMOU\Services\Files\CreateNewDirectoryInStorageDirectoryService;
use InstruktoriBrno\TMOU\Services\Files\FindStorageDirectoriesService;
use InstruktoriBrno\TMOU\Services\Files\FindStorageSubdirFilesService;
use InstruktoriBrno\TMOU\Services\Files\UploadToStorageDirectoryService;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
use Tracy\Debugger;
use Tracy\ILogger;
use function realpath;
use Nette\DI\Attributes\Inject;

final class FilesPresenter extends BasePresenter
{
    #[Inject]
    public FindStorageDirectoriesService $findStorageDirectoriesService;

    #[Inject]
    public FindStorageSubdirFilesService $findStorageSubdirFilesService;

    #[Inject]
    public CreateNewDirectoryFormFactory $createNewDirectoryFormFactory;

    #[Inject]
    public UploadFileFormFactory $uploadFileFormFactory;

    #[Inject]
    public UploadToStorageDirectoryService $uploadToStorageDirectoryService;

    #[Inject]
    public CreateNewDirectoryInStorageDirectoryService $createNewDirectoryInStorageDirectoryService;

    #[Inject]
    public DeleteStorageFileFacade $deleteStorageFileFacade;

    #[Inject]
    public ChangeFileFormFactory $changeFileFormFactory;

    #[Inject]
    public ChangeStorageFileFacade $changeStorageFileFacade;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_FILES,InstruktoriBrno\TMOU\Enums\Action::MANAGE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDefault(?string $subdir = null): void
    {
        $this->template->showedSubdir = $subdir ?? '/';
        $this->template->showedSubdirWithoutStartingSlash = Strings::startsWith($this->template->showedSubdir, '/')
            ? Strings::substring($this->template->showedSubdir, 1)
            : $this->template->showedSubdir;
        $this->template->dirs = ($this->findStorageDirectoriesService)();
        $this->template->files = ($this->findStorageSubdirFilesService)($subdir);
        $this->template->wwwDir = realpath(__DIR__ . '/../../www/');
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_FILES,InstruktoriBrno\TMOU\Enums\Action::MANAGE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function handleShowFolder(?string $subdir): void
    {
        try {
            $this->template->files = ($this->findStorageSubdirFilesService)($subdir);
        } catch (\InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException $exception) {
            Debugger::log($exception, ILogger::WARNING);
            throw new \Nette\Application\BadRequestException('Cannot access.', 403);
        }

        if ($this->isAjax()) {
            $this->redrawControl('files');
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_FILES,InstruktoriBrno\TMOU\Enums\Action::MANAGE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function handleDeleteFile(string $name): void
    {
        $subdir = $this->getParameter('subdir');
        try {
            $wasDirectory = ($this->deleteStorageFileFacade)($name, $subdir);
            if ($wasDirectory) {
                $this->template->dirs = ($this->findStorageDirectoriesService)();
                $this->redrawControl('folders');
            }
            $this->template->files = ($this->findStorageSubdirFilesService)($subdir);
        } catch (\InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileDeleteFailedException $exception) {
            Debugger::log($exception, ILogger::EXCEPTION);
            $this->flashMessage("Smazání souboru {$name} selhalo");
        }

        if ($this->isAjax()) {
            $this->redrawControl('files');
        } else {
            $this->redirect('this');
        }
    }

    public function createComponentUploadForm(): Form
    {
        return $this->uploadFileFormFactory->create(function (Form $form, ArrayHash $values): void {
            if (!($this->user->isAllowed(Resource::ADMIN_FILES, Action::MANAGE))) {
                $this->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $this->redrawControl('files');
                return;
            }
            $subdir = $this->getParameter('subdir');
            /** @var FileUpload[] $fileUploads */
            $fileUploads = $values->files;
            [$stored, $skipped] = ($this->uploadToStorageDirectoryService)($fileUploads, $values->overwrite, $subdir);
            $this->flashMessage("Nahráno bylo {$stored} souborů, přeskočeno bylo {$skipped} souborů.", Flash::SUCCESS);
            $this->template->files = ($this->findStorageSubdirFilesService)($subdir);
            if ($this->isAjax()) {
                $this->redrawControl('files');
            } else {
                $this->redirect('this');
            }
        });
    }

    public function createComponentNewDirectoryForm(): Form
    {
        return $this->createNewDirectoryFormFactory->create(function (Form $form, ArrayHash $values): void {
            if (!($this->user->isAllowed(Resource::ADMIN_FILES, Action::MANAGE))) {
                $this->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $this->redrawControl('files');
                return;
            }
            $subdir = $this->getParameter('subdir');
            try {
                ($this->createNewDirectoryInStorageDirectoryService)($values->name, $subdir);
            } catch (\InstruktoriBrno\TMOU\Services\Files\Exceptions\NewDirectoryAlreadyExistsException $exception) {
                $this->flashMessage("Adresář {$values->name} již existuje.", Flash::DANGER);
                $this->redrawControl('files');
                return;
            } catch (\InstruktoriBrno\TMOU\Services\Files\Exceptions\NewDirectoryCreationException $exception) {
                $this->flashMessage("Vytvoření adresáře {$values->name} selhalo, kontaktujte, prosím správce.", Flash::DANGER);
                $this->redrawControl('files');
                return;
            }
            $this->flashMessage("Adresář {$values->name} byl úspěšně vytvořen.", Flash::SUCCESS);
            $form->setDefaults([], true);
            if ($this->isAjax()) {
                $this->template->dirs = ($this->findStorageDirectoriesService)();
                $this->redrawControl('folders');
                $this->redrawControl('files');
            } else {
                $this->redirect('this');
            }
        });
    }

    public function createComponentChangeFileForm(): Form
    {
        return $this->changeFileFormFactory->create(function (Form $form, ArrayHash $values): void {
            if (!($this->user->isAllowed(Resource::ADMIN_FILES, Action::MANAGE))) {
                $this->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $this->redrawControl('files');
                return;
            }
            $subdir = $this->getParameter('subdir');
            try {
                ($this->changeStorageFileFacade)($subdir, $values);
            } catch (\InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileAlreadyExistsException $exception) {
                $this->flashMessage("Soubor {$values->name} již v cílovém adresáři existuje.", Flash::DANGER);
                $this->redrawControl('files');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileNotFoundException $exception) {
                $this->flashMessage("Zdrojový soubor {$values->original} již zřejmě neexistuje, nepřejmenovali jste jej vy nebo někdo jiný?", Flash::DANGER);
                $this->redrawControl('files');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileMoveFailedException $exception) {
                $this->flashMessage("Přejmenování či přesunutí souboru {$values->original} selhalo, kontaktujte, prosím, správce.", Flash::DANGER);
                $this->redrawControl('files');
                return;
            }
            $this->flashMessage("Soubor {$values->original} byl úspěšně upraven.", Flash::SUCCESS);
            $this->template->files = ($this->findStorageSubdirFilesService)($subdir);
            if ($this->isAjax()) {
                $this->redrawControl('folders');
                $this->redrawControl('files');
            } else {
                $this->redirect('this');
            }
        });
    }
}
