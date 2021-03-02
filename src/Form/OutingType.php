<?php

namespace App\Form;

use App\Entity\Outing;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'=>'Nom de la sortie'
            ])
            ->add('startDateTime', DateTimeType::class,[
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second'
                ],
                'label' => 'Date et heure de la sortie'
            ])
            ->add('duration', IntegerType::class, [
                'label'=> 'Durée'
            ])
            ->add('entryDeadline',DateTimeType::class,[
        'placeholder' => [
            'year' => 'Year', 'month' => 'Month', 'day' => 'Day'
        ],
        'label' => 'Date limite d\'inscription'
    ])
            ->add('maxNumberEntries', IntegerType::class , [
                'label'=> 'Nombre de places'
            ])
            ->add('description', TextType::class, [
                'label'=> 'Description'
            ])

            ->add('place', EntityType::class, [
                'class'=>'App\Entity\Place',
                'label'=>'Lieu',
                'choice_label'=> 'name'
            ])

            ->add('campus',EntityType::class, [
                'class'=>'App\Entity\Campus',
                'label'=>'Campus',
                'choice_label'=> 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
    }
}
