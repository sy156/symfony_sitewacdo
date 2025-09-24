<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderStatutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En cours' => Order::STATUT_EN_COURS,
                    'En préparation' => Order::STATUT_EN_PREPARATION,
                    'Livrée' => Order::STATUT_LIVREE,
                    'Annulée' => Order::STATUT_ANNULEE,
                ],
                'label' => 'Statut de la commande',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
