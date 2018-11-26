<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 20.11.2018
 * Time: 11:51
 */

namespace EmmabotBundle\Search;

use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Component\Routing\Router;

/**
 * Class ElasticSearchService
 *
 * @package EmmabotBundle\Search
 */
class ElasticSearchService implements SearchService
{
    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * ElasticSearchService constructor.
     * 
     * @param IndexManager $indexManager
     */
    public function __construct(IndexManager $indexManager, Router $router){
        $this->router = $router;
        $this->indexManager = $indexManager;
    }

    /**
     * @param $name
     * @param $location
     * @return mixed
     */
    public function search($name, $location=null, $type=null)
    {
        $query = new BoolQuery();

        if($query !== null){
            $nameQuery = new Match();
            $nameQuery->setFieldQuery('name', $name);
            $query->addMust($nameQuery);
        }


        if($location !== null){
            $boolQuery = new BoolQuery();

            $cityQuery = new Match();
            $cityQuery->setFieldQuery('address.city', $location);
            $boolQuery->addShould($cityQuery);

            $zipQuery = new Match();
            $zipQuery->setFieldQuery('address.zip', $location);
            $boolQuery->addShould($zipQuery);


            $query->addMust($boolQuery);
        }

        $search = null;
        switch ($type){

            // Other types omitted for demo

            default:
                $search = $this->indexManager->getIndex('kunde')->createSearch($query);
        }

        return $search->search()->getResults();
    }


    /**
     * @param array $source
     * @return string
     */
    public function createResultHTML(array $source){

        return  sprintf(
            '<a href="%s"><i class="icon-user"></i> %s</a>
            <small>%s
            %s %s</small>', $this->router->generate('kunden_view', ['slug' => $source['slug']]),
            $source['name'], $source['address']['street'], $source['address']['zip'],
            $source['address']['city']);
    }

}