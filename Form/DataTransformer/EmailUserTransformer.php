<?php

namespace ScoutEvent\ManagementBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use ScoutEvent\BaseBundle\Entity\User;

class EmailUserTransformer implements DataTransformerInterface
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
     * Set the group and event to be used for sending emails
     *
     * @param Event $event
     * @param GroupUnit $group
     */
    public function setGroupAndEvent($event, $group) {
        $this->event = $event;
        $this->group = $group;
    }

    /**
     * Transforms an object (user) to a string.
     *
     * @param  User|null $user
     * @return string
     */
    public function transform($user)
    {
        if (null === $user) {
            return "";
        }

        return $user->getEmail();
    }

    /**
     * Transforms a string to an object (user).
     *
     * @param  string $email
     *
     * @return User|null
     */
    public function reverseTransform($email)
    {
        if (!$email) {
            return null;
        }

        $user = $this->om
            ->getRepository('ScoutEventBaseBundle:User')
            ->findOneBy(array('email' => $email));

        if (null === $user) {
            // Create new user, but don't set the password
            $user = new User();
            $user->setEmail($email);
            $user->setIsActive(true);
            $user->setPassword("");  // Disabled - blank hash
            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
    }

}
