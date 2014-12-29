<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use ScoutEvent\ManagementBundle\Form\DataTransformer\EmailUserTransformer;

class MultipleParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('event', 'entity', array(
            'read_only' => true,
            'class' => 'ScoutEventDataBundle:Event',
            'label' => 'Event',
            'choices' => array($options['event'])
        ));
        $groupOptions = array(
            'class' => 'ScoutEventDataBundle:GroupUnit',
            'label' => 'Group'
        );
        if (in_array("ROLE_GROUP_ADMIN", $options['user']->getRoles())) {
            // Can do what they like
        } else {
            $query = $options['em']->createQuery('select g from ScoutEvent\DataBundle\Entity\GroupUnit g WHERE g.owner = :user');
            $query->setParameter('user', $options['user']);
            $groups = $query->getResult();
            if (count($groups) > 1) {
                $groupOptions['choices'] = $groups;
            } else {
                $groupOptions['read_only'] = true;
            }
        }
        $groups = $options['user'];
        $builder->add('groupUnit', 'entity', $groupOptions);
        
        $builder->add('participants', 'collection', array(
            'type' => new SingleParticipantType(),
            'allow_add' => true,
            'options' => array(
                'em' => $options['em'],
                'event' => $options['event'],
                'user' => $options['user']
            )
        ));
        
        $builder->add('Create', 'submit', array(
            'attr'=> array(
                'class' => 'btn btn-success pull-right'
            )
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
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
        return 'multiple_participant';
    }
}
