<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 15.05.2018
 * Time: 22:06
 */

namespace EmmabotBundle\Intent;


use AppKernel;
use EmmabotBundle\Repository\ContextRepository;
use Phpml\Exception\LibsvmCommandException;
use Phpml\ModelManager;
use Phpml\Pipeline;
use Phpml\SupportVectorMachine\SupportVectorMachine;

/**
 * Class IntentSelector
 *
 * @package EmmabotBundle\Intent
 */
class IntentSelector
{
    const THRESHOLD = 0.4;

    /**
     * @var Intent[]
     */
    private $intents;

    /**
     * @var Pipeline
     */
    private $classifier;

    /**
     * @var ContextManager
     */
    private $contextManager;

    /**
     * TopicInputProcessor constructor.
     *
     * @param AppKernel $kernel
     * @param ContextManager $manager
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function __construct(AppKernel $kernel, ContextManager $manager)
    {
        $this->intents = array();

        $this->contextManager = $manager;

        $filename = dirname(__FILE__). '/../Resources/data/classifier/topic';

        $modelManager = new ModelManager();
        $this->classifier = $modelManager->restoreFromFile($filename);
        $this->classifier->getEstimator()->setVarPath($kernel->getRootDir().'/../var/temp');
    }


    /**
     * @param Intent $intent
     * @param $id
     */
    public function addIntent(Intent $intent, $id){

        $this->intents[$id] = $intent;
    }


    /**
     * @param string $input
     * @return string
     * @throws \Doctrine\ORM\ORMException
     * @throws LibsvmCommandException
     */
    public function selectTopic($input, $processedInput){

        $samples = [$input];
        foreach ($this->classifier->getTransformers() as $transformer) {
            $transformer->transform($samples);
        }

        /** @var SupportVectorMachine $estimator */
        $estimator = $this->classifier->getEstimator();
        $probablities = $estimator->predictProbability($samples);
        $probablities = reset($probablities);
        arsort($probablities);

        $oldTopic = $this->contextManager->getContext('intent');

        if(reset($probablities) > self::THRESHOLD){
            reset($probablities);
            $topic = key($probablities);
            $this->contextManager->saveContext('intent', $topic);

        } elseif($oldTopic) {
            $topic = $oldTopic;
        } else {
            return $this->renderNoTopic();
        }

        foreach ($this->intents as $id => $processor){
            if($topic == $id ){
                $processor->setContextManager($this->contextManager);
                return $processor->process($input, $processedInput);
            }
        }

        return $this->renderNoTopic();
    }


    /**
     * @return string
     */
    private function renderNoTopic()
    {
        return 'Tut mir leid. Ich konnte deine Eingabe nicht verstehen.';
    }
}