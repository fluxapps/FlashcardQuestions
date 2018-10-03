<?php
/**
 * Class xfcqPageObjectGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xfcqPageObjectGUI: xfcqQuestionGUI
 * @ilCtrl_Calls xfcqPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls xfcqPageObjectGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 *
 */
class xfcqPageObjectGUI extends ilPageObjectGUI {

    /**
     * xfcqPageObjectGUI constructor.
     * @param xfcqQuestionGUI $parent_gui
     * @param $page_id
     */
    public function __construct(xfcqQuestionGUI $parent_gui, $page_id) {
        parent::__construct(xfcqPageObject::PARENT_TYPE, $page_id);

        // content style
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");

        global $DIC;
        $tpl = $DIC["tpl"];
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(ilObjStyleSheet::lookupObjectStyle($parent_gui->getObjId())));
        $tpl->parseCurrentBlock();
    }

}