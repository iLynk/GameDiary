<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Traits\DateTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    //  utilisé pour générer le createdAt et updatedAt
    use DateTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom d\'utilisateur',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Woona',
                    'autofocus' => true
                )
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre mail',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'superGeek@gmail.com',
                )
            ])
            ->add('password', TextType::class, [
                'label' => 'Votre mot de passe',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'mot de passe',
                )
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
                'attr' => ['class' => 'form-register']
            ])
            // utilisation de l'event POST_SUBMIT pour avoir déjà l'entité User "crée"
            ->addEventListener(FormEvents::POST_SUBMIT, $this->dateTrait());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
