<?php


namespace App\Controller\Annotation;

use App\Entity\Annotations;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnnotationType
{

    public function __invoke()
    {
       return new JsonResponse(Annotations::ANNOTATION_TYPE);
    }


}
