<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use ScoutEvent\ManagementBundle\Form\DataTransformer\EmailUserTransformer;

class SingleParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $transformer = new EmailUserTransformer($options['em']);
        $builder->add($builder->create('owner', 'email', array(
            'label' => 'Email'
        ))->addModelTransformer($transformer));
        $builder->add('youngPerson', 'checkbox', array(
            'required' => false
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScoutEvent\DataBundle\Entity\Participant'
        ))->setRequired(array(
            'em',
            'event',
            'user'
        ))->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'event' => 'ScoutEvent\DataBundle\Entity\Event',
            'user' => 'ScoutEvent\BaseBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'partial_participant';
    }
}
