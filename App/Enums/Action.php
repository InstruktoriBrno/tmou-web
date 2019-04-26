<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

final class Action extends Enum
{
    use AutoInstances;

    // Basic actions
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const CREATE = 'create';
    public const DELETE = 'delete';

    // Specific acttions
    public const LOGIN = 'login';
    public const REGISTER = 'register';
    public const LOGOUT = 'logout';
}
