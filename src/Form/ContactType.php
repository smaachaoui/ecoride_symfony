<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Ex: Jean Dupont',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre nom']),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Votre nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'attr' => [
                    'placeholder' => 'Ex: jean.dupont@email.com',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre email']),
                    new Email(['message' => 'Veuillez saisir un email valide']),
                ],
            ])
            ->add('sujet', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'Ex: Question sur un covoiturage',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un sujet']),
                    new Length([
                        'min' => 5,
                        'max' => 150,
                        'minMessage' => 'Le sujet doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le sujet ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'placeholder' => 'Décrivez votre demande...',
                    'class' => 'form-control',
                    'rows' => 6,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre message']),
                    new Length([
                        'min' => 20,
                        'max' => 2000,
                        'minMessage' => 'Votre message doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre message ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}