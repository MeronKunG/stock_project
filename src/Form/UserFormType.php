<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\WarehouseInfo;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'รหัสผ่านควรมีอย่างน้อย {{ limit }} ตัว',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('email', EmailType::class)
            ->add('defaultWarehouse', EntityType::class, [
                'class' => WarehouseInfo::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.warehouseStatus = 1')
                        ->orderBy('u.warehouseName', 'ASC');
                },
                'choice_label' => 'warehouseName',
                'choice_value' => 'warehouseCode',

            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_SUPER_USER' => 'ROLE_SUPER_USER',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                ],
                'mapped' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'เพิ่มข้อมูล',
                'attr' => [
                    'class' => 'btn btn-outline-primary mt-3 float-right'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
