<?php

namespace ScoutEvent\ManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OverviewController extends Controller
{

    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
    
        // Get the groups they own 
        $user = $this->container->get('security.context')->getToken()->getUser();
        $query = $em->createQueryBuilder()
            ->select('g')
            ->from('ScoutEventDataBundle:GroupUnit', 'g')
            ->where('g.owner = :user');
        $query->setParameter('user', $user);
        
        $results = array();
        
        $groups = $query->getQuery()->getResult();
        foreach ($groups as $group)
        {
            $query = $em->createQueryBuilder()
                ->select('p')
                ->addSelect('count(p) as participants')
                ->addSelect('e.id')
                ->addSelect('e.name')
                ->from('ScoutEventDataBundle:Participant', 'p')
                ->join('p.event', 'e')
                ->where('p.groupUnit = :group')
                ->groupBy('p.event')
                ->orderBy('p.event')
                ->setParameter('group', $group);

            $healthFormQuery = $em->createQueryBuilder()
                    ->select('COUNT(h)')
                    ->addSelect('e.id')
                    ->from('ScoutEventDataBundle:HealthForm', 'h')
                    ->join('h.participant', 'p')                     
                    ->join('p.event', 'e')
                    ->where('p.groupUnit = :group AND p.event = e')
                    ->setParameter('group', $group)
                    ->groupBy('p.event');

            $eventResult = array();
            $events = $query->getQuery()->getResult();
            $healthForms = $healthFormQuery->getQuery()->getResult();
            foreach ($events as $event)
            {
                $data = array(
                    'id' => $event['id'],
                    'name' => $event['name'],
                    'participants' => $event['participants'],
                    'completed' => 0
                );
                foreach ($healthForms as $healthForm)
                {
                    if ($healthForm['id'] == $event['id'])
                    {
                        $data['completed'] = $healthForm[1];
                    }
                }
                $eventResult[] = $data;
            }

            $results[] = array(
                'group' => $group,
                'events' => $eventResult
            );
        }
        
        return $this->render(
            'ScoutEventManagementBundle:Overview:list.html.twig',
            array('results' => $results)
        );
    }

}
