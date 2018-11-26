<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 15.05.2018
 * Time: 21:58
 */

namespace EmmabotBundle\Intent;


class HelpIntent extends Intent
{

    /**
     * @return void
     */
    protected function setSlots()
    {
        // We need no information to perform this task
    }

    /**
     * @param $input
     * @param $processedInput
     * @return mixed|void
     */
    protected function fillSlots($input, $processedInput)
    {
        // We need no information to perform this task
    }

    /**
     * @return string
     */
    public function performAction($input)
    {
        return "Mein Name ist Emma, ich unterstütze dich um effizienter zu arbeiten.
        
            Ich bin dir behilflich in folgenden Aufgaben:
            - Suche
            - Tagesbericht hinzufügen
            - Neue Kunden  hinzufügen";
    }
}