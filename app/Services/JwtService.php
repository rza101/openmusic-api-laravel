<?php

namespace App\Services;

use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;

class JwtService
{
    protected $jwtAccessTokenConfig;
    protected $jwtRefreshTokenConfig;

    public function __construct()
    {
        $jwtAccessTokenSecret = env('ACCESS_TOKEN_KEY', '');
        $jwtRefreshTokenSecret = env('REFRESH_TOKEN_KEY', '');

        if (empty($jwtAccessTokenSecret) || empty($jwtRefreshTokenSecret)) {
            throw new Exception('JWT secret is not set');
        }

        $this->jwtAccessTokenConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($jwtAccessTokenSecret)
        );
        $this->jwtRefreshTokenConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($jwtRefreshTokenSecret)
        );
    }

    public function generateAccessToken($user)
    {
        $now = new DateTimeImmutable();

        return $this->jwtAccessTokenConfig->builder()
            ->issuedBy('http://openmusic.api')
            ->permittedFor('http://openmusic.api')
            ->identifiedBy(bin2hex(random_bytes(16)), true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+15 minutes'))
            ->withClaim('userId', $user->id)
            ->getToken($this->jwtAccessTokenConfig->signer(), $this->jwtAccessTokenConfig->signingKey())
            ->toString();
    }

    public function generateRefreshToken($user)
    {
        $now = new DateTimeImmutable();

        return $this->jwtRefreshTokenConfig->builder()
            ->issuedBy('http://openmusic.api')
            ->permittedFor('http://openmusic.api')
            ->identifiedBy(bin2hex(random_bytes(16)), true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+24 hour'))
            ->withClaim('userId', $user->id)
            ->getToken($this->jwtRefreshTokenConfig->signer(), $this->jwtRefreshTokenConfig->signingKey())
            ->toString();
    }

    public function validateAccessToken($tokenString)
    {
        $token = $this->parseAccessToken($tokenString);
        $constraints = $this->jwtAccessTokenConfig->validationConstraints();

        return $this->jwtAccessTokenConfig->validator()->validate($token, ...$constraints);
    }

    public function validateRefreshToken($tokenString)
    {
        $token = $this->parseRefreshToken($tokenString);
        $constraints = $this->jwtRefreshTokenConfig->validationConstraints();

        return $this->jwtRefreshTokenConfig->validator()->validate($token, ...$constraints);
    }

    public function parseAccessToken($tokenString): Plain
    {
        return $this->jwtAccessTokenConfig->parser()->parse($tokenString);
    }

    public function parseRefreshToken($tokenString): Plain
    {
        return $this->jwtRefreshTokenConfig->parser()->parse($tokenString);
    }
}
