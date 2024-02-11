<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Address;
use App\Entity\ApiUser;
use App\Entity\Person;
use App\Entity\PersonContact;
use App\Enum\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * @method ContainerInterface getContainer()
 * @property EntityManagerInterface $em:
 */
trait SharedContextTrait
{

    private function getEntityManager(): EntityManagerInterface
    {
        Assert::isInstanceOf($em = $this->getContainer()->get('doctrine.orm.entity_manager'), EntityManagerInterface::class);
        return $em;
    }

    private function getEmailSender(): EmailSenderDummy
    {
        Assert::isInstanceOf($sender = $this->getContainer()->get(EmailSenderDummy::class), EmailSenderDummy::class);
        return $sender;
    }

    private function givenApiUser(string $email): ApiUser
    {
        $u = new ApiUser();
        $u->setEmail($email);
        $u->setRoles(['ROLE_API']);
        $this->getEntityManager()->persist($u);
        $this->getEntityManager()->flush();
        return $u;
    }

    private function givenPerson(string $email, string $name, int $age): Person
    {
       $p = new Person();
       $p->setEmail($email);
       $p->setName($name);
       $p->setAge($age);
       $this->getEntityManager()->persist($p);
       $this->getEntityManager()->flush();
       return $p;
    }

    private function givenAddress(Person $person, string $city, string $postcode, string $street): Address
    {
        $address = new Address();
        $address->setCity($city);
        $address->setPostcode($postcode);
        $address->setStreet($street);
        $this->getEntityManager()->persist($address);
        $person->setAddress($address);
        $this->getEntityManager()->flush();
        return $address;
    }

    private function givenPersonContact(Person $person, ContactType $type, string $value): PersonContact
    {
        $contact = new PersonContact();
        $contact->setType($type);
        $contact->setValue($value);
        $this->getEntityManager()->persist($contact);
        $person->addContact($contact);
        $this->getEntityManager()->flush();
        return $contact;
    }
}
