<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Qualification\Exceptions;

class InvalidXmlSchemaException extends \InstruktoriBrno\TMOU\Exceptions\RuntimeException
{
    /** @var string[] */
    private array $errors;

    /**
     * @param string[] $errors
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $errors, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /** @return string[] */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
