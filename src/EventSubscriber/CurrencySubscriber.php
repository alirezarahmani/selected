<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Currency;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Security\Core\Security;

class CurrencySubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent',EventPriorities::PRE_WRITE],
        ];
    }

    public function onViewEvent(ViewEvent $event)
    {
        $currency = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$currency instanceof Currency || !(Request::METHOD_POST === $method || Request::METHOD_PUT===$method)) {
            return;
        }
        if(!$this->security->isGranted('ROLE_SUPER_ADMIN')){
            throw new UnauthorizedHttpException('role','only super admin can create');
        }

        $currency->setSymbol(Currencies::getSymbol($currency->getCode()));
        $currency->setName(Currencies::getName($currency->getCode()));
        return $currency;
    }
}
