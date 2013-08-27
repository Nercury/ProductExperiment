<?php
/**
 * User: darius
 * Date: 8/26/13
 * Time: 4:59 PM
 */

namespace Evispa\Resource\Core\ApiDocBundle\AnnotationHandler;

use Evispa\Resource\Component\MultipartResource\Annotations\Resource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Nelmio\ApiDocBundle\Extractor\Nelmio;
use Nelmio\ApiDocBundle\Extractor\ReflectionMethod;
use Nelmio\ApiDocBundle\Extractor\Symfony;
use Symfony\Component\Routing\Route;

class ResourceAnnotationHandler implements HandlerInterface
{
    /**
     * Parse route parameters in order to populate ApiDoc.
     *
     * @param \Nelmio\ApiDocBundle\Annotation\ApiDoc $annotation
     * @param array                                  $annotations
     * @param \Symfony\Component\Routing\Route       $route
     * @param \ReflectionMethod                      $method
     */
    public function handle(ApiDoc $apiAnnotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Resource) {
                $apiAnnotation->setSection($annotation->getName());
            }
        }
    }
}
