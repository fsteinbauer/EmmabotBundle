<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 26.03.2018
 * Time: 17:18
 */

namespace EmmabotBundle\InputProcessor;


use Fsteinbauer\CoreNLPBundle\Adapter\CoreNLPAdapter;

/**
 * Class EntityExtractionInputProcessor
 *
 * @package EmmabotBundle\InputProcessor
 */
class EntityExtractionInputProcessor implements InputProcessor
{
    const ID = 'emmabot.processor.ner';

    /**
     * @var CoreNLPAdapter
     */
    protected $coreNLPAdapter;

    /**
     * EntityExtractionInputProcessor constructor.
     *
     * @param CoreNLPAdapter $coreNLPAdapter
     */
    public function __construct(CoreNLPAdapter $coreNLPAdapter)
    {
        $this->coreNLPAdapter = $coreNLPAdapter;
    }


    /**
     * Returns an array of extracted entities
     *
     * Structure of array:
     * [
     *  'ORGANIZATION'  => 'Noll GmbH',
     *  'PERSON'        => 'Manfred Noll'
     * ]
     *
     * @param string $input
     * @return array
     */
    public function process($input)
    {
        $result = $this->coreNLPAdapter->request($input);
        return $this->coreNLPAdapter->getNamedEntities($result);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }
}