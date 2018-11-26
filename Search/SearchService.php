<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 20.11.2018
 * Time: 11:52
 */

namespace EmmabotBundle\Search;

/**
 * Interface SearchService
 *
 * @package EmmabotBundle\Search
 */
interface SearchService
{
    /**
     * @param $query
     * @param $location
     * @return mixed
     */
    public function search($query, $location=null, $type=null);


    /**
     * @param array $source
     * @return mixed
     */
    public function createResultHTML(array $source);

}