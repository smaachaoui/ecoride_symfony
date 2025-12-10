<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Renault, Peugeot, Tesla...',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir la marque du véhicule']),
                ],
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Clio, 308, Model 3...',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir le modèle du véhicule']),
                ],
            ])
            ->add('immatriculation', TextType::class, [
                'label' => 'Plaque d\'immatriculation',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: AB-123-CD',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir la plaque d\'immatriculation']),
                    new Regex([
                        'pattern' => '/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/',
                        'message' => 'Format attendu : AB-123-CD',
                    ]),
                ],
            ])
            ->add('energie', ChoiceType::class, [
                'label' => 'Type d\'énergie',
                'choices' => [
                    'Essence' => 'Essence',
                    'Diesel' => 'Diesel',
                    'Électrique' => 'Electrique',
                    'Hybride' => 'Hybride',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
                'placeholder' => 'Sélectionnez le type d\'énergie',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner le type d\'énergie']),
                ],
            ])
            ->add('couleur', TextType::class, [
                'label' => 'Couleur',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Noir, Blanc, Rouge...',
                ],
            ])
            ->add('date_premiere_immatriculation', DateType::class, [
                'label' => 'Date de première immatriculation',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'max' => (new \DateTime())->format('Y-m-d'),
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir la date de première immatriculation']),
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date ne peut pas être dans le futur',
                    ]),
                ],
            ])
            ->add('places_disponibles', IntegerType::class, [
                'label' => 'Nombre de places disponibles (hors conducteur)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 8,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir le nombre de places']),
                    new Range([
                        'min' => 1,
                        'max' => 8,
                        'notInRangeMessage' => 'Le nombre de places doit être entre {{ min }} et {{ max }}',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer le véhicule',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}