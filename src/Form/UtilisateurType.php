<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array('label' => 'Avatar', 'required' => false, 'data_class' => null))
            ->add('email', EmailType::class, array('attr' => array('placeholder' => 'adresse@exemple.com')))
            ->add('confirm_email', EmailType::class, array('attr' => array('placeholder' => 'adresse@exemple.com')))
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
            ->add('nom', TextType::class, array('attr' => array('placeholder' => 'Votre nom de famille')))
            ->add('prenom', TextType::class, array('attr' => array('placeholder' => 'Votre prenom')))
            ->add('telephone', TextType::class, array('attr' => array('placeholder' => 'Votre numero de téléphone')))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}