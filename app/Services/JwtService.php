<?php

namespace App\Services;

use App\Models\User;
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
    private $accessTokenConfig;
    private $refreshTokenConfig;
    private $clock;

    public function __construct()
    {
        $accessTokenSecret = env('ACCESS_TOKEN_KEY', '');
        $refreshTokenSecret = env('REFRESH_TOKEN_KEY', '');

        if (empty($accessTokenSecret) || empty($refreshTokenSecret)) {
            throw new Exception('JWT secret is not set');
        }

        $this->accessTokenConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($accessTokenSecret)
        );
        $this->refreshTokenConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($refreshTokenSecret)
        );
        $this->clock = new NativeClock();
    }

    public function generateAccessToken(User $user)
    {
        $now = new DateTimeImmutable();
        $builder = $this->accessTokenConfig->builder()
            ->issuedBy('http://openmusic.api') // iss
            ->permittedFor('http://openmusic.api') // aud
            ->expiresAt($now->modify('+15 minutes')) // exp
            ->canOnlyBeUsedAfter($now) // nbf
            ->issuedAt($now) // iat
            ->withClaim('userId', $user->id);
        $token = $builder->getToken(
            $this->accessTokenConfig->signer(),
            $this->accessTokenConfig->signingKey()
        );

        return $token->toString();
    }

    public function generateRefreshToken(User $user)
    {
        $now = new DateTimeImmutable();
        $builder = $this->refreshTokenConfig->builder()
            ->issuedBy('http://openmusic.api') // iss
            ->permittedFor('http://openmusic.api') // aud
            ->expiresAt($now->modify('+24 hour')) // exp
            ->canOnlyBeUsedAfter($now) // nbf
            ->issuedAt($now) // iat
            ->withClaim('userId', $user->id);
        $token = $builder->getToken(
            $this->refreshTokenConfig->signer(),
            $this->refreshTokenConfig->signingKey()
        );

        return $token->toString();
    }

    public function validateAccessToken(string $token)
    {
        $validator = new Validator();
        $constraints = [
            new SignedWith(
                $this->accessTokenConfig->signer(),
                $this->accessTokenConfig->signingKey()
            ),
            new StrictValidAt($this->clock),
            new IssuedBy('http://openmusic.api'),
            new PermittedFor('http://openmusic.api'),
            new HasClaim('userId'),
        ];

        return $validator->validate(
            $this->parseToken($token),
            ...$constraints
        );
    }

    public function validateRefreshToken(string $token)
    {
        $validator = new Validator();
        $constraints = [
            new SignedWith(
                $this->refreshTokenConfig->signer(),
                $this->refreshTokenConfig->signingKey()
            ),
            new StrictValidAt($this->clock),
            new IssuedBy('http://openmusic.api'),
            new PermittedFor('http://openmusic.api'),
            new HasClaim('userId'),
        ];

        return $validator->validate(
            $this->parseToken($token),
            ...$constraints
        );
    }

    public function parseToken(string $token): Plain
    {
        return new Parser(new JoseEncoder())->parse($token);
    }
}
