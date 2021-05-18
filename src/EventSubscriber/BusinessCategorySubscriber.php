<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\BusinessCategory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

class BusinessCategorySubscriber implements EventSubscriberInterface
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
        $category = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$category instanceof BusinessCategory || !(Request::METHOD_POST === $method || Request::METHOD_PUT===$method)) {
            return;
        }
        if(!$this->security->isGranted('ROLE_SUPER_ADMIN')){
            throw new UnauthorizedHttpException('role','only super admin can create');
        }
    }
}
