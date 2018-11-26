<?php
/**
 * Created by PhpStorm.
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 15.05.2018
 * Time: 21:30
 */

namespace EmmabotBundle\Intent;

/**
 * Class Slot
 *
 * @package EmmabotBundle\Intent
 */
class Slot
{
    /**
     * @var bool
     */
    protected $required;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var callable
     */
    protected $transformer;

    /**
     * @var array
     */
    protected $questions;

    /**
     * @var callable
     */
    protected $generator;

    /**
     * Slot constructor.
     * @param $id
     * @param $questions
     * @param bool $required
     * @param null $transformer
     */
    public function __construct($id, $questions, $required=true, $transformer = null, $generator=null)
    {
        $this->id = $id;
        $this->questions = $questions;
        $this->required = $required;
        $this->transformer = $transformer;
        $this->generator = $generator;
    }

    /**
     * @return mixed
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @param $data
     */
    public function setData($data){

        if($this->transformer !== null){
            $this->data = ($this->transformer)($data);
        } else {
            $this->data = $data;
        }
    }

    /**
     * @return mixed
     */
    public function getData(){

        return $this->data;
    }

    /**
     * @return bool
     */
    public function isFilled(){

        return !$this->required || !empty($this->data);
    }

    /**
     * @return string
     */
    public function getQuestion(){

        if(is_callable($this->generator)){
            return ($this->generator)();
        }

        return $this->questions[0];
    }
}