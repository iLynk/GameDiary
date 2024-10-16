<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Review;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Traits\DateTrait;
;

class ReviewType extends AbstractType
{
    use DateTrait;
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rate', IntegerType::class, [
                'required' => true,
                'label' => 'Votre note',
                'empty_data' => 0,
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Votre avis',
            ])
            ->add('completed', CheckboxType::class, [
                'required' => false,
                'label' => 'Avez-vous terminé le jeu ?',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer mon avis'
            ])
        ->addEventListener(FormEvents::POST_SUBMIT, $this->dateTrait());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
