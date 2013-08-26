<?php

namespace Evispa\Component\MultipartResource\Storage;

/**
 * Implement this interface to load and save your resource entities.
 *
 * @author nerijus
 */
interface ResourceStorageInterface extends \Doctrine\Common\Persistence\ObjectManager
{
    /**
     * Create a new db object.
     *
     * @return ResourceObjectInterface
     */
    public function create();

    /**
     * Find a database object based on its identifier.
     *
     * @return ResourceObjectInterface
     */
    public function findOne($id);

    /**
     * Find multiple database objects based on their identifiers.
     *
     * @return ResourceObjectInterface[]
     */
    public function find(array $ids);

    /**
     * Remove object from db.
     *
     * @param ResourceObjectInterface $object Object to remove.
     */
    public function remove(ResourceRefInterface $object);

    /**
     * Mark a newly created object reference for persisting.
     *
     * @param ResourceObjectInterface $object Object to add.
     */
    public function persist(ResourceRefInterface $object);

    /**
     * Flush changes made to object references.
     */
    public function flush();
}