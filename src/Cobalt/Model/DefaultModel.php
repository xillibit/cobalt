<?php
/*------------------------------------------------------------------------
# Cobalt
# ------------------------------------------------------------------------
# @author Cobalt
# @copyright Copyright (C) 2012 cobaltcrm.org All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://www.cobaltcrm.org
-------------------------------------------------------------------------*/

namespace Cobalt\Model;

use Cobalt\Helper\RouteHelper;
use Cobalt\Container;
use Cobalt\Pagination;
use Joomla\Model\AbstractDatabaseModel;
use Joomla\Database\DatabaseDriver;

// no direct access
defined( '_CEXEC' ) or die( 'Restricted access' );

class DefaultModel extends AbstractDatabaseModel
{
    public $id;

    protected $__state_set;
    protected $_total;
    protected $_pagination;
    protected $db;
    protected $app;

    public function __construct(DatabaseDriver $db = null)
    {
        if (is_null($db))
        {
            $db = Container::fetch('db');
        }

        $this->db = $db;

        $this->app = Container::fetch('app');

        parent::__construct($db);

        $ids = $this->app->input->get("cids", null, 'array');

        $id = $this->app->input->getInt("id");

        if ($id && $id > 0)
        {
            $this->id = $id;
        }
        elseif (count($ids) == 1)
        {
            $this->id = $ids[0];
        }
        else
        {
            $this->id = $ids;
        }

    }

    /**
     * Modifies a property of the object, creating it if it does not already exist.
     *
     * @param string $property The name of the property.
     * @param mixed  $value    The value of the property to set.
     *
     * @return mixed Previous value of the property.
     *
     * @since   11.1
     */
    public function set($property, $value = null)
    {
        $previous = isset($this->$property) ? $this->$property : null;
        $this->$property = $value;

        return $previous;
    }

    /**
     * returns a property of the object, even if it's protected.
     *
     * @param string $property The name of the property.
     * @param mixed  $default  Default value if the property doesn't exist.
     *
     * @return mixed The value of the property.
     */
    public function get($property, $default = null)
    {
        if (isset($this->$property))
        {
            return $this->$property;
        }

        return $default;
    }

    /**
     * Gets an array of objects from the results of database query.
     *
     * @param string  $query      The query.
     * @param integer $limitstart Offset.
     * @param integer $limit      The number of records.
     *
     * @return array An array of results.
     *
     * @since   11.1
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $this->db->setQuery($query, $limitstart, $limit);

        return $this->db->loadObjectList();
    }

    /**
     * Returns a record count for the query
     *
     * @param string $query The query.
     *
     * @return integer Number of rows for query
     *
     * @since   11.1
     */
    protected function _getListCount($query)
    {
        $this->db->setQuery($query)->execute();

        return $this->db->getNumRows();
    }

     /* Method to get model state variables
     *
     * @param string $property Optional parameter name
     * @param mixed  $default  Optional default value
     *
     * @return object The property where specified, the state object where omitted
     *
     * @since   11.1
     */
    public function getState($property = null, $default = null)
    {
        if (!$this->__state_set) {
            // Protected method to auto-populate the model state.
            $this->populateState();

            // Set the model state set flag to true.
            $this->__state_set = true;
        }

        return $property === null ? $this->state : $this->state->get($property, $default);
    }

    /**
    * Get total number of rows for pagination
    */
    public function getTotal()
    {
      if ( empty ( $this->_total ) ) {
          $query = $this->_buildQuery();
          $this->_total = $this->_getListCount($query);
      }

      return $this->_total;
   }

    /**
     * Generate pagination
     */
    public function getPagination()
    {
      // Lets load the content if it doesn't already exist
      if (empty($this->_pagination)) {
         $this->_pagination = new Pagination( $this->getTotal(), $this->getState($this->_view.'_limitstart'), $this->getState($this->_view.'_limit'),null,RouteHelper::_('index.php?view='.$this->_view.'&layout='.$this->_layout));
      }

      return $this->_pagination;
    }

    /**
     * Set the object properties based on a named array/hash.
     *
     * @param   mixed  $properties  Either an associative array or another object.
     *
     * @return  boolean
     */
    public function setProperties($properties)
    {
        if (is_array($properties) || is_object($properties))
        {
            foreach ((array) $properties as $k => $v)
            {
                // Use the set function which might be overridden.
                $this->set($k, $v);
            }
            return true;
        }

        return false;
    }

    /**
     * Method transforms items to the format jQuery dataTables needs
     * 
     * @param   array of object of items from the database
     * @return  array in format dataTables requires
     */
    public function getDataTableItems($items)
    {
        $tableItems = array();
        $columns = $this->getDataTableColumns();

        foreach ($items as $item)
        {
            $tableItem = new \stdClass;

            foreach ($columns as $column)
            {
                $tableItem->{$column['data']} = $this->getDataTableFieldTemplate($column['data'], (object) $item);
            }

            $tableItems[] = $tableItem;
        }

        return $tableItems;
    }

}
