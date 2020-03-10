<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\MaterialInfo;
use App\Entity\SkuInfo;
use App\Entity\Bom;

class SkuInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('skuCode', TextType::class)
        ->add('skuName', TextType::class)
        ->add('hiddenForm', HiddenType::class)
        ->add('save', SubmitType::class,array(
            'label' => 'เพิ่มข้อมูล',
            'attr' => array('class' => 'btn btn-outline-primary mt-3 float-right')
        ))
        ;
        // ->add('materialName', EntityType::class, [
        //     'class' => MaterialInfo::class,
        //     'query_builder' => function (EntityRepository $er) {
        //         return $er->createQueryBuilder('u')
        //         ->orderBy('u.materialId', 'ASC');
        //     },
        //     'placeholder' => 'กรุณาเลือกสินค้า',
        //     'choice_label' => 'materialName',
        //     'choice_value' => 'materialCode',
        // ])
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
