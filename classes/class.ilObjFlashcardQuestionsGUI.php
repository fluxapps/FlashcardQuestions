<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\FlashcardQuestions\DICTrait;
use srag\DIC\FlashcardQuestions\Exception\DICException;
use srag\Plugins\FlashcardQuestions\GlossaryMigration\GlossaryMigration;
use srag\Plugins\FlashcardQuestions\Object\ObjSettingsFormGUI;

/**
 * Class ilObjFlashcardQuestionsGUI
 *
 * Generated by srag\PluginGenerator v0.7.2
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjFlashcardQuestionsGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjFlashcardQuestionsGUI: ilObjPluginDispatchGUI
 * @ilCtrl_isCalledBy ilObjFlashcardQuestionsGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjFlashcardQuestionsGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjFlashcardQuestionsGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjFlashcardQuestionsGUI: ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjFlashcardQuestionsGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjFlashcardQuestionsGUI: ilObjTaxonomyGUI
 */
class ilObjFlashcardQuestionsGUI extends ilObjectPluginGUI {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilFlashcardQuestionsPlugin::class;
	const CMD_PERMISSIONS = "perm";
	const CMD_SETTINGS = "settings";
	const CMD_SETTINGS_STORE = "settingsStore";
	const CMD_SHOW_CONTENTS = "showContents";
	const CMD_MIGRATE = 'migrate';
	// TABS
	const TAB_PERMISSIONS = "perm_settings";
	const TAB_SETTINGS = "settings";
	const TAB_SHOW_CONTENTS = "showContent";
	const TAB_TAXONOMY = "taxonomy";
	const LANG_MODULE_OBJECT = "object";
	const LANG_MODULE_SETTINGS = "settings";
	/**
	 * Fix autocomplete (Defined in parent)
	 *
	 * @var ilObjFlashcardQuestions
	 */
	var $object;


	/**
	 *
	 */
	protected function afterConstructor()/*: void*/ {

	}


	/**
	 * @return string
	 */
	public final function getType() {
		return ilFlashcardQuestionsPlugin::PLUGIN_ID;
	}


	function executeCommand() {
		// TODO: remove after dev
		//	    $migration = new GlossaryMigrationWKV();
		//	    $migration->run();

		// this is a bit hacky but has to be done, since the 'save' cmd is caught up in the parent::executeCommand()
		$cmd = self::dic()->ctrl()->getCmd();
		$next_class = self::dic()->ctrl()->getNextClass($this);
		if ($next_class == strtolower(ilObjTaxonomyGUI::class) && $cmd == 'save') {
			$this->performCommand($cmd);
		}

		return parent::executeCommand();
	}


	/**
	 * @param string $cmd
	 *
	 * @throws DICException
	 * @throws ilCtrlException
	 */
	public function performCommand($cmd)/*: void*/ {
		self::dic()->help()->setScreenIdComponent(ilFlashcardQuestionsPlugin::PLUGIN_ID);

		$next_class = self::dic()->ctrl()->getNextClass($this);
		$this->renderTitleAndDescription();

		switch (strtolower($next_class)) {
			case strtolower(ilObjTaxonomyGUI::class):
				self::dic()->tabs()->activateTab(self::TAB_TAXONOMY);
				switch ($cmd) {
					default:
						$ilObjTaxonomyGUI = new ilObjTaxonomyGUI();
						$ilObjTaxonomyGUI->setAssignedObject($this->object->getId());
						$ilObjTaxonomyGUI->setMultiple(true);
						$ilObjTaxonomyGUI->setFormAction(self::dic()->ctrl()->getFormActionByClass([
							self::class,
							ilObjTaxonomyGUI::class
						], 'saveTaxonomy'), 'saveTaxonomy');
						self::dic()->ctrl()->setReturn($this, self::TAB_TAXONOMY);
						self::dic()->ctrl()->forwardCommand($ilObjTaxonomyGUI);
						break;
				}
				break;
			case strtolower(xfcqContentGUI::class):
				self::dic()->tabs()->activateTab(self::TAB_SHOW_CONTENTS);
				$xfcqContentGUI = new xfcqContentGUI($this);
				self::dic()->ctrl()->forwardCommand($xfcqContentGUI);
				break;
			default:
				switch ($cmd) {
					case self::CMD_SHOW_CONTENTS:
						// Read commands
						if (!ilObjFlashcardQuestionsAccess::hasReadAccess()) {
							ilObjFlashcardQuestionsAccess::redirectNonAccess(ilRepositoryGUI::class);
						}

						$this->{$cmd}();
						break;

					case self::CMD_SETTINGS:
					case self::CMD_SETTINGS_STORE:
					case self::CMD_MIGRATE:
						// Write commands
						if (!ilObjFlashcardQuestionsAccess::hasWriteAccess()) {
							ilObjFlashcardQuestionsAccess::redirectNonAccess($this);
						}

						$this->{$cmd}();
						break;

					default:
						// Unknown command
						ilObjFlashcardQuestionsAccess::redirectNonAccess(ilRepositoryGUI::class);
						break;
				}
				break;
		}
	}


	/**
	 * @throws DICException
	 */
	protected function renderTitleAndDescription() {
		if (!self::dic()->ctrl()->isAsynch()) {
			self::dic()->ui()->mainTemplate()->setTitle($this->object->getTitle());

			self::dic()->ui()->mainTemplate()->setDescription($this->object->getDescription());

			if (!$this->object->isOnline()) {
				self::dic()->ui()->mainTemplate()->setAlertProperties([
					[
						"alert" => true,
						"property" => self::plugin()->translate("status", self::LANG_MODULE_OBJECT),
						"value" => self::plugin()->translate("offline", self::LANG_MODULE_OBJECT)
					]
				]);
			}
		}
	}


	/**
	 * @param string $a_new_type
	 *
	 * @return ilPropertyFormGUI
	 */
	public function initCreateForm(/*string*/
		$a_new_type) {
		$form = parent::initCreateForm($a_new_type);

		return $form;
	}


	/**
	 *
	 */
	protected function showContents()/*: void*/ {
		self::dic()->ctrl()->redirectByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_STANDARD);
	}


	/**
	 * @return ObjSettingsFormGUI
	 */
	protected function getSettingsForm() {
		$form = new ObjSettingsFormGUI($this);

		return $form;
	}


	/**
	 *
	 */
	protected function settings()/*: void*/ {
		self::dic()->tabs()->activateTab(self::TAB_SETTINGS);

		$form = $this->getSettingsForm();

		self::output()->output($form);
	}


	/**
	 *
	 */
	protected function settingsStore()/*: void*/ {
		self::dic()->tabs()->activateTab(self::TAB_SETTINGS);

		$form = $this->getSettingsForm();

		$form->setValuesByPost();

		if (!$form->checkInput()) {
			self::output()->output($form);

			return;
		}

		$form->updateSettings();

		ilUtil::sendSuccess(self::plugin()->translate("saved", self::LANG_MODULE_SETTINGS), true);

		self::output()->output($form);
	}


	/**
	 *
	 */
	protected function setTabs()/*: void*/ {
		self::dic()->tabs()->addTab(self::TAB_SHOW_CONTENTS, self::plugin()->translate("show_contents", self::LANG_MODULE_OBJECT), self::dic()->ctrl()
			->getLinkTargetByClass(xfcqContentGUI::class, xfcqContentGUI::CMD_STANDARD));

		if (ilObjFlashcardQuestionsAccess::hasWriteAccess()) {
			self::dic()->tabs()->addTab(self::TAB_SETTINGS, self::plugin()->translate("settings", self::LANG_MODULE_SETTINGS), self::dic()->ctrl()
				->getLinkTarget($this, self::CMD_SETTINGS));
		}

		if (ilObjFlashcardQuestionsAccess::hasEditPermissionAccess()) {
			self::dic()->tabs()->addTab(self::TAB_PERMISSIONS, self::plugin()->translate(self::TAB_PERMISSIONS, "", [], false), self::dic()->ctrl()
				->getLinkTargetByClass([
					self::class,
					ilPermissionGUI::class,
				], self::CMD_PERMISSIONS));
		}

		if (ilObjFlashcardQuestionsAccess::hasWriteAccess()) {
			self::dic()->tabs()->addTab(self::TAB_TAXONOMY, self::plugin()->translate("taxonomy", self::LANG_MODULE_OBJECT)
				. ilGlyphGUI::get('next'), self::dic()->ctrl()->getLinkTargetByClass(ilObjTaxonomyGUI::class, 'listTaxonomies'));
		}

		self::dic()->tabs()->manual_activation = true; // Show all tabs as links when no activation
	}


	/**
	 * @return string
	 */
	public static function getStartCmd() {
		return self::CMD_SHOW_CONTENTS;
	}


	/**
	 * @return string
	 */
	public function getAfterCreationCmd() {
		return self::getStartCmd();
	}


	/**
	 * @return string
	 */
	public function getStandardCmd() {
		return self::getStartCmd();
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->obj_id;
	}


	/**
	 * @return ilObjFlashcardQuestions
	 */
	public function getObject() {
		return $this->object;
	}


	/**
	 *
	 */
	protected function migrate() {
		// TODO: start migration via config
		$migration = new GlossaryMigration();
		$migration->run();
	}
}
