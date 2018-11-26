<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 01.05.2018
 * Time: 18:31
 */

namespace EmmabotBundle\Command;

use Phpml\Classification\SVC;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Dataset\ArrayDataset;
use Phpml\Dataset\CsvDataset;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Metric\Accuracy;
use Phpml\ModelManager;
use Phpml\Pipeline;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Tokenization\WordTokenizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class TrainCommand
 *
 * @package EmmabotBundle\Command
 */
class TrainCommand extends ContainerAwareCommand
{
    /**
     * This function registers this train command
     */
    protected function configure()
    {
        $this
            ->setName('emmabot:train')
            ->setDescription('Train a new Dataset for the bot classifier ')
            ->addArgument('topicPath', InputArgument::REQUIRED, 'Argument description')
        ;
    }


    /**
     * This method is the entry point to execute the
     * training logic
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\InvalidArgumentException
     * @throws \Phpml\Exception\SerializeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topicPath = $input->getArgument('topicPath');
        $this->trainTopics($topicPath);
    }


    /**
     * @param $topicPath
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\InvalidArgumentException
     * @throws \Phpml\Exception\SerializeException
     */
    private function trainTopics($topicPath)
    {
        $dataset = new CsvDataset($topicPath, 1);


        $pipeline = new Pipeline([
            new TokenCountVectorizer(new WordTokenizer()),
            new TfIdfTransformer()
        ], new SVC(
            Kernel::RBF,
            10000,
            3,
            null,
            0.0,
            0.001,
            100,
            true,
            true
        ));


        $samples = [];
        foreach ($dataset->getSamples() as $sample) {
            $samples[] = $sample[0];
        }

        $dataset = new ArrayDataset($samples, $dataset->getTargets());

        $pipeline->train($dataset->getSamples(), $dataset->getTargets());

        $filepath = dirname(__FILE__).'/../Resources/data/classifier/topic';
        $modelManager = new ModelManager();
        $modelManager->saveToFile($pipeline, $filepath);
    }

}
