<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 15.05.2018
 * Time: 19:55
 */

namespace EmmabotBundle\InputProcessor;

/**
 * Class InputProcessorChain
 *
 * @package EmmabotBundle\InputProcessor
 */
class InputProcessorChain
{
    /**
     * @var array
     */
    private $processors;


    /**
     * InputProcessorChain constructor.
     */
    public function __construct()
    {
        $this->processors = array();
    }

    /**
     * @param InputProcessor $processor
     */
    public function addProcessor(InputProcessor $processor, $priority){

        $this->processors[$priority] = $processor;
    }

    /**
     * @param $input
     * @return array
     */
    public function process($input){

        $result = array();

        /** @var InputProcessor $processor */
        foreach ($this->processors as $processor){
            $result[$processor->getId()] = $processor->process($input);
        }

        return $result;
    }
}