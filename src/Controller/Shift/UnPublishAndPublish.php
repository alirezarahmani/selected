<?php


namespace App\Controller\Shift;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Shift;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function Doctrine\ORM\QueryBuilder;

class UnPublishAndPublish
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var IriConverterInterface
     */
    private $converter;

    public function __construct(EntityManagerInterface $manager,IriConverterInterface $converter)
    {
        $this->manager = $manager;
        $this->converter = $converter;
    }

    public function __invoke(Request $request)
    {
        $request_content=json_decode($request->getContent(),true);
        $users_array=[];
        if(array_key_exists('publish',$request_content)){
            try{
                $shift_start =(new \DateTimeImmutable($request_content['shiftStartTime']))->format('Y-m-d 00:00') ;
                $shift_end=(new \DateTimeImmutable($request_content['shiftEndTime']))->format('Y-m-d 00:00') ;
                $publish=$request_content['publish'];
                $users=$request_content['users'];
                if (array_key_exists('users',$request_content) && count($users)>0){
                    foreach ($users as $user){
                        $u=$this->converter->getItemFromIri($user);
                        $users_array[]=$u->getId();
                    }
                }

            }catch (HttpException $e){
                throw new \HttpException($e->getMessage(),400);

            }

            $qb=$this->manager->createQueryBuilder();
            $qb->select('s')
                ->from(Shift::class,'s')
                ->where("s.startTime > '".$shift_start."'")
                ->andWhere("s.endTime <'".$shift_end."'")
                ->andWhere($qb->expr()->eq("s.publish",":pub"));
            if (count($users_array)>0){
                $qb->andWhere($qb->expr()->in("s.ownerId",$users_array));
            }

            $qb->setParameter("pub",!$publish);
            $query=$qb->getQuery();
            $shift_array=$query->getResult();


            /**
             * @var Shift $shift
             */
            foreach ($shift_array as $shift){
                $shift->setPublish((bool)$publish);
                $shift->setInformed(false);
                $this->manager->persist($shift);

            }



            $this->manager->flush();
            return new JsonResponse(count($shift_array),200);
        }

    }
}
