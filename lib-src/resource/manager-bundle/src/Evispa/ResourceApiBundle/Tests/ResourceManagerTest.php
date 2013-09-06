<?php
/*
 * Copyright (c) 2013 Evispa Ltd.
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
 * @author Darius Krištapavičius <darius@evispa.lt>
 */

namespace Evispa\ResourceApiBundle\Tests;


use Doctrine\Common\Annotations\AnnotationReader;
use Evispa\ObjectMigration\VersionReader;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject;
use Evispa\ResourceApiBundle\Backend\Unicorn;
use Evispa\ResourceApiBundle\Backend\UnicornBackend;
use Evispa\ResourceApiBundle\Manager\ResourceManager;
use Evispa\ResourceApiBundle\Tests\Mock\MockProduct;
use Evispa\ResourceApiBundle\Tests\Mock\MockProductBackend;
use Symfony\Component\PropertyAccess\PropertyPath;

class ResourceManagerTest extends \PHPUnit_Framework_TestCase
{
        public function testFindOne() {

            $reader = new AnnotationReader();
            $versionsReader = new VersionReader($reader);

            $class = new \ReflectionClass('Evispa\ResourceApiBundle\Tests\Mock\MockProduct');

            $mockBackend = new MockProductBackend();
            $mockBackend->findOneResult['a1'] = new PrimaryBackendResultObject('a1');

            $unicorn = new Unicorn(new UnicornBackend(array(), $mockBackend));

            $manager = new ResourceManager($reader, $versionsReader, array(), $class, array(), $unicorn);

            $product = $manager->findOne('a1');

            $this->assertTrue($product instanceof MockProduct);
            $this->assertEquals('a1', $product->getSlug());

        }
}
