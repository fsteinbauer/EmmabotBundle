<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 26.03.2018
 * Time: 17:18
 */

namespace EmmabotBundle\InputProcessor;

/**
 * Interface InputProcessor
 *
 * @package EmmabotBundle\InputProcessor
 */
interface InputProcessor
{
    /**
     * @param $input
     * @return mixed
     */
    public function process($input);

    /**
     * @return string
     */
    public function getId();
}