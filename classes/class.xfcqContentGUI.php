<?php
use srag\DIC\DICTrait;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestionTableGUI;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestionFormGUI;
use srag\Plugins\FlashcardQuestions\Question\xfcqQuestion;
/**
 * Class xfcqContentGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xfcqContentGUI: ilObjFlashcardQuestionsGUI
 * @ilCtrl_Calls xfcqContentGUI: ilFormPropertyDispatchGUI
 */
class xfcqContentGUI {

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;

    const CMD_STANDARD = 'show';
    const CMD_ADD = 'add';
    const CMD_CREATE = 'create';
    const CMD_EDIT = 'edit';
    const CMD_UPDATE = 'update';
    const CMD_DELETE = 'delete';
    const CMD_CONFIRMED_DELETE = 'confirmedDelete';
    const CMD_ACTIVATE = 'activate';
    const CMD_DEACTIVATE = 'deactivate';

    /**
     * @var ilObjFlashcardQuestionsGUI
     */
    protected $parent_gui;

    /**
     * xfcqContentGUI constructor.
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
                    case self::CMD_CREATE;
                    case self::CMD_EDIT;
                    case self::CMD_UPDATE;
                    case self::CMD_DELETE;
                    case self::CMD_CONFIRMED_DELETE;
                    case self::CMD_ACTIVATE;
                    case self::CMD_DEACTIVATE;
                        $this->$cmd();
                        break;
                    default:
                        break;
                }
                break;
        }
    }

    /**
     * @throws \srag\DIC\Exception\DICException
     */
    protected function initToolbar() {
        $button = ilLinkButton::getInstance();
        $button->setCaption(self::plugin()->translate('add_new_question', 'button'), false);
        $button->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_ADD));
        self::dic()->toolbar()->addButtonInstance($button);
    }

    /**
     *
     */
    protected function show() {
        $xfcqQuestionTableGUI = new xfcqQuestionTableGUI($this);
        self::dic()->template()->setContent($xfcqQuestionTableGUI->getHTML());
    }

    /**
     *
     */
    protected function add() {
        self::dic()->ctrl()->redirectByClass(xfcqQuestionGUI::class);
//        $xfcqQuestionFormGUI = new xfcqQuestionFormGUI($this, new xfcqQuestion());
//        self::dic()->template()->setContent($xfcqQuestionFormGUI->getHTML());
    }

    /**
     *
     */
    protected function create() {

    }

    /**
     *
     */
    protected function edit() {

    }

    /**
     *
     */
    protected function update() {

    }

    /**
     *
     */
    protected function delete() {

    }

    /**
     *
     */
    protected function confirmedDelete() {

    }

    /**
     *
     */
    protected function activate() {
        $ids = count($_POST['id']) ? $_POST['id'] : array($_GET['qst_id']);
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
        $ids = count($_POST['id']) ? $_POST['id'] : array($_GET['qst_id']);
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