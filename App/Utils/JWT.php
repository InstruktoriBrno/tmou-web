<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Firebase\JWT\JWT as FirebaseJWT;
use stdClass;

/**
 * Simple wrapper above our configuration and firebase/jwt-php library
 */
class JWT
{

    /** @var string */
    private $secretKey;

    /** @var string */
    private $algorithm;

    public function __construct(string $secretKey, string $algorithm)
    {
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
    }

    public function encode(array $data): string
    {
        return FirebaseJWT::encode($data, $this->secretKey, $this->algorithm);
    }

    public function decode(string $token): stdClass
    {
        return FirebaseJWT::decode($token, $this->secretKey, [$this->algorithm]);
    }

}
