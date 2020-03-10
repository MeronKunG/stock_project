<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// use App\Entity\MaterialInfo;
use App\Entity\WarehouseInfo;
use Symfony\Component\Validator\Constraints\File;

class MaterialCheckInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $username = $options["data"][0]->getUsername();
        $builder
            ->add('checkInCode', TextType::class)
            ->add('checkInRefNo', TextType::class)
            ->add('checkInNote', TextareaType::class, array(
                'required' => false
            ))
            ->add('warehouseCode', EntityType::class, [
                'class' => WarehouseInfo::class,
                'query_builder' => function (EntityRepository $er) use ( $username ) {
                    return $er->createQueryBuilder('w')
                        ->join('App\Entity\User', 'u','WITH','w.warehouseCode = u.defaultWarehouse')
                        ->where('w.warehouseStatus = 1')
                        ->andWhere('u.username = :username')
                        ->setParameter('username', $username)
                        ->orderBy('w.warehouseName', 'ASC');
                },
                'choice_label' => 'warehouseName',
                'choice_value' => 'warehouseCode',

            ])
            ->add('checkInImage', FileType::class, [
                'mapped' => false,
                'label' => 'เลือกรูปภาพ',
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'กรุณาอัพโหลดไฟล์รูปภาพเท่านั้น',
                    ]),
                ],
                'required' => true
            ])
            ->add('materialName', TextType::class, array(
                'required' => false
            ))
            ->add('hiddenForm', HiddenType::class)
            ->add('save', SubmitType::class, array(
                'label' => 'เพิ่มข้อมูล',
                'attr' => array('class' => 'btn btn-outline-primary mt-3  float-right')
            ));
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
