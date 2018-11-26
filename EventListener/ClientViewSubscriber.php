<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 02.07.2018
 * Time: 14:51
 */

namespace EmmabotBundle\EventListener;


use CRMBundle\Event\Client\ClientViewEvent;
use EmmabotBundle\Intent\ContextManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ClientViewSubscriber
 *
 * Adds the currently viewed Client to the Context
 *
 * @package EmmabotBundle\EventListener
 */
class ClientViewSubscriber implements EventSubscriberInterface
{

    /**
     * @var ContextManager
     */
    protected $contextManager;


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ClientViewEvent::NAME => 'onClientViewed'
        );
    }


    /**
     * ClientViewSubscriber constructor.
     *
     * @param ContextManager $contextManager
     */
    public function __construct(ContextManager $contextManager)
    {
        $this->contextManager = $contextManager;
    }


    /**
     * Adds the currently viewed Client to the Context
     *
     * @param ClientViewEvent $event
     * @throws \Doctrine\ORM\ORMException
     */
    public function onClientViewed(ClientViewEvent $event){

        $this->contextManager->saveContext('relatedEntity', $event->getClient()->toArray());
    }
}