<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 15.05.2018
 * Time: 21:27
 */

namespace EmmabotBundle\Intent;


use Doctrine\ORM\ORMException;

/**
 * Class Intent
 *
 * @package EmmabotBundle\Intent
 */
abstract class Intent
{

    /**
     * @var Slot[]
     */
    protected $slots;

    /**
     * @var ContextManager
     */
    protected $contextManager = null;

    /**
     * @return void
     */
    abstract protected function setSlots();

    /**
     * @param $input
     * @return string
     */
    abstract protected function performAction($input);

    /**
     * @param $input
     * @param $processedInput
     * @return mixed
     */
    abstract protected function fillSlots($input, $processedInput);

    /**
     * Topic constructor.
     */
    public function __construct(){
        $this->slots = array();

        $this->setSlots();
    }

    /**
     * @param ContextManager $manager
     */
    public function setContextManager(ContextManager $manager){
        $this->contextManager = $manager;
    }

    /**
     * @param $id
     * @param $data
     * @throws \Doctrine\ORM\ORMException
     */
    protected function fillSlot($id, $data){

        foreach ($this->slots as $slot){
            if($slot->getId() == $id){
                $slot->setData($data);

                $this->contextManager->saveContext($id, $data);
                return;
            }
        }

        // No Slot was found
        throw new \RuntimeException("No slot was found for id: ".$id);
    }


    /**
     * @param $id
     */
    protected function removeSlotData($id){

        $this->contextManager->removeContext($id);
    }


    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getData($id){

        return $this->contextManager->getContext($id);
    }


    /**
     * @param $id
     * @return mixed|null
     */
    protected function getSlotData($id){

        foreach ($this->slots as $slot) {
            if ($slot->getId() == $id) {
                return $slot->getData();
            }
        }
        return null;
    }


    /**
     * @param $input
     * @param $processedInput
     * @return string
     * @throws \Doctrine\ORM\ORMException
     */
    public function process($input, $processedInput){


        if($this->contextManager == null){
            throw new \RuntimeException("No ContextManager was added for class ".self::class);
        }

        $this->loadSlots();

        $this->handleLastQuestion($input, $processedInput);

        // Extract info and add to the slots
        try{
            $msg = $this->fillSlots($input, $processedInput);
            if($msg !== null){
                return $msg;
            }
        } catch (ORMException $e){
            return "Es gab ein Problem beim Speichern der Informationen: ".$e->getMessage();
        }

        // If not all slots are filled, ask a Question
        foreach ($this->slots as $slot){
            if(!$slot->isFilled()){
                $this->contextManager->saveContext('last_question', $slot->getId());
                return $slot->getQuestion();
            }
        }

        // If we have all the information, perform an action
        return $this->performAction($input);
    }

    /**
     *
     */
    private function loadSlots(){

        foreach ($this->slots as $slot){
            $data = $this->contextManager->getContext($slot->getId());
            if($data !== null){
                $slot->setData($data);
            }
        }
    }

    /**
     * @param $id
     * @param null $text
     * @return null|string
     * @throws ORMException
     */
    protected function askQuestion($id, $text=null){

        foreach ($this->slots as $slot) {
            if ($slot->getId() == $id) {

                $this->contextManager->saveContext('last_question', $id);

                if($text !== null){
                    return $text;
                } else {
                    return $slot->getQuestion();
                }
            }
        }

        // No Slot was found
        throw new \RuntimeException("No slot was found for id: ".$id);
    }

    /**
     * @param $input
     * @param $processedInput
     * @throws ORMException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function handleLastQuestion($input, $processedInput)
    {
        $last_question = $this->contextManager->getContext('last_question');

        if ($last_question == null) {
            return;
        }

        $input = str_replace(array("\r", "\n"), '',$input);

        foreach ($this->slots as $slot) {
            if ($slot->getId() == $last_question) {

                $this->fillSlot($slot->getId(), $input);
            }
        }
    }
}