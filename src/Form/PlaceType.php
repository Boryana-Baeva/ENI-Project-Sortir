<?php

namespace App\Form;

use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'=>'Nom du Lieu',
            ])
            ->add('street', TextType::class, [
                'label'=>'rue',
            ])
            /*->add('zipCode', EntityType::class, [
                'class'=> 'App\Entity\City',
                'choice_label'=>'zipCode',
                'label'=>'Code Postal',
            ])*/
            ->add('city', EntityType::class, [
                'class'=> 'App\Entity\City',
                'choice_label'=>'name',
                'label'=>'Ville'
            ])
            ->add('latitude', TextType::class, [
                'label'=> 'Latitude'
            ])
            ->add('longitude', TextType::class, [
                'label'=> 'Longitude'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}