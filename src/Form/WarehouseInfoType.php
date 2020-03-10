<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\WarehouseInfo;

class WarehouseInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('warehouseCode', TextType::class)
            ->add('warehouseName', TextType::class)
            ->add('save', SubmitType::class,array(
                'label' => 'เพิ่มข้อมูล',
                'attr' => array('class' => 'btn btn-outline-primary mt-3 float-right')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
