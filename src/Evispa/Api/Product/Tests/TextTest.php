<?php

namespace Evispa\Api\Product\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;

/**
 * Description of TextTest
 *
 * @author nerijus
 */
class TextTest extends \PHPUnit_Framework_TestCase
{
    public function testTextAnnotations() {
        $converter = new VersionConverter(new VersionReader(new AnnotationReader()), 'Evispa\Api\Product\Model\Text\LocalizedTextV1', array(
            'locale' => 'lt',
        ));

        // create object

        $text = new \Evispa\Api\Product\Model\Text\TextV1();
        $text->name = "Pavadinimas";

        // migrate to another version

        $localizedText = $converter->migrateFrom($text);

        $this->assertTrue($localizedText instanceof \Evispa\Api\Product\Model\Text\LocalizedTextV1);
        $this->assertEquals("Pavadinimas", $localizedText->items['lt']->name);
    }
}