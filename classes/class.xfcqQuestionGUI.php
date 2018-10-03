<?php

use srag\DIC\DICTrait;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestion;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestionFormGUI;

/**
 * Class xfcqQuestionGUI
 * @package srag\Plugins\FlashcardQuestions\Question
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xfcqQuestionGUI: xfcqContentGUI
 */
class xfcqQuestionGUI {

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;

    const CMD_STANDARD = self::CMD_SETTINGS;
    const CMD_SETTINGS = 'editSettings';
    const CMD_SAVE_SETTINGS = 'saveSettings';
    const CMD_SAVE_SETTINGS_AND_CONTINUE = 'saveSettingsAndContinue';
    const CMD_QUESTION = 'editQuestion';
    const CMD_ANSWER = 'editAnswer';

    /**
     * @var xfcqQuestion
     */
    protected $question;
    /**
     * @var bool
     */
    protected $is_new;
    /**
     * @var xfcqContentGUI
     */
    protected $parent_gui;

    /**
     * xfcqQuestionGUI constructor.
     * @param xfcqContentGUI $parent_gui
     */
    public function __construct(xfcqContentGUI $parent_gui) {
        $this->question = new xfcqQuestion((int) $_GET['qst_id']);
        $this->is_new = !(bool) $_GET['qst_id'];
        $this->parent_gui = $parent_gui;
    }

    /**
     *
     */
    function executeCommand() {
        self::dic()->tabs()->clearTargets();
        self::dic()->tabs()->setBackTarget(self::dic()->language()->txt('back'), self::dic()->ctrl()->getLinkTargetByClass(xfcqContentGUI::class));
        self::dic()->ctrl()->saveParameter($this, 'qst_id');

        $cmd = self::dic()->ctrl()->getCmd(self::CMD_STANDARD);
        $next_class = self::dic()->ctrl()->getNextClass();

        switch ($next_class) {
            case strtolower(xfcqPageObjectGUI::class):
                $xudfPageObjectGUI = new xfcqPageObjectGUI($this, $_GET['step'] == 'qst' ? $this->question->getPageIdQuestion() : $this->question->getPageIdAnswer());
                $html = self::dic()->ctrl()->forwardCommand($xudfPageObjectGUI);
                self::dic()->template()->setContent($this->getHeader() . $html);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SAVE_SETTINGS:
                    case self::CMD_SAVE_SETTINGS_AND_CONTINUE:
                    case self::CMD_SETTINGS:
                    case self::CMD_QUESTION;
                    case self::CMD_ANSWER;
                        $this->$cmd();
                        break;
                    default:
                        break;
                }
                break;
        }
        // these are automatically rendered by the pageobject gui
        self::dic()->tabs()->removeTab('edit');
        self::dic()->tabs()->removeTab('history');
        self::dic()->tabs()->removeTab('clipboard');
        self::dic()->tabs()->removeTab('pg');
    }

    protected function getHeader() {
        if ($_GET['step'] == 'qst') {
            $qst_bold = '<b>';
            $qst_bold_closed = '</b>';
        } elseif ($_GET['step'] == 'ans') {
            $ans_bold = '<b>';
            $ans_bold_closed = '</b>';
        } else {
            $settings_bold = '<b>';
            $settings_bold_closed = '</b>';
        }

        $link_settings = '<a href="' . self::dic()->ctrl()->getLinkTarget($this, self::CMD_SETTINGS) .'">';
        $link_settings_closed = '</a>';


        if (!$this->is_new) {
            $link_qst = '<a href="' . self::dic()->ctrl()->getLinkTarget($this, self::CMD_QUESTION) .'">';
            $link_qst_closed = '</a>';

            $link_ans = '<a href="' . self::dic()->ctrl()->getLinkTarget($this, self::CMD_ANSWER) .'">';
            $link_ans_closed = '</a>';
        }
        return '<h1 style=font-size:30px>'
            . $link_settings . $settings_bold . 'Einstellungen' . $settings_bold_closed . $link_settings_closed
            . ' &rarr; '
            . $link_qst. $qst_bold . 'Frage' . $qst_bold_closed . $link_qst_closed
            . ' &rarr; '
            . $link_ans . $ans_bold . 'Antwort' . $ans_bold_closed . $link_ans_closed
            . '</h1>';
    }
    /**
     *
     */
    protected function editSettings() {
        $xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, $this->question);
        self::dic()->template()->setContent($this->getHeader() . $xfcqQuestionFormGUI->getHTML());
    }

    /**
     * @throws \srag\DIC\Exception\DICException
     */
    protected function saveSettings() {
        $xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, $this->question);
        $xfcqQuestionFormGUI->setValuesByPost();
        if ($xfcqQuestionFormGUI->saveForm()) {
            ilUtil::sendSuccess(self::plugin()->translate('msg_success'), true);
            self::dic()->ctrl()->redirect($this, self::CMD_SETTINGS);
        }
        self::dic()->template()->setContent($this->getHeader() . $xfcqQuestionFormGUI->getHTML());
    }

    /**
     * @throws \srag\DIC\Exception\DICException
     */
    protected function saveSettingsAndContinue() {
        $xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, $this->question);
        $xfcqQuestionFormGUI->setValuesByPost();
        if ($xfcqQuestionFormGUI->saveForm()) {
            ilUtil::sendSuccess(self::plugin()->translate('msg_success'), true);
            self::dic()->ctrl()->redirect($this, self::CMD_QUESTION);
        }
        self::dic()->template()->setContent($this->getHeader() . $xfcqQuestionFormGUI->getHTML());
    }

    /**
     *
     */
    protected function editQuestion() {
        self::dic()->ctrl()->setParameter($this, 'step', 'qst');
        self::dic()->ctrl()->setParameterByClass(xfcqPageObjectGUI::class, 'ref_id', $_GET['ref_id']);
        self::dic()->ctrl()->redirectByClass(xfcqPageObjectGUI::class, 'edit');
    }

    /**
     *
     */
    protected function editAnswer() {
        self::dic()->ctrl()->setParameter($this, 'step', 'ans');
        self::dic()->ctrl()->setParameterByClass(xfcqPageObjectGUI::class, 'ref_id', $_GET['ref_id']);
        self::dic()->ctrl()->redirectByClass(xfcqPageObjectGUI::class, 'edit');
    }

    /**
     * @return int
     */
    public function getObjId(): int {
        return $this->parent_gui->getObjId();
    }

    /**
     * @return ilObjFlashcardQuestions
     */
    public function getObject(): ilObjFlashcardQuestions {
        $this->parent_gui->getObject();
    }
}