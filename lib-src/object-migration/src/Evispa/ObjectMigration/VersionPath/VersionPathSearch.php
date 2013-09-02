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
use Fhaculty\Graph\Exception\OutOfBoundsException;
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

    /**
     * Create edges between versions
     *
     * @param Graph $graph
     * @param       $className
     */
    private function createEdges(Graph $graph, $className)
    {
        $migrationsAnnotations = $this->reader->getClassMigrationMethodInfo($className);

        $parentVertex = $graph->hasVertex($className) ? $graph->getVertex($className) : $graph->createVertex(
            $className
        );

        foreach ($migrationsAnnotations as $migrationsAnnotation) {
            if ($migrationsAnnotation->annotation->from) {
                $fromClass = $migrationsAnnotation->annotation->from;
                $fromVertex = $graph->hasVertex($fromClass) ? $graph->getVertex($fromClass) : $graph->createVertex(
                    $fromClass
                );

                $edgeCreated = false;

                if (!$parentVertex->hasEdgeTo($fromVertex)) {
                    $fromVertex->createEdgeTo($parentVertex);
                    $edgeCreated = true;
                }

                if ($edgeCreated) {
                    $this->createEdges($graph, $fromClass);
                }

                $this->annotations[$fromClass . '_' . $className] = $migrationsAnnotation;
            }

            if ($migrationsAnnotation->annotation->to) {
                $toClass = $migrationsAnnotation->annotation->to;
                $fromVertex = $graph->hasVertex($toClass) ? $graph->getVertex($toClass) : $graph->createVertex(
                    $toClass
                );

                $edgeCreated = false;

                if (!$parentVertex->hasEdgeTo($fromVertex)) {
                    $edge = $parentVertex->createEdgeTo($fromVertex);
                    $this->annotations[$this->getEdgeId($edge)] = $migrationsAnnotation;
                    $edgeCreated = true;
                }

                if ($edgeCreated) {
                    $this->createEdges($graph, $toClass);
                }

                $this->annotations[$className . '_' . $toClass] = $migrationsAnnotation;
            }
        }
    }

    /**
     * Get edge id
     *
     * @param \Fhaculty\Graph\Edge\Directed $edge
     *
     * @return string
     */
    private function getEdgeId($edge)
    {
        return $edge->getVertexStart()->getId() . '_' . $edge->getVertexEnd()->getId();
    }

    /**
     * Find shortest path between versions
     *
     * @param string $fromClassName
     * @param string $toClassName
     *
     * @return MethodInfo[]
     */
    public function find($fromClassName, $toClassName)
    {
        $annotations = array();

        $graph = new Graph();

        $this->createEdges($graph, $fromClassName);
        $this->createEdges($graph, $toClassName);

        try {
            $breadFirst = new BreadthFirst($graph->getVertex($fromClassName));
            $edges = $breadFirst->getEdgesTo($graph->getVertex($toClassName));
            /** @var Directed $edge */
            foreach ($edges as $edge) {
                $id = $edge->getVertexStart()->getId() . '_' . $edge->getVertexEnd()->getId();
                $annotations[] = $this->annotations[$id];
            }
        } catch (OutOfBoundsException $e) {

        }

        return $annotations;
    }

}
