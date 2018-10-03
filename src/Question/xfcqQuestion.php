<?php
namespace srag\Plugins\FlashcardQuestions\Question;

use ActiveRecord;
use \xfcqPageObject;
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
     * @param $field_name
     * @return mixed|null|string
     */
    public function sleep($field_name) {
        switch ($field_name) {
            case 'tax_nodes':
                return implode(',', $this->tax_nodes);
            default:
                return null;
        }
    }

    /**
     * @param $field_name
     * @param $field_value
     * @return array|mixed|null
     */
    public function wakeUp($field_name, $field_value) {
        switch ($field_name) {
            case 'tax_nodes':
                return explode(',', $field_value);
            default:
                return null;
        }
    }

    /**
     *
     */
    public function create() {
        $id_qst = $this->getNextFreePageId();
        $id_ans = $id_qst + 1;

        $this->setPageIdQuestion($id_qst);
        $this->setPageIdAnswer($id_ans);

        parent::create();

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
     * @var array
     *
     * @db_has_field           true
     * @db_fieldtype           text
     * @db_length              4000
     */
    protected $tax_nodes = array();
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
     * @return int
     */
    public function getPageIdQuestion(): int {
        return $this->page_id_qst;
    }

    /**
     * @param int $page_id_qst
     */
    public function setPageIdQuestion(int $page_id_qst) {
        $this->page_id_qst = $page_id_qst;
    }

    /**
     * @return int
     */
    public function getPageIdAnswer(): int {
        return $this->page_id_ans;
    }

    /**
     * @param int $page_id_ans
     */
    public function setPageIdAnswer(int $page_id_ans) {
        $this->page_id_ans = $page_id_ans;
    }
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
     * @return array
     */
    public function getTaxNodes(): array {
        return $this->tax_nodes;
    }

    /**
     * @param array $tax_nodes
     */
    public function setTaxNodes(array $tax_nodes) {
        $this->tax_nodes = $tax_nodes;
    }
}