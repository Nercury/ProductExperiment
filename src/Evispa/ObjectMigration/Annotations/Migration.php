<?php
/**
 * @author nerijus
 */

namespace Evispa\ObjectMigration\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Migration
{
    public function __construct(array $data)
    {
        if ((!isset($data['from']) && !isset($data['to'])) || (isset($data['from']) && isset($data['to']))) {
            throw new \Symfony\Component\Form\Exception\LogicException(
                'Annotation "Migration" requires either "from" or "to" parameters.'
            );
        }
        $this->from = isset($data['from']) ? $data['from'] : null;
        $this->to = isset($data['to']) ? $data['to'] : null;
    }

    /**
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $to;
}