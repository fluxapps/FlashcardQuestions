<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\FlashcardQuestions\DICTrait;

/**
 * Class ilObjFlashcardQuestionsListGUI
 *
 * Generated by srag\PluginGenerator v0.7.2
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ilObjFlashcardQuestionsListGUI extends ilObjectPluginListGUI {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;


	/**
	 * ilObjFlashcardQuestionsListGUI constructor
	 *
	 * @param int $a_context
	 */
	public function __construct(int $a_context = self::CONTEXT_REPOSITORY) {
		parent::__construct($a_context);
	}


	/**
	 * @return string
	 */
	public function getGuiClass() {
		return ilObjFlashcardQuestionsGUI::class;
	}


	/**
	 * @return array
	 */
	public function initCommands() {
		$this->commands_enabled = true;
		$this->copy_enabled = true;
		$this->cut_enabled = true;
		$this->delete_enabled = true;
		$this->description_enabled = true;
		$this->notice_properties_enabled = true;
		$this->properties_enabled = true;

		$this->comments_enabled = false;
		$this->comments_settings_enabled = false;
		$this->expand_enabled = false;
		$this->info_screen_enabled = false;
		$this->link_enabled = false;
		$this->notes_enabled = false;
		$this->payment_enabled = false;
		$this->preconditions_enabled = false;
		$this->rating_enabled = false;
		$this->rating_categories_enabled = false;
		$this->repository_transfer_enabled = false;
		$this->search_fragment_enabled = false;
		$this->static_link_enabled = false;
		$this->subscribe_enabled = false;
		$this->tags_enabled = false;
		$this->timings_enabled = false;

		$commands = [
			[
				"permission" => "read",
				"cmd" => ilObjFlashcardQuestionsGUI::getStartCmd(),
				"default" => true,
			]
		];

		return $commands;
	}


	/**
	 * @return array
	 */
	public function getProperties() {
		$props = [];

		if (ilObjFlashcardQuestionsAccess::_isOffline($this->obj_id)) {
			$props[] = [
				"alert" => true,
				"property" => self::plugin()->translate("status", ilObjFlashcardQuestionsGUI::LANG_MODULE_OBJECT),
				"value" => self::plugin()->translate("offline", ilObjFlashcardQuestionsGUI::LANG_MODULE_OBJECT)
			];
		}

		return $props;
	}


	/**
	 *
	 */
	public function initType()/*: void*/ {
		$this->setType(ilFlashcardQuestionsPlugin::PLUGIN_ID);
	}
}
