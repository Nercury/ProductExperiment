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

/**
 *
 *
 */
class VersionConverter
{
    /**
     * Version reader.
     *
     * @var VersionReader
     */
    private $reader;

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $options;

    public function __construct(VersionReader $reader, $className, $options = array())
    {
        $this->reader = $reader;
        $this->className = $className;

        $requiredOptions = $reader->getRequiredClassOptions($className);

        $this->options = $options;
    }

    /**
     * Migrate object to specified version.
     *
     * @param mixed $object Object instance.
     * @param string $otherVersion Version name.
     *
     * @return mixed Migrated object.
     */
    public function migrateTo($object, $otherVersion) {
        $className = get_class($object);

        if ($className !== $this->className) {
            throw new \LogicException('Converter for class "'.$className.'" can not migrate "'.$className.'" objects.');
        }

        $migrationMethods = $this->reader->getClassMigrationMethods($className);
        if (isset($migrationMethods->to[$otherVersion])) {
            $action = $migrationMethods->to[$otherVersion];
            return $action->run($object, $this->options);
        }
        return null;
    }

    /**
     * Get object from other version.
     *
     * @param mixed $object Object instance.
     * @param string $version Version name.
     *
     * @return mixed Migrated object.
     */
    public function migrateFrom($otherObject) {
        $otherObjectClass = get_class($otherObject);
        $otherVersion = $this->reader->getClassVersion($otherObjectClass);
        $migrationMethods = $this->reader->getClassMigrationMethods($this->className);

        if (isset($migrationMethods->from[$otherVersion])) {
            $action = $migrationMethods->from[$otherVersion];
            return $action->run($otherObject, $this->options);
        }

        return null;
    }
}