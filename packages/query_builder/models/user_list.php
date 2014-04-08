<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('query_builder','query_builder'); //change to your package location

class UserListModel extends BasicQueryModel {
	
	/**
	 * Creates base User List database query items
	 */
    public function __construct() {
        parent::__construct();
        $this->fields = array('u.*');
        $this->setTable('Users u');
		//Adds calls to get the First and Last name attributes for each user
        $this->addAttributeField('first_name', 'first_name', 'fnav', 'text', 'User', 'u');
        $this->addAttributeField('last_name', 'last_name', 'lnav', 'text', 'User', 'u');
        $this->addAttributeField('active', 'active', 'actav', 'boolean', 'User', 'u');
        $this->addField('CONCAT(fnav.value," " , lnav.value) as full_name');
		//Order by first name
        $this->addOrder('fnav.value');
    }
    
	/**
	 * Filter by C5 group (package defines these group names, gID will be different on each install)
	 * @param string $groupName Group Name
	 */
    public function filterByGroup($groupName){
        $group_sql = 'Select gID from Groups where Groups.gName=?';
        $group_vals = array($groupName);
        $group_id = $this->db->GetRow($group_sql,$group_vals);
        $this->addField('ug.gID');
        $this->addJoin('LEFT JOIN UserGroups ug on u.uID=ug.uID');
        $this->addFilter('ug.gID', $group_id['gID']);
    }
}
?>
