<?php


namespace App\Controller\Auth;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Controller\Base;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfirmEmailByToken extends Base
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(Security $security,
                                ValidatorInterface $validator,
                                ParameterBagInterface $parameterBag,
                                EntityManagerInterface $manager,RouterInterface $router)
    {
        parent::__construct($security, $validator, $parameterBag);
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * @Route(
     *     name="confirm_email",
     *     path="/api/users/confirm_email/{token}",
     *     requirements={"token"=".+"},
     *     methods={"GET"},
     *     defaults={
     *
     *         "_api_item_operation_name"="confirmEmail"
     *     }
     * )
     *
     */
    public function __invoke($token)
    {
        $str_info=($this->decryptToken($token));
        $info=explode('}}',$str_info);
        if (count($info)<2)
            return new JsonResponse(array('message'=>'bad token code327'),400);
        /**
         * @var User $user
         */
       $user=$this->manager->getRepository(User::class)->find($info[0]);
       if (sha1($user->getPassword())===$info[1]){
           $user->setMobileIsConfirmed(true);
           $this->manager->persist($user);
           $this->manager->flush();
           $route= $this->router->generate('Home',['path'=>'login']);
            return new RedirectResponse($route);

       }else{
           throw new InvalidArgumentException('bad token');
       }

    }
}
