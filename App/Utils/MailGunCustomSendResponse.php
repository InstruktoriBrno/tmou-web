<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Psr\Http\Message\ResponseInterface;

final class MailGunCustomSendResponse
{
    /** @var string */
    private $id;

    /** @var string */
    private $message;

    /** @var ResponseInterface */
    private $response;

    private function __construct()
    {
    }

    /**
     * @param array{id: mixed, message: mixed} $data
     * @param ResponseInterface $response
     * @return self
     */
    public static function create(array $data, ResponseInterface $response): self
    {
        $model = new self();
        $model->id = $data['id'] ?? '';
        $model->message = $data['message'] ?? '';
        $model->response = $response;

        return $model;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
