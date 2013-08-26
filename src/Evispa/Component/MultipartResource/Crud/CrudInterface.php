<?php
/**
 * @author nerijus
 */

namespace Evispa\Component\MultipartResource\Crud;

/**
 * CRUD requires the object to store the data.
 *
 * It knows about resource object and requires it to retrieve and save information.
 */
interface CrudInterface
{
    /**
     * Get data to create a new object.
     * 
     * @return mixed View model data.
     */
    public function getNewData();

    /**
     * Get partial data for view and editing from the object data.
     *
     * @param mixed $object Database object.
     *
     * @return mixed View model data.
     */
    public function get($object);

    /**
     * Store view model data into the database object.
     *
     * @param mixed $object Database object.
     * @param mixed $data View model data.
     */
    public function update($object, $data);

    /**
     * Executed before object deletion.
     *
     * @param mixed $object Database object.
     */
    public function delete($object);
}
