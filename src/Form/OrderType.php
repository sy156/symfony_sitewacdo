<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('total')
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En cours' => Order::STATUT_EN_COURS,
                    'En préparation' => Order::STATUT_EN_PREPARATION,
                    'Livrée' => Order::STATUT_LIVREE,
                    'Annulée' => Order::STATUT_ANNULEE,
                ],
                'label' => 'Statut',
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // plus lisible que 'id'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
