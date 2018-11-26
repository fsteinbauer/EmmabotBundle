<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 24.03.2018
 * Time: 19:00
 */

namespace EmmabotBundle\Services;


use EmmabotBundle\InputProcessor\InputProcessorChain;
use EmmabotBundle\Intent\IntentSelector;

/**
 * Class EmmaBot
 * @package EmmabotBundle\Services
 */
class EmmaBot implements Bot
{
    /**
     * @var InputProcessorChain
     */
    protected $inputProcessors;

    /**
     * @var IntentSelector
     */
    protected $topicSelector;

    /**
     * EmmaBot constructor.
     */
    public function __construct(InputProcessorChain $inputProcessors, IntentSelector $topicSelector)
    {
        $this->inputProcessors = $inputProcessors;
        $this->topicSelector = $topicSelector;
    }

    public function answer($input){

        $processedInput = $this->inputProcessors->process($input);

        $input = strtolower($input);

        $answer = $this->topicSelector->selectTopic($input, $processedInput);


        return [
            'text'    => $answer
        ];
    }
}