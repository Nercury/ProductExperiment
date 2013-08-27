<?php

namespace Evispa\Component\MultipartResource\Storage;

/**
 * Wraps an object in interface which can return object identifier.
 *
 * @author nerijus
 */
interface ResourceObjectInterface
{
    /**
     * Get object identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * Get database object.
     *
     * @return mixed
     */
    public function getObject();
}