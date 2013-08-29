<?php
/**
 * @author nerijus
 */

namespace Evispa\Api\Product\Tests;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionAnnotations() {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $versionReader = new \Evispa\ObjectMigration\VersionReader($reader);
        $converter = new \Evispa\ObjectMigration\VersionConverter($versionReader);

        // create object

        $code = new \Evispa\Api\Product\Model\Code\ProductCodeV1();
        $code->code = "Hello";

        // migrate to another version

        $simpleCode = $converter->migrate($code, 'vnd.evispa.code.v1');

        $this->assertTrue($simpleCode instanceof \Evispa\Api\Product\Model\Code\CodeV1);
        $this->assertEquals("Hello", $simpleCode->code);
    }
}