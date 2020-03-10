<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\MaterialInfo;
use Symfony\Component\Validator\Constraints\File;

class UpdateMaterialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('materialCode', TextType::class)
            ->add('materialName', TextType::class)
            ->add('materialFullName', TextType::class)
            ->add('materialStatus', ChoiceType::class, [
                'choices' => [
                    'Active' => '1',
                    'Inactive' => '0',
                ],
            ])
            ->add('materialImage', FileType::class, [
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
                'required' => false
            ])
            ->add('update', SubmitType::class, array(
                'label' => 'แก้ไขข้อมูล',
                'attr' => array('class' => 'btn btn-outline-primary mt-3 float-right')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
