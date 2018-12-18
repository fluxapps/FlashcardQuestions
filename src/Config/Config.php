<?php
namespace srag\Plugins\FlashcardQuestions\Config;
use srag\ActiveRecordConfig\FlashcardQuestions\ActiveRecordConfig;

/**
 * Class Config
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class Config extends ActiveRecordConfig {

    const TABLE_NAME = 'xfcq_config';

    const C_REPORT_LOGO = 'report_logo';
    const C_MAX_IMG_WIDTH = 'max_img_width';

    /**
     * @var array
     */
    protected static $fields = [
        self::C_REPORT_LOGO => self::TYPE_INTEGER,
        self::C_MAX_IMG_WIDTH => self::TYPE_INTEGER
    ];

    protected static function getDefaultValue($name, $type, $default_value) {
        switch ($name) {
            case self::C_MAX_IMG_WIDTH:
                return 500;
            default:
                return $default_value;
        }
    }


}