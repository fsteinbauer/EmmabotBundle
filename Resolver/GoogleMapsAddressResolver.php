<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 26.11.2018
 * Time: 11:32
 */

namespace EmmabotBundle\Resolver;


use Geocoder\Geocoder;
use Geocoder\Model\AddressCollection;

/**
 * Class GoogleMapsAddressResolver
 * @package EmmabotBundle\Resolver
 */
class GoogleMapsAddressResolver implements AddressResolver
{
    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * GoogleMapsAddressResolver constructor.
     * @param Geocoder $geocoder
     */
    public function __construct(Geocoder $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    /**
     * @param string $input
     * @return AddressCollection
     */
    public function resolve($input)
    {
        return $this->geocoder->geocode($input);
    }
}