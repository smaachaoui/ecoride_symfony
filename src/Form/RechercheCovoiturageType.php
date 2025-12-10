<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;

class RechercheCovoiturageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champs de recherche principaux
            ->add('ville_depart', TextType::class, [
                'label' => 'Ville de départ',
                'attr' => [
                    'placeholder' => 'Ex: Paris',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une ville de départ']),
                ],
            ])
            ->add('ville_arrivee', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'attr' => [
                    'placeholder' => 'Ex: Lyon',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une ville d\'arrivée']),
                ],
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une date']),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être aujourd\'hui ou ultérieure',
                    ]),
                ],
            ])
            
            // Filtres avancés
            ->add('ecologique', CheckboxType::class, [
                'label' => 'Voyage écologique uniquement',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
            ->add('prix_max', IntegerType::class, [
                'label' => 'Prix maximum (crédits)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 50',
                    'class' => 'form-control',
                    'min' => 1,
                ],
                'constraints' => [
                    new Positive(['message' => 'Le prix doit être positif']),
                ],
            ])
            ->add('duree_max', ChoiceType::class, [
                'label' => 'Durée maximum',
                'required' => false,
                'placeholder' => 'Peu importe',
                'choices' => [
                    '1 heure' => 60,
                    '2 heures' => 120,
                    '3 heures' => 180,
                    '4 heures' => 240,
                    '5 heures' => 300,
                    '6 heures et plus' => 360,
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('note_min', ChoiceType::class, [
                'label' => 'Note minimale du chauffeur',
                'required' => false,
                'placeholder' => 'Peu importe',
                'choices' => [
                    '⭐ 1 et plus' => 1,
                    '⭐⭐ 2 et plus' => 2,
                    '⭐⭐⭐ 3 et plus' => 3,
                    '⭐⭐⭐⭐ 4 et plus' => 4,
                    '⭐⭐⭐⭐⭐ 5' => 5,
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            
            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-primary w-100',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}