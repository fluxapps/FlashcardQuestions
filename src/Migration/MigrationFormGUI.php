<?php

namespace srag\Plugins\FlashcardQuestions\GlossaryMigration;

use ilFlashcardQuestionsConfigGUI;
use ilTextInputGUI;
use srag\ActiveRecordConfig\FlashcardQuestions\ActiveRecordConfigFormGUI;
use ilFlashcardQuestionsPlugin;
use srag\Plugins\FlashcardQuestions\Config\Config;

/**
 * Class MigrationFormGUI
 * @package srag\Plugins\FlashcardQuestions\GlossaryMigration
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MigrationFormGUI extends ActiveRecordConfigFormGUI {

    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;
    const CONFIG_CLASS_NAME = Config::class;

    const F_TITLE_PATTERN = 'title_pattern';


    /**
     * @param string $key
     * @return mixed|void
     */
    protected function getValue($key) {
    }


    /**
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    protected function initCommands() {
        $this->addCommandButton(ilFlashcardQuestionsConfigGUI::CMD_CONFIRM_MIGRATE, self::plugin()->translate(ilFlashcardQuestionsConfigGUI::CMD_CONFIRM_MIGRATE));
    }

    /**
     *
     */
    protected function initFields() {
        $this->fields = [
            self::F_TITLE_PATTERN => [
                self::PROPERTY_CLASS => ilTextInputGUI::class,
                self::PROPERTY_REQUIRED => true
            ]
        ];
    }

    /**
     *
     */
    protected function initId() {
    }

    /**
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    protected function initTitle() {
        $this->setTitle(self::plugin()->translate('config_migration'));
    }

    /**
     * @param $key
     * @param $value
     * @return mixed|void
     */
    protected function storeValue($key, $value) {
    }

}