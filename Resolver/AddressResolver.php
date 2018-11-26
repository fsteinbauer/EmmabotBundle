<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 26.11.2018
 * Time: 11:31
 */

namespace EmmabotBundle\Resolver;

/**
 * Interface AddressResolver
 * @package EmmabotBundle\Resolver
 */
interface AddressResolver
{
    /**
     * @param string $input
     * @return mixed
     */
    public function resolve($input);
}