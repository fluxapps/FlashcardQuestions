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

    /**
     * @var array
     */
    protected static $fields = [
        self::C_REPORT_LOGO => self::TYPE_INTEGER
    ];

}