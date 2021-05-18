<?php

namespace App\Security\Voter;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;

class BusinessRoleVoter extends Voter
{

    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;


    /**
     * @var BusinessFinder
     */
    private $businessFinder;

    public function __construct(CacheInterface $cache,
                                EntityManagerInterface $manager,
                                ParameterBagInterface $parameterBag,
                                BusinessFinder $businessFinder)
    {

        $this->manager = $manager;
        $this->parameterBag = $parameterBag;
        $this->businessFinder = $businessFinder;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['BUSINESS_EMPLOYEE', 'BUSINESS_SUPERVISOR','BUSINESS_MANAGER','BUSINESS_ACCOUNT']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /**
         * @var User $user
         */


        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        //in the service business existence check
        $business_id=$this->businessFinder->getUserBusiness();


        $useBusinessRole_repo=$this->manager->getRepository(UserBusinessRole::class);
        $userBusiness=$useBusinessRole_repo->findBy(array('user'=>$user->getId(),'business'=>$business_id));
        $system_roles=$this->parameterBag->get('roles');


        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'BUSINESS_EMPLOYEE':
                /**
                 * @var UserBusinessRole $businessRole
                 */
                foreach ($userBusiness as $businessRole){
                    if ($businessRole->getRole()===$this->parameterBag->get('roles')['employee']||
                        $businessRole->getRole()===$this->parameterBag->get('roles')['supervisor']||
                        $businessRole->getRole()===$this->parameterBag->get('roles')['manager']||
                        $businessRole->getRole()===$this->parameterBag->get('roles')['account']){
                        return true;
                    };
                    return false;
                }
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case 'BUSINESS_SUPERVISOR':
                // logic to determine if the user can EDIT
                // return true or false

                foreach ($userBusiness as $businessRole){
                    if ($businessRole->getRole()===$this->parameterBag->get('roles')['supervisor']||
                        $businessRole->getRole()===$this->parameterBag->get('roles')['manager']||
                        $businessRole->getRole()===$this->parameterBag->get('roles')['account']){
                        return true;
                    };
                    return false;
                }
                break;
            case 'BUSINESS_MANAGER':
                // logic to determine if the user can VIEW
                // return true or false
                foreach ($userBusiness as $businessRole){
                    if ($businessRole->getRole()===$this->parameterBag->get('roles')['manager'] ||
                        $businessRole->getRole()===$this->parameterBag->get('roles')['account']){

                        return true;
                    };

                    return false;
                }
                break;
            case 'BUSINESS_ACCOUNT':
                // logic to determine if the user can VIEW
                // return true or false
                foreach ($userBusiness as $businessRole){
                    if ($businessRole->getRole()===$this->parameterBag->get('roles')['account']){
                        return true;
                    };
                    return false;
                }
                break;
            default:
                    return true;
        }

        return true;
    }
}
