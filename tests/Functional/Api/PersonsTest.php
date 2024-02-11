<?php declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Address;
use App\Entity\Person;
use App\Entity\PersonContact;
use App\Enum\ContactType;
use App\Tests\Functional\ApiTestTrait;
use App\Tests\Functional\SharedContextTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonsTest extends WebTestCase
{
    use ApiTestTrait, SharedContextTrait;

    /** @var EntityManagerInterface */
    private $em;

    public function setUp(): void
    {
        $this->em = $this->getEntityManager();
    }

    public function testGet(): void
    {
        // GIVEN
        $apiUser = $this->givenApiUser('api2@user.com');
        $client = $this->createApiClient(['apiKey' => $apiUser->getApiKey()]);

        $p1 = $this->givenPerson('ge@t.c', 'Name', 20);
        $a1 = $this->givenAddress($p1, 'City', '12345', 'Street');
        $p1c1 = $this->givenPersonContact($p1, ContactType::EMAIL, 'e@t.c');
        $p1c2 = $this->givenPersonContact($p1, ContactType::PHONE, '+1234567890');
        $p2 = $this->givenPerson('ge2@t.c', 'Name2', 21);
        $a2 = $this->givenAddress($p2, 'City2', '123-46', 'Street2');
        $p2c1 = $this->givenPersonContact($p2, ContactType::EMAIL, 'e2@t.c');

        // WHEN
        $response = $client->callApi('GET', '/api/persons');

        //THEN
        self::assertEquals(200, $response->getStatusCode());
        $items = json_decode($response->getContent(), true);
        self::assertCount(2, $items);
        self::assertSame(['id', 'name', 'age', 'contacts', 'address', 'email'], array_keys($items[0]));
        self::assertSame($p1->getName(), $items[0]['name']);
        self::assertSame($p2->getName(), $items[1]['name']);

        // WHEN
        $response = $client->callApi('GET', '/api/persons/'.$p1->getId());

        //THEN
        self::assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        self::assertSame($p1->getName(), $data['name']);
        self::assertArrayHasKey('contacts', $data);
        self::assertArrayHasKey('address', $data);
        self::assertArrayHasKey('email', $data);
        self::assertSame($p1->getAddress()?->getCity(), $data['address']['city']);
    }

    public function testPost(): void
    {
        // GIVEN
        $emailSender = $this->getEmailSender();
        $apiUser = $this->givenApiUser('pa@u.e');
        $client = $this->createApiClient(['apiKey' => $apiUser->getApiKey()]);

        // WHEN
        $response = $client->callApi('POST', '/api/persons',
            [
                'json' => $submitted = [
                    'email' => 'pe@t.c',
                    'name' => 'Name Surname',
                    'age' => 20,
                    'address' => [
                        'city' => 'City',
                        'postcode' => '12345',
                        'street' => 'Street'
                    ],
                    'contacts' => [
                        ['type' => ContactType::EMAIL->value, 'value' => 'e@t.c'],
                        ['type' => ContactType::PHONE->value, 'value' => '+1234567890']
                    ]
                ]
            ]
        );

        //THEN
        self::assertEquals(201, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('name', $data);
        self::assertSame($submitted['name'], $data['name']);
        self::assertArrayHasKey('address', $data);
        $address = $data['address'];
        self::assertArrayHasKey('city', $address);
        self::assertSame($submitted['address']['city'], $address['city']);
        self::assertArrayHasKey('contacts', $data);
        self::assertCount(2, $data['contacts']);
        self::assertArrayHasKey('value', $data['contacts'][0]);
        self::assertSame($submitted['contacts'][0]['value'], $data['contacts'][0]['value']);
        self::assertSame($submitted['contacts'][0]['type'], $data['contacts'][0]['type']);
        self::assertInstanceOf(Person::class, $emailSender->sentEmails[0]);
        self::assertArrayHasKey('id', $data);
        self::assertSame($data['id'], $emailSender->sentEmails[0]->getId());
    }

    public function testDelete(): void
    {
        // GIVEN
        $apiUser = $this->givenApiUser('da@u.e');
        $client = $this->createApiClient(['apiKey' => $apiUser->getApiKey()]);

        $p1 = $this->givenPerson('de@t.c', 'Name', 20);
        $a1 = $this->givenAddress($p1, 'City', '12345', 'Street');
        $p1c1 = $this->givenPersonContact($p1, ContactType::EMAIL, 'e@t.c');
        $p1c2 = $this->givenPersonContact($p1, ContactType::PHONE, '+1234567890');
        $p1Id = $p1->getId();
        $a1Id = $a1->getId();
        $p1c1Id = $p1c1->getId();
        $p1c2Id = $p1c2->getId();

        //WHEN
        $response = $client->callApi('DELETE', '/api/persons/'.$p1Id);

        //THEN
        self::assertEquals(204, $response->getStatusCode());
        self::assertNull($this->em->find(Person::class, $p1Id));
        self::assertNull($this->em->find(Address::class, $a1Id));
        self::assertNull($this->em->find(PersonContact::class, $p1c1Id));
        self::assertNull($this->em->find(PersonContact::class, $p1c2Id));

        //WHEN
        $response = $client->callApi('DELETE', '/api/persons/'.time());

        //THEN
        self::assertEquals(404, $response->getStatusCode());
        self::assertStringContainsString('Person not found', $response->getContent());
    }

    public function testPut(): void
    {
        // GIVEN
        $apiUser = $this->givenApiUser('pu@t.c');
        $client = $this->createApiClient(['apiKey' => $apiUser->getApiKey()]);
        $p1 = $this->givenPerson('pu@t.c', 'Name', 20);
        $a1 = $this->givenAddress($p1, 'City', '12345', 'Street');
        $p1c1 = $this->givenPersonContact($p1, ContactType::EMAIL, 'e@t.c');
        $this->givenPersonContact($p1, ContactType::PHONE, '+1234567890');

        // WHEN
        $response = $client->callApi('PUT', '/api/persons/'.$p1->getId(),
            [
                'json' => $submitted = [
                    'email' => 'new@t.c',
                    'name' => 'New Name Surname',
                    'age' => 30,
                    'address' => [
                        'city' => 'New City',
                        'postcode' => 'A12345',
                        'street' => 'newStreet'
                    ],
                    'contacts' => [
                        ['type' => ContactType::PHONE->value, 'value' => '+0987654321'],
                    ]
                ]
            ]
        );

        //THEN
        self::assertEquals(200, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('name', $data);
        self::assertSame($submitted['name'], $data['name']);
        self::assertArrayHasKey('address', $data);
        $address = $data['address'];
        self::assertArrayHasKey('city', $address);
        self::assertSame($submitted['address']['city'], $address['city']);
        self::assertArrayHasKey('contacts', $data);
        self::assertCount(1, $data['contacts']);
        self::assertSame($submitted['contacts'][0]['value'], $data['contacts'][0]['value']);
        self::assertSame($submitted['contacts'][0]['type'], $data['contacts'][0]['type']);

        $this->em->refresh($p1);
        self::assertSame(1, $p1->getContacts()->count());
        self::assertSame($p1c1->getId(), $p1->getContacts()->first()?->getId());
        self::assertSame($a1->getId(), $p1->getAddress()?->getId());
    }

    public function testPatch(): void
    {
        // GIVEN
        $apiUser = $this->givenApiUser('pa@t.c');
        $client = $this->createApiClient(['apiKey' => $apiUser->getApiKey()]);
        $p1 = $this->givenPerson('pa@t.c', 'Name', 20);
        $a1 = $this->givenAddress($p1, 'City', '12345', 'Street');
        $p1c1 = $this->givenPersonContact($p1, ContactType::EMAIL, 'e@t.c');
        $p1c2 = $this->givenPersonContact($p1, ContactType::PHONE, '+1234567890');

        // WHEN
        $response = $client->callApi('PATCH', '/api/persons/'.$p1->getId(),
            [
                'json' => $submitted = [
                    'name' => 'Patched Name Surname',
                    'age' => 25,
                    'address' => [
                        'street' => 'Patched Street'
                    ],
                    'contacts' => [
                        ['value' => '+00000000123'],
                    ]
                ]
            ]
        );

        //THEN
        self::assertEquals(200, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('name', $data);
        self::assertSame($submitted['name'], $data['name']);
        self::assertArrayHasKey('address', $data);
        $address = $data['address'];
        self::assertArrayHasKey('city', $address);
        self::assertSame($submitted['address']['street'], $address['street']);
        self::assertArrayHasKey('contacts', $data);
        self::assertCount(1, $data['contacts']);
        self::assertSame($submitted['contacts'][0]['value'], $data['contacts'][0]['value']);
        self::assertSame($p1c1->getType()->value, $data['contacts'][0]['type']);
        $this->em->refresh($p1);
        self::assertSame($submitted['address']['street'], $p1->getAddress()?->getStreet());
        self::assertSame('City', $p1->getAddress()->getCity());
        self::assertSame(1, $p1->getContacts()->count());
        self::assertSame($p1c1->getId(), $p1->getContacts()->first()?->getId());
    }

    public function testPostValidation(): void
    {
        // GIVEN
        $apiUser = $this->givenApiUser('pv@u.e');
        $client = $this->createApiClient(['apiKey' => $apiUser->getApiKey()]);

        // WHEN
        $response = $client->callApi('POST', '/api/persons',
            [
                'json' => [
                    'email' => 'non-email',
                    'name' => 'sn',
                    'age' => 10,
                    'address' => [
                        'city' => 'NY',
                        'postcode' => 'non_postcode',
                        'street' => ''
                    ],
                    'contacts' => [
                        ['type' => 'unknown', 'value' => 'e@t.c'],
                        ['type' => ContactType::PHONE->value, 'value' => '']
                    ]
                ]
            ]
        );

        //THEN
        $content = $response->getContent();
        self::assertEquals(400, $response->getStatusCode(), $content);
        self::assertStringContainsString('email: This value is not a valid email address.', $content);
        self::assertStringContainsString('name: This value is too short', $content);
        self::assertStringContainsString('age: Age must be between 18 and 65', $content);
        self::assertStringContainsString('address.city: This value is too short', $content);
        self::assertStringContainsString('address.postcode: Invalid postcode', $content);
        self::assertStringContainsString('address.street: This value should not be blank', $content);
        self::assertStringContainsString('contacts.0.type: The selected choice is invalid', $content);
        self::assertStringContainsString('contacts.1.value: This value should not be blank', $content);
    }
}
