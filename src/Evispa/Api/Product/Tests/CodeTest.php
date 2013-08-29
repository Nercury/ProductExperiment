<?php
/*
 * Copyright (c) 2013 Evispa Ltd. Vilnius
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Nerijus Arlauskas <nercury@gmail.com>
 */

namespace Evispa\Api\Product\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Evispa\Api\Product\Model\Code\CodeV1;
use Evispa\Api\Product\Model\Code\ProductCodeV1;
use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionAnnotations() {
        $converter = new VersionConverter(new VersionReader(new AnnotationReader()));

        // create object

        $code = new ProductCodeV1();
        $code->code = "Hello";

        // migrate to another version

        $simpleCode = $converter->migrate($code, 'vnd.evispa.code.v1');

        $this->assertTrue($simpleCode instanceof CodeV1);
        $this->assertEquals("Hello", $simpleCode->code);
    }
}