<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentairesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('auteur', TextType::class, [
                'label' => "Votre nom/pseudo",
                'required' => false,
                'attr' => [
                    'placeholder' => "Saisir votre nom/pseudo"
                    
                ],
                'constraints' => [
                    new Length([
                        'min' => 5,
                        'max' => 50,
                        'minMessage' => "Nom/pseudo trop court (min 5 caractères)",
                        'maxMessage' => "Nom/pseudo trop long (max 50 caractères)"
                    ]),
                    new NotBlank([
                        'message' => "Merci de saisir un nom/pseudo"
                    ])
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => "Commentaire",
                'required' => false,
                'attr' => [
                    'placeholder' => "Saisir le contenu du commentaire ",
                    'rows' => 10
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => "Merci de saisir un commentaire"
                    ])
                ]
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
