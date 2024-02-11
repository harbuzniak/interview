<?php declare(strict_types=1);

namespace App\Security;

use App\Repository\ApiUserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public const API_KEY_HEADER = 'X-API-KEY';

    public function __construct(private readonly ApiUserRepository $repository)
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::API_KEY_HEADER);
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get(   self::API_KEY_HEADER);
        if(null === $apiKey) {
            throw new AuthenticationException('API Key is missing');
        }
        $user = $this->repository->findOneBy(['apiKey' => $apiKey]);
        if(null === $user) {
            throw new AuthenticationException('API Key is invalid');
        }
        return new SelfValidatingPassport(new UserBadge($user->getEmail()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Authentication Failed', Response::HTTP_FORBIDDEN);
    }
}
