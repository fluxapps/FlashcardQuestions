<?php

namespace srag\Plugins\FlashcardQuestions\Question;

use ActiveRecord;
use arConnector;
use ilObjTaxonomy;
use ilTaxonomyTree;
use \xfcqPageObject;
use ilFlashcardQuestionsPlugin;
use srag\DIC\FlashcardQuestions\DICTrait;

/**
 * Class xfcqQuestion
 *
 * @package srag\Plugins\FlashcardQuestions\Question
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqQuestion extends ActiveRecord {

    const TABLE_NAME = 'xfcq_question';

    /**
     * @return string
     */
    public function getConnectorContainerName() {
        return self::TABLE_NAME;
    }

    public function __construct($primary_key = 0, arConnector $connector = null) {
        parent::__construct($primary_key, $connector);
        if ($primary_key != 0) {
            $this->loadTaxNodes();
        }
    }

    /**
     *
     */
    public function create($omit_creating_page_object = false) {
        if (!$omit_creating_page_object) {
            $id_qst = $this->getNextFreePageId();
            $id_ans = $id_qst + 1;

            $this->setPageIdQuestion($id_qst);
            $this->setPageIdAnswer($id_ans);
        }

        parent::create();

        if (!$omit_creating_page_object) {
            // create page object for question
            $page_obj = new xfcqPageObject();
            $page_obj->setId($id_qst);
            $page_obj->setParentId($this->getObjId());
            $page_obj->create();
            // create page object for answer
            $page_obj = new xfcqPageObject();
            $page_obj->setId($id_ans);
            $page_obj->setParentId($this->getObjId());
            $page_obj->create();
        }

        if (!empty($this->tax_nodes)) {
            xfcqQuestionTaxNode::setNodesForQuestion($this->getId(), $this->getTaxNodes());
        }
    }

    /**
     *
     */
    public function update() {
        parent::update();
        xfcqQuestionTaxNode::setNodesForQuestion($this->getId(), $this->getTaxNodes());
    }

    /**
     *
     */
    public function delete() {
        $xfcqPageObject = new xfcqPageObject($this->getPageIdQuestion());
        $xfcqPageObject->delete();
        $xfcqPageObject = new xfcqPageObject($this->getPageIdAnswer());
        $xfcqPageObject->delete();
        parent::delete();
    }


    /**
     * @return int
     */
    public function getNextFreePageId() {
        global $DIC;
        $query = $DIC->database()->query("select max(page_id) id from page_object where parent_type = 'xfcq'");
        $set = $DIC->database()->fetchAssoc($query);
        return $set['id'] + 1;
    }
    /**
     * @var int
     *
     * @db_has_field          true
     * @db_is_unique          true
     * @db_is_primary         true
     * @db_fieldtype          integer
     * @db_length             8
     * @db_sequence           true
     */
    protected $id = 0;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_is_notnull       true
     * @db_length           8
     */
    protected $obj_id;
    /**
     * @var bool
     *
     * @db_has_field           true
     * @db_fieldtype           integer
     * @db_length              1
     */
    protected $active = 0;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $page_id_qst;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $page_id_ans;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $origin_glo_id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $origin_term_id;


    /**
     * @var array
     */
    protected $tax_nodes = array();

    /**
     * @return int
     */
    public function getPageIdQuestion() {
        return $this->page_id_qst;
    }

    /**
     * @param int $page_id_qst
     */
    public function setPageIdQuestion($page_id_qst) {
        $this->page_id_qst = $page_id_qst;
    }

    /**
     * @return int
     */
    public function getPageIdAnswer() {
        return $this->page_id_ans;
    }

    /**
     * @param int $page_id_ans
     */
    public function setPageIdAnswer($page_id_ans) {
        $this->page_id_ans = $page_id_ans;
    }
    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getObjId() {
        return $this->obj_id;
    }

    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id) {
        $this->obj_id = $obj_id;
    }

    /**
     * @return bool
     */
    public function isActive() {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getOriginGloId() {
        return $this->origin_glo_id;
    }

    /**
     * @param int $origin_glo_id
     */
    public function setOriginGloId($origin_glo_id) {
        $this->origin_glo_id = $origin_glo_id;
    }

    /**
     * @return int
     */
    public function getOriginTermId() {
        return $this->origin_term_id;
    }

    /**
     * @param int $origin_term_id
     */
    public function setOriginTermId($origin_term_id) {
        $this->origin_term_id = $origin_term_id;
    }

    /**
     * @return array
     */
    public function getTaxNodes() {
        if (empty($this->tax_nodes)) {
            $this->loadTaxNodes();
        }
        return $this->tax_nodes;
    }

    /**
     * @param array $tax_nodes
     */
    public function setTaxNodes(array $tax_nodes) {
        $this->tax_nodes = $tax_nodes;
    }

    /**
     * @param Int $tax_id
     * @return array
     */
    public function getTaxNodesForTaxId(Int $tax_id) {
        if (isset($this->tax_nodes[$tax_id])) {
            return $this->tax_nodes[$tax_id];
        }
        return array();
    }

    /**
     * @param array $tax_nodes
     * @param Int $tax_id
     */
    public function setTaxNodesForTaxId(array $tax_nodes, Int $tax_id) {
        $this->tax_nodes[$tax_id] = $tax_nodes;
    }

    /**
     * @param $tax_title
     * @return array|mixed
     */
    public function getTaxNodesForTaxTitle($tax_title) {
        foreach ($this->getTaxNodes() as $tax_id => $nodes) {
            if (ilObjTaxonomy::_lookupTitle($tax_id) == $tax_title) {
                return $nodes;
            }
        }
        return array();
    }

    /**
     *
     */
    public function loadTaxNodes() {
        $tax_nodes = [];
        /** @var xfcqQuestionTaxNode $qst_tax_node */
        foreach (xfcqQuestionTaxNode::where(['qst_id' => $this->getId()])->get() as $qst_tax_node) {
            $tax_nodes[$qst_tax_node->getTaxId()][] = $qst_tax_node->getTaxNodeId();
        }
        $this->setTaxNodes($tax_nodes);
    }
}