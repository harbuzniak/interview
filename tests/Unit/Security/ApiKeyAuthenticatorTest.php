<?php declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\ApiUser;
use App\Repository\ApiUserRepository;
use App\Security\ApiKeyAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ApiKeyAuthenticatorTest extends TestCase
{
    /** @var ApiKeyAuthenticator&MockObject */
    private $authenticator;
    /** @var ApiUserRepository&MockObject */
    private $repo;

    public function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->getMockBuilder(ApiUserRepository::class)->disableOriginalConstructor()->getMock();
        $this->authenticator = new ApiKeyAuthenticator($this->repo);
    }

    public function testSupport(): void
    {
        // GIVEN
        $request = new Request();

        //WHEN
        $request->headers->add([ApiKeyAuthenticator::API_KEY_HEADER => '123']);
        $isSupport = $this->authenticator->supports($request);
        //THEN
        $this->assertTrue($isSupport);

        //WHEN
        $request->headers->remove(ApiKeyAuthenticator::API_KEY_HEADER);
        $isSupport = $this->authenticator->supports($request);
        //THEN
        $this->assertFalse($isSupport);
    }

    public function testAuthenticateSuccess(): void
    {
        // GIVEN
        $apiKey = '123';
        $apiUser = new ApiUser();
        $apiUser->setEmail('a@u.c');
        $request = new Request();
        $this->repo->method('findOneBy')->with(['apiKey' => $apiKey])->willReturn($apiUser);

        //WHEN
        $request->headers->add([ApiKeyAuthenticator::API_KEY_HEADER => $apiKey]);
        $result = $this->authenticator->authenticate($request);
        //THEN
        $this->assertInstanceOf(Passport::class, $result);
        $this->assertCount(1, $result->getBadges());
        $this->assertArrayHasKey(UserBadge::class, $result->getBadges());
        $this->assertSame('a@u.c', $result->getBadges()[UserBadge::class]->getUserIdentifier());
    }

    public function testFailOnApiKeyMissing(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('API Key is missing');

        $request = new Request();
        $this->authenticator->authenticate($request);
    }

    public function testFailOnApiKeyNotFound(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('API Key is invalid');

        $apiKey = '123';
        $request = new Request();
        $this->repo->method('findOneBy')->with(['apiKey' => $apiKey])->willReturn(null);

        $request->headers->add([ApiKeyAuthenticator::API_KEY_HEADER => $apiKey]);
        $this->authenticator->authenticate($request);
    }
}
