<?php


namespace App\Controller\PricingQuestion;


use App\Entity\PricingQuestion;
use Doctrine\ORM\EntityManagerInterface;

class ListPricingQuestion
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke()
    {
       $pricing_list=$this->manager->getRepository(PricingQuestion::class)->findBy(['final'=>false]);
        return $pricing_list;
    }
}
