<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('query_builder','query_builder'); //change to your package location

class EventListModel extends QueryBuilderModel {
	/**
	 * Creates base Event List database query items
	 */
    public function __construct() {
        parent::__construct();
		//Returned Fields
        $this->fields = array(
			'e.*',
			'ROUND(AVG(er.rating),2) as rScore',
			'COUNT(er.rating) as rCount',
			'el.*', //All location info
			'ou.uEmail AS ouEmail');//Organizer Email Address for contacting
		//Join Tables
        $this->joins = array(
			'LEFT OUTER JOIN ClientEventRatings er on e.eID=er.eID',
			'LEFT JOIN ClientEventLocation el on e.lID=el.lID',
			'LEFT JOIN Users as ou on ou.uID=e.oUID');
		//Set base Table Name
        $this->setTable('ClientEvent e');
		//Add Event Organizer first and last name
        $this->addAttributeField('first_name', 'ofirst_name', 'ofnav', 'text', 'User', 'e','oUID');
        $this->addAttributeField('last_name', 'olast_name', 'olnav', 'text', 'User', 'e','oUID');
        $this->addField('CONCAT(ofnav.value," " , olnav.value) as organizer_full_name');
		//Add Event Chairperson first and last name
        $this->addAttributeField('first_name', 'cfirst_name', 'cfnav', 'text', 'User', 'e','cUID');
        $this->addAttributeField('last_name', 'clast_name', 'clnav', 'text', 'User', 'e','cUID');
        $this->addField('CONCAT(cfnav.value," " , clnav.value) as chair_full_name');
		//Group by Event
        $this->addGroup('e.eID');
    }
    
	/**
	 * Sort by Event Date Ascending
	 */
    public function sortASC() {
        $this->addOrder('e.EventDate', 'ASC');
    }
    
	/**
	 * Sort by Event Date Descending
	 */
    public function sortDESC() {
        $this->addOrder('e.EventDate', 'DESC');
    }
}
?>
