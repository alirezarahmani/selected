<?php


namespace App\Controller\Shift;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CountPublishAndUnPublish
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request)
    {
        $startTime=$request->query->get('startTime');
        $endTime=$request->query->get('startTime');
        $connection=$this->entityManager->getConnection();
        $SQL_j= "SELECT  
                    COUNT(IF(`publish`=1,1,null)) published, 
                    COUNT(IF(`publish`=0,1,null)) unPunlished 
                    FROM shift";

        $stmt_j = $connection->prepare($SQL_j);
        $stmt_j->execute();
        $result= $stmt_j->fetchAll();
       return new JsonResponse($result[0]);
    }

}
