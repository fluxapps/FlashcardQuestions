<?php

namespace srag\Plugins\FlashcardQuestions\Question;

use srag\DIC\DICTrait;
use ilFlashcardQuestionsPlugin;
use \ilTable2GUI;
use \xfcqContentGUI;
use \xfcqPageObject;
use \ilUtil;
use \ilAdvancedSelectionListGUI;
use \xfcqQuestionGUI;
/**
 * Class xfcqQuestionTableGUI
 * @package srag\Plugins\FlashcardQuestions\Question
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqQuestionTableGUI extends ilTable2GUI {

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;

    const LANG_MODULE = 'table';

    const PREFIX = 'xfcq_qst_';

    /**
     * @var
     */
    protected $selectable_columns = array(

    );

    /**
     * @var xfcqContentGUI
     */
    protected $parent_gui;

    /**
     * xfcqQuestionTableGUI constructor.
     * @param xfcqContentGUI $parent_gui
     * @throws \srag\DIC\Exception\DICException
     */
    public function __construct(xfcqContentGUI $parent_gui) {
        $this->setPrefix(self::PREFIX);
        $this->setId(self::PREFIX . '_' . $_GET['ref_id']);
        $this->setTitle(self::plugin()->translate('question_table_title', 'table'));
        parent::__construct($parent_gui);
        $this->setFormAction(self::dic()->ctrl()->getFormAction($parent_gui));
        $this->setRowTemplate(self::plugin()->directory() . '/templates/default/tpl.generic_table_row.html');
        $this->initColumns();
        $this->initFilter();
        $this->addMultiCommand(xfcqContentGUI::CMD_DELETE, self::dic()->language()->txt('delete'));
        $this->addMultiCommand(xfcqContentGUI::CMD_ACTIVATE, self::dic()->language()->txt('activate'));
        $this->addMultiCommand(xfcqContentGUI::CMD_DEACTIVATE, self::dic()->language()->txt('deactivate'));
        $this->setSelectAllCheckbox('id');
        $this->setData(xfcqQuestion::getArray());
    }

    /**
     * @param xfcqQuestion $a_set
     */
    protected function fillRow($a_set) {
        $this->tpl->setVariable('ROW_ID', $a_set['id']);

        if ($this->isColumnSelected('title')) {
            $this->tpl->setCurrentBlock('row');
            $this->tpl->setVariable('VALUE', $a_set['title']);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isColumnSelected('question')) {
            $this->tpl->setCurrentBlock('row');
            $this->tpl->setVariable('VALUE', $this->getPagePreview($a_set['page_id_qst']));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isColumnSelected('answer')) {
            $this->tpl->setCurrentBlock('row');
            $this->tpl->setVariable('VALUE', $this->getPagePreview($a_set['page_id_ans']));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isColumnSelected('taxonomy')) {
            $this->tpl->setCurrentBlock('row');
            $this->tpl->setVariable('VALUE', implode(', ', array_map('ilTaxonomyNode::_lookupTitle', $a_set['tax_nodes'])));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isColumnSelected('active')) {
            $this->tpl->setCurrentBlock('row');
            $this->tpl->setVariable('VALUE', $this->getActiveIcon($a_set['active']));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock('row');
        $this->tpl->setVariable('VALUE', $this->getActionMenu($a_set));
        $this->tpl->parseCurrentBlock();
    }

    /**
     *
     */
    protected function initColumns() {
        // checkbox column
        $this->addColumn('', '', 10, true);

        foreach ($this->getSelectableColumns() as $title => $props) {
            if ($this->isColumnSelected($title)) {
                $this->addColumn($props['txt'], $props['sort_field'], $props['width']);
            }
        }

        // action column
        $this->addColumn('', '', 30, true);
    }

    /**
     * @param $column
     *
     * @return bool
     */
    public function isColumnSelected($column): bool {
        if (!array_key_exists($column, $this->getSelectableColumns())) {
            return true;
        }

        return in_array($column, $this->getSelectedColumns());
    }

    /**
     * @return array
     * @throws \srag\DIC\Exception\DICException
     */
    public function getSelectableColumns(): array {
        return [
            'title' => ['txt' => self::plugin()->translate('row_title', self::LANG_MODULE), 'sort_field' => 'title', 'width' => '', 'default' => true],
            'question' => ['txt' => self::plugin()->translate('row_question', self::LANG_MODULE), 'sort_field' => false, 'width' => '', 'default' => true],
            'answer' => ['txt' => self::plugin()->translate('row_answer', self::LANG_MODULE), 'sort_field' => false, 'width' => '', 'default' => true],
            'taxonomy' => ['txt' => self::plugin()->translate('row_taxonomy', self::LANG_MODULE), 'sort_field' => false, 'width' => '', 'default' => true],
            'active' => ['txt' => self::plugin()->translate('row_active', self::LANG_MODULE), 'sort_field' => false, 'width' => '', 'default' => true],
        ];
    }

    /**
     * @param $page_id
     * @return string
     */
    protected function getPagePreview($page_id): String {
        $page = new xfcqPageObject($page_id);
        $page->buildDom();
        $rendered_content = $page->getRenderedContent();
        $short_str = $page->getFirstParagraphText();
        $short_str = strip_tags($short_str, "<br>");
        return $short_str;
//        $short_str = ilUtil::shortenText($short_str, $ltexe+6, true);
    }

    /**
     * @param $active
     * @return string
     */
    protected function getActiveIcon($active): String {
        if ($active) {
            $icon_path = ilUtil::getImagePath('icon_ok.svg');
        } else {
            $icon_path = ilUtil::getImagePath('icon_not_ok.svg');
        }
        return '<img src="' . $icon_path . '">';
    }

    /**
     * @param $a_set
     * @return String
     */
    protected function getActionMenu($a_set): String {
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle(self::dic()->language()->txt('actions'));

        self::dic()->ctrl()->setParameterByClass(xfcqQuestionGUI::class, 'qst_id', $a_set['id']);
        self::dic()->ctrl()->setParameterByClass(xfcqContentGUI::class, 'qst_id', $a_set['id']);
        $actions->addItem(self::plugin()->translate('cmd_' . xfcqQuestionGUI::CMD_EDIT_SETTINGS, self::LANG_MODULE), xfcqQuestionGUI::CMD_EDIT_SETTINGS, self::dic()->ctrl()->getLinkTargetByClass(xfcqQuestionGUI::class, xfcqQuestionGUI::CMD_EDIT_SETTINGS));
        $actions->addItem(self::plugin()->translate('cmd_' . xfcqQuestionGUI::CMD_EDIT_QUESTION, self::LANG_MODULE), xfcqQuestionGUI::CMD_EDIT_QUESTION, self::dic()->ctrl()->getLinkTargetByClass(xfcqQuestionGUI::class, xfcqQuestionGUI::CMD_EDIT_QUESTION));
        $actions->addItem(self::plugin()->translate('cmd_' . xfcqQuestionGUI::CMD_EDIT_ANSWER, self::LANG_MODULE), xfcqQuestionGUI::CMD_EDIT_ANSWER, self::dic()->ctrl()->getLinkTargetByClass(xfcqQuestionGUI::class, xfcqQuestionGUI::CMD_EDIT_ANSWER));
        $actions->addItem(self::dic()->language()->txt('delete'), 'delete', self::dic()->ctrl()->getLinkTargetByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_DELETE));
        if ($a_set['active']) {
            $actions->addItem(self::dic()->language()->txt('deactivate'), 'deactivate', self::dic()->ctrl()->getLinkTargetByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_DEACTIVATE));
        } else {
            $actions->addItem(self::dic()->language()->txt('activate'), 'activate', self::dic()->ctrl()->getLinkTargetByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_ACTIVATE));
        }

        return $actions->getHTML();
    }
}