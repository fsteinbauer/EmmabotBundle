<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 01.07.2018
 * Time: 23:16
 */

namespace EmmabotBundle\Intent;


use CRMBundle\Entity\Client;
use EmmabotBundle\InputProcessor\EntityExtractionInputProcessor;
use EmmabotBundle\Search\SearchService;

/**
 * Class TagesberichtIntent
 * @package EmmabotBundle\Intent
 */
class TagesberichtIntent extends Intent
{
    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * TagesberichtIntent constructor.
     * @param SearchService $searchService
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function setSlots()
    {

        /**
         * @param $data
         * @return null|string
         */
        $contactTransformer = function ($data) {

            if (($number = intval($data)) !== 0) {
                switch ($number) {
                    case 1:
                        return "Telefonanruf";
                    case 2:
                        return "E-Mail";
                    case 3:
                        return "Persönlicher Besuch";
                }
            }

            if (preg_match('/tel|anruf/', $data)) {
                return 'Telefonanruf';
            }

            if (preg_match('/mail/', $data)) {
                return 'E-Mail';
            }

            if (preg_match('/besuch|pers/', $data)) {
                return 'Persönlicher Besuch';
            }

            return null;
        };

        /**
         * @param $data
         * @return false|int|null
         */
        $timeTransformer = function ($data){

            if(($time = strtotime($data)) === false){
                return null;
            }

            return $data;
        };

        /**
         * @return string
         */
        $confirmGenerator = function () {

            $entity = $this->getSlotData('report.entity');

            return sprintf("Ich werde den Tagesbericht mit folgenden Werten erstellen:\n
                %s
                Besuchszeit: %s
                Kontaktart: %s\n                
                Sind diese Eingaben korrekt?",
                $this->searchService->createResultHTML($entity),
                $this->getSlotData('report.time'),
                $this->getSlotData('report.contact')
            );
        };

        $this->slots = [
            new Slot('report.entity', ['Für welchen Kunden soll der Tagesbericht erstellt werden?'], true),
            new Slot('report.time', ['Wann wurde der Kunde besucht?'], true, $timeTransformer),
            new Slot('report.contact', ["Wie wurde mit dem Kunden Kontakt aufgenommen?\n\n1. Telefonanruf\n2. E-Mail\n3. Persönlicher Besuch"], true, $contactTransformer),
            new Slot('report.confirm', [""], true, null, $confirmGenerator)
        ];
    }


    /**
     * @param $input
     * @return string
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function performAction($input)
    {
        if ($this->getData('last_question') == 'report.confirm' &&
            strtolower($this->getSlotData('report.confirm')) !== 'ja') {
            $this->removeSlotData('report.confirm');
            $this->contextManager->saveContext('last_question', 'report.correct');
            return 'Was soll berichtigt werden?';
        }


        // Action to create the Tagesbericht

        $this->removeSlotData('report%');
        $this->removeSlotData('intent');

        return sprintf("Ich habe den Tagesbericht für \"%s\" erfolgreich erstellt. \n\n Was willst du als nächstes machen?",
            $this->getSlotData('report.entity')["name"]);
    }


    /**
     * @param $input
     * @param $processedInput
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    protected function fillSlots($input, $processedInput)
    {
        $this->setRelatedEntity();

        // Find related entity
        if ($this->getData('last_question') == 'report.entity') {
            return $this->handleEntitySlot($input, $processedInput);
        }

        // Perform correction
        if ($this->getData('last_question') == 'report.correct') {

            // Correct Time
            if (preg_match('/zeit|uhr/', $input)) {
                $time = null;

                if (array_key_exists(EntityExtractionInputProcessor::ID, $processedInput) &&
                    array_key_exists("time", $processedInput[EntityExtractionInputProcessor::ID])) {
                    $time = $processedInput[EntityExtractionInputProcessor::ID]["time"];
                }

                $this->fillSlot('report.time', $time);
            }

            // Correct Kontaktart
            if (preg_match('/art|kontakt|anruf|telefon|mail|besuch/', $input)) {
                $this->fillSlot('report.contact', null);
            }
        }
    }


    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    private function setRelatedEntity()
    {
        $relatedEntity = $this->contextManager->getContext('relatedEntity');
        if ($relatedEntity == null) {
            return;
        }
        $this->fillSlot('report.entity', $relatedEntity);
    }


    /**
     * @param $input
     * @param $processedInput
     * @return string
     * @throws \Doctrine\ORM\ORMException
     */
    private function handleEntitySlot($input, $processedInput)
    {

        if (array_key_exists(EntityExtractionInputProcessor::ID, $processedInput) &&
            !empty($processedInput[EntityExtractionInputProcessor::ID])) {
            $input = key($processedInput[EntityExtractionInputProcessor::ID]);
        }

        $results = $this->searchService->search($input);


        switch (count($results)) {
            case 0:
                $msg = "Ich konnte ich keine Resultate finden.\n" . $this->askQuestion('report.entity');
                break;
            case 1:
                $this->removeSlotData('report.entity');
                $this->fillSlot('report.entity', $results[0]->getSource());
                $msg = sprintf("Für \"%s\" konnte ich folgenden Kunden finden:\n\n%s\n\n%s",
                    $input,
                    $this->searchService->createResultHTML($results[0]->getSource()),
                    $this->askQuestion('report.time')
                );
                break;
            default:
                $msg = sprintf("Deine Suche hat %d Ergebnisse geliefert:\n\n", count($results));
                foreach ($results as $result) {
                    $msg .= $this->createResultEntry($result) . "\n\n";
                }

        }

        return $msg;

    }
}