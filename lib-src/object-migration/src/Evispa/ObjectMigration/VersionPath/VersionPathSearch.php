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

use Evispa\ObjectMigration\Annotations\Migration;
use Evispa\ObjectMigration\Annotations\Version;
use Evispa\ObjectMigration\Migration\MethodInfo;
use Evispa\ObjectMigration\VersionReader;
use Fhaculty\Graph\Algorithm\ShortestPath\BreadthFirst;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph as Graph;

class VersionPathSearch
{
    /**
     * Version reader.
     *
     * @var VersionReader
     */
    private $reader;

    private $annotations;

    function __construct($reader)
    {
        $this->reader = $reader;
    }

    private function createEdges(Graph $graph, $className)
    {
        $migrationsAnnotations = $this->reader->getClassMigrationAnnotations($className);

        $parentVertex = $graph->hasVertex($className) ? $graph->getVertex($className) : $graph->createVertex(
            $className
        );

        foreach ($migrationsAnnotations as $migrationsAnnotation) {
            if ($migrationsAnnotation->annotation->from) {
                $fromClass = $migrationsAnnotation->annotation->from;
                $id = $fromClass;
                $fromVertex = $graph->hasVertex($id) ? $graph->getVertex($id) : $graph->createVertex($id);

                $edgeCreated = false;

                if (!$parentVertex->hasEdgeTo($fromVertex)) {
                    $fromVertex->createEdgeTo($parentVertex);
                    $edgeCreated = true;
                }

                if ($edgeCreated) {
                    $this->createEdges($graph, $fromClass);
                }

                $this->annotations[$id] = $migrationsAnnotation;
            }

            if ($migrationsAnnotation->annotation->to) {
                $toClass = $migrationsAnnotation->annotation->to;
                $id = $toClass;
                $fromVertex = $graph->hasVertex($id) ? $graph->getVertex($id) : $graph->createVertex($id);

                $edgeCreated = false;

                if (!$parentVertex->hasEdgeTo($fromVertex)) {
                    $parentVertex->createEdgeTo($fromVertex);
                    $edgeCreated = true;
                }

                if ($edgeCreated) {
                    $this->createEdges($graph, $toClass);
                }

                $this->annotations[$id] = $migrationsAnnotation;
            }
        }
    }

    /**
     * @param $fromClassName
     * @param $toClassName
     *
     * @return MethodInfo[]
     */
    public function find($fromClassName, $toClassName)
    {
        $graph = new Graph();

        $this->createEdges($graph, $fromClassName);
        $this->createEdges($graph, $toClassName);

        $breadFirst = new BreadthFirst($graph->getVertex($fromClassName));
        $edges = $breadFirst->getEdgesTo($graph->getVertex($toClassName));

        $annotations = array();

        /** @var Directed $edge */
        foreach ($edges as $edge) {

            //var_dump($edge->getVertexStart()->getId() . " => " . $edge->getVertexEnd()->getId());
        }

        return $annotations;
    }

}
