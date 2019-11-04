<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Application\UI;

use Contributte\ReCaptcha\Forms\InvisibleReCaptchaField;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nextras\Forms\Controls\DatePicker;
use Nextras\Forms\Controls\DateTimePicker;

/**
 * @method DateTimePicker addDateTimePicker(string $name, string $label)
 * @method DatePicker addDatePicker(string $name, string $label)
 * @method InvisibleReCaptchaField addInvisibleReCaptcha(string $name)
 */
class BaseForm extends Form
{

    /** @var SubmitButton */
    protected $primarySubmit;

    public function getPrimarySubmit(): SubmitButton
    {
        return $this->primarySubmit;
    }

    /**
     * @param string $name
     * @param string|object $caption
     * @param bool $centered
     *
     * @return SubmitButton
     */
    public function addPrimarySubmit($name, $caption, $centered = false): SubmitButton
    {
        if ($this->primarySubmit !== null) {
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
     * @param string|object $caption
     *
     * @return SubmitButton
     */
    public function addCancel($name, $caption): SubmitButton
    {
        return $this->addSubmit($name, $caption)
            ->setValidationScope(false);
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
}
