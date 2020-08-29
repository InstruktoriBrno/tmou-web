<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\System;

use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\SmartObject;
use function is_string;

class RememberedNicknameService
{
    use SmartObject;

    private const SESSION_NAMESPACE = 'remembered';
    private const KEY = 'nickname';

    private SessionSection $sessionSection;

    public function __construct(Session $session)
    {
        $this->sessionSection = $session->getSection(self::SESSION_NAMESPACE);
    }

    public function get(): ?string
    {
        $value = $this->sessionSection->offsetGet(self::KEY);
        if (is_string($value) && $value !== '') {
            return $value;
        }
        return null;
    }

    public function set(string $nickname): void
    {
        $this->sessionSection->offsetSet(self::KEY, $nickname);
    }
}
