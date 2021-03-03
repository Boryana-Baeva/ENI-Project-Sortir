<?php

namespace App\Form;

use App\Entity\Outing;

use Doctrine\ORM\EntityRepository;
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
                'label'=> 'Description',
                'required' => false
            ])
            ->add('place', EntityType::class, [
                'class'=>'App\Entity\Place',
                'label'=>'Lieu',
                'choice_label'=> 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.addDate', 'DESC');
                },
            ])
            ->add('campus',EntityType::class, [
                'class'=>'App\Entity\Campus',
                'label'=>'Campus',
                'choice_label'=> 'name',
                'disabled'=> true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,

        ]);
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'csrf_protection' => false,
            // Rest of options omitted
        );
    }
}
