<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 12.06.2018
 * Time: 18:39
 */

namespace EmmabotBundle\Intent;

use CRMBundle\Entity\Kunde;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use EmmabotBundle\InputProcessor\EntityExtractionInputProcessor;
use EmmabotBundle\Resolver\AddressResolver;
use EmmabotBundle\Resolver\GoogleMapsAddressResolver;
use Fsteinbauer\CoreNLPBundle\Adapter\CoreNLPAdapter;
use Geocoder\Exception\NoResult;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Symfony\Component\Routing\Router;

/**
 * Class AddCustomerIntent
 *
 * @package EmmabotBundle\Intent
 */
class AddCustomerIntent extends Intent
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var AddressResolver
     */
    protected $addressResolver;

    /**
     * AddCustomerIntent constructor.
     *
     * @param EntityManager $em
     * @param Router $router
     * @param GoogleMapsAddressResolver $addressResolver
     */
    public function __construct(EntityManager $em, Router $router, GoogleMapsAddressResolver $addressResolver)
    {
        $this->em = $em;
        $this->router = $router;
        $this->addressResolver = $addressResolver;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function setSlots()
    {
        /**
         * @param $data
         * @return string
         */
        $nameTansformer = function ($data){

            if("string" === gettype($data)){
                return ucwords($data);
            }
            return $data;
        };

        /**
         * @return string
         */
        $confirmGenerator = function (){

            $msg =  sprintf("Ich werde folgenden Kunden erstellen:\n
                <b>%s</b>
                %s\n",
                $this->getSlotData('newcustomer.name'),
                $this->buildAddressHTML($this->getSlotData('newcustomer.address'))
            );

            $uidNr = $this->getSlotData('newcustomer.uidnr');
            if($uidNr !== null){
                $msg .= "UID-Nr: ".$uidNr."\n";
            }

            $debNr = $this->getSlotData('newcustomer.debnr');
            if($debNr !== null){
                $msg .= "Kundennummer: ".$debNr."\n";
            }

            $msg .= "Sind diese Eingaben korrekt?";

            return $msg;

        };


        $this->slots = [
            new Slot('newcustomer.name', ['Wie lautet der Name des Kundens?'], true, $nameTansformer),
            new Slot('newcustomer.address', ['Was ist die Adresse des Kundens?'], true),
            new Slot('newcustomer.confirm', [""], true, null, $confirmGenerator)
        ];
    }

    /**
     * @param $input
     * @return string
     */
    protected function performAction($input)
    {

        $customer = $this->createCustomer();

        if($customer === null){
            return "Bei der Erstellung des Kundens ist ein Fehler aufgetreten.";
        }

        $msg =  sprintf(
            "Ich habe folgenden Kunden erstellt.\n
            <a href=\"%s\"><i class=\"icon-user\"></i> %s</a>
            <small>%s</small>
            
            Was willst du als nächstes machen?",
            $this->router->generate('kunden_view', ['slug' => $customer->getSlug()]),
            $customer->getName(), $customer->getAddress()->getHtml()
        );

        $this->removeSlotData('newcustomer.%');
        $this->removeSlotData('intent');
        try {
            $this->fillSlot('relatedEntity', $customer->toArray());
        } catch (ORMException $e){
            $msg .= "\n(Achtung: Der Kunde konnte nicht zum Context hinzugefügt werden)";
        }

        return $msg;
    }

    /**
     * @return Kunde|null
     */
    private function createCustomer()
    {
        $customer = new Kunde();
        $customer->setName($this->getSlotData('newcustomer.name'));
        $customer->setAddressGeocode($this->getSlotData('newcustomer.address'));
        $customer->setDebnr($this->getSlotData('newcustomer.debnr'));
        $customer->setUid($this->getSlotData('newcustomer.uidnr'));

        try {
            $this->em->persist($customer);
            $this->em->flush();
        } catch (ORMException $e){
            return null;
        }

        return $customer;
    }

    /**
     * @param $input
     * @param $processedInput
     * @return mixed
     * @throws ORMException
     */
    protected function fillSlots($input, $processedInput)
    {

        if($this->getData('last_question') == 'newcustomer.address'){
            try {
                /** @var AddressCollection $results */
                $results = $this->addressResolver->resolve($input);
                if($results->count() == 1){

                    $this->removeSlotData('newcustomer.address');
                    $this->fillSlot('newcustomer.address', $results->first());
                } else {

                    $msg = sprintf("Für die Eingabe \"%s\" wurden mehrere Adressen gefunden:\n\n", $input);

                    $i = 1;
                    foreach ($results as $result){
                        $msg .= $this->buildAddressHTML($result, $i++);
                    }
                    $msg .= "Bitte wähle per Nummer aus, welche Adresse korrekt ist.";

                    return $msg;
                }
            } catch (NoResult $e){
                return sprintf("Ich konnte die Addresse \"%s\" nicht finden. Bitte versuche es erneut.", $input);
            }
        }


        if ($this->getData('last_question') == 'newcustomer.confirm' &&
            strtolower($this->getSlotData('newcustomer.confirm')) !== 'ja') {
            $this->removeSlotData('newcustomer.confirm');
            $this->contextManager->saveContext('last_question', 'newcustomer.correct');
            return 'Was soll berichtigt werden?';
        }

        // Perform correction
        if ($this->getData('last_question') == 'newcustomer.correct') {

            // Correct Name
            if (preg_match('/name|kunde/', $input)) {
                $name = null;

                if (array_key_exists(EntityExtractionInputProcessor::ID, $processedInput) && (
                        array_key_exists(CoreNLPAdapter::ENTITY_PERSON, $processedInput[EntityExtractionInputProcessor::ID]) ||
                    array_key_exists(CoreNLPAdapter::ENTITY_ORGANIZATION, $processedInput[EntityExtractionInputProcessor::ID]))) {
                    $name = current($processedInput[EntityExtractionInputProcessor::ID]);
                }

                $this->fillSlot('newcustomer.name', $name);
            }

            // Correct Address
            if (preg_match('/adresse|strasse|straße|anschrift|land|plz/', $input)) {
                $this->fillSlot('newcustomer.address', null);
            }
        }
    }

    /**
     * @param $result
     * @param string $i
     * @return string
     */
    private function buildAddressHTML(Address$result, $i="" ){

        return sprintf("%s <small>%s %s\n%s %s</small>\n\n",
            $i,
            $result->getStreetName(),
            $result->getStreetNumber(),
            $result->getPostalCode(),
            $result->getLocality()
        );
    }


}