<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method string[] getReachableRoleNames(string[] $roles)
 */
class UserRoleHierarchyVoter extends Voter implements RoleHierarchyInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(EntityManagerInterface $manager,BusinessFinder $finder,ParameterBagInterface $bag)
    {
        $this->manager = $manager;
        $this->finder = $finder;
        $this->bag = $bag;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['USER_EDIT', 'USER_REMOVE'])
            && $subject instanceof \App\Entity\User;
    }
    //it checks user role inex to check user permit to edits
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /**
         * @var User $user
         */
        $user = $token->getUser();
        $result=false;

        $role_hierarchy=array(
            $this->bag->get('roles')['employee'],
            $this->bag->get('roles')['manager'],
            $this->bag->get('roles')['manager'],
            $this->bag->get('roles')['account'],
        );
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        /**
         * @var User $subject;
         */
        $subject_role=$this->manager->getRepository(UserBusinessRole::class)->findOneBy(['user'=>$subject,'business'=>$this->finder->getUserBusiness()]);
        $sub_role=$subject_role->getRole();

        $user_role=$user->getUserBusinessRoles()->filter(function ($entry) use ($role_hierarchy,$sub_role,$result){
            /**
             * @var UserBusinessRole $entry
             */
            if ($entry->getBusiness()->getId()== $this->finder->getUserBusiness())
                return true;
        });
      foreach ($user_role as $business_role){
          if (array_search($business_role->getRole(),$role_hierarchy) >= array_search($sub_role,$role_hierarchy,$result)){
              return true;
          }
      }



    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string[] getReachableRoleNames(string[] $roles)
    }
}
