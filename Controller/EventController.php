<?php

namespace ScoutEvent\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ScoutEvent\ManagementBundle\Form\Type\EventType;
use ScoutEvent\DataBundle\Entity\Event;

class EventController extends Controller
{

    public function listAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('ScoutEventDataBundle:Event')
            ->findAll();
        
        $create = $this->get('security.context')->isGranted('ROLE_EVENT_ADMIN');
        
        return $this->render(
            'ScoutEventManagementBundle:Event:list.html.twig',
            array('events' => $events, 'create' => $create)
        );
    }
    
    public function createAction(Request $request)
    {
        $additions = $this->get('scout.event.additionChain');
        $form = $this->createForm(new EventType($additions), new Event(), array(
            'action' => $this->generateUrl('scout_event_create')
        ));
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $event = $form->getData();
            
            $em->persist($event);
            $em->flush();
            $additions->persist($form, $event);
            $em->flush();

            return $this->redirect($this->generateUrl('scout_event_list'));
        }

        return $this->render(
            'ScoutEventManagementBundle:Event:create.html.twig',
            array('form' => $form->createView())
        );
    }

    public function editAction(Request $request, $eventId)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('ScoutEventDataBundle:Event')->find($eventId);
    
        $additions = $this->get('scout.event.additionChain');
        $form = $this->createForm(new EventType($additions), $event, array(
            'action' => $this->generateUrl('scout_event_edit', array("eventId" => $eventId))
        ));
        $form->handleRequest($request);

        if ($form->get('Delete')->isClicked()) {
            $additions->remove($event);
            $em->remove($event);
            $em->flush();
            return $this->redirect($this->generateUrl('scout_event_list'));
        } else if ($form->isValid()) {
            $event = $form->getData();
            $em->flush();
            $additions->persist($form, $event);
            $em->flush();
            return $this->redirect($this->generateUrl('scout_event_list'));
        }

        return $this->render(
            'ScoutEventManagementBundle:Event:edit.html.twig',
            array('form' => $form->createView())
        );
    }
}
