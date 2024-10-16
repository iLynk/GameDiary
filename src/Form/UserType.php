<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Traits\DateTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Image;

class UserType extends AbstractType
{
    use DateTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $options['is_edit'] ? 'Votre nouveau nom d\utilisateur' : 'Votre nom d\'utilisateur',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Woona',
                    'autofocus' => true
                ),
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'label' => $options['is_edit'] ? 'Votre nouveau mail' : 'Votre mail',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'superGeek@gmail.com',
                ),
                'empty_data' => '',
            ])
            // Si c'est une création, le champ mot de passe est obligatoire
            ->add('password', PasswordType::class, [
                'label' => $options['is_edit'] ? 'Votre nouveau mot de passe' : 'Votre mot de passe',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Mot de passe',
                ),
                'empty_data' => '',
            ])
            ->add('submit', SubmitType::class, ['label' => $options['is_edit'] ? 'Mettre à jour' : 'S\'inscrire',
            ])
            // utilisation de l'event POST_SUBMIT pour avoir déjà l'entité User "crée"
            ->addEventListener(FormEvents::POST_SUBMIT, $this->dateTrait());
    }


    public
    function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false, // Par défaut, ce n'est pas une édition
        ]);
    }
}
