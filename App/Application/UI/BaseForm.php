<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Application\UI;

use Contributte\ReCaptcha\Forms\InvisibleReCaptchaField;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nextras\FormComponents\Controls\DateControl;
use Nextras\FormComponents\Controls\DateTimeControl;
use Nette\Utils\Html;

/**
 * @method InvisibleReCaptchaField addInvisibleReCaptcha(string $name)
 */
class BaseForm extends Form
{

    protected SubmitButton $primarySubmit;

    public function getPrimarySubmit(): SubmitButton
    {
        return $this->primarySubmit;
    }

    /**
     * @param string $name
     * @param string|Html $caption
     * @param bool $centered
     *
     * @return SubmitButton
     */
    public function addPrimarySubmit($name, $caption, $centered = false): SubmitButton
    {
        if (isset($this->primarySubmit)) {
            throw new \Nette\InvalidStateException("This form already has primary button [{$this->primarySubmit->getName()}].");
        }
        $temp = $this->addSubmit($name, $caption);
        if (!$centered) {
            $temp->setHtmlAttribute('class', 'btn-primary');
        } else {
            $temp->setHtmlAttribute('class', 'center-block btn-primary');
        }

        $this->primarySubmit = $temp;
        return $temp;
    }

    /**
     * @param string $name
     * @param string|Html $caption
     *
     * @return SubmitButton
     */
    public function addCancel($name, $caption): SubmitButton
    {
        return $this->addSubmit($name, $caption)
            ->setValidationScope(null);
    }

    public function disable(): void
    {
        foreach ($this->getComponents() as $component) {
            if ($component instanceof BaseControl) {
                $component->setDisabled();
            }
        }
    }

    public function enableAjax(): void
    {
        assert($this->form !== null);
        $this->form->getElementPrototype()->addAttributes(['class' => 'ajax']);
    }

    public function addDateTimePicker(string $name, string $label): DateTimeControl
    {
        $control = new DateTimeControl($label);
        $this->addComponent($control, $name);
        return $control;
    }

    public function addDatePicker(string $name, string $label): DateControl
    {
        $control = new DateControl($label);
        $this->addComponent($control, $name);
        return $control;
    }
}
