<?php
namespace srag\Plugins\FlashcardQuestions\Config;
use ilFlashcardQuestionsPlugin;
use ILIAS\FileUpload\Location;
use ilImageFileInputGUI;
use ilMediaItem;
use ilMimeTypeUtil;
use ilObjFile;
use ilObjMediaObject;
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

    /**
     *
     */
    protected function initFields() {
        $this->fields = [
            Config::C_REPORT_LOGO => [
                self::PROPERTY_CLASS => ilImageFileInputGUI::class,
            ]
        ];
    }

    /**
     * @param $key
     * @param $value
     * @return mixed|void
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \srag\ActiveRecordConfig\FlashcardQuestions\Exception\ActiveRecordConfigException
     * @throws \srag\DIC\FlashcardQuestions\Exception\DICException
     */
    protected function storeValue($key, $value) {
        switch ($key) {
            case CONFIG::C_REPORT_LOGO:
                self::dic()->upload()->process();

                $file_obj = new ilObjFile();
                $file_obj->setType("file");
                $file_obj->setTitle($_FILES[Config::C_REPORT_LOGO]["name"]);
                $file_obj->setFileName($_FILES[Config::C_REPORT_LOGO]["name"]);
                $file_obj->setFileType(ilMimeTypeUtil::getMimeType("", $_FILES[Config::C_REPORT_LOGO]["name"], $_FILES[Config::C_REPORT_LOGO]["type"]));
                $file_obj->setFileSize($_FILES[Config::C_REPORT_LOGO]["size"]);
                $file_obj->create();

                $file_obj->getUploadFile($_FILES[Config::C_REPORT_LOGO]["tmp_name"], $_FILES[Config::C_REPORT_LOGO]["name"]);

                Config::setField(Config::C_REPORT_LOGO, $file_obj->getId());
        }
    }

    /**
     * @param $key
     * @return mixed|string
     * @throws \srag\ActiveRecordConfig\FlashcardQuestions\Exception\ActiveRecordConfigException
     */
    protected function getValue($key) {
        switch ($key) {
            case CONFIG::C_REPORT_LOGO:
               $file_obj_id = Config::getField(Config::C_REPORT_LOGO);
               if ($file_obj_id) {
//                   $this->getItemByPostVar(Config::C_REPORT_LOGO)->setValue(ilObjFile::_lookupTitle($file_obj_id));
                   return ilObjFile::_lookupAbsolutePath($file_obj_id);
               }
               return '';
        }
    }


}