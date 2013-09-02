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

namespace Evispa\ObjectMigration\VersionPath;

use Evispa\ObjectMigration\VersionReader;

class VersionPathSearch
{
    /**
     * Version reader.
     *
     * @var VersionReader
     */
    private $reader;

    function __construct($reader)
    {
        $this->reader = $reader;
    }

    private function getQueue($className)
    {
        $queue = array();

        $migrationsAnnotations = $this->reader->getClassMigrationAnnotations($className);
        foreach ($migrationsAnnotations as $migrationsAnnotation) {
            $className = $migrationsAnnotation->annotation->to;
            $queue[] = $className;
        }

        return $queue;
    }

    public function find($fromClassName, $toClassName)
    {
        $agent = array(
            'path' => array($fromClassName),
            'queue' => $this->getQueue($fromClassName)
        );

        $agents = array($agent);

        $visited = array();

        while (!empty($agents)) {

            $agent = array_pop($agents);

            $visited[] = end($agent['path']);

            if (end($agent['path']) === $toClassName) {
                return $agent['path'];
            }

            if (count($agent['queue']) > 0) {
                foreach($agent['queue'] as $className) {
                    if(!in_array($className, $visited)) {

                        $newAgent = array(
                            'path' => $agent['path'],
                            'queue' => $this->getQueue($className)
                        );

                        array_push($newAgent['path'], $className);

                        array_push($agents, $newAgent);
                    }
                }
            }
        }

        return array();
    }

}
