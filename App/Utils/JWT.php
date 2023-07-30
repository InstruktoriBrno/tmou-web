<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

/**
 * Simple wrapper above our configuration and firebase/jwt-php library
 */
class JWT
{

    private string $secretKey;

    private string $algorithm;

    public function __construct(string $secretKey, string $algorithm)
    {
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
    }

    /**
     * @param array<string, mixed> $data
     * @return string
     */
    public function encode(array $data): string
    {
        return FirebaseJWT::encode($data, $this->secretKey, $this->algorithm);
    }

    /**
     * @param string $token
     * @return object
     */
    public function decode(string $token)
    {
        return FirebaseJWT::decode($token, new Key($this->secretKey, $this->algorithm));
    }
}
