<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('query_builder'); //change to your package location

class EventImagesListModel extends QueryBuilderModel {

    public function __construct() {
        parent::__construct();
        $this->fields = array(
            'f.fID',
            'f.uID',
            'fv.fvTitle',
            'fv.fvDescription',
            'u.uName as fuName',
            'ei.id as eiID',
            'ei.Points',
            'ei.PopularVote',
            'ei.deleted',
            'ei.deletedReason',
            'ei.eccID',
            'ecc.ecID',
            'esc.SubcategoryName',
            'g.gName',
            'ecc.fsID',
            'ec.CategoryName',
            'ecc.eID',
            'e.EventName',
            'e.EventDate',
            'e.PublishDate',
            'e.etID',
            'et.EventTypeName',
            'ia.aID',
            'a.sort_order',
            'a.AwardName');
        $this->setTable('Files f');
        $this->joins = array(
            'LEFT JOIN FileVersions fv ON f.fID = fv.fID and fvID=(SELECT MAX(fvID) from FileVersions fv where f.fID=fv.fID)',
            'LEFT JOIN Users u on u.uID = f.uID',
            'LEFT JOIN ClientEventImage ei on f.fID=ei.fID',
            'LEFT JOIN ClientEventCategories ecc on ei.eccID = ecc.id',
            'LEFT JOIN ClientEventSubcategory esc on ecc.scID=esc.id',
            'LEFT JOIN ClientEventCategory ec on ecc.ecID=ec.id',
            'LEFT JOIN ClientEvent e on ecc.eID=e.id',
            'LEFT JOIN ClientEventType et on e.etID=et.id',
            'LEFT JOIN ClientImageAwards ia on ei.id=ia.eiID',
            'LEFT JOIN ClientCompetitionAwards a on ia.aID=a.id',
            'LEFT JOIN Groups g on ecc.gID=g.gID');
        $this->addAttributeField('first_name', 'first_name', 'fnav', 'text', 'User', 'u');
        $this->addAttributeField('last_name', 'last_name', 'lnav', 'text', 'User', 'u');
    }

    public function filterByEventType($id) {
        $this->addFilter('et.id', $id);
    }

    public function filterByEvent($id) {
        $this->addFilter('e.id', $id);
    }

    public function filterByImageCategory($id) {
        $this->addFilter('ec.id', $id);
    }

    public function filterByEventImageCategory($id) {
        $this->addFilter('ecc.id', $id);
    }

    public function filterByUser($id) {
        $this->addFilter('f.uID', $id);
    }

    public function filterByAward($id) {
        $this->addFilter('ia.aID', $id);
    }

    public function filterByAwardWinner() {
        $this->addFilter('ia.aID', '0', '>');
    }

    public function filterByfID($fID) {
        $this->addFilter('f.fID', $fID);
    }

    public function filterByeiID($eiID) {
        $this->addFilter('ei.id', $eiID);
    }

    public function filterByText($text) {
        $this->addTextFilter(array('fv.fvTitle', 'fv.fvDescription'), $text);
    }

    public function getWithImage() {
        $results = $this->get();
        foreach ($results as $key => $array) {
            $results[$key]['image'] = File::getByID($array['fID']);
        }
        return $results;
    }
}

?>
