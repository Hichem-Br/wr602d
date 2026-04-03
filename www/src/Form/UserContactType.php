<?php

namespace App\Form;

use App\Entity\UserContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Entrez le prénom']
            ])
            ->add('lastname', TextType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le nom']
            ])
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank(), new Email()],
                'label' => 'Adresse E-mail',
                'attr' => ['placeholder' => 'Entrez l\'adresse e-mail']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserContact::class,
        ]);
    }
}
