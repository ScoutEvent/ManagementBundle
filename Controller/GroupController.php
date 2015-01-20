<?php

namespace ScoutEvent\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use ScoutEvent\ManagementBundle\Form\Type\GroupType;
use ScoutEvent\ManagementBundle\Form\Type\UnauthenticatedGroupType;
use ScoutEvent\DataBundle\Entity\GroupUnit;
use ScoutEvent\PasswordResetBundle\Entity\PasswordReset;

class GroupController extends Controller
{

    public function listAction()
    {
        $groups = $this->getDoctrine()
            ->getRepository('ScoutEventDataBundle:GroupUnit')
            ->findAll();
        
        $create = $this->get('security.context')->isGranted('ROLE_GROUP_ADMIN');
        
        return $this->render(
            'ScoutEventManagementBundle:Group:list.html.twig',
            array('groups' => $groups, 'create' => $create)
        );
    }
    
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $authenticated = ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') === true);

        if ($authenticated) {
            $form = $this->createForm(new GroupType(), new GroupUnit(), array(
                'action' => $this->generateUrl('scout_group_create'),
                'admin' => $this->get('security.context')->isGranted('ROLE_GROUP_ADMIN'),
                'user' => $this->container->get('security.context')->getToken()->getUser(),
                'em' => $em
            ));
        } else {
            $form = $this->createForm(new UnauthenticatedGroupType(), new GroupUnit(), array(
                'action' => $this->generateUrl('scout_group_create'),
                'em' => $this->getDoctrine()->getManager()
            ));
        }
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $group = $form->getData();
            
            $em->persist($group);
            
            if (!$authenticated) {
                // Unauthenticated creates a new owner
                if (!$this->checkUser($em, $group)) {
                    $this->sendNewGroupEmail($group);
                }
            }
            
            $em->flush();

            if ($authenticated) {
                return $this->redirect($this->generateUrl('scout_group_list'));
            } else {
                return $this->redirect($this->generateUrl('scout_base_app_list'));
            }
        }

        return $this->render(
            'ScoutEventManagementBundle:Group:create.html.twig',
            array('form' => $form->createView())
        );
    }

    public function editAction(Request $request, $groupId)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('ScoutEventDataBundle:GroupUnit')->find($groupId);
    
        $form = $this->createForm(new GroupType(), $group, array(
            'action' => $this->generateUrl('scout_group_edit', array("groupId" => $groupId)),
            'admin' => $this->get('security.context')->isGranted('ROLE_GROUP_ADMIN'),
            'user' => $this->container->get('security.context')->getToken()->getUser(),
            'em' => $em
        ));
        $form->handleRequest($request);

        if ($form->get('Delete')->isClicked()) {
            $em->remove($group);
            $em->flush();
            return $this->redirect($this->generateUrl('scout_group_list'));
        } else if ($form->isValid()) {
            $group = $form->getData();
            $em->flush();
            return $this->redirect($this->generateUrl('scout_group_list'));
        }

        return $this->render(
            'ScoutEventManagementBundle:Group:edit.html.twig',
            array('form' => $form->createView())
        );
    }

    // Check the group owner for a new user that hasn't registered
    // and hasn't got a pending password reset.  If so, send a password reset.
    private function checkUser($em, $group)
    {
        $user = $group->getOwner();
        if ($user->getPassword() != '')
        {
            // Already registered, no need to send reset
            return false;
        }
        $qb = $em->createQueryBuilder();
        $qb->select('count(p.token)');
        $qb->from('ScoutEventPasswordResetBundle:PasswordReset', 'p');
        $qb->where('p.user = :user')->setParameter('user', $user);
        if ($qb->getQuery()->getSingleScalarResult() > 0) {
            // Already have a password reset, no need to send reset
            return false;
        }
        
        // Create new password reset request
        $reset = new PasswordReset($group->getOwner());
        $em->persist($reset);
        $em->flush();
        
        // Send password reset email
        $message = \Swift_Message::newInstance();
        $message->setFrom($this->container->getParameter('mailer_from'));
        $message->setSubject($group->getName() . ' Registration');
        
        $resetLink = $this->generateUrl('scout_password_reset', array(
            'token' => $reset->getToken()
        ));
        $resetLink = $this->getRequest()->getUriForPath($resetLink);
        $message->setTo($group->getOwner()->getEmail())
            ->setBody(
                $this->renderView(
                    'ScoutEventManagementBundle:Email:group_registration.txt.twig',
                    array(
                        'group' => $group,
                        'resetLink' => $resetLink
                    )
                )
            );
        $this->get('mailer')->send($message);
        
        return true;
    }
    
    // Send email to existing user to tell them they have a new group.
    private function sendNewGroupEmail($group)
    {
        // Send password reset email
        $message = \Swift_Message::newInstance();
        $message->setFrom($this->container->getParameter('mailer_from'));
        $message->setSubject($group->getName() . ' Registration');
        
        $formLink = $this->generateUrl('scout_base_app_list');
        $formLink = $this->getRequest()->getUriForPath($formLink);
        $message->setTo($group->getOwner()->getEmail())
            ->setBody(
                $this->renderView(
                    'ScoutEventManagementBundle:Email:group_additionalRegistration.txt.twig',
                    array(
                        'group' => $group,
                        'formLink' => $formLink
                    )
                )
            );
        $this->get('mailer')->send($message);
        
        return true;
    }

}
