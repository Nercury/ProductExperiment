<?php
/**
 * @author nerijus
 */
namespace Evispa\Component\MultipartResource\Config;

use Evispa\Component\MultipartResource\Crud\ExternalCrudInterface;
use Evispa\Component\MultipartResource\Crud\InternalCrudInterface;

/**
 * Description of ResourcePartConfig
 */
class ResourcePartConfig
{
    /**
     * Action identifier.
     *
     * Example: "create"
     *
     * @var string
     */
    private $id;

    /**
     * Internal CRUD manager for object.
     *
     * @var InternalCrudInterface
     */
    private $internalCrud = null;

    /**
     * External CRUD manager for object.
     *
     * @var ExternalCrudInterface
     */
    private $externalCrud = null;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Get resource part identifier.
     *
     * @return type
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get internal CRUD interface.
     *
     * @return InternalCrudInterface
     */
    public function getInternalCrud()
    {
        return $this->internalCrud;
    }

    /**
     * Set internal CRUD interface.
     *
     * @param InternalCrudInterface $internalCrud Internal CRUD.
     *
     * @return ResourcePartConfig
     */
    public function setInternalCrud(InternalCrudInterface $internalCrud)
    {
        $this->internalCrud = $internalCrud;
        return $this;
    }

    /**
     * Get external CRUD interface.
     *
     * @return ExternalCrudInterface
     */
    public function getExternalCrud()
    {
        return $this->externalCrud;
    }

    /**
     * Set external CRUD interface.
     *
     * @param ExternalCrudInterface $externalCrud External CRUD.
     *
     * @return ResourcePartConfig
     */
    public function setExternalCrud(ExternalCrudInterface $externalCrud)
    {
        $this->externalCrud = $externalCrud;
        return $this;
    }
}
