<?php


namespace App\Controller\AttendanceTimes;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\User;
use App\Service\AttendanceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;



class LoginTerminalAuth
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var AttendanceService
     */
    private $attendanceService;

    public function __construct(EntityManagerInterface $manager,ValidatorInterface $validator,AttendanceService $attendanceService)
    {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->attendanceService = $attendanceService;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        if (!isset($params)){
            throw new InvalidValueException("invalid params structure");
        }
        $this->attendanceService->validateTerminalToken($params["header"]);
        $constraint = new Assert\Collection([
            // the keys correspond to the keys in the input array
            'header' => new Assert\NotBlank(),
            'email' => [new Assert\Email(),new Assert\NotBlank()]
        ]);

        $violation=[];
        $errors=$this->validator->validate($params,$constraint);
        if (count($errors)>0){
            /**
             * @var ConstraintViolation $er
             */
            foreach ($errors as $er){

                $violation[$er->getPropertyPath()]=$er->getMessage();
            }
            throw new InvalidArgumentException(json_encode($violation));
        }
        $user=$this->manager->getRepository(User::class)->findOneBy(["email"=>$params["email"]]);
        if (isset($user)){
            return $user;
        }else{
            throw new InvalidValueException("user not found");
        }




    }

}
