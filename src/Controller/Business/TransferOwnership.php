<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\BusinessBank;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class TransferOwnership
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var IriConverterInterface
     */
    private $converter;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(Security $security,
                                EntityManagerInterface $manager,
                                BusinessFinder $finder,
                                ParameterBagInterface $bag,
                                IriConverterInterface $converter)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->converter = $converter;
        $this->finder = $finder;
        $this->bag = $bag;
    }

    /**
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        if(! $this->security->isGranted('BUSINESS_ACCOUNT')){
            throw new InvalidArgumentException("your not an accountant");
        }
       $roles=($this->bag->get('roles'));

        $params=json_decode($request->getContent(),true);
        $replacement=$params["user"];
        $refresh_bank=$params["refresh_bank"];
        /**
         * @var User $new_accouter
         */
        $new_accouter=$this->converter->getItemFromIri($replacement);
        //now we are shure current user is admin of a business because granted condition above
        /**
         * @var User $admin
         */
        $admin=$this->security->getUser();
        $admin_roles=$admin->getUserBusinessRoles()->getValues();
        /**
         * @var Business $current_business
         */
        $current_business=$this->finder->getCurrentUserBusiness();

        $user_business_roles=($new_accouter->getUserBusinessRoles()->getValues());
        $has_role=false;
        /**
         * @var UserBusinessRole $role
         */
        foreach ($user_business_roles as $role){
            /*if1*/
            if ($role->getBusiness()===$current_business){
                    $role->setRole($roles["account"]);
                    $this->manager->persist($role);
                    $has_role=true;

            }
        }
        if (!$has_role){//if has role false means above (if1) never be true
            throw new InvalidArgumentException("the user you selected is not in this buisness");
        }
        //remove owner ship from current admin

        /**
         * @var UserBusinessRole $business_roles
         */
        foreach ($admin_roles as $business_roles){
            if($business_roles->getBusiness()===$current_business){
                $business_roles->setRole($roles["employee"]);
                $this->manager->persist($business_roles);
                if ($refresh_bank){
                    /**
                     * @var BusinessBank $current_business_bank
                     */
                    $current_business_bank=$this->manager->getRepository(BusinessBank::class)->findOneBy(['business'=>$current_business,'cancel'=>false]);
                    if (isset($current_business_bank)){
                        $current_business_bank->setCancel(true);
                        $this->manager->persist($current_business_bank);
                    }


                }

            }
        }
        //when ownerShip cancel last bank account info should be canceled

        $this->manager->flush();
        return $new_accouter;


    }

}
