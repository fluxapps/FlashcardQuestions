<?php

/**
 * Class xfcqPageObjectConfig
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xfcqPageObjectConfig extends ilPageConfig {

	/**
	 * Init
	 */
	function init() {
		// config
		$this->setPreventHTMLUnmasking(true);
		$this->setEnableInternalLinks(false);
		$this->setEnableWikiLinks(false);
		$this->setEnableActivation(false);
	}
}
