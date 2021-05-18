<?php


namespace App\Controller\Auth;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\User;
use App\Service\MobileConfirmation;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfirmMobile
{
    /**
     * @var MobileConfirmation
     */
    private $confirmation;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RefreshTokenManagerInterface
     */
    private $refreshTokenManager;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var JWTTokenManagerInterface
     */
    private $JWTManager;


    /**
     * ConfirmMobile constructor.
     * @param MobileConfirmation $confirmation
     * @param IriConverterInterface $iriConverter
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param JWTTokenManagerInterface $JWTManager
     * @param RefreshTokenManagerInterface $refreshTokenManager
     */
    public function __construct(MobileConfirmation $confirmation,
                                IriConverterInterface $iriConverter,
                                EntityManagerInterface $entityManager,
                                ValidatorInterface $validator,
                                JWTTokenManagerInterface $JWTManager,
                                RefreshTokenManagerInterface $refreshTokenManager)
    {
        $this->confirmation = $confirmation;
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->validator = $validator;
        $this->JWTManager = $JWTManager;
    }

    public function __invoke(Request $request)
    {

       $params=json_decode($request->getContent(),true);
       $key=$params['code'];
       $user_iri=$params['user'];
        /**
         * @var User $user
         */

       $user=$this->iriConverter->getItemFromIri($user_iri);
       if (!isset($key))
           throw new InvalidArgumentException('code is required');


       $code=$this->confirmation->getCode($user);

       if($code!==(int)$key){
           throw new InvalidArgumentException('code is not correct');
       }

       $user->setMobileIsConfirmed(true);
       $this->entityManager->persist($user);
       $this->entityManager->flush();

       //generate reresh token
        $datetime = new \DateTime();
        $datetime->modify('+2592000 seconds');

        $refreshToken = $this->refreshTokenManager->create();

        $accessor = new PropertyAccessor();
        $userIdentityFieldValue = $accessor->getValue($user, 'email');

        $refreshToken->setUsername($userIdentityFieldValue);
        $refreshToken->setRefreshToken();
        $refreshToken->setValid($datetime);

        $valid = false;
        while (false === $valid) {
            $valid = true;
            $errors = $this->validator->validate($refreshToken);
            if ($errors->count() > 0) {
                foreach ($errors as $error) {
                    if ('refreshToken' === $error->getPropertyPath()) {
                        $valid = false;
                        $refreshToken->setRefreshToken();
                    }
                }
            }
        }

        $this->refreshTokenManager->save($refreshToken);
        $refreshToken = $refreshToken->getRefreshToken();
        return new JsonResponse([
            'token' => $this->JWTManager->create($user),
            'refresh_token'=>$refreshToken]);

    }

}
