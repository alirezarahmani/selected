<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

class FireEmployee
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(Security $security,BusinessFinder $finder,EntityManagerInterface $manager)
    {
        $this->security = $security;
        $this->finder = $finder;
        $this->manager = $manager;
    }

    public function __invoke(Request $request)
    {
        /**
         * @var User $sec_user
         */
        $sec_user=$this->security->getUser();
        $request_content=json_decode($request->getContent(),true);
        $fired_emp=$request_content['id_employee'];
        $usr=$this->manager->getRepository(User::class)->find($fired_emp);
        if (empty($usr))
            throw new ItemNotFoundException('user not found to fire');

        $business=$this->finder->getCurrentUserBusiness();
        $user_business_role=$this->manager->getRepository(UserBusinessRole::class)->findOneBy(['user'=>$fired_emp,'business'=>$business]);
       if (empty($user_business_role))
            throw new NotFoundHttpException(sprintf('%s not found in business',$fired_emp));
       if ($sec_user->getId() === $usr->getId()){
           throw new InvalidArgumentException('you cannot fire yourself');
       }
       if ($this->security->isGranted('BUSINESS_SUPERVISOR') && $this->security->isGranted('USER_EDIT',$usr)){
            $this->manager->remove($user_business_role);
            $this->manager->flush();
        }
        return $this->finder->getCurrentUserBusiness();
    }


}
