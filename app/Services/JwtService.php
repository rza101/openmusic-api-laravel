<?php

namespace App\Services;

use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\HasClaim;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Component\Clock\NativeClock;

class JwtService
{
    private $jwtAccessTokenConfig;
    private $jwtRefreshTokenConfig;
    private $clock;

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
        $this->clock = new NativeClock();
    }

    public function generateAccessToken($user)
    {
        $now = new DateTimeImmutable();
        $builder = $this->jwtAccessTokenConfig->builder()
            ->issuedBy('http://openmusic.api') // iss
            ->permittedFor('http://openmusic.api') // aud
            ->expiresAt($now->modify('+15 minutes')) // exp
            ->canOnlyBeUsedAfter($now) // nbf
            ->issuedAt($now) // iat
            ->withClaim('userId', $user->id);
        $token = $builder->getToken(
            $this->jwtAccessTokenConfig->signer(),
            $this->jwtAccessTokenConfig->signingKey()
        );

        return $token->toString();
    }

    public function generateRefreshToken($user)
    {
        $now = new DateTimeImmutable();
        $builder = $this->jwtRefreshTokenConfig->builder()
            ->issuedBy('http://openmusic.api') // iss
            ->permittedFor('http://openmusic.api') // aud
            ->expiresAt($now->modify('+24 hour')) // exp
            ->canOnlyBeUsedAfter($now) // nbf
            ->issuedAt($now) // iat
            ->withClaim('userId', $user->id);
        $token = $builder->getToken(
            $this->jwtRefreshTokenConfig->signer(),
            $this->jwtRefreshTokenConfig->signingKey()
        );

        return $token->toString();
    }

    public function validateAccessToken($tokenString)
    {
        $token = $this->parseToken($tokenString);

        $validator = new Validator();
        $constraints = [
            new SignedWith(
                $this->jwtAccessTokenConfig->signer(),
                $this->jwtAccessTokenConfig->signingKey()
            ),
            new StrictValidAt($this->clock),
            new IssuedBy('http://openmusic.api'),
            new PermittedFor('http://openmusic.api'),
            new HasClaim('userId'),
        ];

        return $validator->validate($token, ...$constraints);
    }

    public function validateRefreshToken($tokenString)
    {
        $token = $this->parseToken($tokenString);

        $validator = new Validator();
        $constraints = [
            new SignedWith(
                $this->jwtRefreshTokenConfig->signer(),
                $this->jwtRefreshTokenConfig->signingKey()
            ),
            new StrictValidAt($this->clock),
            new IssuedBy('http://openmusic.api'),
            new PermittedFor('http://openmusic.api'),
            new HasClaim('userId'),
        ];

        return $validator->validate($token, ...$constraints);
    }

    public function parseToken($tokenString): Plain
    {
        return new Parser(new JoseEncoder())->parse($tokenString);
    }
}
