<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// use App\Entity\MaterialInfo;
use App\Entity\WarehouseInfo;
use Symfony\Component\Validator\Constraints\File;

class MaterialCheckOutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $username = $options["data"][0]->getUsername();
        $builder
            ->add('checkOutCode', TextType::class)
            ->add('checkOutRefNo', TextType::class)
            ->add('checkOutType', ChoiceType::class, [
                'choices' => [
                    'สินค้าไม่ผ่าน QC' => 1,
                    'เบิกเพื่อทดสอบ' => 2,
                    'อื่นๆ' => 3,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('warehouseCode', TextType::class)
            ->add('checkOutImage', FileType::class, [
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
