<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\ParcelSize;

class UpdateCourierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('courierCode', TextType::class)
        ->add('courierName', TextType::class)
        ->add('courierPrefix', TextType::class, [
            'required' => false
        ])
            ->add('sizeCode', EntityType::class, [
                'class' => ParcelSize::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.sizeCode', 'ASC');
                },
                'choice_label' => 'sizeName',
                'choice_value' => 'sizeCode',

            ])
        ->add('courierStatus', ChoiceType::class, [
            'choices'  => [
                'Active' => '1',
                'Inactive' => '0',
            ],
        ])
        ->add('update', SubmitType::class, array(
                'label' => 'แก้ไขข้อมูล',
                'attr' => array('class' => 'btn btn-outline-primary mt-3 float-right')
            )
        )
        // ->add('save', SubmitType::class, array(
        //     'label' => 'เพิ่มข้อมูล',
        //     'attr' => array('class' => 'btn btn-primary mt-3 float-right')
        // ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
