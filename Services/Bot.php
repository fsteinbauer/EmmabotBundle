<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 25.03.2018
 * Time: 16:20
 */

namespace EmmabotBundle\Services;

/**
 * Interface Bot
 *
 * @package EmmabotBundle\Services
 */
interface Bot
{
    const ERROR_CONNECT_NLP = 101;


    /**
     * @param $input
     * @return mixed
     */
    public function answer($input);
}