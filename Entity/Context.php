<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 16.05.2018
 * Time: 13:54
 */

namespace EmmabotBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

/**
 * Context
 *
 * @ORM\Table(name="context")
 * @ORM\Entity(repositoryClass="EmmabotBundle\Repository\ContextRepository")
 */
class Context
{
    use Timestampable;
    use Blameable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100)
     */
    private $type;

    /**
     * @var json
     *
     * @ORM\Column(name="data", type="json")
     */
    private $data;

    /**
     * Context constructor.
     * @param string $type
     * @param mixed $data
     */
    public function __construct($type, $data)
    {
        $this->setType($type);

        $this->setData(serialize($data));
    }


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Context
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set data.
     *
     * @param json $data
     *
     * @return Context
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     *
     * @return json
     */
    public function getData()
    {
        return $this->data;
    }
}
