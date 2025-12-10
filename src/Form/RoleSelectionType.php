<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', ChoiceType::class, [
                'label' => 'Je souhaite être',
                'choices' => [
                    'Passager uniquement' => Utilisateur::ROLE_PASSAGER,
                    'Chauffeur uniquement' => Utilisateur::ROLE_CHAUFFEUR,
                    'Chauffeur et Passager' => Utilisateur::ROLE_CHAUFFEUR_PASSAGER,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'role-selection',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider mon choix',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}