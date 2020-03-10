<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\MaterialInfo;

class MaterialTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
