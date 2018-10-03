<?php

namespace srag\Plugins\FlashcardQuestions\Question;

use srag\DIC\DICTrait;
use ilFlashcardQuestionsPlugin;
use \ilTable2GUI;
use \xfcqContentGUI;
/**
 * Class xfcqQuestionTableGUI
 * @package srag\Plugins\FlashcardQuestions\Question
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqQuestionTableGUI extends ilTable2GUI {

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;

    const PREFIX = 'xfcq_qst_';

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
        $this->setId($_GET['ref_id']);
        $this->setTitle(self::plugin()->translate('question_table_title', 'table'));
        parent::__construct($parent_gui);

    }


}