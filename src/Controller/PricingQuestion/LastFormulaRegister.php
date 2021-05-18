<?php


namespace App\Controller\PricingQuestion;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\PricingQuestion;
use Doctrine\ORM\EntityManagerInterface;
use FormulaParser\FormulaParser;
use Symfony\Component\HttpFoundation\Request;

class LastFormulaRegister
{
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

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        $last_formula=$params["formula"];
        $match=preg_match_all('/\{([a-zA-z\/\d]*)\}/',$last_formula,$matches);

       if ($match)
            foreach ($matches[1] as $pricing_question){
               $item= $this->iriConverter->getItemFromIri($pricing_question);
            }
        $copy_formula=preg_replace('/\{([a-zA-z\/\d]*)\}/','x',$last_formula);
        $parser=new FormulaParser($copy_formula,2);
        $parser->setVariables(['x' => 1]);


        $result=$parser->getResult();
        if(!is_infinite((float)$result[1]) && is_numeric((float)$result[1])){
            $final=$this->manager->getRepository(PricingQuestion::class)->findOneBy(['final'=>true]);
            if (isset($final)){
                $this->manager->remove($final);
            }
          $pricing_q=new PricingQuestion();
          $pricing_q->setQuestion('last formula');
          $pricing_q->setFormula($last_formula);
          $pricing_q->setFinal(true);
          $this->manager->persist($pricing_q);

          $this->manager->flush();
          return $pricing_q;

        }else{
            throw new InvalidValueException('this variable for this formula result '.$result[1].' '.$last_formula);
        }

    }



}
