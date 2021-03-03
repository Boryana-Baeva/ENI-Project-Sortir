<?php

namespace App\Form;

use App\Data\SearchData;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus',  EntityType::class,  [
            'label'=> 'Campus',
            'required'=>false,
            'class'=> 'App\Entity\Campus',
            'choice_label' => 'name',
            ])
            ->add('q', TextType::class, [
                'label' => false,
                'required'=>  false,
                'attr'=> [
                    'placeholder'=>'Rechercher'
                ]
            ])
            ->add('minDate', DateType::class, [
                'label' => 'Entre ',
                'required'=>  false,
                'attr'=> [
                    'placeholder'=>' / / '
                ]
            ])
            ->add('maxDate', DateType::class, [
                'label' => ' et ',
                'required'=>  false,
                'attr'=> [
                    'placeholder'=>' / / '
                ]
            ])
            ->add('organizer', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false
            ])
            ->add('pastOutings', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false
            ])
            ->add('subscribed', CheckboxType::class, [
                'label'=>'Sorties auquelles je suis inscrit/e',
                'required'=> false
            ])
            ->add('unsubscribed', CheckboxType::class, [
                'label'=>'Sorties auquelles je ne suis pas inscrit/e',
                'required'=> false
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
