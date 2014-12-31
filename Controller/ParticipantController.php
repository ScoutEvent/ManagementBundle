<?php

namespace ScoutEvent\ManagementBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

use ScoutEvent\ManagementBundle\Form\Type\ParticipantType;
use ScoutEvent\ManagementBundle\Form\Type\MultipleParticipantType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormType;
use ScoutEvent\DataBundle\Entity\Participant;
use ScoutEvent\DataBundle\Entity\HealthForm;
use ScoutEvent\PasswordResetBundle\Entity\PasswordReset;

class ParticipantController extends Controller
{

    private function isAdmin()
    {
        return true === $this->get('security.authorization_checker')->isGranted('ROLE_GROUP_ADMIN');
    }

    public function listAction($eventId)
    {
        $em = $this->getDoctrine()->getManager();
        
        $event = $em->getRepository('ScoutEventDataBundle:Event')->find($eventId);
        $user = $this->container->get('security.context')->getToken()->getUser();
        $admin = $this->isAdmin();
        
        if ($admin) {
            // Group admin, select all
            $query = $em->createQueryBuilder()
                ->select('p')
                ->from('ScoutEventDataBundle:Participant', 'p')
                ->where('p.event = :event');
        } else {
            // No group admin role, so select only elements they own
            $query = $em->createQueryBuilder()
                ->select('p')
                ->from('ScoutEventDataBundle:Participant', 'p')
                ->join('p.groupUnit', 'g', 'WITH', 'g.owner = :user OR p.owner = :user')
                ->where('p.event = :event');
            $query->setParameter('user', $user);
        }
        $subQuery = $em->createQueryBuilder();
        $subQuery->select('COUNT(h)')
                 ->from('ScoutEventDataBundle:HealthForm', 'h')
                 ->where('h.participant = p');
        $query->addSelect(sprintf('(%s) AS health_forms', $subQuery->getDql()));
        $query->setParameter('event', $event);
        
        return $this->render(
            'ScoutEventManagementBundle:Participant:list.html.twig',
            array(
                'participants' => $query->getQuery()->getResult(),
                'eventId' => $eventId,
                'user' => $user,
                'admin' => $admin
            )
        );
    }
    
    // Check the participants owner for a new user that hasn't registered
    // and hasn't got a pending password reset.  If so, send a password reset.
    private function checkUser($em, $participant)
    {
        $user = $participant->getOwner();
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
        $reset = new PasswordReset($participant->getOwner());
        $em->persist($reset);
        $em->flush();
        
        // Send password reset email
        $message = \Swift_Message::newInstance();
        $message->setFrom($this->container->getParameter('mailer_from'));
        $message->setSubject($participant->getEvent()->getName() . ' Registration');
        
        $resetLink = $this->generateUrl('scout_password_reset', array(
            'token' => $reset->getToken()
        ));
        $resetLink = $this->getRequest()->getUriForPath($resetLink);
        $message->setTo($participant->getOwner()->getEmail())
            ->setBody(
                $this->renderView(
                    'ScoutEventManagementBundle:Email:registration.txt.twig',
                    array(
                        'participant' => $participant,
                        'resetLink' => $resetLink
                    )
                )
            );
        $this->get('mailer')->send($message);
        
        return true;
    }
    
    // Send email to existing user to get them to fill in the registration form.
    private function sendNewParticipantEmail($participant)
    {
        // Send password reset email
        $message = \Swift_Message::newInstance();
        $message->setFrom($this->container->getParameter('mailer_from'));
        $message->setSubject($participant->getEvent()->getName() . ' Registration');
        
        $formLink = $this->generateUrl('scout_base_app_list');
        $formLink = $this->getRequest()->getUriForPath($formLink);
        $message->setTo($participant->getOwner()->getEmail())
            ->setBody(
                $this->renderView(
                    'ScoutEventManagementBundle:Email:additionalRegistration.txt.twig',
                    array(
                        'participant' => $participant,
                        'formLink' => $formLink
                    )
                )
            );
        $this->get('mailer')->send($message);
        
        return true;
    }
    
    public function createAction(Request $request, $eventId)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('ScoutEventDataBundle:Event')->find($eventId);
        
        $token = $this->container->get('security.context')->getToken();
        $user = $token->getUser();
        
        if ($this->isAdmin()) {
            // That's ok
        } else {
            // Check they have a group in the event
            $query = $em->createQuery('select COUNT(*) from ScoutEvent\DataBundle\Entity\GroupUnit g WHERE g.owner = :user');
            $query->setParameter("user", $user);
            if ($query->getSingleScalarResult() == 0) {
                // No, you're not allowed!
                throw new AccessDeniedException();
            }
        }

        $form = $this->createForm(new MultipleParticipantType(), null, array(
            'action' => $this->generateUrl('scout_participant_create', array(
                'eventId' => $eventId
            )),
            'event' => $event,
            'user' => $user,
            'em' => $em
        ));
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            foreach ($data['participants'] as $participant) {
                $participant->setEvent($data['event']);
                $participant->setGroupUnit($data['groupUnit']);
                $em->persist($participant);
                
                if (!$this->checkUser($em, $participant)) {
                    $this->sendNewParticipantEmail($participant);
                }
            }
            $em->flush();

            return $this->redirect($this->generateUrl('scout_participant_list', array(
                'eventId' => $eventId
            )));
        }

        return $this->render(
            'ScoutEventManagementBundle:Participant:create.html.twig',
            array(
                'form' => $form->createView(),
                'eventId' => $eventId
            )
        );
    }

    public function editAction(Request $request, $participantId)
    {
        $em = $this->getDoctrine()->getManager();
        $participant = $em->getRepository('ScoutEventDataBundle:Participant')->find($participantId);
        $previousUser = $participant->getOwner()->getEmail();
        $eventId = $participant->getEvent()->getId();
        
        $token = $this->container->get('security.context')->getToken();
        $user = $token->getUser();
        
        if ($this->isAdmin()) {
            // That's ok
        } else if ($participant->getGroupUnit()->getOwner()->getId() == $user->getId()) {
            // Yep, fine...
        } else {
            // No, you're not allowed!
            throw new AccessDeniedException();
        }
    
        $form = $this->createForm(new ParticipantType(), $participant, array(
            'action' => $this->generateUrl('scout_participant_edit', array("participantId" => $participantId)),
            'event' => $participant->getEvent(),
            'user' => $user,
            'em' => $em
        ));
        $form->handleRequest($request);

        if ($form->get('Delete')->isClicked()) {
            $user = $participant->getOwner();
            $em->remove($participant);
            $em->flush();
            
            if ($user->getPassword() == '') {
                // User not registered yet, check if they have any other participants
                $qb = $em->createQueryBuilder();
                $qb->select('count(p.id)');
                $qb->from('ScoutEventDataBundle:Participant', 'p');
                $qb->where('p.owner = :owner')->setParameter('owner', $user);
                if ($qb->getQuery()->getSingleScalarResult() == 0) {
                    // No participants and not registered, delete them
                    $em->remove($user);
                    $em->flush();
                }
            }

            return $this->redirect($this->generateUrl('scout_participant_list', array(
                'eventId' => $eventId
            )));
        } else if ($form->isValid()) {
            $participant = $form->getData();
            
            if ($participant->getOwner()->getEmail() != $previousUser) {
                if (!$this->checkUser($em, $participant)) {
                    $this->sendNewParticipantEmail($participant);
                }
            }
            
            $em->flush();
            return $this->redirect($this->generateUrl('scout_participant_list', array(
                'eventId' => $eventId
            )));
        }

        return $this->render(
            'ScoutEventManagementBundle:Participant:edit.html.twig',
            array(
                'form' => $form->createView(),
                'eventId' => $participant->getEvent()->getId()
            )
        );
    }
    
    public function healthFormAction(Request $request, $participantId)
    {
        $em = $this->getDoctrine()->getManager();
        $participant = $em->getRepository('ScoutEventDataBundle:Participant')->find($participantId);

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        if ($this->isAdmin()) {
            // That's ok
        } else if ($participant->getOwner()->getId() == $user->getId()) {
            // Yep, fine...
        } else {
            // No, you're not allowed!
            throw new AccessDeniedException();
        }
        
        $healthForm = $em->getRepository('ScoutEventDataBundle:HealthForm')->findOneBy(array('participant' => $participant));
        if ($healthForm === null)
        {
            $healthForm = new HealthForm($participant);
        }
        
        $flow = $this->get('scout.form.flow.healthForm'); // must match the flow's service id
        $flow->bind($healthForm);

        // form of the current step
        $form = $flow->createForm();
        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {
                // flow finished
                $healthForm->setSignatureDate(new \DateTime());
                $em->persist($healthForm);
                $em->flush();

                $flow->reset(); // remove step data from the session

                return $this->redirect($this->generateUrl('scout_participant_list', array(
                    'eventId' => $participant->getEvent()->getId()
                )));
            }
        }
        
        return $this->render(
            'ScoutEventManagementBundle:Participant:healthForm.html.twig',
            array(
                'form' => $form->createView(),
                'flow' => $flow,
                'name' => $participant->getName()
            )
        );
    }
    
}
