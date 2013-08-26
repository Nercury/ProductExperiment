<?php
/**
 * @author nerijus
 */

namespace Evispa\Component\MultipartResource\Tests;

use Evispa\Component\MultipartResource\Config\ResourceConfig;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function testResource() {

        $resourceConfig = new ResourceConfig(
            'product',
            'product.title',
            ''
        );

        $nameConfig = new \Evispa\Component\MultipartResource\Config\ResourcePartConfig('name');
        $nameConfig->setInternalCrud(new Mock\MockInternalName());
        $resourceConfig->addPart($nameConfig);

        $manager = new Mock\MockResourceArrayManager($resourceConfig);

        $entities = array(
            new Product(),
            new Product(),
        );

        $resources = $manager->fromObjs($entities, array('qty'));
        //$form = $manager->getFormType();

        $manager->updateEntities($resources, $entities);

        $em->persist($entities[0]);
        $em->persist($entities[1]);
        $em->flush();

        $mm = new MasterManager($em);

        $manager->postUpdate($mm, array(
            $id => $res,
            $id2 => $res2,
        ));
    }
}
