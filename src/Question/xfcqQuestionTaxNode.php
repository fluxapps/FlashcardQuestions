<?php

namespace srag\Plugins\FlashcardQuestions\Question;


use ActiveRecord;

/**
 * Class xfcqQuestionTaxNode
 * @package srag\Plugins\FlashcardQuestions\Question
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqQuestionTaxNode extends ActiveRecord {

    const TABLE_NAME = 'xfcq_question_tax_node';

    /**
     * @return string
     */
    public function getConnectorContainerName() {
        return self::TABLE_NAME;
    }

    /**
     * @param $qst_id int
     * @param $node_ids array
     */
    public static function setNodesForQuestion($qst_id, $node_ids) {
        self::resetNodesForQuestion($qst_id);
        foreach ($node_ids as $tax_id => $nodes) {
            foreach ($nodes as $node_id) {
                $self = new self();
                $self->setQstId($qst_id);
                $self->setTaxId($tax_id);
                $self->setTaxNodeId($node_id);
                $self->create();
            }
        }
    }

    public static function resetNodesForQuestion($qst_id) {
        /** @var static $item */
        foreach (self::where(['qst_id' => $qst_id])->get() as $item) {
            $item->delete();
        }
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
     * @db_length           8
     * @db_is_notnull       true
     */
    protected $qst_id;

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_notnull       true
     */
    protected $tax_id;

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_notnull       true
     */
    protected $tax_node_id;

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
    public function getQstId() {
        return $this->qst_id;
    }

    /**
     * @param int $qst_id
     */
    public function setQstId($qst_id) {
        $this->qst_id = $qst_id;
    }

    /**
     * @return int
     */
    public function getTaxId() {
        return $this->tax_id;
    }

    /**
     * @param int $tax_id
     */
    public function setTaxId($tax_id) {
        $this->tax_id = $tax_id;
    }

    /**
     * @return int
     */
    public function getTaxNodeId() {
        return $this->tax_node_id;
    }

    /**
     * @param int $tax_node_id
     */
    public function setTaxNodeId($tax_node_id) {
        $this->tax_node_id = $tax_node_id;
    }

}