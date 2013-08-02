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

use JFactory;
use Cobalt\Helper\DateHelper;
use Cobalt\Helper\UsersHelper;
use Cobalt\Helper\ActivityHelper;
use Cobalt\Helper\CobaltHelper;
use Cobalt\Table\ConversationTable;

// no direct access
defined( '_CEXEC' ) or die( 'Restricted access' );

class Conversation extends DefaultModel
{
    public $published = 1;
    public $deal_id = null;

    /**
     * Method to store a record
     *
     * @return boolean True on success
     */
    public function store()
    {

        $app = JFactory::getApplication();

        //Load Tables
        $row = new ConversationTable;
        $oldRow = new ConversationTable;
        $data = $app->input->getRequest( 'post' );

        //date generation
        $date = DateHelper::formatDBDate(date('Y-m-d H:i:s'));

        if ( !array_key_exists('id',$data) ) {
            $data['created'] = $date;
            $status = "created";
        } else {
            $row->load($data['id']);
            $oldRow->load($data['id']);
            $status = "updated";
        }

        $data['modified'] = $date;
        $data['author'] = UsersHelper::getUserId();

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

        $id = array_key_exists('id',$data) ? $data['id'] : $this->db->insertId();

        ActivityHelper::saveActivity($oldRow, $row,'conversation', $status);

        return $id;
    }

    public function getConversations()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select("c.*, u.first_name as owner_first_name, u.last_name as owner_last_name, author.email");
        $query->from("#__conversations AS c");
        $query->leftJoin("#__users as u on u.id = c.author");
        $query->leftJoin("#__users AS author ON author.id = u.id");
        $query->where("c.deal_id=".$this->deal_id);
        $query->where("c.published>0");
        $query->order("c.modified DESC");
        //grab results
        $db->setQuery($query);
        $conversations = $db->loadAssocList();

        for ($i=0;$i<count($conversations);$i++) {
            $conversations[$i]['owner_avatar'] = CobaltHelper::getGravatar($conversations[$i]['email']);
        }

        return $conversations;
    }

    /*
     * Method to access conversations
     *
     * @return array
     */
    public function getConversation($id)
    {
        //grab db
        $db = JFactory::getDBO();

        //initialize query
        $query = $db->getQuery(true);

        //gen query string
        $query->select("c.*, u.first_name as owner_first_name, u.last_name as owner_last_name,author.email");
        $query->from("#__conversations as c");
        $query->where("c.id=".$id);
        $query->where("c.published=".$this->published);
        $query->leftJoin("#__users AS u ON u.id = c.author");
        $query->leftJoin("#__users AS author on author.id=u.id");

        //load results
        $db->setQuery($query);
        $results = $db->loadAssocList();

        //clean results
        if ( count($results) > 0 ) {
            foreach ($results as $key => $convo) {
                $results[$key]['created_formatted'] = DateHelper::formatDate($convo['created']);
                $results[$key]['owner_avatar'] = CobaltHelper::getGravatar($convo['email']);
            }
        }

        //return results
        return $results;
    }

}
