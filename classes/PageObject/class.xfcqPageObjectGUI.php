<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\FlashcardQuestions\DICTrait;

/**
 * Class xfcqPageObjectGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xfcqPageObjectGUI: xfcqQuestionGUI, ilObjFlashcardQuestionsGUI
 * @ilCtrl_Calls      xfcqPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls      xfcqPageObjectGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 *
 */
class xfcqPageObjectGUI extends ilPageObjectGUI {

	use DICTrait;


	/**
	 * xfcqPageObjectGUI constructor
	 *
	 * @param $page_id
	 * @param $obj_id
	 */
	public function __construct($page_id, $obj_id) {
		parent::__construct(xfcqPageObject::PARENT_TYPE, $page_id);

		// content style
		self::dic()->ui()->mainTemplate()->setCurrentBlock("SyntaxStyle");
		self::dic()->ui()->mainTemplate()->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
		self::dic()->ui()->mainTemplate()->parseCurrentBlock();

		self::dic()->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
		self::dic()->ui()->mainTemplate()
			->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(ilObjStyleSheet::lookupObjectStyle($obj_id)));
		self::dic()->ui()->mainTemplate()->parseCurrentBlock();
	}
}
