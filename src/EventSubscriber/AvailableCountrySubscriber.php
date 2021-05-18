<?php


namespace App\EventSubscriber;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\AvailableCountry;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\IFTTTHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Intl\Countries;

class AvailableCountrySubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent', EventPriorities::PRE_WRITE],
        ];
    }

    public function onViewEvent(ViewEvent $event)
    {
        $country = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$country instanceof AvailableCountry || !(Request::METHOD_POST === $method || Request::METHOD_PUT === $method)) {
            return;
        }
        $long=Countries::getName($country->getName());
        $country->setLongName($long);
        $this->manager->persist($country);

        return $country;

    }
}

