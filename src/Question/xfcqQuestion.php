<?php
namespace srag\Plugins\FlashcardQuestions\Question;

use ActiveRecord;
/**
 * Class xfcqQuestion
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqQuestion extends ActiveRecord {

    const TABLE_NAME = 'xfcq_question';

    /**
     * @return string
     */
    public function getConnectorContainerName() {
        return self::TABLE_NAME;
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
     */
    protected $obj_id;
    /**
     * @var string
     *
     * @db_has_field           true
     * @db_is_notnull          true
     * @db_fieldtype           text
     * @db_length              2048
     */
    protected $title;
    /**
     * @var bool
     *
     * @db_has_field           true
     * @db_fieldtype           integer
     * @db_length              1
     */
    protected $active = 0;
    /**
     * @var string
     *
     * @db_has_field           true
     * @db_is_notnull          true
     * @db_fieldtype           text
     * @db_length              4000
     */
    protected $tax_nodes = array();

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id) {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getObjId(): int {
        return $this->obj_id;
    }

    /**
     * @param int $obj_id
     */
    public function setObjId(int $obj_id) {
        $this->obj_id = $obj_id;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function isActive(): bool {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active) {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getTaxNodes(): string {
        return $this->tax_nodes;
    }

    /**
     * @param string $tax_nodes
     */
    public function setTaxNodes(string $tax_nodes) {
        $this->tax_nodes = $tax_nodes;
    }
}