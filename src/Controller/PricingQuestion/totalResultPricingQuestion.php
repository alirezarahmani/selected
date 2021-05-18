<?php


namespace App\Controller\PricingQuestion;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\PricingQuestion;
use Doctrine\ORM\EntityManagerInterface;
use FormulaParser\FormulaParser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class totalResultPricingQuestion
{
    //@todo:parse result using https://github.com/denissimon/formula-parser
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(EntityManagerInterface $manager,IriConverterInterface $iriConverter)
    {
        $this->manager = $manager;
        $this->iriConverter = $iriConverter;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * it get all formula that last formula contain theri pricing question and calculate last result
     */
    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        $question_list=$params['answers'];
        $total=0;

        /**
         * @var PricingQuestion $final
         */
        $final=$this->manager->getRepository(PricingQuestion::class)->findOneBy(['final'=>true]);
        if (isset($final)){
            $formula=$final->getFormula();
            $match=preg_match_all('/\{([a-zA-z\/\d]*)\}/',$formula,$matches);

            if ($match){
                $params=[];
                foreach ($matches[1] as $key=>$pricing_question){
                    /**
                     * @var PricingQuestion $item
                     */
                    $item= $this->iriConverter->getItemFromIri($pricing_question);
                    $frmla=$item->getFormula();
                    if (!isset($question_list[$pricing_question])){
                       throw new InvalidValueException('for this question no answer prepared');
                    }



                    $parser=new FormulaParser($frmla,2);
                    $parser->setVariables(['x' => $question_list[$pricing_question]]);


                    $result=$parser->getResult();
                    if($result[0]!=='done'){
                        throw new InvalidValueException($frmla.' has syntax error with the answer '.$question_list[$pricing_question]);
                    }
                    $params[$matches[0][$key]]=$result[1];

                }
            }

            $last_formula = str_replace(array_keys($params), $params, $final->getFormula());

            $parser=new FormulaParser($last_formula,2);
            $parser->setVariables(['x' => 1]);


            $result_last=$parser->getResult();

            if ($result_last[0]==='done'){
               $final->setAnswer($result_last[1]);
               return  $final;
            }else{
                throw new InvalidValueException("syntax error");
            }



        }else{
            throw new InvalidValueException('no final formula find');
    }

//        foreach ($question_list as $answers){
//            foreach ($answers as $answer){
//
//                /**
//                 * @var PricingQuestion $question
//                 */
//
//                $question=$this->iriConverter->getItemFromIri($answer['pricing_question']);
//                $variable=(int)$answer["answer"];
//
//                $parser=new FormulaParser($question->getFormula(),2);
//                $parser->setVariables(['x' => $variable]);
//
//
//                $result=$parser->getResult();
//                if(!is_infinite((float)$result[1]) && is_numeric((float)$result[1])){
//                    $total+=$result[1];
//                }else{
//                    throw new InvalidValueException('this variable for this formula result '.$result[1].' '.$question->getFormula());
//                }
//            }
//        }
        return new JsonResponse(['result'=>$total]);
    }


}
