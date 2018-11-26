<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 16.05.2018
 * Time: 13:10
 */

namespace EmmabotBundle\Intent;


use Doctrine\ORM\ORMException;
use Elastica\Result;
use EmmabotBundle\InputProcessor\EntityExtractionInputProcessor;
use EmmabotBundle\Search\SearchService;
use Fsteinbauer\CoreNLPBundle\Adapter\CoreNLPAdapter;
use Symfony\Component\Routing\Router;

/**
 * Class SearchIntent
 * @package EmmabotBundle\Intent
 */
class SearchIntent extends Intent
{
    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * SearchIntent constructor.
     * @param Router $router
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
        $this->slots = [
            new Slot('search.name', ['Wen suchst du?'] , false  ),
            new Slot('search.type', ['Ist der gesuchte Datensatz ein Kunde, Lokal, Handelsunternehmen oder eine Person?'], false),
            new Slot('search.location', ['Wo befindet sich der Kunde?'], false)
        ];
    }

    /**
     * @param $input
     * @param $processedInput
     * @return mixed|void
     * @throws ORMException
     */
    protected function fillSlots($input, $processedInput)
    {
        $nerId = EntityExtractionInputProcessor::ID;
        if(array_key_exists($nerId, $processedInput) and !empty($processedInput[$nerId]) ){
            foreach ($processedInput[$nerId] as $value => $type){

                if($type == CoreNLPAdapter::ENTITY_PERSON){
                    $this->fillSlot('search.name', $value);
                    $this->fillSlot('search.type', CoreNLPAdapter::ENTITY_PERSON);
                }

                if($type == CoreNLPAdapter::ENTITY_ORGANIZATION){
                    $this->fillSlot('search.name', $value);
                    $this->fillSlot('search.type', CoreNLPAdapter::ENTITY_ORGANIZATION);
                }

                if($type == CoreNLPAdapter::ENTITY_LOCATION){
                    $this->fillSlot('search.location', $value);
                }
            }
        }
    }


    /**
     * @param $input
     * @return string
     * @throws ORMException
     */
    protected function performAction($input)
    {
        $name = $this->getSlotData('search.name');
        $type = $this->getSlotData('sarch.type');
        $location = $this->getSlotData('search.location');

        if($name === null && $type === null && $location === null){
            return $this->askQuestion('search.name');
        }

        $results = $this->searchService->search($name, $location, $type);
        $count = count($results);

        switch ($count){
            case 0:
                $msg = "Ich konnte ich keine Resultate finden.\n".$this->askQuestion('search.name');
                break;
            case 1:
                $msg = "Deine Suche hat folgendes Ergebnis geliefert:\n\n".
                    $this->searchService->createResultHTML($results[0]->getSource());
                break;
            default:
               $msg = sprintf("Deine Suche hat %d Ergebnisse geliefert:\n\n", $count);
               foreach($results as $result) {

                   $msg .= $this->searchService->createResultHTML($result->getSource())."\n\n";
               }

               if($this->resultsHaveDifferentLocations($results) && $this->getSlotData('search.location') == null){
                    $msg .= $this->askQuestion('search.location');
               } else if($this->resultsHaveDifferentTypes($results) && $this->getSlotData('search.type') == null){
                    $msg .= $this->askQuestion('search.type');
               }
        }

        try {
            $this->addResultToContext($results);
        } catch (ORMException $e){
            $msg .= "(Fehler: Ich konnte mir dieses Suchergebnis nicht für die Zukunft merken)";
        }

        return $msg;
    }

    /**
     * @param $results
     * @return bool
     */
    private function resultsHaveDifferentLocations($results)
    {
        return true;
    }

    /**
     * @param $results
     * @return bool
     */
    private function resultsHaveDifferentTypes($results)
    {
        return true;
    }


    /**
     * @param array $results
     * @throws \Doctrine\ORM\ORMException
     */
    private function addResultToContext(array $results)
    {
        if(count($results) == 0){
            return;
        }

        $result = $results[0]->getSource();

        $this->contextManager->saveContext('relatedEntity', $result);
    }
}