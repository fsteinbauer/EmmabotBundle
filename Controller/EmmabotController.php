<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 24.03.2018
 * Time: 14:40
 */

namespace EmmabotBundle\Controller;


use Phpml\Classification\SVC;
use Phpml\Dataset\ArrayDataset;
use Phpml\Dataset\CsvDataset;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\ModelManager;
use Phpml\Pipeline;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Tokenization\WordTokenizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class EmmabotController
 *
 * @package EmmabotBundle\Controller
 * @Route("/emma")
 */
class EmmabotController extends Controller
{
    /**
     * This method is the entrypoint when a HTTP-Request
     * is sent to the controller to reply to a message
     *
     * @Route("/answer",
     *     name="emma_answer",
     *     options={"expose": true}
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function answerAction(Request $request){

        $input = $request->get('input');
        if(trim($input) == 'clear'){
            $this->deleteContext();
            return new JsonResponse([
                'success'   => true,
                'answer'    => 'Context deleted'
            ]);
        }

        $answer = $this->get('emmabot')->answer($input);

        return new JsonResponse([
            'success'   => true,
            'answer'    => $answer['text']
        ]);
    }


    /**
     * The chat-window is loaded asynchronously.
     * This method generates the HTML to be appended to the document.
     *
     * @Route("/load",
     *     name="emma_load",
     *     options={"expose": true}
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadChatWindow(Request $request){

        return new JsonResponse([
            'success'   => true,
            'html'      => $this->renderView('EmmabotBundle:Bot:chatbox.html.twig')
        ]);
    }


    /**
     * @Route("/train")
     */
    public function train(){

        $filename = dirname(__FILE__). '\..\Resources\data\topics.csv';
        $dataset = new CsvDataset($filename, 1);


        $pipeline = new Pipeline([
            new TokenCountVectorizer(new WordTokenizer()),
            new TfIdfTransformer()
        ], new SVC(Kernel::RBF, 10000));


        $samples = [];
        foreach ($dataset->getSamples() as $sample) {
            $samples[] = $sample[0];
        }

        $dataset = new ArrayDataset($samples, $dataset->getTargets());

        $pipeline->train($dataset->getSamples(), $dataset->getTargets());

        $filepath = dirname(__FILE__).'\..\Resources\data\classifier\topic';
        $modelManager = new ModelManager();
        $modelManager->saveToFile($pipeline, $filepath);

    }


    /**
     * This method is added for development purposes.
     * When the string "clear" is received, the all the context from
     * previous conversations is lost.
     */
    private function deleteContext()
    {
        $connection = $this->getDoctrine()->getConnection();
        $platform   = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL('context', true));
    }
}