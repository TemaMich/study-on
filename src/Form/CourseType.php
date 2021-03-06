<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'attr' => ['placeholder' => "Code"],
                'constraints' =>	[
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Код должен быть больше {{ limit }} символов',
                    ])
                ],
            ])
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => "Title"],
                'constraints' =>	[
                    new Length([
                        'min' => 4,
                        'minMessage' => 'Имя должно быть больше {{ limit }} символов',
                        'max' => 255,
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['placeholder' => "Content"],
                "constraints"   =>  [
                    new Length([
                        'min'   => 10,
                        'minMessage' => 'Контент должен быть больше {{ limit }} символов',
                    ])
                ],
            ])
            ->add('price', TextType::class, [
                'label' => "Цена",
            ])
            ->add('type', IntegerType::class, [
                'label' => "Тип",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
