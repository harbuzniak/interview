<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\Person;
use App\Form\PersonType;
use App\Message\SendEmailMessage;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PersonsController extends AbstractController
{
    public function __construct(private readonly SerializerInterface $serializer, private readonly EntityManagerInterface $em)
    {

    }
    public function show(?Person $person): Response
    {
        if ($person === null) {
            return new JsonResponse(['error' => 'Person not found'], Response::HTTP_NOT_FOUND);
        }
        $json = $this->serializer->serialize($person, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function list(PersonRepository $repository): Response
    {
        $persons = $repository->findAll();
        $json = $this->serializer->serialize($persons, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function new(Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(PersonType::class, $person = new Person());
        $form->submit($request->getPayload()->all());
        if (!$form->isValid()) {
            return new JsonResponse(['error' => 'Invalid data', 'details' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($person);
        $this->em->flush();
        $bus->dispatch(new SendEmailMessage($person->getId()));
        return new JsonResponse($this->serializer->serialize($person, 'json'), Response::HTTP_CREATED, [], true);
    }

    public function edit(?Person $person, Request $request): Response
    {
        if ($person === null) {
            return new JsonResponse(['error' => 'Person not found'], Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(PersonType::class, $person);
        $form->submit($request->getPayload()->all(), false);
        if (!$form->isValid()) {
            return new JsonResponse(['error' => 'Invalid data', 'details' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }
        $this->em->flush();
        return new JsonResponse($this->serializer->serialize($person, 'json'), Response::HTTP_OK, [], true);
    }

    public function delete(?Person $person): Response
    {
        if ($person === null) {
            return new JsonResponse(['error' => 'Person not found'], Response::HTTP_NOT_FOUND);
        }
        $this->em->remove($person);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        /** @var FormError $error */
        foreach ($form->getErrors(true, true) as $error) {
            $path = $this->cleanUpPropertyPath($error->getCause()->getPropertyPath());
            $errors[$path][] = $path . ': ' . $error->getMessage();
        }

        return $errors;
    }

    private function cleanUpPropertyPath(string $propertyPath): string
    {
        return str_replace(['children[', ']', '.data'], '', $propertyPath);
    }
}
