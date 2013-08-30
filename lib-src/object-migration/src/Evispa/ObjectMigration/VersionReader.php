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
 * @author Nerijus Arlauskas <nercury@gmail.com>
 */

namespace Evispa\ObjectMigration;

use Doctrine\Common\Annotations\Reader;
use Evispa\ObjectMigration\Action\CloneAction;
use Evispa\ObjectMigration\Action\CreateAction;

class VersionReader
{
    private $classReflections = array();
    private $classVersions = array();
    private $classMigrationMethods = array();

    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Get the reflection class instance for a class name.
     *
     * @param string $className Full class name.
     *
     * @return \ReflectionClass
     */
    public function getReflectionClass($className) {
        if (isset($this->classReflections[$className])) {
            return $this->classReflections[$className];
        }

        $class = new \ReflectionClass($className);
        $this->classReflections[$className] = $class;

        return $class;
    }

    /**
     * Get class version annotation for a class name.
     *
     * @param string $className Full class name.
     *
     * @return Annotations\Version
     *
     * @throws Exception\NotVersionedException If object is not versioned.
     */
    public function getClassVersionAnnotation($className) {
        if (isset($this->classVersions[$className])) {
            return $this->classVersions[$className];
        }

        $class = $this->getReflectionClass($className);

        $versionAnnotation = $this->reader->getClassAnnotation(
            $class, 'Evispa\ObjectMigration\Annotations\Version'
        );

        if (null === $versionAnnotation) {
            throw new Exception\NotVersionedException($className);
        }

        $this->classVersions[$className] = $versionAnnotation;

        return $versionAnnotation;
    }

    private function getClassMigrationAnnotations($className) {
        $class = $this->getReflectionClass($className);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $annotations = array();

        foreach ($methods as $method) {
            $migrationAnnotation = $this->reader->getMethodAnnotation(
                $method, 'Evispa\ObjectMigration\Annotations\Migration'
            );

            if (null === $migrationAnnotation) {
                continue;
            }

            $annotations[] = array(
                'method' => $method,
                'annotation' => $migrationAnnotation,
            );
        }

        return $annotations;
    }

    public function getClassMigrationMethods($className) {
        if (isset($this->classMigrationMethods[$className])) {
            return $this->classMigrationMethods[$className];
        }

        $migrationAnnotations = $this->getClassMigrationAnnotations($className);

        $migrationMethods = array('from' => array(), 'to' => array());

        foreach ($migrationAnnotations as $migrationAnnotation) {
            $method = $migrationAnnotation['method'];
            $migrationAnnotation = $migrationAnnotation['annotation'];

            if (null !== $migrationAnnotation->from) {
                if (false === $method->isStatic() || 1 !== $method->getNumberOfParameters()) {
                    throw new \LogicException(
                        'Method "'.$method->getName().'" in "'.$className.'" should be static and require 1 parameter.'
                    );
                }

                if ($migrationAnnotation->from === $className) {
                    throw new \LogicException(
                        'Method "'.$method->getName().'" in "'.$className.'" should have a migration from a different class.'
                    );
                }

                $otherClass = $migrationAnnotation->from;

                $migrationMethods['from'][] = new CreateAction($method);
            } elseif (null !== $migrationAnnotation->to) {
                if (true === $method->isStatic() || 0 !== $method->getNumberOfParameters()) {
                    throw new \LogicException(
                        'Method "'.$method->getName().'" in "'.$className.'" should not be static and require 0 parameters.'
                    );
                }

                if ($migrationAnnotation->to === $className) {
                    throw new \LogicException(
                        'Method "'.$method->getName().'" in "'.$className.'" should have a migration to a different class.'
                    );
                }

                $otherClass = $migrationAnnotation->to;

                $migrationMethods['to'][] = array(
                    'method' => $method,
                    'class' => $class,
                    'other_class' => $otherClass,
                );
            }
        }

        $this->classMigrationMethods[$className] = $migrationMethods;

        return $migrationMethods;
    }

    public function getRequiredClassOptions($className) {
        return array();
    }

    /**
     * Get information about object version and available migrations to other objects.
     *
     * @param string $className Class name.
     *
     * @return MigrationMetadata
     */
    public function getMigrationMetadata($className) {

        $versionAnnotation = $this->getClassVersionAnnotation($className);

        $migrationMethods = $this->getClassMigrationMethods($className);

        $migrationsFrom = array();
        $migrationsTo = array();

        foreach ($migrationMethods['from'] as $migrationMethod) {
            $otherClassName = $migrationMethod['other_class'];
            $otherClassVersionAnnotation = $this->getClassVersionAnnotation($otherClassName);
            $migrationsFrom[$otherClassVersionAnnotation->version] = $migrationMethod;
        }

        foreach ($migrationMethods['to'] as $migrationMethod) {
            $otherClassName = $migrationMethod['other_class'];
            $otherClassVersionAnnotation = $this->getClassVersionAnnotation($otherClassName);
            $migrationsTo[$otherClassVersionAnnotation->version] = new CloneAction($migrationMethod);
        }

        return new MigrationMetadata($versionAnnotation->version, $migrationsFrom, $migrationsTo);
    }

}