<?php
namespace srag\Plugins\FlashcardQuestions\Config;
use ilFlashcardQuestionsPlugin;
use ILIAS\FileUpload\Location;
use ilImageFileInputGUI;
use ilWACSignedPath;
use srag\ActiveRecordConfig\FlashcardQuestions\ActiveRecordConfigFormGUI;
use \ilUtil;

/**
 * Class ConfigFormGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ConfigFormGUI extends ActiveRecordConfigFormGUI {

    const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;
    const CONFIG_CLASS_NAME = Config::class;

    const DIR_LOGO = 'xfcq/logo';

    protected function initFields() {
        $this->fields = [
            Config::C_REPORT_LOGO => [
                self::PROPERTY_CLASS => ilImageFileInputGUI::class,
            ]
        ];
    }

    protected function storeValue($key, $value) {
        switch ($key) {
            case CONFIG::C_REPORT_LOGO:
                self::dic()->upload()->process();
                if (self::dic()->filesystem()->web()->hasDir(self::DIR_LOGO)) {
                    self::dic()->filesystem()->web()->deleteDir(self::DIR_LOGO);
                }
                $result = array_shift(self::dic()->upload()->getResults());
                self::dic()->upload()->moveOneFileTo($result, self::DIR_LOGO, Location::STORAGE);
        }
    }

    protected function getValue($key) {
        switch ($key) {
            case CONFIG::C_REPORT_LOGO:
                if (!self::dic()->filesystem()->web()->hasDir(self::DIR_LOGO)) {
                    return '';
                }
                $contents = self::dic()->filesystem()->web()->listContents(self::DIR_LOGO);
                return empty($contents) ? '' : ilWACSignedPath::signFile(ILIAS_DATA_DIR . '/' . CLIENT_ID . '/' . $contents[0]->getPath());
        }
    }


}