<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * @method static GameStatus REGISTERED()
 * @method static GameStatus QUALIFIED()
 * @method static GameStatus NOT_QUALIFIED()
 * @method static GameStatus PLAYING()
 */
final class GameStatus extends Enum
{
    use AutoInstances;

    private const REGISTERED = 'REGISTERED';
    private const QUALIFIED = 'QUALIFIED';
    private const NOT_QUALIFIED = 'NOT_QUALIFIED';
    private const PLAYING = 'PLAYING';
}
