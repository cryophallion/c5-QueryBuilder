<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('query_builder'); //change to your package location

class EventListModel extends QueryBuilderModel {
    public function __construct() {
        parent::__construct();
        $this->fields = array(
			'e.*',
			'et.EventTypeName',
			'et.Color',
			'et.Competition',
			'et.bccEvent',
			'ROUND(AVG(er.rating),2) as rScore',
			'COUNT(er.rating) as rCount',
			'el.LocationName',
			'el.LocationAddress1',
			'el.LocationAddress2',
			'el.LocationCity',
			'el.LocationState',
			'el.LocationZip',
			'el.LocationURL',
			'el.LocationMapURL',
			'el.LocationfID',
			'ou.uEmail AS ouEmail');
        $this->joins = array(
			'LEFT JOIN ClientEventType et on e.etID=et.id',
			'LEFT OUTER JOIN ClientEventRatings er on e.id=er.eID',
			'LEFT JOIN ClientEventLocation el on e.lID=el.id',
			'LEFT JOIN Users as ou on ou.uID=e.oUID');
        $this->setTable('ClientEvent e');
        $this->addAttributeField('first_name', 'jfirst_name', 'jfnav', 'text', 'User', 'e','jUID');
        $this->addAttributeField('last_name', 'jlast_name', 'jlnav', 'text', 'User', 'e','jUID');
        $this->addField('CONCAT(jfnav.value," " , jlnav.value) as judge_full_name');
        $this->addAttributeField('first_name', 'ofirst_name', 'ofnav', 'text', 'User', 'e','oUID');
        $this->addAttributeField('last_name', 'olast_name', 'olnav', 'text', 'User', 'e','oUID');
        $this->addField('CONCAT(ofnav.value," " , olnav.value) as organizer_full_name');
        $this->addAttributeField('first_name', 'cfirst_name', 'cfnav', 'text', 'User', 'e','cUID');
        $this->addAttributeField('last_name', 'clast_name', 'clnav', 'text', 'User', 'e','cUID');
        $this->addField('CONCAT(cfnav.value," " , clnav.value) as chair_full_name');
        $this->addGroup('e.id');
    }
    
    public function sortASC() {
        $this->addOrder('e.EventDate', 'ASC');
    }
    
    public function sortDESC() {
        $this->addOrder('e.EventDate', 'DESC');
    }
}
?>
