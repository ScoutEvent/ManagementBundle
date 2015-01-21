<?php

namespace ScoutEvent\ManagementBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

use ScoutEvent\BaseBundle\Entity\User;

class AssistantUserTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (user) to a string.
     *
     * @param  array $array
     * @return ArrayCollection
     */
    public function transform($array)
    {
        return $array;
    }

    /**
     * Transforms a string to an object (user).
     *
     * @param  array $array
     *
     * @return PersistentCollection|ArrayCollection
     */
    public function reverseTransform($array)
    {
        $newArray = array();
 
        if (!$array) {
            return new ArrayCollection();
        }
 
        foreach ($array as $key => $value) {
            $user = $this->om
                ->getRepository('ScoutEventBaseBundle:User')
                ->findOneBy(array('email' => $value->getEmail()));
            
            if (is_null($user)) {            
                // Create new user, but don't set the password
                $user = $value;
                $this->om->persist($user);
            } else {
                $this->om->remove($value);
            }
            
            $newArray[$key] = $user;
        }
        $this->om->flush();
 
        return new PersistentCollection($this->om, 'ScoutEvent\BaseBundle\Entity\User', new ArrayCollection($newArray));
    }

}
