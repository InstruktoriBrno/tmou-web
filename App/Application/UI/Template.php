<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Application\UI;

use Nette\Application\UI\Template as NetteTemplate;
use ReflectionClass;

/**
 * Trait for lookup and rendering of template with <name>.<view> + .latte extension
 */
trait Template
{
    /** @var string */
    protected $view;

    /**
     * Creates component template
     * @param string|null $class
     * @return NetteTemplate
     */
    protected function createTemplate(?string $class = null) : NetteTemplate
    {
        /** @var NetteTemplate $template */
        $template = parent::createTemplate();
        $this->prepareTemplateFile($template);
        return $template;
    }

    protected function createTemplateForView(string $view): NetteTemplate
    {
        $template = $this->createTemplate();
        $template->setFile(preg_replace('/\.[^.]+$/', '.' . $view . '.latte', $this->getFileName()));
        return $template;
    }

    private function getFileName() : string
    {
        $reflector = new ReflectionClass(static::class);
        if ($reflector->getFileName() === false) {
            throw new \InstruktoriBrno\TMOU\Application\Exceptions\CannotDetermineTemplateFileNameException;
        }
        return $reflector->getFileName();
    }

    protected function setView(string $name) : void
    {
        $this->view = $name;
    }

    public function render() : void
    {
        $this->prepareTemplateFile();

        $this->getTemplate()->render();
    }

    protected function prepareTemplateFile(?NetteTemplate $template = null) : void
    {
        if ($template === null) {
            /** @var NetteTemplate $template */
            $template = $this->getTemplate();
        }

        if ($this->view !== null) {
            $template->setFile(preg_replace('/\.[^.]+$/', '.' . $this->view . '.latte', $this->getFileName()));
        } else {
            $template->setFile(preg_replace('/\.[^.]+$/', '.latte', $this->getFileName()));
        }
    }

    /** @var NetteTemplate */
    abstract protected function getTemplate();
}
