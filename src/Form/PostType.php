<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Users;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('slug', null, ['required' => false])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé'
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu de l\'article'
            ])
            ->add('created_at', null, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de création',
            ])
            ->add('updated_at', null, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de modification',
            ])
            ->add('author', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'full_name',
                'label' => 'Auteur',
            ])
            ->add('modifier', SubmitType::class)
            ->addEventListener(FormEvents:: PRE_SUBMIT, $this->autoSlug(...))
            ->addEventListener(FormEvents:: PRE_SUBMIT, $this->attachTimestamps(...))
        ;
    }

    public function autoSlug(PreSubmitEvent $event): void
    {
        $data = $event->getData();
        if(empty($data['slug'])) {
            $slugger = new AsciiSlugger();
            $data['slug'] = strtolower($slugger->slug($data['title']));
            $event->setData($data);
        }
    }

    public function attachTimestamps(PreSubmitEvent $event): void
    {
        $data = $event->getData();
        $data['updated_at'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        if (empty($data['created_at'])) {
            $data['created_at'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        }
    
        $event->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'csrf_protection' => false,
        ]);
    }
}
