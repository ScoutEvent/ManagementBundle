<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use ScoutEvent\ManagementBundle\Form\DataTransformer\AssistantUserTransformer;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Group name'
        ));
        $builder->add('contact', 'text');
        if ($options['admin'] === true)
        {
            $builder->add('owner', 'entity', array(
                'class' => 'ScoutEventBaseBundle:User',
                'label' => 'User',
                'data' => $options['user']
            ));
        }
        else
        {
            $builder->add('owner', 'entity', array(
                'class' => 'ScoutEventBaseBundle:User',
                'label' => 'User',
                'read_only' => true,
                'data' => $options['user'],
                'choices' => array($options['user'])
            ));
        }

        $builder->add('phone', 'text');

        $transformer = new AssistantUserTransformer($options['em']);
        $builder->add($builder->create('assistants', 'collection', array(
            'type' => new AssistantType(),
            'allow_add' => true,
            'by_reference' => false,
            'allow_delete' => true
        ))->addModelTransformer($transformer));

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
            'data_class' => 'ScoutEvent\DataBundle\Entity\GroupUnit'
        ))->setRequired(array(
            'em',
            'admin',
            'user'
        ))->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
            'user' => 'ScoutEvent\BaseBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'group';
    }
}
