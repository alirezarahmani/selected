<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\PricingQuestion;
use FormulaParser\FormulaParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class PricingQuestionWriteListenerSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public function onKernelView(ViewEvent $event)
    {

        /**
         * @var PricingQuestion $pricingQuestion
         */
        $pricingQuestion=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (!$pricingQuestion instanceof PricingQuestion || $pricingQuestion->getFinal()){
            return;
        }
        if ($method===Request::METHOD_POST && $method===Request::METHOD_PUT){
            return;
        }

        $parser=new FormulaParser($pricingQuestion->getFormula(),0);
        $parser->setVariables(['x' => 0]);
        $result = $parser->getResult(); // [0 => 'done', 1 => 16.38]

        if ($result[0]!=='done'){
                throw new InvalidValueException('formula is not valid');
        }



    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.view' => ['onKernelView',EventPriorities::PRE_WRITE],
        ];
    }
}
