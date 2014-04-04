<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('query_builder'); //change to your package location

class EventImagesListModel extends QueryBuilderModel {
	/**
	 * Creates base Event Image List database query items
	 */
    public function __construct() {
        parent::__construct();
        $this->fields = array(
            'f.fID',
            'f.uID',
            'fv.fvTitle',
            'fv.fvDescription',
            'u.uName as fuName',//File user name (other user names happen in other queries)
            'ei.eiID',
            'ei.Points',
            'ei.eccID',
            'ecc.ecID',
            'g.gName',
            'ec.CategoryName',
            'ecc.eID',
            'e.EventName',
            'e.EventDate',
            'e.PublishDate',
            'ia.aID',
            'a.AwardOrder',
            'a.AwardName');
        $this->setTable('Files f');
        $this->joins = array(
            'LEFT JOIN FileVersions fv ON f.fID = fv.fID and fvID=(SELECT MAX(fvID) from FileVersions fv where f.fID=fv.fID)',//Gets the most recent FileVersion for every fID
            'LEFT JOIN Users u on u.uID = f.uID',
            'LEFT JOIN ClientEventImage ei on f.fID=ei.fID',
            'LEFT JOIN ClientEventCategories ecc on ei.eccID = ecc.id',
            'LEFT JOIN ClientEventCategory ec on ecc.ecID=ec.id',
            'LEFT JOIN ClientEvent e on ecc.eID=e.id',
            'LEFT JOIN ClientImageAwards ia on ei.id=ia.eiID',
            'LEFT JOIN ClientCompetitionAwards a on ia.aID=a.id',
            'LEFT JOIN Groups g on ecc.gID=g.gID');
		//Add attributes for a Image Creator first and last name
        $this->addAttributeField('first_name', 'first_name', 'fnav', 'text', 'User', 'u');
        $this->addAttributeField('last_name', 'last_name', 'lnav', 'text', 'User', 'u');
    }

	/**
	 * Filter by a specific Event
	 * @param int $eID
	 */
    public function filterByEvent($eID) {
        $this->addFilter('e.id', $eID);
    }
	
	/**
	 * Filter by Category
	 * @param int $ecID Category ID
	 */
    public function filterByImageCategory($ecID) {
        $this->addFilter('ec.id', $ecID);
    }
	
	/**
	 * Filter By a Specific Category in an Event
	 * @param int $eccID Event Category ID
	 */
    public function filterByEventImageCategory($eccID) {
        $this->addFilter('ecc.id', $eccID);
    }
	
	/**
	 * Filter by User ID
	 * @param int $uID User ID
	 */
    public function filterByUser($uID) {
        $this->addFilter('f.uID', $uID);
    }
	
	/**
	 * Filter by specific award
	 * @param int $aID Award ID
	 */
    public function filterByAward($aID) {
        $this->addFilter('ia.aID', $aID);
    }
	
	/**
	 * Search by whether image has won any award
	 */
    public function filterByAwardWinner() {
        $this->addFilter('ia.aID', '0', '>');
    }
	
	/**
	 * Search by specific File ID
	 * @param type $fID
	 */
    public function filterByfID($fID) {
        $this->addFilter('f.fID', $fID);
    }
	
	/**
	 *  Search by specific Event Image (FileIds can be duplicated in different events, Event Image IDs are unique instances
	 * @param int $eiID Event Image ID
	 */
    public function filterByeiID($eiID) {
        $this->addFilter('ei.eiID', $eiID);
    }
	
	/**
	 * Filters results based on text in the description and title fields (requires index on both fields at once)
	 * @param string $text Search String
	 */
    public function filterByText($text) {
        $this->addTextFilter(array('fv.fvTitle', 'fv.fvDescription'), $text);
    }

	/**
	 * Helper function to get all array items with C5 image object
	 * @return array Image array with Images included
	 */
    public function getWithImage() {
        $results = $this->get();
        foreach ($results as $key => $array) {
            $results[$key]['image'] = File::getByID($array['fID']);
        }
        return $results;
    }
}

?>
