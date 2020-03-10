<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\WarehouseInfo;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UpdateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('userStatus', ChoiceType::class, [
                'choices' => [
                    'Active' => '1',
                    'Inactive' => '0',
                ],
            ])
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
                'label' => 'แก้ไขข้อมูล',
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
