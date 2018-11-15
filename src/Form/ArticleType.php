<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre')
            ->add('sous_titre')
            ->add('contenu', TextareaType::class, array(
                'attr' => array(
                    'id' => 'editor test')))
            ->add('images', FileType::class, array(
                'multiple' => true, 'data_class' => null, 'mapped' => false, 'required' => false, 'attr' => array(
                    'accept' => 'image/*', 'multiple' => 'multiple')))
            ->add('mp3', FileType::class, array(
                'label' => 'Version audio', 'required' => false, 'data_class' => null))
            ->add('categories')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
