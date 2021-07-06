<?php

namespace App\Form;

use App\Entity\Lesson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Название урока",
                "constraints"   =>  [
                    new Length([
                        'min'   => 5,
                        'minMessage' => 'Название должно быть больше {{ limit }} символов',
                        'max' => 1000,
                    ])
                ]
            ])
            ->add('content', TextType::class, [
                'label' => "Контент урока",
                "constraints"   =>  [
                    new Length([
                        'min'   => 10,
                        'minMessage' => 'Контент должен быть больше {{ limit }} символов',
                    ])
                ]
            ])
            ->add('number', IntegerType::class, [
                'label' => 'Номер урока'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}