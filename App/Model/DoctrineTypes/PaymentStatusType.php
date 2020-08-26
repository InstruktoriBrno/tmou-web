<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
use function is_string;
use Nette\Utils\Strings;
use function sprintf;

class PaymentStatusType extends Type
{
    public const NAME = 'paymentStatusType';
    private const LENGTH = 128;

    /**
     * @param mixed[] $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return sprintf('VARCHAR(%d)', self::LENGTH);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return PaymentStatus|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?PaymentStatus
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            try {
                return PaymentStatus::fromScalar($value);
            } catch (\Grifart\Enum\MissingValueDeclarationException $e) {
                throw new \InstruktoriBrno\TMOU\Model\Exceptions\ConvertToPhpValueException('Could not convert given database value to PHP.', 0, $e);
            }
        }
        throw new \InstruktoriBrno\TMOU\Model\Exceptions\ConvertToPhpValueException('Could not convert given database value to PHP.');
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof PaymentStatus) {
            $valueString = $value->toScalar();
            if (Strings::length($valueString) > self::LENGTH) {
                $errorMessage = sprintf('Value "%s" has a length greater than %d.', $valueString, self::LENGTH);
                throw new \InstruktoriBrno\TMOU\Model\Exceptions\ConvertToDatabaseValueException($errorMessage);
            }
            return (string) $valueString;
        }
        throw new \InstruktoriBrno\TMOU\Model\Exceptions\ConvertToDatabaseValueException('Unexpected value when converting to database value.');
    }
}
