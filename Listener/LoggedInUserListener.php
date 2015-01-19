<?php

namespace ScoutEvent\ManagementBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class LoggedInUserListener
{
    private $router;
    private $container;
    private $em;

    public function __construct($router, $container, $em)
    {
        $this->router = $router;
        $this->container = $container;
        $this->em = $em;
    }    

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $container = $this->container;
        try
        {
            $authenticated = $container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY');
        }
        catch (AuthenticationCredentialsNotFoundException $e)
        {
            // Loading something without a firewall (probably profiler), ignore
            return;
        }
        
        if (!$authenticated)
        {
            // anonymous user
            $routeName = $container->get('request')->get('_route');
            if ($routeName == "scout_base_app_list")
            {
                // Forward to event list
                $url = $this->router->generate("scout_event_list");
                $event->setResponse(new RedirectResponse($url));
            }
        }
        else
        {
            // Authenticated, check for pending health form
            $user = $container->get('security.context')->getToken()->getUser();
            
            $query = $this->em->createQueryBuilder()
                ->select('p')
                ->from('ScoutEventDataBundle:Participant', 'p');
            $subQuery = $this->em->createQueryBuilder();
            $subQuery->select('COUNT(h)')
                     ->from('ScoutEventDataBundle:HealthForm', 'h')
                     ->where('h.participant = p');
            $query->addSelect(sprintf('(%s) AS health_forms', $subQuery->getDql()))
                ->where('health_forms = 0 AND p.owner = :user')
                ->setParameter('user', $user)
                ->setMaxResults(1);
            
            $result = $query->getQuery()->getResult();
            if (count($result) == 0)
            {
                // No pending health forms
                return;
            }
            
            $routeName = $container->get('request')->get('_route');
            if ($this->startsWith($routeName, "scout_participant"))
            {
                // Allow them to fill in the form...
                return;
            }
            
            $url = $this->router->generate(
                "scout_participant_health_form",
                array('participantId' => $result[0][0]->getId())
            );
            $event->setResponse(new RedirectResponse($url));
        }
    }
}