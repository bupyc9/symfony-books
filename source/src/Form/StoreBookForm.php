<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Author;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class StoreBookForm extends AbstractForm
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Length(['max' => 255]),
                    ],
                ]
            )
            ->add('author', EntityType::class,
                  [
                      'class' => Author::class,
                      'constraints' => [
                          new NotBlank(),
                      ],
                  ]
            )
            ->add('year', IntegerType::class,
                  [
                      'constraints' => [
                          new NotBlank(),
                          new GreaterThan(['value' => 0]),
                      ],
                  ]
            )
            ->add('pages', IntegerType::class,
                  [
                      'constraints' => [
                          new NotBlank(),
                          new GreaterThan(['value' => 0]),
                      ],
                  ]
            );
    }
}
