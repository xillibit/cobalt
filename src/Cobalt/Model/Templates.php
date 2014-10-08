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

use Cobalt\Table\TemplateDataTable;
use Cobalt\Table\TemplatesTable;
use Joomla\Registry\Registry;

// no direct access
defined( '_CEXEC' ) or die( 'Restricted access' );

class Templates extends DefaultModel
{
    public $_view = "templates";

    public function store()
    {
        //Load Tables
        $row = $this->getTable('Templates');
        $data = $this->app->input->post->getArray();

        //date generation
        $date = date('Y-m-d H:i:s');
        if ( !array_key_exists('id',$data) ) {
            $data['created'] = $date;
        }
        $data['modified'] = $date;

        //assign default
        //TODO make this a function that updates the database table so there is only ONE default
        $data['default'] = ( array_key_exists('default',$data) AND $data['default'] == 'on' ) ? 1 : 0;

        //generate custom items for template
        $items = array();
        for ( $i=0; $i<count($data['items']); $i++ ) {
            $id   = $data['items'][$i];
            $name = $data['names'][$i];
            $day  = $data['days'][$i];
            $type = $data['types'][$i];
            $items[] = array(   'name'  =>  $name,
                                'id'    =>  $id,
                                'day'   =>  $day,
                                'type'  =>  $type   );
        }
        unset($data['items']);
        unset($data['names']);
        unset($data['days']);
        unset($data['types']);

        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($this->db->getErrorMsg());

            return false;
        }

        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($this->db->getErrorMsg());

            return false;
        }

        // Store the web link table to the database
        if (!$row->store()) {
            $this->setError($this->db->getErrorMsg());

            return false;
        }

        //get newly inserted template id
        if ( !array_key_exists('id',$data) ) {
            $template_id = $this->db->insertid();
        } else {
            $template_id = $data['id'];
        }

        //loop through template events and bind the tables to update the database
        //TODO remove ids that are no longer used associated with the template
        for ( $i=0; $i<count($items); $i++ ) {
	        $temp_table = $this->getTable('TemplateData');
            $item = $items[$i];
            $item['template_id'] = $template_id;
            if ( !array_key_exists('id',$item) AND $item['id'] == null ) {
                $data['created'] = $date;
            }
            $data['modified'] = $date;
	        try
	   	    {
	   		    $temp_table->save($item);
	   	    }
	   	    catch (\Exception $exception)
	   	    {
	   		    $this->app->enqueueMessage($exception->getMessage(), 'error');

	   		    return false;
	   	    }
        }

        return true;
    }

    public function _buildQuery()
    {
        $query = $this->db->getQuery(true);

        //query
        $query->select("t.*");
        $query->from("#__templates AS t");

        return $query;

    }

    /**
     * Get list of templates
     * @param  int   $id specific search id
     * @return mixed $results results
     */
    public function getTemplates()
    {
        //database
        $query = $this->_buildQuery();

        //sort
        $query->order($this->getState('Templates.filter_order') . ' ' . $this->getState('Templates.filter_order_Dir'));

        //return results
        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        //return data
        return $results;

    }

    public function getTemplate($id=null)
    {
        $id = $id ? $id : $this->id;

        if ($id > 0) {

            //database
            $query = $this->_buildQuery();

            //sort
            $query->order($this->getState('Templates.filter_order') . ' ' . $this->getState('Templates.filter_order_Dir'));
            $query->where("t.id=$id");

            //return results
            $this->db->setQuery($query);
            $result = $this->db->loadAssoc();

            //left join essential data if we are searching for a specific template
            $query = $this->db->getQuery(true);
            $query->select("t.*");
            $query->from("#__template_data AS t");
            $query->where("t.template_id=$id");
            $this->db->setQuery($query);
            $result['data'] = $this->db->loadAssocList();

            //return data
            return $result;

        } else {
            return (array) $this->getTable('Templates');

        }

    }

    public function populateState()
    {
        //get states
        $filter_order = $this->app->getUserStateFromRequest('Templates.filter_order','filter_order','t.name');
        $filter_order_Dir = $this->app->getUserStateFromRequest('Templates.filter_order_Dir','filter_order_Dir','asc');

        $state = new Registry;

        //set states
        $state->set('Templates.filter_order', $filter_order);
        $state->set('Templates.filter_order_Dir',$filter_order_Dir);

        $this->setState($state);
    }

    public function remove($id)
    {
        //get dbo
        $query = $this->db->getQuery(true);

        //delete id
        $query->delete('#__templates')->where('id = '.$id);
        $this->db->setQuery($query);
        $this->db->execute();
    }

}
