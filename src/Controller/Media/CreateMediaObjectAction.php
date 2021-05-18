<?php


namespace App\Controller\Media;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateMediaObjectAction{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {

        $this->manager = $manager;
    }

    public function __invoke(Request $request): Media
    {
        $uploadedFile = $request->files->get('file');
        $objectable=$request->request->get('objectable');
        if (!isset($uploadedFile) || !isset($objectable) ){
            throw new InvalidArgumentException("objectable and file is required");
        }
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $mediaObject = new Media();
        try {
            $mediaObject->setFile($uploadedFile);
        } catch (\Exception $e) {
            throw new \HttpException($e->getMessage());
        }
        $mediaObject->setObjectable($objectable);
        $this->manager->persist($mediaObject);
        $this->manager->flush();

        return $mediaObject;
    }
}
