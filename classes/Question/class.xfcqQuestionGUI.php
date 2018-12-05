<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\FlashcardQuestions\DICTrait;
use srag\DIC\FlashcardQuestions\Exception\DICException;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestion;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestionFormGUI;

/**
 * Class xfcqQuestionGUI
 *
 * @package           srag\Plugins\FlashcardQuestions\Question
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xfcqQuestionGUI: xfcqContentGUI
 */
class xfcqQuestionGUI {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;
	const CMD_STANDARD = self::CMD_EDIT_SETTINGS;
	const CMD_EDIT_SETTINGS = 'editSettings';
	const CMD_SAVE_SETTINGS = 'saveSettings';
	const CMD_SAVE_SETTINGS_AND_CONTINUE = 'saveSettingsAndContinue';
	const CMD_EDIT_QUESTION = 'editQuestion';
	const CMD_EDIT_ANSWER = 'editAnswer';
	const GET_QUESTION_ID = 'qst_id';
	const GET_PAGE_ID = 'xfcq_page_id';
	/**
	 * @var xfcqQuestion
	 */
	protected $question;

	/**
	 * @var xfcqContentGUI
	 */
	protected $parent_gui;


    /**
     * xfcqQuestionGUI constructor.
     * @param xfcqContentGUI $parent_gui
     * @throws DICException
     */
	public function __construct(xfcqContentGUI $parent_gui) {
		$this->question = new xfcqQuestion((int)$_GET[self::GET_QUESTION_ID]);
		$is_new = !(bool)$_GET[self::GET_QUESTION_ID];
		$this->parent_gui = $parent_gui;
		if ($is_new) {
		    $this->question->setObjId($this->getObjId());
		    $this->question->create();
            $_GET[self::GET_QUESTION_ID] = $this->question->getId();
            self::dic()->ctrl()->setParameter($this, self::GET_QUESTION_ID, $this->question->getId());
		    ilUtil::sendInfo(self::plugin()->translate('msg_question_created'));
        }
	}


	/**
	 *
	 */
	function executeCommand() {
		$cmd = self::dic()->ctrl()->getCmd(self::CMD_STANDARD);
		$next_class = self::dic()->ctrl()->getNextClass();

		self::dic()->tabs()->clearTargets();
		self::dic()->tabs()->setBackTarget(self::dic()->language()->txt('back'), self::dic()->ctrl()->getLinkTargetByClass(xfcqContentGUI::class));
		self::dic()->ctrl()->saveParameter($this, self::GET_QUESTION_ID);
		self::dic()->ctrl()->saveParameter($this, self::GET_PAGE_ID);

		switch ($next_class) {
			case strtolower(xfcqPageObjectGUI::class):
				$xudfPageObjectGUI = new xfcqPageObjectGUI($_GET[self::GET_PAGE_ID], $this->getObjId());
				$html = self::dic()->ctrl()->forwardCommand($xudfPageObjectGUI);
				if ($html) {
					$this->showEdit($html);
				}
				break;
			default:
				switch ($cmd) {
					case self::CMD_SAVE_SETTINGS:
					case self::CMD_SAVE_SETTINGS_AND_CONTINUE:
					case self::CMD_EDIT_SETTINGS:
					case self::CMD_EDIT_QUESTION;
					case self::CMD_EDIT_ANSWER;
						$this->$cmd();
						break;
					default:
						break;
				}
				break;
		}

        $this->removePageEditorTabs();
    }


	/**
	 *
	 */
	protected function editSettings() {
		self::dic()->ui()->mainTemplate()->addCss(self::plugin()->directory() . '/templates/css/edit_question.css');
		$template = self::plugin()->template('default/tpl.edit_settings.html');

		$xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, $this->question);
		$template->setVariable('SETTINGS', $xfcqQuestionFormGUI->getHTML());

		$template->setVariable('QUESTION_HEADER', self::dic()->language()->txt('question'));
		$template->setVariable('ANSWER_HEADER', self::dic()->language()->txt('answer', 'assessment'));
        $question_gui = new xfcqPageObjectGUI($this->question->getPageIdQuestion(), $this->getObjId());
        $template->setVariable('QUESTION', $question_gui->getHTML());
        $template->setVariable('LINK_EDIT_QUESTION', self::dic()->ctrl()->getLinkTarget($this, self::CMD_EDIT_QUESTION));

        $answer_gui = new xfcqPageObjectGUI($this->question->getPageIdAnswer(), $this->getObjId());
        $template->setVariable('ANSWER', $answer_gui->getHTML());
        $template->setVariable('LINK_EDIT_ANSWER', self::dic()->ctrl()->getLinkTarget($this, self::CMD_EDIT_ANSWER));

        $template->setVariable('LABEL_EDIT', self::dic()->language()->txt('edit'));

		self::dic()->mainTemplate()->setContent($template->get());
	}


	/**
	 *
	 */
	protected function editQuestion() {
		self::dic()->ctrl()->setParameter($this, self::GET_PAGE_ID, $this->question->getPageIdQuestion());
		self::dic()->ctrl()->setParameterByClass(xfcqPageObjectGUI::class, 'ref_id', $_GET['ref_id']);
		self::dic()->ctrl()->redirectByClass(xfcqPageObjectGUI::class, 'edit');
	}


	/**
	 *
	 */
	protected function editAnswer() {
		self::dic()->ctrl()->setParameter($this, self::GET_PAGE_ID, $this->question->getPageIdAnswer());
		self::dic()->ctrl()->setParameterByClass(xfcqPageObjectGUI::class, 'ref_id', $_GET['ref_id']);
		self::dic()->ctrl()->redirectByClass(xfcqPageObjectGUI::class, 'edit');
	}


	/**
	 * @param $page_id
	 *
	 * @throws ilException
	 */
	protected function showEdit($html) {
		if ($_GET[self::GET_PAGE_ID] == $this->question->getPageIdQuestion()) {
			$this->showEditQuestion($html);
		} elseif ($_GET[self::GET_PAGE_ID] == $this->question->getPageIdAnswer()) {
			$this->showEditAnswer($html);
		} else {
			throw new ilException('Page ID does not match question or answer');
		}
	}


	/**
	 * @throws DICException
	 * @throws ilTemplateException
	 */
	protected function showEditQuestion($html) {
		self::dic()->ui()->mainTemplate()->addCss(self::plugin()->directory() . '/templates/css/edit_question.css');
		$template = self::plugin()->template('default/tpl.edit_question.html');

		$xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, $this->question);
		$template->setVariable('SETTINGS', $xfcqQuestionFormGUI->getHTML());

		$template->setVariable('QUESTION_HEADER', self::dic()->language()->txt('question'));
		$template->setVariable('QUESTION', $html);

		$answer_gui = new xfcqPageObjectGUI($this->question->getPageIdAnswer(), $this->getObjId());
		$template->setVariable('ANSWER_HEADER', self::dic()->language()->txt('answer', 'assessment'));
		$template->setVariable('ANSWER', $answer_gui->getHTML());
		$template->setVariable('LINK_EDIT_ANSWER', self::dic()->ctrl()->getLinkTarget($this, self::CMD_EDIT_ANSWER));

		$template->setVariable('LABEL_EDIT', self::dic()->language()->txt('edit'));

		$back_button = ilLinkButton::getInstance();
		$back_button->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_EDIT_SETTINGS));
		$back_button->setCaption(self::plugin()->translate('exit_button'), false);
		$template->setVariable('EXIT_BUTTON', $back_button->getToolbarHTML());

        self::dic()->mainTemplate()->setContent($template->get());
	}


	/**
	 * @throws DICException
	 * @throws ilTemplateException
	 */
	protected function showEditAnswer($html) {
		self::dic()->ui()->mainTemplate()->addCss(self::plugin()->directory() . '/templates/css/edit_question.css');
		$template = self::plugin()->template('default/tpl.edit_answer.html');

		$xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, $this->question);
		$template->setVariable('SETTINGS', $xfcqQuestionFormGUI->getHTML());

		$question_gui = new xfcqPageObjectGUI($this->question->getPageIdQuestion(), $this->getObjId());
		$template->setVariable('QUESTION_HEADER', self::dic()->language()->txt('question'));
		$template->setVariable('QUESTION', $question_gui->getHTML());
		$template->setVariable('LINK_EDIT_QUESTION', self::dic()->ctrl()->getLinkTarget($this, self::CMD_EDIT_QUESTION));

		$template->setVariable('ANSWER_HEADER', self::dic()->language()->txt('answer', 'assessment'));
		$template->setVariable('ANSWER', $html);

		$template->setVariable('LABEL_EDIT', self::dic()->language()->txt('edit'));

		$back_button = ilLinkButton::getInstance();
		$back_button->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_EDIT_SETTINGS));
		$back_button->setCaption(self::plugin()->translate('exit_button'), false);
		$template->setVariable('EXIT_BUTTON', $back_button->getToolbarHTML());

        self::dic()->mainTemplate()->setContent($template->get());
	}


	/**
	 * @throws DICException
	 */
	protected function saveSettings() {
		$xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, $this->question);
		$xfcqQuestionFormGUI->setValuesByPost();
		if ($xfcqQuestionFormGUI->saveForm()) {
			ilUtil::sendSuccess(self::plugin()->translate('msg_success'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_EDIT_SETTINGS);
		}
        self::dic()->mainTemplate()->setContent($xfcqQuestionFormGUI->getHTML());
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


	/**
	 *
	 */
	protected function removePageEditorTabs() {
		// these are automatically rendered by the pageobject gui
		self::dic()->tabs()->removeTab('edit');
		self::dic()->tabs()->removeTab('history');
		self::dic()->tabs()->removeTab('clipboard');
		self::dic()->tabs()->removeTab('pg');
		// and we have to do it two times, since there are two page editors :)
		self::dic()->tabs()->removeTab('edit');
		self::dic()->tabs()->removeTab('history');
		self::dic()->tabs()->removeTab('clipboard');
		self::dic()->tabs()->removeTab('pg');
	}
}
