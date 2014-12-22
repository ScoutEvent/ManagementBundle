<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use ScoutEvent\ManagementBundle\Form\DataTransformer\EmailUserTransformer;

class ParticipantType extends AbstractType
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
        $builder->add('name', 'text');
        $transformer = new EmailUserTransformer($options['em']);
        $builder->add($builder->create('owner', 'email', array(
            'label' => 'Email'
        ))->addModelTransformer($transformer));
        $builder->add('youngPerson', 'checkbox', array(
            'required' => false
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            $form = $event->getForm();

            if (!$entity || null === $entity->getId()) {
                // New event
                $form->add('Create', 'submit', array(
                    'attr'=> array(
                        'class' => 'btn btn-success pull-right'
                    )
                ));
            } else {
                // Existing event
                $form->add('Edit', 'submit', array(
                    'attr' => array(
                        'class' => 'btn btn-success pull-right'
                    )
                ));
                $form->add('Delete', 'submit', array(
                    'attr' => array(
                        'class' => 'btn btn-warning pull-right',
                        'onclick' => 'return confirm("Are you sure?")'
                    )
                ));
            }
        });
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
        return 'participant';
    }
}
