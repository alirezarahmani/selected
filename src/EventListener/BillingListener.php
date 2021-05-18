<?php


namespace App\EventListener;


use App\Entity\Billing;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class BillingListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Billing || !$entity->getIsDefault()) {
            return;
        }

        $entityManager = $args->getObjectManager();
        $billingRepo=$entityManager->getRepository(Billing::class);
        $defaults=$billingRepo->findBy(['isDefault'=>true]);
        /**
         * @var Billing $default
         */
        foreach ($defaults as $default) {
            $default->setIsDefault(false);
            $entityManager->persist($default);
        }
        $entityManager->flush();





    }

}
