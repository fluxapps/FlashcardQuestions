<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\FlashcardQuestions\DICTrait;
use srag\DIC\FlashcardQuestions\Exception\DICException;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestion;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestionTableGUI;

/**
 * Class xfcqContentGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xfcqContentGUI: ilObjFlashcardQuestionsGUI
 * @ilCtrl_Calls      xfcqContentGUI: ilFormPropertyDispatchGUI
 */
class xfcqContentGUI {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;
	const CMD_STANDARD = 'show';
	const CMD_ADD = 'add';
	const CMD_DELETE = 'delete';
	const CMD_CONFIRMED_DELETE = 'confirmedDelete';
	const CMD_ACTIVATE = 'activate';
	const CMD_DEACTIVATE = 'deactivate';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	/**
	 * @var ilObjFlashcardQuestionsGUI
	 */
	protected $parent_gui;


	/**
	 * xfcqContentGUI constructor
	 *
	 * @param ilObjFlashcardQuestionsGUI $parent_gui
	 */
	public function __construct(ilObjFlashcardQuestionsGUI $parent_gui) {
		$this->parent_gui = $parent_gui;
	}


	/**
	 *
	 */
	function executeCommand() {
		$cmd = self::dic()->ctrl()->getCmd(self::CMD_STANDARD);
		$next_class = self::dic()->ctrl()->getNextClass();

		switch ($next_class) {
			case strtolower(xfcqQuestionGUI::class):
				$xfcqQuestionGUI = new xfcqQuestionGUI($this);
				self::dic()->ctrl()->forwardCommand($xfcqQuestionGUI);
				break;
			default:
				switch ($cmd) {
					case self::CMD_STANDARD:
						$this->initToolbar();
						$this->$cmd();
						break;
					case self::CMD_ADD;
					case self::CMD_DELETE;
					case self::CMD_CONFIRMED_DELETE;
					case self::CMD_ACTIVATE;
					case self::CMD_DEACTIVATE;
					case self::CMD_APPLY_FILTER;
					case self::CMD_RESET_FILTER;
						$this->$cmd();
						break;
					default:
						break;
				}
				break;
		}
	}


	/**
	 * @throws DICException
	 */
	protected function initToolbar() {
		$button = ilLinkButton::getInstance();
		$button->setCaption(self::plugin()->translate('add_new_question', 'button'), false);
		$button->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_ADD));
		self::dic()->toolbar()->addButtonInstance($button);
	}


	/**
	 * @throws DICException
	 * @throws ilTaxonomyException
	 */
	protected function show() {
		$xfcqQuestionTableGUI = new xfcqQuestionTableGUI($this);
		self::output()->output($xfcqQuestionTableGUI);
	}


	/**
	 * @throws DICException
	 * @throws ilTaxonomyException
	 */
	protected function applyFilter() {
		$xfcqQuestionTableGUI = new xfcqQuestionTableGUI($this);
		$xfcqQuestionTableGUI->resetOffset();
		$xfcqQuestionTableGUI->writeFilterToSession();
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 * @throws DICException
	 * @throws ilTaxonomyException
	 */
	protected function resetFilter() {
		$xfcqQuestionTableGUI = new xfcqQuestionTableGUI($this);
		$xfcqQuestionTableGUI->resetOffset();
		$xfcqQuestionTableGUI->resetFilter();
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function add() {
		self::dic()->ctrl()->redirectByClass(xfcqQuestionGUI::class);
	}


	/**
	 *
	 */
	protected function delete() {
		$confirmationGUI = new ilConfirmationGUI();
		$confirmationGUI->setHeaderText(self::plugin()->translate('delete_confirmation_text'));
		$confirmationGUI->setConfirm(self::dic()->language()->txt('delete'), self::CMD_CONFIRMED_DELETE);
		$confirmationGUI->setCancel(self::dic()->language()->txt('cancel'), self::CMD_STANDARD);
		$confirmationGUI->setFormAction(self::dic()->ctrl()->getFormAction($this));

		$ids = count($_POST['id']) ? $_POST['id'] : array( $_GET['qst_id'] );
		if (empty(array_filter($ids))) {
			ilUtil::sendFailure(self::plugin()->translate('msg_no_question_selected'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
		}

		foreach ($ids as $id) {
			/** @var xfcqQuestion $xfcqQuestion */
			$xfcqQuestion = xfcqQuestion::find($id);
			$text = "ID: {$xfcqQuestion->getId()}<br>";
			$confirmationGUI->addItem('qst_id[]', $id, $text);
		}

		self::output()->output($confirmationGUI);
	}


	/**
	 *
	 */
	protected function confirmedDelete() {
		$ids = is_array($_POST['qst_id']) ? $_POST['qst_id'] : [ $_POST['qst_id'] ];
		foreach ($ids as $id) {
			xfcqQuestion::find($id)->delete();
		}
		ilUtil::sendSuccess(self::plugin()->translate('msg_deleted_successfully'), true);
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function activate() {
		$ids = count($_POST['id']) ? $_POST['id'] : array( $_GET['qst_id'] );
		if (empty(array_filter($ids))) {
			ilUtil::sendFailure(self::plugin()->translate('msg_no_question_selected'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		foreach ($ids as $id) {
			/** @var xfcqQuestion $xfcqQuestion */
			$xfcqQuestion = xfcqQuestion::find($id);
			$xfcqQuestion->setActive(1);
			$xfcqQuestion->update();
		}

		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function deactivate() {
		$ids = count($_POST['id']) ? $_POST['id'] : array( $_GET['qst_id'] );
		if (empty(array_filter($ids))) {
			ilUtil::sendFailure(self::plugin()->translate('msg_no_question_selected'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		foreach ($ids as $id) {
			/** @var xfcqQuestion $xfcqQuestion */
			$xfcqQuestion = xfcqQuestion::find($id);
			$xfcqQuestion->setActive(0);
			$xfcqQuestion->update();
		}

		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->parent_gui->getObjId();
	}


	/**
	 * @return ilObjFlashcardQuestions
	 */
	public function getObject() {
		return $this->parent_gui->getObject();
	}
}
