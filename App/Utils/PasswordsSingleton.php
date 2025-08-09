<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Nette\Security\Passwords;

final class PasswordsSingleton
{
    private static Passwords $passwords;

    public function __construct(
        Passwords $passwords
    ) {
        self::$passwords = $passwords;
    }

    public function getPasswords(): Passwords
    {
        return self::$passwords;
    }

    public static function getPasswordsStatic(): Passwords
    {
        return self::$passwords;
    }
}
