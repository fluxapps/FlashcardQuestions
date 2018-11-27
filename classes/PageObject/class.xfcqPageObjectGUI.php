<?php

use srag\DIC\DICTrait;

/**
 * Class xfcqPageObjectGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xfcqPageObjectGUI: xfcqQuestionGUI
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
		require_once "Services/Style/Content/classes/class.ilObjStyleSheet.php";

		self::dic()->ui()->mainTemplate()->setCurrentBlock("SyntaxStyle");
		self::dic()->ui()->mainTemplate()->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
		self::dic()->ui()->mainTemplate()->parseCurrentBlock();

		self::dic()->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
		self::dic()->ui()->mainTemplate()
			->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(ilObjStyleSheet::lookupObjectStyle($obj_id)));
		self::dic()->ui()->mainTemplate()->parseCurrentBlock();
	}
}
