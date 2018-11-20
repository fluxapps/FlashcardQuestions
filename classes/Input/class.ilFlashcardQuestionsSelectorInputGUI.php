<?php

/**
 * Class ilFlashcardQuestionsSelectorInputGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilFlashcardQuestionsSelectorInputGUI: ilFormPropertyDispatchGUI
 */
class ilFlashcardQuestionsSelectorInputGUI extends ilRepositorySelectorInputGUI {

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setClickableTypes(array('xfcq'));
    }

    /**
     * Render item
     * (modified class name in links and respect disabled status)
     */
    function render($a_mode = "property_form")
    {
        global $lng, $ilCtrl, $ilObjDataCache, $tree;

        // modification:
        $tpl = new ilTemplate("tpl.prop_xfcq_select.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/FlashcardQuestions");
        // modification.

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));

        // modification:
        if (!$this->getDisabled())
        {
            switch ($a_mode)
            {
                case "property_form":
                    $parent_gui = ilPropertyFormGUI::class;
                    break;

                case "table_filter":
                    $parent_gui = get_class($this->getParent());
                    break;
            }

            $ilCtrl->setParameterByClass(self::class,
                "postvar", $this->getPostVar());

            $tpl->setVariable("TXT_SELECT", $this->getSelectText());
            $tpl->setVariable("HREF_SELECT",
                $ilCtrl->getLinkTargetByClass(array($parent_gui, ilFormPropertyDispatchGUI::class, self::class),
                    "showRepositorySelection"));
            if ($this->getValue() > 0)
            {
                $tpl->setVariable("TXT_RESET", $lng->txt("reset"));
                $tpl->setVariable("HREF_RESET",
                    $ilCtrl->getLinkTargetByClass(array($parent_gui, ilFormPropertyDispatchGUI::class, self::class),
                        "reset"));

            }
        }
        // modification.

        if ($this->getValue() > 0 && $this->getValue() != ROOT_FOLDER_ID)
        {
            // modification:
            require_once("Services/Locator/classes/class.ilLocatorGUI.php");
            $loc_gui = new ilLocatorGUI();
            $loc_gui->addContextItems($this->getValue());
            $tpl->setVariable("TXT_ITEM", $loc_gui->getHTML());
            // modification.
        }
        else
        {
            $nd = $tree->getNodeData(ROOT_FOLDER_ID);
            $title = $nd["title"];
            if ($title == "ILIAS")
            {
                $title = $lng->txt("repository");
            }
            if (in_array($nd["type"], $this->getClickableTypes()))
            {
                $tpl->setVariable("TXT_ITEM", $title);
            }
        }
        return $tpl->get();
    }

}