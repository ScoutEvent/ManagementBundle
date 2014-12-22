<?php

namespace ScoutEvent\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ScoutEvent\ManagementBundle\Form\Type\GroupType;
use ScoutEvent\DataBundle\Entity\GroupUnit;

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
        $form = $this->createForm(new GroupType(), new GroupUnit(), array(
            'action' => $this->generateUrl('scout_group_create')
        ));
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $group = $form->getData();
            
            $em->persist($group);
            $em->flush();

            return $this->redirect($this->generateUrl('scout_group_list'));
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
            'action' => $this->generateUrl('scout_group_edit', array("groupId" => $groupId))
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
}
