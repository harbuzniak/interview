<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Person;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3, 'max' => 100]),
                ]
            ])
            ->add('age', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 18, 'max' => 65], 'Age must be between 18 and 65'),
                ]
            ])
            ->add('address', AddressType::class, [
                'required' => false,
            ])
            ->add('contacts', CollectionType::class, [
                'entry_type' => PersonContactType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' =>false
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'csrf_protection' => false,
            'constraints' => [
                new UniqueEntity(['fields' => ['email']]),
            ]
        ]);
    }
}
