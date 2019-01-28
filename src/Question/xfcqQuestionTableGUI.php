<?php

namespace srag\Plugins\FlashcardQuestions\Question;

use ilAdvancedSelectionListGUI;
use ilCheckboxInputGUI;
use ilDateDurationInputGUI;
use ilDurationInputGUI;
use ilFlashcardQuestionsPlugin;
use ilFormPropertyGUI;
use ilObjTaxonomy;
use ilSelectInputGUI;
use ilTable2GUI;
use ilTaxonomyException;
use ilTaxonomyNode;
use ilTaxSelectInputGUI;
use ilTextInputGUI;
use ilUtil;
use srag\DIC\FlashcardQuestions\DICTrait;
use srag\DIC\FlashcardQuestions\Exception\DICException;
use srag\Plugins\FlashcardQuestions\Report\xfcqMPDF;
use xfcqContentGUI;
use xfcqPageObjectGUI;
use xfcqQuestionGUI;

/**
 * Class xfcqQuestionTableGUI
 *
 * @package srag\Plugins\FlashcardQuestions\Question
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqQuestionTableGUI extends ilTable2GUI {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;
	const LANG_MODULE = 'table';
	const PREFIX = 'xfcq_qst';
	const FILTER_ACTIVE_TRUE = 1;
	const FILTER_ACTIVE_FALSE = 2;

    const EXPORT_QUESTIONS_ANSWERS_ID = 1000;
    const EXPORT_QUESTIONS_ANSWERS = 2000;
    const EXPORT_QUESTIONS_ID = 3000;
    const EXPORT_QUESTIONS = 4000;

    /**
     * @var array
     */
    protected static $exports = array(
        self::EXPORT_QUESTIONS_ANSWERS_ID => 'export_pdf_format_1',
        self::EXPORT_QUESTIONS_ANSWERS => 'export_pdf_format_2',
        self::EXPORT_QUESTIONS_ID => 'export_pdf_format_3',
        self::EXPORT_QUESTIONS => 'export_pdf_format_4',
    );

	/**
	 * @var array
	 */
	protected $filter;
	/**
	 * @var xfcqContentGUI
	 */
	protected $parent_gui;

	/**
	 * xfcqQuestionTableGUI constructor
	 *
	 * @param xfcqContentGUI $parent_gui
	 *
	 * @throws ilTaxonomyException
	 * @throws DICException
	 */
	public function __construct(xfcqContentGUI $parent_gui) {
		$this->parent_gui = $parent_gui;
		$this->setPrefix(self::PREFIX);
		$this->setId(self::PREFIX . '_' . $_GET['ref_id']);
        $this->setTitle(self::plugin()->translate('question_table_title', 'table'));
        parent::__construct($parent_gui);
        $this->setFilterCommand(xfcqContentGUI::CMD_APPLY_FILTER);
        $this->setFormAction(self::dic()->ctrl()->getFormAction($parent_gui));
        $this->setRowTemplate(self::plugin()->directory() . '/templates/default/tpl.generic_table_row.html');
        $this->initColumns();
        $this->initFilter();
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);
        $this->determineOffsetAndOrder();
        $this->determineLimit();
        $this->addMultiCommand(xfcqContentGUI::CMD_DELETE, self::dic()->language()->txt('delete'));
		$this->addMultiCommand(xfcqContentGUI::CMD_ACTIVATE, self::dic()->language()->txt('activate'));
		$this->addMultiCommand(xfcqContentGUI::CMD_DEACTIVATE, self::dic()->language()->txt('deactivate'));
		$this->setSelectAllCheckbox('id');
		$this->setDisableFilterHiding(true);

        foreach (static::$exports as $id => $lang_key) {
            $this->export_formats[$id] = self::plugin()->getPluginObject()->getPrefix() . '_' . $lang_key;
        }

        $this->buildData();
        self::dic()->mainTemplate()->addInlineCss('tr.xfcq_table_row img { max-width: 400px !important; } tr.xfcq_table_row .carousel-inner img { max-width: 100% !important;}');
    }


	/**
	 * @param array $a_set
	 *
	 * @throws DICException
	 */
	protected function fillRow($a_set) {

		self::dic()->ctrl()->setParameterByClass(xfcqQuestionGUI::class, 'qst_id', $a_set['qst_id']);
		self::dic()->ctrl()->setParameterByClass(xfcqContentGUI::class, 'qst_id', $a_set['qst_id']);

		$this->tpl->setVariable('ROW_ID', $a_set['qst_id']);

        foreach ($this->getSelectableColumns() as $title => $props) {
            if ($this->isColumnSelected($title)) {
                $this->tpl->setCurrentBlock('row');
                $this->tpl->setVariable('VALUE', $a_set[$title]);
                $this->tpl->parseCurrentBlock();
            }
        }

		$this->tpl->setCurrentBlock('row');
		$this->tpl->setVariable('VALUE', $this->getActionMenu($a_set));
		$this->tpl->parseCurrentBlock();
	}

    /**
     * @param $data
     * @return array
     * @throws DICException
     */
    protected function formatData($data) {
        $formatted_data = [];
        $data = $this->formatTaxNodes($data);
        foreach ($data as $a_set) {
            $formatted_set = [];
            $formatted_set['active'] = $this->getActiveIcon($a_set['active']);
            $formatted_set['id'] =  $a_set['obj_id'] . '.' . $a_set['id'];
            $formatted_set['qst_id'] = $a_set['id'];
            $formatted_set['question'] =  $this->getPagePreview($a_set['page_id_qst']);
            $formatted_set['answer'] =  $this->getPagePreview($a_set['page_id_ans']);

            foreach ($this->parent_gui->getObject()->getTaxonomyIds() as $tax_id) {
                $tax_node_ids = $formatted_set['tax_node_ids'] = $a_set['tax_node_ids'];

                if (isset($tax_node_ids[$tax_id])) {
                    $formatted_set['taxonomy_' . $tax_id] =  implode(', ', array_map('ilTaxonomyNode::_lookupTitle', $tax_node_ids[$tax_id]));
                } else {
                    $formatted_set['taxonomy_' . $tax_id] =  '&nbsp';
                }
            }

            $formatted_data[] = $formatted_set;
        }
        return $formatted_data;
	}


    /**
     * @throws DICException
     */
	function initFilter() {
	    $filter_item = new ilTextInputGUI(self::plugin()->translate('row_id', self::LANG_MODULE), 'id');
	    $this->addAndReadFilterItem($filter_item);

		$filter_item = new ilSelectInputGUI(self::dic()->language()->txt('active'), 'active');
		$filter_item->setOptions([
			'' => self::dic()->language()->txt('please_select'),
			self::FILTER_ACTIVE_TRUE => self::dic()->language()->txt('yes'),
			self::FILTER_ACTIVE_FALSE => self::dic()->language()->txt('no'),
		]);
		$this->addAndReadFilterItem($filter_item);

		foreach ($this->parent_gui->getObject()->getTaxonomyIds() as $tax_id) {
			$filter_item = new ilTaxSelectInputGUI($tax_id, "taxonomy_$tax_id", true);
			$this->addAndReadFilterItem($filter_item);
		}
	}

	/**
	 * @param $item
	 */
	protected function addAndReadFilterItem(ilFormPropertyGUI $item) {
		$this->addFilterItem($item);
		$item->readFromSession();

		switch (true) {
			case ($item instanceof ilCheckboxInputGUI):
				$this->filter[$item->getPostVar()] = $item->getChecked();
				break;
			case ($item instanceof ilDateDurationInputGUI):
				$this->filter[$item->getPostVar()] = array(
					'start' => $item->getStart(),
					'end' => $item->getEnd(),
				);
				break;
			case ($item instanceof ilDurationInputGUI):
				$this->filter[$item->getPostVar()] = $item->getSeconds();
				break;
			default:
				$this->filter[$item->getPostVar()] = $item->getValue();
				break;
		}
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
	 * @throws DICException
	 */
	public function isColumnSelected($column) {
		if (!array_key_exists($column, $this->getSelectableColumns())) {
			return true;
		}

		return in_array($column, $this->getSelectedColumns());
	}

    function numericOrdering($a_field) {
        return (bool) $a_field == 'qst_id';
    }


    /**
	 * @return array
	 * @throws DICException
	 */
	public function getSelectableColumns() {
		$columns = [
			'active' => [
				'txt' => self::plugin()->translate('row_active', self::LANG_MODULE),
				'sort_field' => false,
				'width' => '',
				'default' => true
			],
			'id' => [
				'txt' => self::plugin()->translate('row_id', self::LANG_MODULE),
				'sort_field' => 'qst_id',
                'numeric_ordering' => true,
				'width' => '',
				'default' => true
			],
			'question' => [
				'txt' => self::plugin()->translate('row_question', self::LANG_MODULE),
				'sort_field' => false,
				'width' => '',
				'default' => true
			],
			'answer' => [
				'txt' => self::plugin()->translate('row_answer', self::LANG_MODULE),
				'sort_field' => false,
				'width' => '',
				'default' => true
			],
		];

		foreach ($this->parent_gui->getObject()->getTaxonomyIds() as $tax_id) {
			$ilObjTaxonomy = new ilObjTaxonomy($tax_id);
			$columns['taxonomy_' . $tax_id] = [ 'txt' => $ilObjTaxonomy->getTitle(), 'sort_field' => false, 'width' => '', 'default' => true ];
		}

		return $columns;
	}


	/**
	 * @param int $page_id
	 *
	 * @return string
	 */
	protected function getPagePreview($page_id) {
        $page = new xfcqPageObjectGUI($page_id, $this->parent_gui->getObjId());
        $page->setTemplateOutput(true);
        $page->setEnableEditing(false);
        $page->setOutputMode(IL_PAGE_PRINT);
        $page->setEnabledTabs(false);
        return $page->getHTML();
    }


	/**
	 * @param $active
	 *
	 * @return string
	 */
	protected function getActiveIcon($active) {
		if ($active) {
			$icon_path = ilUtil::getImagePath('icon_ok.svg');
		} else {
			$icon_path = ilUtil::getImagePath('icon_not_ok.svg');
		}

		return '<img src="' . $icon_path . '">';
	}

	/**
	 * @param array $a_set
	 *
	 * @return string
	 * @throws DICException
	 */
	protected function getActionMenu(array $a_set) {
		$actions = new ilAdvancedSelectionListGUI();

		$actions->setListTitle(self::dic()->language()->txt('actions'));
		$actions->addItem(self::dic()->language()->txt('edit'), xfcqQuestionGUI::CMD_EDIT_SETTINGS, self::dic()->ctrl()
			->getLinkTargetByClass(xfcqQuestionGUI::class, xfcqQuestionGUI::CMD_EDIT_SETTINGS));
		$actions->addItem(self::dic()->language()->txt('delete'), 'delete', self::dic()->ctrl()
			->getLinkTargetByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_DELETE));
		if ($a_set['active']) {
			$actions->addItem(self::dic()->language()->txt('deactivate'), 'deactivate', self::dic()->ctrl()
				->getLinkTargetByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_DEACTIVATE));
		} else {
			$actions->addItem(self::dic()->language()->txt('activate'), 'activate', self::dic()->ctrl()
				->getLinkTargetByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_ACTIVATE));
		}

		return $actions->getHTML();
	}

    /**
     * @param int $format
     * @param bool $send
     * @throws DICException
     * @throws \Mpdf\MpdfException
     */
    public function exportData($format, $send = false)
    {
        $pdf = new xfcqMPDF($this->parent_gui->getObject(), $this->getData());
        switch ($format) {
            case self::EXPORT_QUESTIONS_ANSWERS:
                $pdf->printID(false);
                break;
            case self::EXPORT_QUESTIONS_ID:
                $pdf->printAnswers(false);
                break;
            case self::EXPORT_QUESTIONS:
                $pdf->printID(false);
                $pdf->printAnswers(false);
                break;
        }
        $pdf->parse();
        $pdf->download(date('d-m-Y') . '-question_pool_export.pdf');
        exit();
    }

    /**
     *
     */
    protected function buildData() {
        $count_query = self::dic()->database()->query($this->buildQuery(true));
        $this->setMaxCount((int) $count_query->fetchAssoc()['total_rows']);

        $data_query = self::dic()->database()->query($this->buildQuery(false));
        $data = self::dic()->database()->fetchAll($data_query);
        if(count($data) > 0) {
	        $data = $this->formatData($data);
        }

        $this->setData($data);
    }

    /**
     * @param $data array
     * @return array
     */
    protected function formatTaxNodes($data) {
        $qst_by_id = [];
        foreach ($data as $set) {
            $qst_by_id[$set['id']] = $set;
        }
        $tax_query = self::dic()->database()->query(
            'SELECT GROUP_CONCAT(tax_node_id) AS tax_node_ids, tax_id, qst_id FROM xfcq_question_tax_node WHERE qst_id IN (' . implode(',', array_keys($qst_by_id)) . ') GROUP BY tax_id, qst_id'
        );
        while ($rec = $tax_query->fetchAssoc()) {
            if (!is_array($qst_by_id[$rec['qst_id']]['tax_node_ids'])) {
                $qst_by_id[$rec['qst_id']]['tax_node_ids'] = [];
            }
            $qst_by_id[$rec['qst_id']]['tax_node_ids'][$rec['tax_id']] = explode(',', $rec['tax_node_ids']);
        }
        return $qst_by_id;
    }

    /**
     * @param $count bool
     * @return string
     */
    protected function buildQuery($count) {
        $query = $count ? 'SELECT count(*) as total_rows FROM (' : '';
        $query .= 'SELECT qst.*, tax_nodes.tax_node_ids ';
        $query .= 'FROM ' . xfcqQuestion::TABLE_NAME . ' qst ';
        $query .= 'LEFT JOIN ';
        $query .= '(SELECT GROUP_CONCAT(tax_node_id) as tax_node_ids, qst_id FROM ' . xfcqQuestionTaxNode::TABLE_NAME . ' GROUP BY qst_id) tax_nodes ON tax_nodes.qst_id = qst.id ';

        $where_statement = $this->buildWhereStatement();
        $query .= 'WHERE obj_id = ' . $this->parent_gui->getObjId() . ' ';
        $query .= $where_statement ? $where_statement : '';
        $query .= 'GROUP BY qst.id ';
        $query .= $count ? ') as subtable' : '';
        $query .= isset($_GET[$this->getPrefix() . '_xpt']) || $count ? '' : 'LIMIT ' . (int) $this->getLimit() . ' OFFSET ' . (int) $this->getOffset();

        // DEBUG
//        self::dic()->log()->write($query);

        return $query;
    }

    /**
     * @return string
     */
    protected function buildWhereStatement() {
        $where = '';

        // id
        if ($id = $this->filter['id']) {
            $where .= 'AND CONCAT(obj_id, ".", qst.id) LIKE "%' . $id . '%" ';
        }

        // active
        if ($this->filter['active'] == self::FILTER_ACTIVE_TRUE) {
            $where .= 'AND qst.active = 1 ';
        }

        // inactive
        if ($this->filter['active'] == self::FILTER_ACTIVE_FALSE) {
            $where .= 'AND qst.active = 0 ';
        }

        //taxonomies
        foreach ($this->parent_gui->getObject()->getTaxonomyIds() as $tax_id) {
            $tax_nodes = array_filter($this->filter['taxonomy_' . $tax_id]);
            $or = '';
            if (count($tax_nodes)) {
                $where .= 'AND (';
                foreach ($tax_nodes as $tax_node) {
                    $where .= $or . 'tax_node_ids LIKE "%,' .$tax_node . '" OR tax_node_ids LIKE "' .$tax_node . ',%" OR tax_node_ids LIKE "%,' .$tax_node . ',%" ';
                    $or = 'OR ';
                }
                $where .= ') ';
            }
        }

        return $where;
    }

    /**
     * @return bool|string
     */
    protected function buildHavingStatement() {
        $having = '';
        //taxonomies
        $and = '';
        foreach ($this->parent_gui->getObject()->getTaxonomyIds() as $tax_id) {
            $tax_nodes = array_filter($this->filter['taxonomy_' . $tax_id]);
            $or = '';
            if (count($tax_nodes)) {
                $having .= $and;
                foreach ($tax_nodes as $tax_node) {
                    $having .= $or . '(tax_node_ids LIKE "%,' .$tax_node . '" OR tax_node_ids LIKE "' .$tax_node . ',%" OR tax_node_ids LIKE "%,' .$tax_node . ',%") ';
                    $or = 'OR ';
                    $and = 'AND ';
                }
            }
        }

        return $having ? 'HAVING ' . $having : false;
    }

}
