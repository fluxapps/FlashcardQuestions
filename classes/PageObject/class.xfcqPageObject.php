<?php

/**
 * Class xfcqPageObject
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqPageObject extends ilPageObject {

	const PARENT_TYPE = ilFlashcardQuestionsPlugin::PLUGIN_ID;


	function getParentType() {
		return self::PARENT_TYPE;
	}
}
