<?php

namespace App\Form;

use App\Entity\Covoiturage;
use App\Entity\Vehicule;
use App\Repository\VehiculeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            // Date de départ
            ->add('date_depart_jour', DateType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une date de départ']),
                ],
            ])
            // Heure de départ
            ->add('heure_depart', TimeType::class, [
                'label' => 'Heure de départ',
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une heure de départ']),
                ],
            ])
            // Date d'arrivée
            ->add('date_arrivee_jour', DateType::class, [
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une date d\'arrivée']),
                ],
            ])
            // Heure d'arrivée
            ->add('heure_arrivee', TimeType::class, [
                'label' => 'Heure d\'arrivée',
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une heure d\'arrivée']),
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
                    $label = $vehicule->getMarque() . ' ' . $vehicule->getModele() . ' (' . $vehicule->getImmatriculation() . ')';
                    if ($vehicule->getEnergie() === 'Electrique' || $vehicule->getEnergie() === 'Hybride') {
                        $label .= ' 🌱';
                    }
                    return $label;
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
                    'class' => 'btn btn-primary btn-lg w-100',
                ],
            ]);

        // Pré-remplir les champs date/heure si le covoiturage existe déjà
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $covoiturage = $event->getData();
            $form = $event->getForm();

            if ($covoiturage && $covoiturage->getDateDepart()) {
                $form->get('date_depart_jour')->setData($covoiturage->getDateDepart());
                $form->get('heure_depart')->setData($covoiturage->getDateDepart());
            }

            if ($covoiturage && $covoiturage->getDateArrivee()) {
                $form->get('date_arrivee_jour')->setData($covoiturage->getDateArrivee());
                $form->get('heure_arrivee')->setData($covoiturage->getDateArrivee());
            }
        });

        // Combiner date et heure avant la soumission
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $covoiturage = $event->getData();
            $form = $event->getForm();

            // Combiner date et heure de départ
            $dateDepart = $form->get('date_depart_jour')->getData();
            $heureDepart = $form->get('heure_depart')->getData();

            if ($dateDepart && $heureDepart) {
                $dateTimeDepart = new \DateTime($dateDepart->format('Y-m-d') . ' ' . $heureDepart->format('H:i:s'));
                $covoiturage->setDateDepart($dateTimeDepart);
            }

            // Combiner date et heure d'arrivée
            $dateArrivee = $form->get('date_arrivee_jour')->getData();
            $heureArrivee = $form->get('heure_arrivee')->getData();

            if ($dateArrivee && $heureArrivee) {
                $dateTimeArrivee = new \DateTime($dateArrivee->format('Y-m-d') . ' ' . $heureArrivee->format('H:i:s'));
                $covoiturage->setDateArrivee($dateTimeArrivee);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Covoiturage::class,
        ]);
    }
}