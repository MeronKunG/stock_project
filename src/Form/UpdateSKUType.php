<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\MaterialInfo;
use App\Entity\SkuInfo;
use App\Entity\Bom;

class UpdateSKUType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('skuCode', TextType::class)
        ->add('skuName', TextType::class)
        ->add('materialSizeForm', HiddenType::class, array(
            'mapped' => false,
            'required' => false))
        ->add('hiddenForm', HiddenType::class, array(
            'mapped' => false,
            'required' => false))
        ->add('skuStatus', ChoiceType::class, [
                'choices'  => [
                    'Active' => '1',
                    'Inactive' => '0',
                ],
            ])
        ->add('update', SubmitType::class,array(
            'label' => 'แก้ไขข้อมูล',
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
