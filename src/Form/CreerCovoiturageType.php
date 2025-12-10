<?php

namespace App\Form;

use App\Entity\Covoiturage;
use App\Entity\Vehicule;
use App\Repository\VehiculeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class CreerCovoiturageType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            ->add('ville_depart', TextType::class, [
                'label' => 'Ville de départ',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Paris',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une ville de départ']),
                ],
            ])
            ->add('ville_arrivee', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Lyon',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une ville d\'arrivée']),
                ],
            ])
            ->add('date_depart', DateTimeType::class, [
                'label' => 'Date et heure de départ',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime('+1 hour'))->format('Y-m-d\TH:i'),
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une date de départ']),
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'La date de départ doit être dans le futur',
                    ]),
                ],
            ])
            ->add('prix', IntegerType::class, [
                'label' => 'Prix par passager (en crédits)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 3,
                    'placeholder' => 'Ex: 10',
                ],
                'help' => '2 crédits seront prélevés par la plateforme. Vous recevrez le reste.',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un prix']),
                    new GreaterThanOrEqual([
                        'value' => 3,
                        'message' => 'Le prix minimum est de 3 crédits (2 pour la plateforme + 1 pour vous)',
                    ]),
                ],
            ])
            ->add('places_restantes', IntegerType::class, [
                'label' => 'Nombre de places disponibles',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 8,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir le nombre de places']),
                    new Positive(['message' => 'Le nombre de places doit être positif']),
                ],
            ])
            ->add('vehicule_id', EntityType::class, [
                'class' => Vehicule::class,
                'label' => 'Véhicule',
                'choice_label' => function (Vehicule $vehicule) {
                    return $vehicule->getMarque() . ' ' . $vehicule->getModele() . ' (' . $vehicule->getImmatriculation() . ')';
                },
                'query_builder' => function (VehiculeRepository $repo) use ($user) {
                    return $repo->createQueryBuilder('v')
                        ->where('v.utilisateur_id = :user')
                        ->setParameter('user', $user)
                        ->orderBy('v.marque', 'ASC');
                },
                'placeholder' => 'Sélectionnez un véhicule',
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un véhicule']),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier le covoiturage',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Covoiturage::class,
        ]);
    }
}