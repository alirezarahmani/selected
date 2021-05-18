<?php


namespace App\Controller\Auth;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class GetAvailableMobileCode
{
    /**
     * @var string
     */
    private $phon_dir;

    public function __construct(KernelInterface $kernel)
    {
        $this->phon_dir=$kernel->getProjectDir().'/public/json/phone.json';
    }

    public function __invoke()
    {
        $file=file_get_contents($this->phon_dir);
        return new JsonResponse(json_decode($file));

    }

}
