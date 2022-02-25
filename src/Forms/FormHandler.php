<?php

namespace A2billing\Forms;

use A2billing\Logger;
use A2billing\Table;
use Profiler_Console as Console;

/***************************************************************************
 *
 * Class.FormHandler.php : FormHandler - PHP : Handle, Form Generator (FG) for A2Billing
 * Written for PHP 4.x & PHP 5.X versions.
 *
 * A2Billing -- Billing solution for use with Asterisk(tm).
 * Copyright (C) 2004, 2009 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.a2billing.org for more information about
 * the A2Billing project.
 * Please submit bug reports, patches, etc to <areski _atl_ gmail com>
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/
class FormHandler
{
    private static $Instance;
    public $_action = '';
    public $_vars = null;
    public $_processed = [];
    public $DBHandle;
    public $VALID_SQL_REG_EXP = true;
    public $RESULT_QUERY = false;

    /* CONFIG THE VIEWER : CV */
    public $CV_NO_FIELDS = "THERE IS NO RECORD !";
    public $CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = true;
    public $CV_TITLE_TEXT = '';
    public $CV_TEXT_TITLE_ABOVE_TABLE = "DIRECTORY";
    public $CV_DISPLAY_FILTER_ABOVE_TABLE = true;
    public $CV_FILTER_ABOVE_TABLE_PARAM = "?id=";
    public $CV_FOLLOWPARAMETERS = '';
    public $CV_DO_ARCHIVE_ALL = false;


    public $CV_DISPLAY_RECORD_LIMIT = true;
    public $CV_DISPLAY_BROWSE_PAGE = true;

    public $CV_CURRENT_PAGE = 0;

    public $FG_VIEW_TABLE_WITDH = '100%';
    public $FG_ACTION_SIZE_COLUMN = '25%';
    /**
     * Sets the debug output (1 = low, 2 = Normal, 3 = High). Default value is "0" .
     *
     * @public    -    @type integer
     */
    public $FG_DEBUG = 0;

    /**
     * Sets the table name.
     *
     * @public    -    @type string
     */
    public $FG_TABLE_NAME = "";

    /**
     * Sets the table name used for count.
     *
     * @public    -    @type string
     */
    public $FG_TABLE_NAME_COUNT = "";


    /**
     * Sets the instance_name, used to descripbe the name of the element your are managing
     *
     * @public    -    @type string
     */
    public $FG_INSTANCE_NAME = "";

    /**
     * Sets the main clause - Clause to execute on the table
     *
     * @public    -    @type string
     */
    public $FG_TABLE_CLAUSE = "";

    /**
     * Sets the table list you will need to feed the SELECT from element
     *
     * @public    -    @type array - ( String to display, value to save)
     */
    public $tablelist = [];

    /**
     * ARRAY with the list of element to display in the ViewData page
     *
     * @public    -    @type array
     */
    public $FG_TABLE_COL = [];

    /**
     * Sets the fieldname of the SQL query to display in the ViewData page, ie: "id, name, mail"
     *
     * @public    -    @type string
     */
    public $FG_COL_QUERY = "";

    /**
     * Keep the number of column  -  Number of column in the html table
     *
     * @public    -    @type integer
     */
    public $FG_NB_TABLE_COL = 0;
    public $FG_TOTAL_TABLE_COL = 0;


    /**
     * Keep the ID of the table
     *
     * @public    -    @type string
     */
    public $FG_TABLE_ID = 'id';

    /**
     * Sets if we want a colum "ACTION" to EDIT or to DELETE
     *
     * @public    -    @type boolean
     */
    public $FG_ADDITION = false;
    public $FG_DELETION = false;
    public $FG_INFO = false;
    public $FG_EDITION = false;
    public $FG_OTHER_BUTTON1 = false;
    public $FG_OTHER_BUTTON2 = false;
    public $FG_OTHER_BUTTON3 = false;
    public $FG_OTHER_BUTTON4 = false;
    public $FG_OTHER_BUTTON5 = false;


    /**
     * Keep the link for the action (EDIT & DELETE)
     *
     * @public    -    @type string
     */
    public $FG_EDITION_LINK = '';
    public $FG_DELETION_LINK = '';
    public $FG_DELETION_FORBIDDEN_ID = [];
    public $FG_INFO_LINK = '';
    public $FG_OTHER_BUTTON1_LINK = '';
    public $FG_OTHER_BUTTON2_LINK = '';
    public $FG_OTHER_BUTTON3_LINK = '';
    public $FG_OTHER_BUTTON4_LINK = '';
    public $FG_OTHER_BUTTON5_LINK = '';

    public $FG_OTHER_BUTTON1_IMG = '';
    public $FG_OTHER_BUTTON2_IMG = '';
    public $FG_OTHER_BUTTON3_IMG = '';
    public $FG_OTHER_BUTTON4_IMG = '';
    public $FG_OTHER_BUTTON5_IMG = '';

    public $FG_ADD_PAGE_CONFIRM_BUTTON = '';

    /**
     * Sets the number of record to show by page
     *
     * @public    -    @type integer
     */
    public $FG_LIMITE_DISPLAY = 10;
    public $SQL_GROUP = null;

    /**
     * Sets the variable to control the View Module
     *
     * @public    -    @type integer
     */
    public $FG_CURRENT_PAGE = 0;
    public $FG_ORDER = '';
    public $FG_SENS = '';

    public $FG_NB_RECORD_MAX = 0;
    public $FG_NB_RECORD = 0;

    /**
     * Sets the variables to control the Apply filter
     *
     * @public  - @type string
     */
    public $FG_FILTER_FORM_ACTION = 'list';

    public $FG_FILTER_APPLY = false;
    public $FG_FILTERTYPE = 'INPUT'; // INPUT :: SELECT :: POPUPVALUE
    public $FG_FILTERFIELD = '';
    public $FG_FILTERFIELDNAME = '';
    public $FG_FILTERPOPUP = [
        'CC_entity_card.php?popup_select=1&', ", 'CardNumberSelection','width=550,height=350,top=20,left=100'",
    ];

    // SECOND FILTER
    public $FG_FILTER_APPLY2 = false;
    public $FG_FILTERTYPE2 = 'INPUT'; // INPUT :: SELECT :: POPUPVALUE
    public $FG_FILTERFIELD2 = '';
    public $FG_FILTERFIELDNAME2 = '';
    public $FG_FILTERPOPUP2 = [];


    /**
     * Sets the variables to control the search filter
     *
     * @public  - @type boolean , array , string
     */
    public $FG_FILTER_SEARCH_FORM = false;

    public $FG_FILTER_SEARCH_1_TIME = false;
    public $FG_FILTER_SEARCH_1_TIME_TEXT = '';
    public $FG_FILTER_SEARCH_1_TIME_FIELD = 'creationdate';

    public $FG_FILTER_SEARCH_1_TIME_BIS = false;
    public $FG_FILTER_SEARCH_1_TIME_TEXT_BIS = '';
    public $FG_FILTER_SEARCH_1_TIME_FIELD_BIS = '';

    public $FG_FILTER_SEARCH_3_TIME = false;
    public $FG_FILTER_SEARCH_3_TIME_TEXT = '';
    public $FG_FILTER_SEARCH_3_TIME_FIELD = 'creationdate';

    public $FG_FILTER_SEARCH_FORM_1C = [];
    public $FG_FILTER_SEARCH_FORM_2C = [];
    public $FG_FILTER_SEARCH_FORM_SELECT = [];
    public $FG_FILTER_SEARCH_FORM_SELECT_TEXT = '';
    public $FG_FILTER_SEARCH_TOP_TEXT = "";
    public $FG_FILTER_SEARCH_SESSION_NAME = '';
    public $FG_FILTER_SEARCH_DELETE_ALL = true;


    /**
     * Sets the variable to define if we want a splitable field into the form
     *
     * @public  - @type void , string (fieldname)
     * ie : the value of a splitable field might be something like 12-14 or 15;16;17 and it will make multiple insert
     * according to the values/ranges defined.
     */
    public $FG_SPLITABLE_FIELD = '';

    /**
     * Sets the variables to control the CSV export
     *
     * @public  - @type boolean
     */
    public $FG_EXPORT_CSV = false;
    public $FG_EXPORT_XML = false;
    public $FG_EXPORT_SESSION_VAR = '';

    /**
     * Sets the fieldname of the SQL query for Export e.g:name, mail"
     *
     * @public    -    @type string
     */
    public $FG_EXPORT_FIELD_LIST = "";

    /**
     * Sets the TEXT to display above the records displayed
     *
     * @public   -  @string
     */
    public $FG_INTRO_TEXT = "You can browse through our #FG_INSTANCE_NAME# and modify their different properties<br>";


    /**
     * Sets the ALT TEXT after mouse over the bouton
     *
     * @public   -  @string
     */

    public $FG_OTHER_BUTTON1_ALT = '';
    public $FG_OTHER_BUTTON2_ALT = '';
    public $FG_OTHER_BUTTON3_ALT = '';
    public $FG_OTHER_BUTTON4_ALT = '';
    public $FG_OTHER_BUTTON5_ALT = '';

    public $FG_OTHER_BUTTON1_HTML_CLASS = '';
    public $FG_OTHER_BUTTON2_HTML_CLASS = '';
    public $FG_OTHER_BUTTON3_HTML_CLASS = '';
    public $FG_OTHER_BUTTON4_HTML_CLASS = '';
    public $FG_OTHER_BUTTON5_HTML_CLASS = '';

    public $FG_OTHER_BUTTON1_HTML_ID = '';
    public $FG_OTHER_BUTTON2_HTML_ID = '';
    public $FG_OTHER_BUTTON3_HTML_ID = '';
    public $FG_OTHER_BUTTON4_HTML_ID = '';
    public $FG_OTHER_BUTTON5_HTML_ID = '';

    public $FG_OTHER_BUTTON1_CONDITION = '';
    public $FG_OTHER_BUTTON2_CONDITION = '';
    public $FG_OTHER_BUTTON3_CONDITION = '';
    public $FG_OTHER_BUTTON4_CONDITION = '';
    public $FG_OTHER_BUTTON5_CONDITION = '';

    public $FG_EDITION_CONDITION = '';
    public $FG_DELETION_CONDITION = '';

    //	-------------------- DATA FOR THE EDITION --------------------

    /**
     * ARRAY with the list of element to EDIT/REMOVE/ADD in the edit page
     *
     * @public    -    @type array
     */
    public $FG_TABLE_EDITION = [];
    public $FG_TABLE_ADITION = [];

    /**
     * ARRAY with the comment below each fields
     *
     * @public    -    @type array
     */
    public $FG_TABLE_COMMENT = [];

    /**
     * ARRAY with the regular expression to check the form
     *
     * @public    -    @type array
     */
    public $FG_regular = [];

    /**
     * Array that will contain the field where the regularexpression check have found errors
     *
     * @public    -    @type array
     */
    public $FG_fit_expression = [];

    /**
     * Set the fields  for the EDIT/ADD query
     *
     * @public    -    @type string
     */
    public $FG_QUERY_EDITION = '';
    public $FG_QUERY_ADITION = '';

    /**
     * Keep the number of the column into EDIT FORM
     *
     * @public    -    @type integer
     */
    public $FG_NB_TABLE_EDITION = 0;
    public $FG_NB_TABLE_ADITION = 0;


    /**
     * Set the SQL Clause for the edition
     *
     * @public    -    @type string
     */
    public $FG_EDITION_CLAUSE = " id='%id' ";

    /**
     * Set the HIDDED VALUE for the edition/addition
     * to insert some values that you do not want to display into the Form but as an hidden field
     * FG_QUERY_EDITION_HIDDEN_FIELDS = "field1, field2"
     * FG_QUERY_EDITION_HIDDEN_VALUE = "value1, value2"
     * FG_QUERY_ADITION_HIDDEN_FIELDS = "field1, field2"
     * FG_QUERY_ADITION_HIDDEN_VALUE = "value1, value2"
     *
     * @public    -    @type string
     */
    public $FG_QUERY_EDITION_HIDDEN_FIELDS = '';
    public $FG_QUERY_EDITION_HIDDEN_VALUE = '';
    public $FG_QUERY_ADITION_HIDDEN_FIELDS = '';
    public $FG_QUERY_ADITION_HIDDEN_VALUE = '';

    public $FG_EDITION_HIDDEN_PARAM = '';
    public $FG_EDITION_HIDDEN_PARAM_VALUE = '';
    public $FG_ADITION_HIDDEN_PARAM = '';
    public $FG_ADITION_HIDDEN_PARAM_VALUE = '';

    /**
     * Set the EXTRA HIDDED VALUES for the edition/addition
     *
     * @public    -    @type array
     */
    public $FG_QUERY_EXTRA_HIDDED = '';

    /**
     * Sets the link where to go after an ACTION (EDIT/DELETE/ADD)
     *
     * @public    -    @type string
     */
    public $FG_GO_LINK_AFTER_ACTION;
    public $FG_GO_LINK_AFTER_ACTION_ADD;
    public $FG_GO_LINK_AFTER_ACTION_DELETE;
    public $FG_GO_LINK_AFTER_ACTION_EDIT;


    /** ####################################################
     * if yes that allow your form to edit the form after added succesfully a instance
     * in the case if you don't have the same option in the edition and the adding option
     *
     * @public   -  @string
     */

    public $FG_ADITION_GO_EDITION = "no";

    public $FG_ADITION_GO_EDITION_MESSAGE = "The document has been created correctly. Now, you can define the different tariff that you want to associate.";


    // ------------------- ## MESSAGE SECTION  ## -------------------

    public $FG_INTRO_TEXT_EDITION = "You can modify, through the following form, the different properties of your #FG_INSTANCE_NAME#<br>";

    public $FG_INTRO_TEXT_ASK_DELETION = "If you really want remove this #FG_INSTANCE_NAME#, click on the delete button.";

    public $FG_INTRO_TEXT_DELETION = "A #FG_INSTANCE_NAME# has been deleted!";

    public $FG_INTRO_TEXT_ADD = "you can add easily a new #FG_INSTANCE_NAME#.<br>Fill the following fields and confirm by clicking on the button add.";

    public $FG_INTRO_TEXT_ADITION = "Add a \"#FG_INSTANCE_NAME#\" now.";

    public $FG_TEXT_ADITION_CONFIRMATION = "Your new #FG_INSTANCE_NAME# has been inserted. <br>";

    public $FG_TEXT_ADITION_ERROR = '<font color="Red"> Your new #FG_INSTANCE_NAME# has not been inserted. </font><br> ';

    public $FG_TEXT_ERROR_DUPLICATION = "You cannot choose more than one !";


    // ------------------- ## BUTTON/IMAGE SECTION  ## -------------------
    public $FG_BUTTON_ADITION_SRC = "Images_Path/en/continue_boton.gif";
    public $FG_BUTTON_EDITION_SRC = "Images_Path/en/continue_boton.gif";

    public $FG_BUTTON_ADITION_BOTTOM_TEXT = "";

    public $FG_BUTTON_EDITION_BOTTOM_TEXT = "";

    public $FG_ADDITIONAL_FUNCTION_BEFORE_ADD = '';
    public $FG_ADDITIONAL_FUNCTION_AFTER_ADD = '';
    public $FG_ADDITIONAL_FUNCTION_BEFORE_DELETE = '';
    public $FG_ADDITIONAL_FUNCTION_AFTER_DELETE = '';
    public $FG_ADDITIONAL_FUNCTION_BEFORE_EDITION = '';
    public $FG_ADDITIONAL_FUNCTION_AFTER_EDITION = '';

    public $FG_TABLE_ALTERNATE_ROW_COLOR = [];

    public $FG_TABLE_DEFAULT_ORDER = "id";
    public $FG_TABLE_DEFAULT_SENS = "ASC";

    // Delete Foreign Keys or not
    // if it is set to true and confirm flag is true confirm box will be showed.
    public $FG_FK_DELETE_ALLOWED = false;

    // Foreign Key Tables
    public $FG_FK_TABLENAMES = [];

    //Foreign Key Field Names
    public $FG_FK_EDITION_CLAUSE = [];

    //Foreign Key Delete Message Display, it will display the confirm delete dialog if there is some
    //some detail table exists. depends on the values of FG_FK_DELETE_ALLOWED
    public $FG_FK_DELETE_CONFIRM = false;

    //Foreign Key Records Count
    public $FG_FK_RECORDS_COUNT = 0;

    //Foreign Key Exists so Warn only not to delete ,,Boolean
    public $FG_FK_WARNONLY = false;

    //is Child Records exists
    public $FG_ISCHILDS = true;

    // Delete Message for FK
    public $FG_FK_DELETE_MESSAGE = "Are you sure to delete all records connected to this instance.";

    //To enable Disable Selection List
    public $FG_DISPLAY_SELECT = false;

    //Selection List Field Name to get from Database
    public $FG_SELECT_FIELDNAME = "";

    // Configuration Key value Field Name
    public $FG_CONF_VALUE_FIELDNAME = "";

    //*****************************
    //This variable define the width of the HTML table
    public $FG_HTML_TABLE_WIDTH = "95%";

    // text for multi-page navigation.
    public $lang = [
        'strfirst' => '&lt;&lt; First', 'strprev' => '&lt; Prev', 'strnext' => 'Next &gt;',
        'strlast' => 'Last &gt;&gt;',
    ];

    public $logger = null;

    public $FG_ENABLE_LOG = ENABLE_LOG;

    // CSRF TOKEN
    public $FG_CSRF_STATUS = true;
    public $FG_CSRF_TOKEN_SALT = CSRF_SALT;
    public $FG_CSRF_TOKEN_KEY = null;
    public $FG_CSRF_TOKEN = null;
    public $FG_CSRF_FIELD = 'csrf_token';
    public $FG_FORM_UNIQID_FIELD = 'form_id';


    // ----------------------------------------------
    // CLASS CONSTRUCTOR : FormHandler
    //	@public
    //	@returns void
    //	@ $tablename + $instance_name
    // ----------------------------------------------
    public $Host;
    /**
     * @var string
     */
    public $FG_FORM_UNIQID;
    /**
     * @var mixed
     */
    public $FG_FORM_RECEIVED_UNIQID;
    /**
     * @var mixed
     */
    public $FG_FORM_RECEIVED_TOKEN;
    public $FG_CLAUSE;
    public $FG_FILTER_SEARCH_3_TIME_FIELD_BIS;
    /**
     * @var mixed
     */
    public $FG_CSRF_RECEIVED_TOKEN;
    public $FG_CSRF_RECEIVED_FIELD;
    /**
     * @var bool
     */
    private $alarm_db_error_duplication;

    public function __construct($tablename = null, $instance_name = null, $action = null, $tablename_count = null)
    {
        Console::log('Construct FormHandler');
        Console::logMemory($this, 'FormHandler Class : Line ' . __LINE__);
        Console::logSpeed('FormHandler Class : Line ' . __LINE__);
        self:: $Instance = $this;
        $this->FG_TABLE_NAME = $tablename;
        $this->FG_INSTANCE_NAME = $instance_name;
        $this->FG_TABLE_NAME_COUNT = $tablename_count;

        if ($this->FG_DEBUG) {
            echo $this->Host;
        }

        $this->set_regular_expression();

        $this->_action = $action ?: filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

        // If anti CSRF protection is enabled
        if ($this->FG_CSRF_STATUS == true) {
            // Initializing anti csrf token (Generate a key, concat it with salt and hash it)
            $this->FG_CSRF_TOKEN_KEY = $this->genCsrfTokenKey();
            $this->FG_CSRF_TOKEN = $this->FG_CSRF_TOKEN_SALT . $this->FG_CSRF_TOKEN_KEY;
            $this->FG_CSRF_TOKEN = hash('SHA256', $this->FG_CSRF_TOKEN);
            $this->FG_FORM_UNIQID = uniqid();
            // print $this -> FG_FORM_UNIQID;
            // echo "<br/>------_POST-------<br/>";
            // print_r($_POST);
            // echo "<br/>-------_SESSION------<br/>";
            // print_r($_SESSION);

            $this->FG_FORM_RECEIVED_UNIQID = $_POST[$this->FG_FORM_UNIQID_FIELD];
            $this->FG_FORM_RECEIVED_TOKEN = $_POST[$this->FG_CSRF_FIELD];
            $this->FG_CSRF_RECEIVED_TOKEN = $_SESSION['CSRF_TOKEN'][$this->FG_FORM_RECEIVED_UNIQID];
            $_SESSION['CSRF_TOKEN'][$this->FG_FORM_UNIQID] = $this->FG_CSRF_TOKEN;
            // echo "<br/>------_SESSION::-------<br/>";
            // print_r($_SESSION);

            if ($this->FG_DEBUG) {
                echo 'FG_FORM_UNIQID : ' . $this->FG_FORM_UNIQID . '<br />';
                echo 'CSRF NEW TOKEN : ' . $this->FG_CSRF_TOKEN . '<br />';
                echo 'CSRF RECEIVED TOKEN : ' . $this->FG_CSRF_RECEIVED_TOKEN . '<br />';
            }
            if (!empty($_POST)) {
                // Check CSRF
                if (!$this->FG_CSRF_RECEIVED_TOKEN or
                    ($this->FG_CSRF_RECEIVED_TOKEN != $this->FG_FORM_RECEIVED_TOKEN)) {
                    echo "CSRF Error!";
                    exit();
                } else {
                    //Remove key from the session
                    // echo "Remove key from the session";
                    unset($_SESSION['CSRF_TOKEN'][$this->FG_FORM_RECEIVED_UNIQID]);
                }
            }
        }

        $this->_vars = array_merge($_GET, $_POST);

        $this->def_list();

        //initializing variables with gettext
        $this->CV_NO_FIELDS = gettext("No data found!");
        $this->CV_TEXT_TITLE_ABOVE_TABLE = gettext("DIRECTORY");
        $this->CV_TITLE_TEXT = $instance_name . ' ' . gettext("list");
        $this->FG_FILTER_SEARCH_TOP_TEXT = gettext("Define criteria to make a precise search");
        $this->FG_INTRO_TEXT = gettext("You can browse through our") . " #FG_INSTANCE_NAME# " . gettext("and modify their different properties") . '<br>';
        $this->FG_ADITION_GO_EDITION_MESSAGE = gettext("The document has been created correctly. Now, you can define the different tariff that you want to associate.");
        $this->FG_INTRO_TEXT_EDITION = gettext("You can modify, through the following form, the different properties of your") . " #FG_INSTANCE_NAME#" . '<br>';
        $this->FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this") . " #FG_INSTANCE_NAME#, " . gettext("Click on the delete button.");
        $this->FG_INTRO_TEXT_DELETION = gettext("One") . " #FG_INSTANCE_NAME# " . gettext("has been deleted!");

        $this->FG_INTRO_TEXT_ADD = gettext("you can add easily a new") . " #FG_INSTANCE_NAME#.<br>" . gettext("Fill the following fields and confirm by clicking on the button add.");
        $this->FG_INTRO_TEXT_ADITION = gettext("Add a") . " \"#FG_INSTANCE_NAME#\" " . gettext("now.");
        $this->FG_TEXT_ADITION_CONFIRMATION = gettext("Your new") . " #FG_INSTANCE_NAME# " . gettext("has been inserted." . '<br>');
        $this->FG_TEXT_ADITION_ERROR = '<font color="Red">' . gettext("Your new") . " #FG_INSTANCE_NAME# " . gettext("hasn't been inserted.") . '<br>' . "</font>";
        $this->FG_TEXT_ERROR_DUPLICATION = gettext("You cannot choose more than one !");

        $this->FG_FK_DELETE_MESSAGE = gettext("Are you sure to delete all records connected to this instance.");

        /* used once in admin/FG_var_signup.inc */
        $this->FG_ADD_PAGE_CONFIRM_BUTTON = gettext('Confirm Data');

        if ($this->FG_ENABLE_LOG == 1) {
            $this->logger = new Logger();
        }
    }


    /*
    * Generate a csrf token
    */
    private function genCsrfTokenKey(): string
    {
        $token1 = microtime();
        $token2 = uniqid(null, true);
        $token3 = session_id();
        $token4 = mt_rand();

        return base64_encode($token1 . $token2 . $token3 . $token4);
    }

    public static function GetInstance(): FormHandler
    {
        return self:: $Instance;
    }

    public function setDBHandler($DBHandle = null)
    {
        Console::log('FormHandler -> setDBHandler');
        Console::logMemory($this, 'FormHandler -> setDBHandler : Line ' . __LINE__);
        Console::logSpeed('FormHandler -> setDBHandler : Line ' . __LINE__);

        $this->DBHandle = $DBHandle;
    }

    /**
     * Perform the execution of some actions to prepare the form generation
     *
     * @public
     */
    public function init()
    {
        $processed = $this->getProcessed();

        Console::log('FormHandler -> init');
        Console::logMemory($this, 'FormHandler -> init : Line ' . __LINE__);
        Console::logSpeed('FormHandler -> init : Line ' . __LINE__);

        if ($processed['section'] != "") {
            $section = $processed['section'];
            $_SESSION["menu_section"] = intval($section);
        }
        $ext_link = '&current_page=' . $processed['current_page'] ?? ''
            . '&order=' . $processed['order'] ?? ''
            . '&sens=' . $processed['sens'] ?? '';
        $this->FG_EDITION_LINK = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) . "?form_action=ask-edit" . $ext_link . "&id=";
        $this->FG_DELETION_LINK = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) . "?form_action=ask-delete" . $ext_link . "&id=";

        $this->FG_INTRO_TEXT = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_INTRO_TEXT);
        $this->FG_INTRO_TEXT_EDITION = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_INTRO_TEXT_EDITION);
        $this->FG_INTRO_TEXT_ASK_DELETION = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_INTRO_TEXT_ASK_DELETION);
        $this->FG_INTRO_TEXT_DELETION = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_INTRO_TEXT_DELETION);
        $this->FG_INTRO_TEXT_ADD = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_INTRO_TEXT_ADD);
        $this->FG_INTRO_TEXT_ADITION = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_INTRO_TEXT_ADITION);
        $this->FG_TEXT_ADITION_CONFIRMATION = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_TEXT_ADITION_CONFIRMATION);
        $this->FG_TEXT_ADITION_ERROR = str_replace('#FG_INSTANCE_NAME#', $this->FG_INSTANCE_NAME, $this->FG_TEXT_ADITION_ERROR);
        $this->FG_FILTER_SEARCH_TOP_TEXT = gettext("Define criteria to make a precise search");

        $this->FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F2EE";
        $this->FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FCFBFB";

        $this->FG_TOTAL_TABLE_COL = $this->FG_NB_TABLE_COL;
        if ($this->FG_DELETION || $this->FG_INFO || $this->FG_EDITION || $this->FG_OTHER_BUTTON1 || $this->FG_OTHER_BUTTON2 || $this->FG_OTHER_BUTTON3 || $this->FG_OTHER_BUTTON4 || $this->FG_OTHER_BUTTON5) {
            $this->FG_TOTAL_TABLE_COL++;
        }
    }

    /**
     * Define the list
     *
     * @public
     */
    public function def_list()
    {
        Console::log('FormHandler -> def_list');
        Console::logMemory($this, 'FormHandler -> def_list : Line ' . __LINE__);
        Console::logSpeed('FormHandler -> def_list : Line ' . __LINE__);

        $this->tablelist['status_list']["1"] = [gettext("INSERTED"), "1"];
        $this->tablelist['status_list']["2"] = [gettext("ENABLE"), "2"];
        $this->tablelist['status_list']["3"] = [gettext("DISABLE"), "3"];
        $this->tablelist['status_list']["4"] = [gettext("FREE"), "4"];
    }

    public function &getProcessed(): array
    {
        foreach ($this->_vars as $key => $value) {
            if (!$this->_processed[$key] or empty($this->_processed[$key])) {
                $this->_processed[$key] = sanitize_data($value);
                if ($key == 'username') {
                    //rebuild the search parameter to filter character to format card number
                    $filtered_char = [" ", "-", "_", "(", ")", "+"];
                    $this->_processed[$key] = str_replace($filtered_char, "", $this->_processed[$key]);
                }
                if ($key == 'pwd_encoded' && !empty($value)) {
                    $this->_processed[$key] = hash("whirlpool", $this->_processed[$key]);
                }
            }
        }

        return $this->_processed;
    }

    public function sanitize_tag($input)
    {
        $search = [
            '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
            '@<[/!]*?[^<>]*?>@si',            // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
        ];

        return preg_replace($search, '', $input);
    }

    // ----------------------------------------------
    // RECIPIENT METHODS
    // ----------------------------------------------

    /**
     * Adds a "element" to the FG_TABLE_COL.  Returns void.
     *
     * @public
     * @ 1. $displayname
     * @ 2. $fieldname
     * @ 3. $colpercentage
     * @ 4. $textalign
     * @ 5 .$sort
     * @ 6. $char_limit
     * @ 7. $lie_type ("lie", "list") , where lie is used for sql. ( TODO : any reason to keep lie instead of sql ?.)
     * @ 8. $lie_with (SQL query with the tag '%1' || a defined list: $tablelist["nbcode"] )
     * OLD
     * @ 8. $lie_with tablename
     * @ 9. $lie_fieldname
     * @ 10. $lie_clause
     * @ 11. $lie_display
     * @ 12. $function render
     */

    public function AddViewElement($displayname, $fieldname, $colpercentage, $textalign = 'center', $sort = 'sort', $char_limit = null, $lie_type = null, $lie_with = null, $lie_fieldname = null, $lie_clause = null, $lie_display = null, $myfunc = null, $link_file = null)
    {
        $cur = count($this->FG_TABLE_COL);

        $this->FG_TABLE_COL[$cur] = [
            $displayname, $fieldname, $colpercentage, $textalign, $sort, $char_limit, $lie_type, $lie_with,
            $lie_fieldname, $lie_clause, $lie_display, $myfunc, $link_file,
        ];

        $this->FG_NB_TABLE_COL = count($this->FG_TABLE_COL);
    }

    //----------------------------------------------------
    // Method to Add the Field which will be included in the export file
    //----------------------------------------------------
    /*
        Add Field to FG_EXPORT_COL array, Returns Void
        *fieldname is the Field Name which will be included in the export file

    */

    public function FieldExportElement($fieldname)
    {
        if (strlen($fieldname) > 0) {
            $this->FG_EXPORT_FIELD_LIST = $fieldname;
        }
    }


    /**
     * Sets Query fieldnames for the View module
     *
     * @public
     * @ $col_query    , option to append id ( by default )
     */

    public function FieldViewElement($fieldname, $add_id = 1)
    {
        $this->FG_COL_QUERY = $fieldname;
        // For each query we need to have the ID at the lenght FG_NB_TABLE_COL
        if ($add_id) {
            $this->FG_COL_QUERY .= ", " . $this->FG_TABLE_ID;
        }
    }

    // ----------------------------------------------
    // METHOD FOR THE EDITION
    // ----------------------------------------------

    /**
     * Adds a "element" to the FG_TABLE_EDITION array.  Returns void.
     *
     * @public
     * @.0 $displayname - name of the column for the current field
     * @.1 $fieldname - name of the field to edit
     * @.2 $defaultvalue - value of the field
     * @.3 $fieldtype - type of edition (INPUT / SELECT / TEXTAREA / RADIOBUTTON/ CHECKBOX/ SUBFORM /...)        ##
     * @.4 $fieldproperty - property of the field (ie: "size=6 maxlength=6")
     * @.5 $regexpr_nb the regexp number (check set_regular_expression function), used to this is this match with the value introduced
     * @.6 $error_message - set the error message
     * @.7 $type_selectfield - if the fieldtype = SELECT, set the type of field feed  (LIST or SQL)
     * @.8 $feed_selectfield - if the fieldtype = SELECT, [define a sql to feed it] OR [define a array to use]
     * @.9 $displayformat_selectfield - if the fieldtype = SELECT and fieldname of sql > 1 is useful to define the format to show the data (ie: "%1 : (%2)")
     * @.10 $config_radiobouttonfield - if the fieldtype = RADIOBUTTON : config format - valuename1 :value1, valuename2 :value2,...  (ie: "Yes:t,No:f")
     * @.12 $check_emptyvalue - ("no" or "yes") if "no" we we check the regularexpression only if a value has been entered
     * @.13 $attach2table - yes
     * @.14 $attach2table_conf - "doc_tariff:call_tariff_id:call_tariff:webm_retention, id, country_id:id IN (select call_tariff_id from doc_tariff where document_id = %id) AND cttype='PHONE':document_id:%1 - (%3):2:country:label, id:%1:id='%1'"
     * @.END $comment - set a comment to display below the field
     */

    /*
	// THE VARIABLE $FG_TABLE_EDITION WOULD DEFINE THE COL THAT WE WANT SHOW IN YOUR EDITION TABLE
	// 0. NAME OF THE COLUMN IN THE HTML PAGE,
	// 1. NAME OF THE FIELD
	// 2. VALUE OF THE FIELD
	// 3. THE TYPE OF THE FIELD (INPUT/SELECT/TEXTAREA)
	// 4. THE PROPERTY OF THIS FIELD
	// 5. REGEXPRES TO CHECK THE VALUE
	//    "^.{3}$": A STRING WITH EXACTLY 3 CHARACTERS.
	//     ^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$  : EMAIL ADRESSE
	// 6. ERROR MESSAGE // Used IF SELECT for ask-add as option with value -1
	// 7.  IF THE FIELD TYPE IS A SELECT,  DEFINE LIST OR SQL
	// 8.  IF SQL,		THE TABLE NAME
	// 9.  IF SQL,		THE FIELDS  : Three MAXIMUM IN ORDER (NAME, VALUE, ...other that we need for the display) ;)
	// 10. IF SQL,		THE CLAUSE
	// 11. IF LIST,		THE NAME OF THE LIST
	// 12. IF LIST,		DISPLAY : %1 : (%2) ; IF SELECT , show the content of that field
	// 13. CHECK_EMPTYVALUE - ("no" or "yes") if "no" we we check the regularexpression only if a value has been entered - if NO-NULL, if the value is
	// 	 					  not entered the field will not be include in the update/addition query
	// 14. COMMENT ( that is not included in FG_TABLE_EDITION or FG_TABLE_ADITION )
	// 15. SQL CUSTOM QUERY : customer SQL   or   function to display the edit input
	// 16. DISPLAYINPUT_DEFAULTSELECT : IF INPUT : FUNCTION TO DISPLAY THE VALUE OF THE FIELD ; IF SELECT IT WILL DISPLAY THE OPTION PER DEFAUTL, ie:
	//									'<OPTION  value="-1" selected>NOT DEFINED</OPTION>'
	// 17. COMMENT ABOVE : this will insert a comment line above the edition line, useful to separate section and to provide some detailed instruction
	 */

    public function AddEditElement(
        $displayname,
        $fieldname,
        $defaultvalue,
        $fieldtype,
        $fieldproperty,
        $regexpr_nb,
        $error_message,
        $type_selectfield,
        $lie_tablename,
        $lie_tablefield,
        $lie_clause,
        $listname,
        $displayformat_selectfield,
        $check_emptyvalue,
        $comment,
        $custom_query = null,
        $displayinput_defaultselect = null,
        $comment_above = null,
        $field_enabled = true
    )
    {
        $fieldtype = strtoupper($fieldtype);
        if ($fieldtype === "LABEL" && strtoupper($_REQUEST['form_action']) == "EDIT") {
            return;
        }

        if ($field_enabled == true) {
            $cur = count($this->FG_TABLE_EDITION);
            $assoc = [
                "label" => $displayname,
                "name" => $fieldname,
                "default" => $defaultvalue,
                "type" => strtoupper($fieldtype),
                "attributes" => $fieldproperty,
                "regex" => $regexpr_nb,
                "error" => $error_message,
                "select_type" => strtoupper($type_selectfield),
                "sql_table" => $lie_tablename,
                "sql_field" => $lie_tablefield,
                "sql_clause" => $lie_clause,
                "select_fields" => $listname,
                "select_format" => $displayformat_selectfield,
                "check_empty" => $check_emptyvalue,
                "custom_query" => $custom_query,
                "first_option" => $displayinput_defaultselect,
                "section_name" => $comment_above,
                // extra repeated values because same index is used for multiple purposes
                "radio_options" => $lie_clause,
                "popup_dest" => $displayformat_selectfield, //12
                "popup_params" => $check_emptyvalue, //13
                "popup_timeval" => $custom_query, //14
                "custom_function" => $displayinput_defaultselect, //15
            ];
            $this->FG_TABLE_EDITION[$cur] = $assoc + array_values($assoc);
            $this->FG_TABLE_COMMENT[$cur] = $comment;
            $this->FG_TABLE_ADITION[$cur] = $this->FG_TABLE_EDITION[$cur];
            $this->FG_NB_TABLE_ADITION = $this->FG_NB_TABLE_EDITION = count($this->FG_TABLE_EDITION);
        }
    }

    /**
     * Sets Search form fieldnames for the view module
     *
     * @public
     * @ $displayname , $fieldname, $fieldvar
     */
    public function AddSearchElement_C1($displayname, $fieldname, $fieldvar)
    {
        $cur = count($this->FG_FILTER_SEARCH_FORM_1C);
        $this->FG_FILTER_SEARCH_FORM_1C[$cur] = [$displayname, $fieldname, $fieldvar];
    }

    public function AddSearchElement_C2($displayname, $fieldname1, $fielvar1, $fieldname2, $fielvar2, $sqlfield)
    {
        $cur = count($this->FG_FILTER_SEARCH_FORM_2C);
        $this->FG_FILTER_SEARCH_FORM_2C[$cur] = [
            $displayname, $fieldname1, $fielvar1, $fieldname2, $fielvar2, $sqlfield,
        ];
    }

    /**
     * Sets Search form select rows for the view module
     *
     * @public
     * @ $displayname , SQL or array to fill select and the name of select box
     */
    public function AddSearchElement_Select($displayname, $table = null, $fields = null, $clause = null,
                                            $order = null, $sens = null, $select_name = '', $sql_type = 1, $array_content = null, $search_table = null)
    {

        $cur = count($this->FG_FILTER_SEARCH_FORM_SELECT);

        if ($sql_type) {
            $sql = [$table, $fields, $clause, $order, $sens];
            $this->FG_FILTER_SEARCH_FORM_SELECT[$cur] = [$displayname, $sql, $select_name, null, $search_table];
        } else {
            $this->FG_FILTER_SEARCH_FORM_SELECT[$cur] = [$displayname, 0, $select_name, $array_content, null];
        }
    }


    /**
     * Sets Query fieldnames for the Edit/ADD module
     *
     * @public
     * @ $col_query
     */
    public function FieldEditElement($fieldname)
    {
        if ($this->FG_DISPLAY_SELECT == true) {
            if (strlen($this->FG_SELECT_FIELDNAME) > 0) {
                $fieldname .= ", " . $this->FG_SELECT_FIELDNAME;
            }
        }
        $this->FG_QUERY_EDITION = $fieldname;
        $this->FG_QUERY_ADITION = $fieldname;
    }


    public function set_regular_expression()
    {
        // 0.  A STRING WITH EXACTLY 3 CHARACTERS.
        $this->FG_regular[] = [
            "^.{3}",
            gettext("(at least 3 characters)"),
        ];

        // 1.  EMAIL ADRESSE
        $this->FG_regular[] = [
            "^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*$",
            gettext("(must match email structure. Example : name@domain.com)"),
        ];

        // 2 . IF AT LEAST FIVE SUCCESSIVE CHARACTERS APPEAR AT THE END OF THE STRING.
        $this->FG_regular[] = [
            ".{5}$",
            gettext("(at least 5 successive characters appear at the end of this string)"),
        ];

        // 3. IF AT LEAST 4 CHARACTERS
        $this->FG_regular[] = [
            ".{4}",
            gettext("(at least 4 characters)"),
        ];

        // 4
        $this->FG_regular[] = [
            "^[0-9]+$",
            gettext("(number format)"),
        ];

        // 5
        $this->FG_regular[] = [
            "^([0-9]{4})[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$",
            "(YYYY-MM-DD)",
        ];

        // 6
        $this->FG_regular[] = [
            "^[0-9]{8,}$",
            gettext("(only number with more that 8 digits)"),
        ];

        // 7
        $this->FG_regular[] = [
            "^[0-9][ .0-9\/\-]{6,}[0-9]$",
            gettext("(at least 8 digits using . or - or the space key)"),
        ];

        // 8
        $this->FG_regular[] = [
            ".{5}",
            gettext("network adress format"),
        ];

        // 9
        $this->FG_regular[] = [
            "^.{1}",
            gettext("at least 1 character"),
        ];

        // 10
        $this->FG_regular[] = [
            "^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$",
            "(YYYY-MM-DD HH:MM:SS)",
        ];

        // 11
        $this->FG_regular[] = [
            "^.{2}",
            gettext("(AT LEAST 2 CARACTERS)"),
        ];

        // 12
        $this->FG_regular[] = [
            "^(-){0,1}[0-9]+(\.){0,1}[0-9]*$",
            gettext("(NUMBER FORMAT WITH/WITHOUT DECIMAL, use '.' for decimal)"),
        ];

        // 13  - RATECARD
        $this->FG_regular[] = [
            "^(defaultprefix|[-,0-9]+|_[-[.[.][.].]0-9XZN(){}|.,_]+)$",
            "(NUMBER FORMAT OR 'defaultprefix' OR ASTERISK/POSIX REGEX FORMAT)",
        ];

        // 14  - DNID PREFIX FOR RATECARD
        $this->FG_regular[] = [
            "^(all|[0-9]+)$",
            "(NUMBER FORMAT OR 'all')",
        ];

        // 15 - RATECARD TIME
        $this->FG_regular[] = [
            "^([0-9]{2}):([0-9]{2})$",
            "(HH:MM)",
        ];

        // 16  TEXT > 15 caract
        $this->FG_regular[] = [
            ".{15}",
            gettext("You must write something."),
        ];

        // 17  TEXT > 15 caract
        $this->FG_regular[] = [
            ".{8}",
            gettext("8 characters alphanumeric"),
        ];

        // 18 - CALLERID - PhoneNumber
        $this->FG_regular[] = [
            "^(\+|[0-9]{1})[0-9]+$",
            "Phone Number format",
        ];
        // 19 - CAPTCHAIMAGE - Alpahnumeric
        $this->FG_regular[] = [
            "^(" . strtoupper($_SESSION["captcha_code"]) . ")|(" . strtolower($_SESSION["captcha_code"]) . ")$",
            gettext("(at least 6 Alphanumeric characters)"),
        ];
        //20 TIME
        $this->FG_regular[] = [
            "^([0-9]{2}):([0-9]{2}):([0-9]{2})$",
            "(HH:MM:SS)",
        ];
        // check_select
        // TO check if a select have a value different -1
        // 21 -> Check percent more of 0 and under 100
        $this->FG_regular[] = [
            "^100$|^(([0-9]){0,2})((\.)([0-9]*))?$",
            gettext("(PERCENT FORMAT WITH/WITHOUT DECIMAL, use '.' for decimal and don't use '%' character. e.g.: 12.4 )"),
        ];

    }


    // ----------------------------------------------
    // FUNCTION FOR THE FORM
    // ----------------------------------------------

    public function do_field_duration($sql, $fld, $fldsql)
    {
        $processed = $this->getProcessed();

        $fldtype = $fld . 'type';

        if (isset($processed[$fld]) && ($processed[$fld] != '')) {
            if (strpos($sql, 'WHERE') > 0) {
                $sql = "$sql AND ";
            } else {
                $sql = "$sql WHERE ";
            }
            $sql = "$sql $fldsql";
            if (isset ($processed[$fldtype])) {
                switch ($processed[$fldtype]) {
                    case 1:
                        $sql = "$sql ='" . $processed[$fld] . "'";
                        break;
                    case 2:
                        $sql = "$sql <= '" . $processed[$fld] . "'";
                        break;
                    case 3:
                        $sql = "$sql < '" . $processed[$fld] . "'";
                        break;
                    case 4:
                        $sql = "$sql > '" . $processed[$fld] . "'";
                        break;
                    case 5:
                        $sql = "$sql >= '" . $processed[$fld] . "'";
                        break;
                }
            } else {
                $sql = "$sql = '" . $processed[$fld] . "'";
            }
        }

        return $sql;
    }

    public function do_field($sql, $fld, $simple = 0, $processed = null, $search_table = null)
    {
        $fldtype = $fld . 'type';
        if (empty($processed)) {
            $processed = $this->getProcessed();
        }

        if (isset($processed[$fld]) && ($processed[$fld] != '')) {
            if (strpos($sql, 'WHERE') > 0) {
                $sql = "$sql AND ";
            } else {
                $sql = "$sql WHERE ";
            }
            if (empty($search_table)) {
                $sql = "$sql $fld";
            } else {
                $sql = "$sql $search_table.$fld";
            }
            if (DB_TYPE == "postgres") {
                $LIKE = "ILIKE";
                $CONVERT = "";
            } else {
                $LIKE = "LIKE";
                $CONVERT = " COLLATE utf8_unicode_ci";
            }

            if ($simple == 0) {
                if (isset ($processed[$fldtype])) {
                    switch ($processed[$fldtype]) {
                        case 1:
                            $sql = "$sql='" . $processed[$fld] . "'";
                            break;
                        case 2:
                            $sql = "$sql $LIKE '" . $processed[$fld] . "%'" . $CONVERT;
                            break;
                        case 3:
                            $sql = "$sql $LIKE '%" . $processed[$fld] . "%'" . $CONVERT;
                            break;
                        case 4:
                            $sql = "$sql $LIKE '%" . $processed[$fld] . "'" . $CONVERT;
                    }
                } else {
                    $sql = "$sql $LIKE '%" . $processed[$fld] . "%'" . $CONVERT;
                }
            } else {
                $sql = "$sql ='" . $processed[$fld] . "'";
            }
        }

        return $sql;
    }

    /**
     * Function to execture the appropriate action
     *
     * @public
     */
    public function perform_action(&$form_action)
    {
        //security check
        $list = [];
        switch ($form_action) {
            case "ask-add":
            case "add":
                if (!$this->FG_ADDITION) {
                    header("Location: " . filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL));
                    die();
                }
                break;
            case "ask-edit":
            case "edit":
                if (!$this->FG_EDITION) {
                    header("Location: " . filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL));
                    die();
                }
                break;
            case "ask-del-confirm":
            case "ask-delete":
            case "delete":
                if (!$this->FG_DELETION) {
                    header("Location: " . filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL));
                    die();
                }
                break;
        }
        switch ($form_action) {
            case "add":
                $this->perform_add($form_action);
                break;
            case "edit":
                $this->perform_edit($form_action);
                break;
            case "delete":
                $this->perform_delete();
                break;
        }

        $processed = $this->getProcessed();  //$processed['firstname']

        if ($form_action == "ask-delete" && in_array($processed['id'], $this->FG_DELETION_FORBIDDEN_ID)) {
            if (!empty($this->FG_GO_LINK_AFTER_ACTION_DELETE)) {
                header("Location: " . $this->FG_GO_LINK_AFTER_ACTION_DELETE . $processed['id']);
            } else {
                header("Location: " . filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL));
            }
            die();
        }

        if ($form_action == "list" || $form_action == "edit" || $form_action == "ask-delete" ||
            $form_action == "ask-edit" || $form_action == "add-content" || $form_action == "del-content" || $form_action == "ask-del-confirm") {

            $this->FG_ORDER = $processed['order'];
            $this->FG_SENS = $processed['sens'];
            $this->CV_CURRENT_PAGE = $processed['current_page'];

            $session_limit = $this->FG_TABLE_NAME . "-displaylimit";
            if (isset($_SESSION[$session_limit]) && is_numeric($_SESSION[$session_limit])) {
                $this->FG_LIMITE_DISPLAY = $_SESSION[$session_limit];
            }

            /* Add CSRF protection */
            if ($this->FG_CSRF_STATUS && $form_action === 'edit') {
                if ($this->_processed[$this->FG_CSRF_RECEIVED_FIELD] != $this->FG_CSRF_RECEIVED_TOKEN) {
                    header("Location: " . filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL));
                    die();
                }
            }

            if (isset($processed['mydisplaylimit']) && (is_numeric($processed['mydisplaylimit']) || ($processed['mydisplaylimit'] == 'ALL'))) {
                if ($processed['mydisplaylimit'] == 'ALL') {
                    $this->FG_LIMITE_DISPLAY = 5000;
                } else {
                    $this->FG_LIMITE_DISPLAY = $processed['mydisplaylimit'];
                }
                $_SESSION[$this->FG_TABLE_NAME . "-displaylimit"] = $this->FG_LIMITE_DISPLAY;
            }

            if ($this->FG_ORDER == "" || $this->FG_SENS == "") {
                $this->FG_ORDER = $this->FG_TABLE_DEFAULT_ORDER;
                $this->FG_SENS = $this->FG_TABLE_DEFAULT_SENS;
            }

            if ($form_action == "list") {
                $sql_calc_found_rows = '';
                if (DB_TYPE != "postgres") {
                    $sql_calc_found_rows = 'SQL_CALC_FOUND_ROWS';
                }
                $instance_table = new Table($this->FG_TABLE_NAME, "$sql_calc_found_rows " . $this->FG_COL_QUERY, null, null, null, true, $this->FG_TABLE_NAME_COUNT);

                $this->prepare_list_subselection($form_action);

                // Code here to call the Delete Selected items Fucntion
                if (isset($processed['deleteselected'])) {
                    $this->Delete_Selected();
                }

                if ($this->FG_DEBUG >= 2) {
                    echo "FG_CLAUSE:$this->FG_CLAUSE";
                    echo "FG_ORDER = " . $this->FG_ORDER . "<br>";
                    echo "FG_SENS = " . $this->FG_SENS . "<br>";
                    echo "FG_LIMITE_DISPLAY = " . $this->FG_LIMITE_DISPLAY . "<br>";
                    echo "CV_CURRENT_PAGE = " . $this->CV_CURRENT_PAGE . "<br>";
                }

                $list = $instance_table->Get_list($this->DBHandle, $this->FG_TABLE_CLAUSE, $this->FG_ORDER, $this->FG_SENS, null, null,
                    $this->FG_LIMITE_DISPLAY, $this->CV_CURRENT_PAGE * $this->FG_LIMITE_DISPLAY, $this->SQL_GROUP);
                if ($this->FG_DEBUG == 3) {
                    echo "<br>Clause : " . $this->FG_TABLE_CLAUSE;
                }
                if (DB_TYPE == "postgres") {
                    $this->FG_NB_RECORD = $instance_table->Table_count($this->DBHandle, $this->FG_TABLE_CLAUSE);
                } else {
                    $res_count = $instance_table->SQLExec($this->DBHandle, "SELECT FOUND_ROWS() as count");
                    $this->FG_NB_RECORD = $res_count[0][0];
                }

                if ($this->FG_DEBUG >= 1) {
                    var_dump($list);
                }

                if ($this->FG_NB_RECORD <= $this->FG_LIMITE_DISPLAY) {
                    $this->FG_NB_RECORD_MAX = 1;
                } else {
                    $this->FG_NB_RECORD_MAX = ceil($this->FG_NB_RECORD / $this->FG_LIMITE_DISPLAY);
                }

                if ($this->FG_DEBUG == 3) {
                    echo "<br>Nb_record : " . $this->FG_NB_RECORD;
                }
                if ($this->FG_DEBUG == 3) {
                    echo "<br>Nb_record_max : " . $this->FG_NB_RECORD_MAX;
                }

            } else {

                $instance_table = new Table($this->FG_TABLE_NAME, $this->FG_QUERY_EDITION);
                $list = $instance_table->Get_list($this->DBHandle, $this->FG_EDITION_CLAUSE, null, null, null, null, 1, 0);

                //PATCH TO CLEAN THE IMPORT OF PASSWORD FROM THE DATABASE
                if (substr_count($this->FG_QUERY_EDITION, "pwd_encoded") > 0) {
                    $tab_field = explode(',', $this->FG_QUERY_EDITION);
                    for ($i = 0; $i < count($tab_field); $i++) {
                        if (trim($tab_field[$i]) == "pwd_encoded") {
                            $list[0][$i] = "";
                        }
                    }
                }

                if (isset($list[0]["pwd_encoded"])) {
                    $list[0]["pwd_encoded"] = "";
                }
            }

            if ($this->FG_DEBUG >= 2) {
                print_r($list);
            }
        }

        return $list;

    }

    /**
     * Function to prepare the clause from the session filter
     *
     * @public
     */
    public function prepare_list_subselection($form_action)
    {

        $processed = $this->getProcessed();

        if ($form_action == "list" && $this->FG_FILTER_SEARCH_FORM) {

            if (isset($processed['cancelsearch']) && ($processed['cancelsearch'] == true)) {
                $_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME] = '';
            }

            // RETRIEVE THE CONTENT OF THE SEARCH SESSION AND
            if (strlen($_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME]) > 5 && ($processed['posted_search'] != 1)) {
                $element_arr = explode("|", $_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME]);
                foreach ($element_arr as $val_element_arr) {
                    $pos = strpos($val_element_arr, '=');
                    if ($pos !== false) {
                        $entity_name = substr($val_element_arr, 0, $pos);
                        $entity_value = substr($val_element_arr, $pos + 1);
                        $this->_processed[$entity_name] = $entity_value;
                    }
                }
            }

            if (($processed['posted_search'] != 1 && isset($_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME]) && strlen($_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME]) > 10)) {
                $arr_session_var = explode("|", $_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME]);
                foreach ($arr_session_var as $arr_val) {
                    [$namevar, $valuevar] = explode("=", $arr_val);
                    $this->_processed[$namevar] = $valuevar;
                    $processed[$namevar] = $valuevar;
                    $_POST[$namevar] = $valuevar;
                }
                $processed['posted_search'] = 1;
            }

            // Search Form On
            if (($processed['posted_search'] == 1)) {

                $this->_processed["fromstatsday_sday"] = normalize_day_of_month($processed["fromstatsday_sday"], $processed["fromstatsmonth_sday"], 1);
                $this->_processed["tostatsday_sday"] = normalize_day_of_month($processed["tostatsday_sday"], $processed["tostatsmonth_sday"], 1);
                $this->_processed["fromstatsday_sday_bis"] = normalize_day_of_month($processed["fromstatsday_sday_bis"], $processed["fromstatsmonth_sday_bis"], 1);
                $this->_processed["tostatsday_sday_bis"] = normalize_day_of_month($processed["tostatsday_sday_bis"], $processed["tostatsmonth_sday_bis"], 1);

                $SQLcmd = '';

                $search_parameters = "Period=$processed[Period]|frommonth=$processed[frommonth]|fromstatsmonth=$processed[fromstatsmonth]|tomonth=$processed[tomonth]";
                $search_parameters .= "|tostatsmonth=$processed[tostatsmonth]|fromday=$processed[fromday]|fromstatsday_sday=$processed[fromstatsday_sday]";
                $search_parameters .= "|fromstatsmonth_sday=$processed[fromstatsmonth_sday]|today=$processed[today]|tostatsday_sday=$processed[tostatsday_sday]";
                $search_parameters .= "|tostatsmonth_sday=$processed[tostatsmonth_sday]";
                $search_parameters .= "|Period_bis=$processed[Period_bis]|frommonth_bis=$processed[frommonth_bis]|fromstatsmonth_bis=$processed[fromstatsmonth_bis]|tomonth_bis=$processed[tomonth_bis]";
                $search_parameters .= "|tostatsmonth_bis=$processed[tostatsmonth_bis]|fromday_bis=$processed[fromday_bis]|fromstatsday_sday_bis=$processed[fromstatsday_sday_bis]";
                $search_parameters .= "|fromstatsmonth_sday_bis=$processed[fromstatsmonth_sday_bis]|today_bis=$processed[today_bis]|tostatsday_sday_bis=$processed[tostatsday_sday_bis]";
                $search_parameters .= "|tostatsmonth_sday_bis=$processed[tostatsmonth_sday_bis]";

                foreach ($this->FG_FILTER_SEARCH_FORM_1C as $r) {
                    $search_parameters .= "|$r[1]=" . $processed[$r[1]] . "|$r[2]=" . $processed[$r[2]];
                    $SQLcmd = $this->do_field($SQLcmd, $r[1], 0, $processed);
                }

                foreach ($this->FG_FILTER_SEARCH_FORM_2C as $r) {
                    $search_parameters .= "|$r[1]=" . $processed[$r[1]] . "|$r[2]=" . $processed[$r[2]];
                    $search_parameters .= "|$r[3]=" . $processed[$r[3]] . "|$r[4]=" . $processed[$r[4]];
                    $SQLcmd = $this->do_field_duration($SQLcmd, $r[1], $r[5]);
                    $SQLcmd = $this->do_field_duration($SQLcmd, $r[3], $r[5]);
                }

                foreach ($this->FG_FILTER_SEARCH_FORM_SELECT as $r) {
                    $search_parameters .= "|$r[2]=" . $processed[$r[2]];
                    $SQLcmd = $this->do_field($SQLcmd, $r[2], 1, null, $r[4]);
                }

                $_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME] = $search_parameters;

                $date_clause = '';

                if ($processed['fromday'] && isset($processed['fromstatsday_sday']) && isset($processed['fromstatsmonth_sday'])) {
                    $date_clause .= " AND " . $this->FG_FILTER_SEARCH_1_TIME_FIELD . " >= TIMESTAMP('$processed[fromstatsmonth_sday]-$processed[fromstatsday_sday]')";
                }
                if ($processed['today'] && isset($processed['tostatsday_sday']) && isset($processed['tostatsmonth_sday'])) {
                    $date_clause .= " AND " . $this->FG_FILTER_SEARCH_1_TIME_FIELD . " <= TIMESTAMP('$processed[tostatsmonth_sday]-" . sprintf("%02d", intval($processed["tostatsday_sday"])/*+1*/) . " 23:59:59')";
                }


                if ($processed["Period"] == "month_older_rad") {
                    $from_month = $processed["month_earlier"];
                    $date_clause .= " AND DATE_SUB(NOW(),INTERVAL $from_month MONTH) > " . $this->FG_FILTER_SEARCH_3_TIME_FIELD;
                }

                //BIS FIELD
                if ($processed['fromday_bis'] && isset($processed['fromstatsday_sday_bis']) && isset($processed['fromstatsmonth_sday_bis'])) {
                    $date_clause .= " AND " . $this->FG_FILTER_SEARCH_1_TIME_FIELD_BIS . " >= TIMESTAMP('$processed[fromstatsmonth_sday_bis]-$processed[fromstatsday_sday_bis]')";
                }
                if ($processed['today_bis'] && isset($processed['tostatsday_sday_bis']) && isset($processed['tostatsmonth_sday_bis'])) {
                    $date_clause .= " AND " . $this->FG_FILTER_SEARCH_1_TIME_FIELD_BIS . " <= TIMESTAMP('$processed[tostatsmonth_sday_bis]-" . sprintf("%02d", intval($processed["tostatsday_sday_bis"])/*+1*/) . " 23:59:59')";
                }


                if ($processed['Period_bis'] == "month_older_rad") {
                    $from_month = $processed['month_earlier_bis'];
                    $date_clause .= " AND DATE_SUB(NOW(),INTERVAL $from_month MONTH) > " . $this->FG_FILTER_SEARCH_3_TIME_FIELD_BIS;
                }


                if (strpos($SQLcmd, 'WHERE') > 0) {
                    if (strlen($this->FG_TABLE_CLAUSE) > 0) {
                        $this->FG_TABLE_CLAUSE .= " AND ";
                    }
                    $this->FG_TABLE_CLAUSE .= substr($SQLcmd, 6) . $date_clause;
                } elseif (strpos($date_clause, 'AND') > 0) {
                    if (strlen($this->FG_TABLE_CLAUSE) > 0) {
                        $this->FG_TABLE_CLAUSE .= " AND ";
                    }
                    $this->FG_TABLE_CLAUSE .= substr($date_clause, 5);
                }
            }
        }
    }

    /****************************************
     * Function to delete all pre selected records,
     * This Function Gets the selected records and delete them from DB
     ******************************************/
    public function Delete_Selected()
    {
        //if ( $form_action == "list" && $this->FG_FILTER_SEARCH_FORM)
        {
            $instance_table = new Table($this->FG_TABLE_NAME, $this->FG_COL_QUERY);
            $instance_table->Delete_Selected($this->DBHandle, $this->FG_TABLE_CLAUSE, $this->FG_ORDER, $this->FG_SENS, null, null,
                $this->FG_LIMITE_DISPLAY, $this->CV_CURRENT_PAGE * $this->FG_LIMITE_DISPLAY, $this->SQL_GROUP);
        }
    }

    /**
     * Function to perform the add action after inserting all data in required fields
     *
     * @public
     */
    public function perform_add(&$form_action)
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->VALID_SQL_REG_EXP = true;
        $param_add_fields = "";
        $param_add_value = "";
        $arr_value_to_import = [];

        for ($i = 0; $i < $this->FG_NB_TABLE_ADITION; $i++) {

            $pos = strpos($this->FG_TABLE_ADITION[$i][14], ":"); // SQL CUSTOM QUERY
            $pos_mul = strpos($this->FG_TABLE_ADITION[$i][4], "multiple");

            if (!$pos) {

                $fields_name = $this->FG_TABLE_ADITION[$i][1];
                $regexp = $this->FG_TABLE_ADITION[$i][5];

                // FIND THE MULTIPLE SELECT
                if ($pos_mul && is_array($processed[$fields_name])) {
                    $total_mult_select = 0;
                    foreach ($processed[$fields_name] as $value) {
                        $total_mult_select += $value;
                    }

                    if ($this->FG_DEBUG == 1) {
                        echo "<br>$fields_name : " . $total_mult_select;
                    }

                    if ($i > 0) {
                        $param_add_fields .= ", ";
                    }
                    $param_add_fields .= $fields_name;
                    if ($i > 0) {
                        $param_add_value .= ", ";
                    }
                    $param_add_value .= "'" . addslashes(trim($total_mult_select)) . "'";

                } else {
                    // NO MULTIPLE SELECT

                    // CHECK ACCORDING TO THE REGULAR EXPRESSION DEFINED
                    if (is_numeric($regexp) && !(strtoupper(substr($this->FG_TABLE_ADITION[$i][13], 0, 2)) == "NO" && $processed[$fields_name] == "")) {
                        $this->FG_fit_expression[$i] = preg_match('/' . $this->FG_regular[$regexp][0] . '/', $processed[$fields_name]);
                        if ($this->FG_DEBUG == 1) {
                            echo "<br>->  $fields_name => " . $this->FG_regular[$regexp][0] . " , " . $processed[$fields_name];
                        }
                        if (!$this->FG_fit_expression[$i]) {
                            $this->VALID_SQL_REG_EXP = false;
                            $form_action = "ask-add";
                        }
                    } elseif ($regexp == "check_select") {
                        // FOR SELECT FIELD WE HAVE THE check_select THAT WILL ENSURE WE DEFINE A VALUE FOR THE SELECTABLE FIELD
                        if ($processed[$fields_name] == -1) {
                            $this->FG_fit_expression[$i] = false;
                            $this->VALID_SQL_REG_EXP = false;
                            $form_action = "ask-add";
                        }
                    }
                    // CHECK IF THIS IS A SPLITABLE FIELD LIKE 012-014 OR 15;16;17
                    if ($fields_name == $this->FG_SPLITABLE_FIELD && substr($processed[$fields_name], 0, 1) != '_') {
                        $splitable_value = $processed[$fields_name];
                        $arr_splitable_value = explode(",", $splitable_value);
                        foreach ($arr_splitable_value as $arr_value) {
                            $arr_value = trim($arr_value);
                            $arr_value_explode = explode("-", $arr_value, 2);
                            if (count($arr_value_explode) > 1) {
                                if (is_numeric($arr_value_explode[0]) && is_numeric($arr_value_explode[1]) && $arr_value_explode[0] < $arr_value_explode[1]) {
                                    $kk = strlen($arr_value_explode[0]) - strlen(ltrim($arr_value_explode[0], '0'));
                                    $prefix = substr($arr_value_explode[0], 0, $kk);
                                    for ($kk = $arr_value_explode[0]; $kk <= $arr_value_explode[1]; $kk++) {
                                        $arr_value_to_import[] = $prefix . $kk;
                                    }
                                } elseif (is_numeric($arr_value_explode[0])) {
                                    $arr_value_to_import[] = $arr_value_explode[0];
                                } elseif (is_numeric($arr_value_explode[1])) {
                                    $arr_value_to_import[] = $arr_value_explode[1];
                                }
                            } else {
                                $arr_value_to_import[] = $arr_value_explode[0];
                            }
                        }

                        if (!is_null($processed[$fields_name]) && ($processed[$fields_name] != "") && ($this->FG_TABLE_ADITION[$i][4] != "disabled")) {
                            if ($i > 0) {
                                $param_add_fields .= ", ";
                            }
                            $param_add_fields .= str_replace('myfrom_', '', $fields_name);
                            if ($i > 0) {
                                $param_add_value .= ", ";
                            }
                            $param_add_value .= "'%TAGPREFIX%'";
                        }
                    } else {
                        if ($this->FG_DEBUG == 1) {
                            echo "<br>$fields_name : " . $processed[$fields_name];
                        }
                        if (!is_null($processed[$fields_name]) && ($processed[$fields_name] != "") && ($this->FG_TABLE_ADITION[$i][4] != "disabled")) {
                            if (strtoupper($this->FG_TABLE_ADITION[$i][3]) != strtoupper("CAPTCHAIMAGE")) {
                                if ($i > 0) {
                                    $param_add_fields .= ", ";
                                }
                                $param_add_fields .= str_replace('myfrom_', '', $fields_name);
                                if ($i > 0) {
                                    $param_add_value .= ", ";
                                }
                                $param_add_value .= "'" . addslashes(trim($processed[$fields_name])) . "'";
                            }
                        }
                    }
                }
            }
        }

        if (!is_null($this->FG_QUERY_ADITION_HIDDEN_FIELDS) && $this->FG_QUERY_ADITION_HIDDEN_FIELDS != "") {
            if ($i > 0) {
                $param_add_fields .= ", ";
            }
            $param_add_fields .= $this->FG_QUERY_ADITION_HIDDEN_FIELDS;
            if ($i > 0) {
                $param_add_value .= ", ";
            }
            $split_hidden_fields_value = explode(",", trim($this->FG_QUERY_ADITION_HIDDEN_VALUE));
            for ($cur_hidden = 0; $cur_hidden < count($split_hidden_fields_value); $cur_hidden++) {
                $param_add_value .= "'" . trim($split_hidden_fields_value[$cur_hidden]) . "'";
                if ($cur_hidden < count($split_hidden_fields_value) - 1) {
                    $param_add_value .= ",";
                }
            }
        }

        if ($this->FG_DEBUG == 1) {
            echo "<br><hr> $param_add_fields";
        }
        if ($this->FG_DEBUG == 1) {
            echo "<br><hr> $param_add_value";
        }

        $res_funct = true;

        // CALL DEFINED FUNCTION BEFORE THE ADDITION

        if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_ADD) > 0 && ($this->VALID_SQL_REG_EXP)) {
            $res_funct = call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_ADD]);
        }

        if ($res_funct) {

            $instance_table = new Table($this->FG_TABLE_NAME, $param_add_fields);
            // CHECK IF WE HAD FOUND A SPLITABLE FIELD THEN WE MIGHT HAVE %TAGPREFIX%
            if (strpos($param_add_value, '%TAGPREFIX%')) {
                foreach ($arr_value_to_import as $current_value) {
                    $param_add_value_replaced = str_replace("%TAGPREFIX%", $current_value, $param_add_value);
                    if ($this->VALID_SQL_REG_EXP) {
                        $this->RESULT_QUERY = $instance_table->Add_table($this->DBHandle, $param_add_value_replaced, null, null, $this->FG_TABLE_ID);
                    }
                }
            } elseif ($this->VALID_SQL_REG_EXP) {
                $this->RESULT_QUERY = $instance_table->Add_table($this->DBHandle, $param_add_value, null, null, $this->FG_TABLE_ID);
            }
            if ($this->FG_ENABLE_LOG == 1) {
                $this->logger->insertLog_Add($_SESSION["admin_id"], 2, "NEW " . strtoupper($this->FG_INSTANCE_NAME) . " CREATED", "User added a new record in database", $this->FG_TABLE_NAME, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], $param_add_fields, $param_add_value);
            }
            // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
            if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD) > 0 && ($this->VALID_SQL_REG_EXP)) {
                call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_ADD]);
            }
            if ($this->FG_ADITION_GO_EDITION == "yes") {
                $form_action = "ask-edit";
                $this->FG_ADITION_GO_EDITION = "yes-done";
            }
            $id = $this->RESULT_QUERY;
            if (!empty($id) && ($this->VALID_SQL_REG_EXP) && (isset($this->FG_GO_LINK_AFTER_ACTION_ADD))) {
                if ($this->FG_DEBUG == 1) {
                    echo "<br> GOTO ; " . $this->FG_GO_LINK_AFTER_ACTION_ADD . $id;
                }
                //echo "<br> GOTO ; ".$this->FG_GO_LINK_AFTER_ACTION_ADD.$id;
                header("Location: " . $this->FG_GO_LINK_AFTER_ACTION_ADD . $id);
            }
        }
    }


    /**
     * Function to edit the fields
     *
     * @public
     */
    public function perform_edit(&$form_action)
    {
        $param_update = "";
        $processed = $this->getProcessed();  //$processed['firstname']

        $this->VALID_SQL_REG_EXP = true;

        $instance_table = new Table($this->FG_TABLE_NAME, $this->FG_QUERY_EDITION);

        if (!empty($processed['id'])) {
            $this->FG_EDITION_CLAUSE = str_replace("%id", $processed['id'], $this->FG_EDITION_CLAUSE);
        }

        for ($i = 0; $i < $this->FG_NB_TABLE_EDITION; $i++) {

            $pos = strpos($this->FG_TABLE_EDITION[$i][14], ":"); // SQL CUSTOM QUERY
            $pos_mul = strpos($this->FG_TABLE_EDITION[$i][4], "multiple");
            if (!$pos) {
                $fields_name = $this->FG_TABLE_EDITION[$i][1];
                $regexp = $this->FG_TABLE_EDITION[$i][5];

                if ($pos_mul && is_array($processed[$fields_name])) {
                    $total_mult_select = 0;
                    foreach ($processed[$fields_name] as $value) {
                        $total_mult_select += $value;
                    }
                    if ($this->FG_DEBUG == 1) {
                        echo "<br>$fields_name : " . $total_mult_select;
                    }
                    if ($i > 0) {
                        $param_update .= ", ";
                    }
                    $param_update .= "$fields_name = '" . addslashes(trim($total_mult_select)) . "'";
                } else {
                    if (is_numeric($regexp) && !(strtoupper(substr($this->FG_TABLE_ADITION[$i][13], 0, 2)) == "NO" && $processed[$fields_name] == "")) {
                        $this->FG_fit_expression[$i] = preg_match('/' . $this->FG_regular[$regexp][0] . '/', $processed[$fields_name]);
                        if ($this->FG_DEBUG == 1) {
                            echo "<br>-> $i)  " . $this->FG_regular[$regexp][0] . " , " . $processed[$fields_name];
                        }
                        if (!$this->FG_fit_expression[$i]) {
                            $this->VALID_SQL_REG_EXP = false;
                            if ($this->FG_DEBUG == 1) {
                                echo "<br>-> $i) Error Match";
                            }
                            $form_action = "ask-edit";
                        }
                    }

                    if ($this->FG_DEBUG == 1) {
                        echo "<br>$fields_name : " . $processed[$fields_name];
                    }
                    if ($i > 0 && $this->FG_TABLE_EDITION[$i][3] != "SPAN") {
                        $param_update .= ", ";
                    }
                    if (empty($processed[$fields_name]) && strtoupper(substr($this->FG_TABLE_ADITION[$i][13], 3, 4)) == "NULL") {
                        $param_update .= $fields_name . " = NULL ";
                    } elseif ($this->FG_TABLE_EDITION[$i][3] != "SPAN") {
                        $param_update .= $fields_name . " = '" . addslashes(trim($processed[$fields_name])) . "' ";
                    }
                }

            } elseif (strtoupper($this->FG_TABLE_EDITION[$i][3]) == strtoupper("CHECKBOX")) {
                $table_split = explode(":", $this->FG_TABLE_EDITION[$i][1]);
                $checkbox_data = $table_split[0];    //doc_tariff
                $instance_sub_table = new Table($table_split[0], $table_split[1] . ", " . $table_split[5]);
                $SPLIT_FG_DELETE_CLAUSE = $table_split[5] . "='" . trim($processed['id']) . "'";
                $instance_sub_table->Delete_table($this->DBHandle, $SPLIT_FG_DELETE_CLAUSE);

                if (!is_array($processed[$checkbox_data])) {
                    $snum = 0;
                    $this->VALID_SQL_REG_EXP = false;
                    $this->FG_fit_expression[$i] = false;
                } else {
                    $snum = count($processed[$checkbox_data]);
                }

                $checkbox_data_tab = $processed[$checkbox_data];
                for ($j = 0; $j < $snum; $j++) {
                    $this->RESULT_QUERY = $instance_sub_table->Add_table($this->DBHandle, "'" . addslashes(trim($checkbox_data_tab[$j])) . "', '" . addslashes(trim($processed['id'])) . "'");
                    if (!$this->RESULT_QUERY) {
                        $findme = 'duplicate';
                        $pos_find = strpos($instance_sub_table->errstr, $findme);

                        // Note our use of ===.  Simply == would not work as expected
                        // because the position of 'a' was the 0th (first) character.
                        if ($pos_find === false) {
                            echo $instance_sub_table->errstr;
                        } else {
                            //echo $FG_TEXT_ERROR_DUPLICATION;
                            $this->alarm_db_error_duplication = true;
                        }
                    }
                }
            }
        }

        if (!is_null($this->FG_QUERY_EDITION_HIDDEN_FIELDS) && $this->FG_QUERY_EDITION_HIDDEN_FIELDS != "") {

            $table_split_field = explode(",", $this->FG_QUERY_EDITION_HIDDEN_FIELDS);
            $table_split_value = explode(",", $this->FG_QUERY_EDITION_HIDDEN_VALUE);

            for ($k = 0; $k < count($table_split_field); $k++) {
                $param_update .= ", ";
                $param_update .= "$table_split_field[$k] = '" . addslashes(trim($table_split_value[$k])) . "'";
            }
        }

        if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION) > 0 && ($this->VALID_SQL_REG_EXP)) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION]);
        }

        if ($this->FG_DEBUG == 1) {
            echo "<br><hr> PARAM_UPDATE: $param_update<br>" . $this->FG_EDITION_CLAUSE;
        }

        if ($this->VALID_SQL_REG_EXP) {
            $this->RESULT_QUERY = $instance_table->Update_table($this->DBHandle, $param_update, $this->FG_EDITION_CLAUSE);
        }

        if ($this->FG_ENABLE_LOG == 1) {
            $this->logger->insertLog_Update($_SESSION["admin_id"], 3, "A " . strtoupper($this->FG_INSTANCE_NAME) . " UPDATED", "A RECORD IS UPDATED, EDITION CALUSE USED IS " . $this->FG_EDITION_CLAUSE, $this->FG_TABLE_NAME, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], $param_update);
        }

        if ($this->FG_DEBUG == 1) {
            echo $this->RESULT_QUERY;
        }
        // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
        if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION) > 0 && ($this->VALID_SQL_REG_EXP)) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_EDITION]);
        }

        if (($this->VALID_SQL_REG_EXP) && (isset($this->FG_GO_LINK_AFTER_ACTION_EDIT))) {
            if ($this->FG_DEBUG == 1) {
                echo "<br> GOTO ; " . $this->FG_GO_LINK_AFTER_ACTION_EDIT . $processed['id'];
            }
            $ext_link = '';
            if (is_numeric($processed['current_page'])) {
                $ext_link .= "&current_page=" . $processed['current_page'];
            }
            if (!empty($processed['order']) && !empty($processed['sens'])) {
                $ext_link .= "&order=" . $processed['order'] . "&sens=" . $processed['sens'];
            }
            header("Location: " . $this->FG_GO_LINK_AFTER_ACTION_EDIT . $processed['id'] . $ext_link);
        }
    }


    /**
     * Function to delete a record
     *
     * @public
     */
    public function perform_delete()
    {
        if (strlen($this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE) > 0) {
            call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE]);
        }
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->VALID_SQL_REG_EXP = true;

        $instance_table = null;
        $tableCount = count($this->FG_FK_TABLENAMES);
        $clauseCount = count($this->FG_FK_EDITION_CLAUSE);

        if (($tableCount == $clauseCount) && $clauseCount > 0 && $this->FG_FK_DELETE_ALLOWED) {
            if (!empty($processed['id'])) {
                $instance_table = new Table($this->FG_TABLE_NAME, $this->FG_QUERY_EDITION, $this->FG_FK_TABLENAMES, $this->FG_FK_EDITION_CLAUSE, $processed['id'], $this->FG_FK_WARNONLY);
            }
        } else {
            $instance_table = new Table($this->FG_TABLE_NAME, $this->FG_QUERY_EDITION);
        }
        $instance_table->FK_DELETE = !$this->FG_FK_WARNONLY;

        if (!empty($processed['id'])) {
            $this->FG_EDITION_CLAUSE = str_replace("%id", $processed['id'], $this->FG_EDITION_CLAUSE);
        }

        $this->RESULT_QUERY = $instance_table->Delete_table($this->DBHandle, $this->FG_EDITION_CLAUSE);
        if ($this->FG_ENABLE_LOG == 1) {
            $this->logger->insertLog($_SESSION["admin_id"], 3, "A " . strtoupper($this->FG_INSTANCE_NAME) . " DELETED", "A RECORD IS DELETED, EDITION CLAUSE USED IS " . $this->FG_EDITION_CLAUSE, $this->FG_TABLE_NAME, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
        }
        if (!$this->RESULT_QUERY) {
            echo gettext("error deletion");
        }

        $this->FG_INTRO_TEXT_DELETION = str_replace("%id", $processed['id'], $this->FG_INTRO_TEXT_DELETION);
        $this->FG_INTRO_TEXT_DELETION = str_replace("%table", $this->FG_TABLE_NAME, $this->FG_INTRO_TEXT_DELETION);
        if (isset($this->FG_GO_LINK_AFTER_ACTION_DELETE)) {
            if ($this->FG_DEBUG == 1) {
                echo "<br> GOTO ; " . $this->FG_GO_LINK_AFTER_ACTION_DELETE . $processed['id'];
            }
            if ($this->FG_GO_LINK_AFTER_ACTION_DELETE) {
                $ext_link = '';
                if (is_numeric($processed['current_page'])) {
                    $ext_link = "&current_page=" . $processed['current_page'];
                }
                if (!empty($processed['order']) && !empty($processed['sens'])) {
                    $ext_link .= "&order=" . $processed['order'] . "&sens=" . $processed['sens'];
                }
                if (substr($this->FG_GO_LINK_AFTER_ACTION_DELETE, -3) == "id=") {
                    header("Location: " . $this->FG_GO_LINK_AFTER_ACTION_DELETE . $processed['id'] . $ext_link);
                } else {
                    header("Location: " . $this->FG_GO_LINK_AFTER_ACTION_DELETE . $ext_link);
                }
            }
        }

    }

    /*
      Function to check for the Dependent Data
    */
    public function isFKDataExists(): bool
    {
        $processed = $this->getProcessed();
        $tableCount = count($this->FG_FK_TABLENAMES);
        $clauseCount = count($this->FG_FK_EDITION_CLAUSE);
        $rowcount = 0;
        if (($tableCount == $clauseCount) && $clauseCount > 0) {
            for ($i = 0; $i < $tableCount; $i++) {
                if (!empty($processed['id'])) {
                    $instance_table = new Table($this->FG_FK_TABLENAMES[$i]);
                    $rowcount = $rowcount + $instance_table->Table_count($this->DBHandle, $this->FG_FK_EDITION_CLAUSE[$i], $processed['id']);
                }
            }
        }
        $this->FG_FK_RECORDS_COUNT = $rowcount;

        return ($rowcount > 0);
    }

    /**
     * Function to add_content
     *
     * @public
     */
    public function perform_add_content($sub_action, $id)
    {
        $processed = $this->getProcessed();
        $table_split = explode(":", $this->FG_TABLE_EDITION[$sub_action][14]);
        $instance_sub_table = new Table($table_split[0], $table_split[1] . ", " . $table_split[5]);

        $arr = is_array($processed[$table_split[1]]) ? $processed[$table_split[1]] : [$processed[$table_split[1]]];
        foreach ($arr as $value) {
            if (empty($table_split[12]) || preg_match('/' . $this->FG_regular[$table_split[12]][0] . '/', $value)) {
                // RESPECT REGULAR EXPRESSION
                $result_query = $instance_sub_table->Add_table($this->DBHandle, "'" . addslashes(trim($value)) . "', '" . addslashes(trim($id)) . "'");

                if (!$result_query) {
                    $findme = 'duplicate';
                    $pos_find = strpos($instance_sub_table->errstr, $findme);

                    if ($pos_find === false) {
                        echo $instance_sub_table->errstr;
                    } else {
                        $this->alarm_db_error_duplication = true;
                    }
                }
            }
        }
    }


    /**
     * Function to del_content
     *
     * @public
     */
    public function perform_del_content($sub_action, $id)
    {
        $processed = $this->getProcessed();
        $table_split = explode(":", $this->FG_TABLE_EDITION[$sub_action][14]);
        if (array_key_exists($table_split[1] . '_hidden', $processed)) {
            $value = trim($processed[$table_split[1] . '_hidden']);
        } else {
            $value = trim($processed[$table_split[1]]);
        }
        $instance_sub_table = new Table($table_split[0], $table_split[1] . ", " . $table_split[5]);
        $SPLIT_FG_DELETE_CLAUSE = $table_split[1] . "='" . $value . "' AND " . $table_split[5] . "='" . trim($id) . "'";
        $instance_sub_table->Delete_table($this->DBHandle, $SPLIT_FG_DELETE_CLAUSE);
    }


    /**
     * Function to create the top page section
     *
     * @public
     */
    public function create_toppage($form_action)
    {
        $html = '';
        $msg = '';
        if ($form_action === "ask-edit" || $form_action === "edit" || $form_action === "add-content" || $form_action === "del-content") {
            if ($this->FG_ADITION_GO_EDITION == "yes-done") {
                $msg = "<p class=\"danger\">$this->FG_ADITION_GO_EDITION_MESSAGE</p>";
            }
            if ($this->alarm_db_error_duplication) {
                $msg .= "<p class=\"danger\">$this->FG_TEXT_ERROR_DUPLICATION</p>";
            } else {
                $msg .= $this->FG_INTRO_TEXT_EDITION;
            }
            $html = "<div class='row pb-3 align-items-center'><div class='col'>$msg</div></div>";
        } elseif ($form_action == "ask-add" && !empty($this->FG_INTRO_TEXT_ADITION) > 1) {
            $html = "<div class='row pb-3 align-items-center'><div class='col'>$this->FG_INTRO_TEXT_ADITION</div></div>";
        }
        echo $html;
    }


    /**
     * CREATE_ACTIONFINISH : Function to display result
     * I think the only time this is used is if there is a database error when adding from A2B_entity_friend.php ???
     * @public
     */
    public function create_actionfinish($form_action)
    {
        if ($form_action === "delete") {
            $msg1 = "$this->FG_INSTANCE_NAME " . _("Deletion");
            $msg2 = $this->FG_INTRO_TEXT_DELETION;
        } elseif ($form_action === "add") {
            $msg1 = _("Insert New ") . $this->FG_INSTANCE_NAME;
            $msg2 = empty($this->RESULT_QUERY) ? "<span class='danger'>$this->FG_TEXT_ADITION_ERROR</span>" : $this->FG_INTRO_TEXT_ADITION;
        } else {
            return;
        }
        $html = "<div class='row pb-3'><div class='col'><p>$msg1</p><p>$msg2</p></div></div>";
        echo $html;
    }

    /**
     *  CREATE_CUSTOM : Function to display a custom message using form_action
     *
     * @public        TODO : maybe is better to allow use a string as parameter
     */
    public function create_custom($form_action)
    {
        $msg = "$form_action " . _("Done");
        $html = "<div class='row pb-3 align-items-center'><div class='col'><strong>$msg</strong></div></div>";
        echo $html;
    }

    /**
     * Function to create the search form
     *
     * @public
     */
    public function create_search_form()
    {
        Console::logSpeed('Time taken to get to line ' . __LINE__);
        $processed = $this->getProcessed();
        $list = null;

        $cur = 0;
        foreach ($this->FG_FILTER_SEARCH_FORM_SELECT as $select) {
            // 	If is a sql_type
            if ($select[1]) {
                $instance_table = new Table($select[1][0], $select[1][1]);
                $list = $instance_table->Get_list($this->DBHandle, $select[1][2], $select[1][3], $select[1][4]);
                $this->FG_FILTER_SEARCH_FORM_SELECT[$cur][1] = $list;
            } else {
                $this->FG_FILTER_SEARCH_FORM_SELECT[$cur][1] = $select[3];
            }
            $cur++;
        }
        $this->show_search($processed, $list);
    }

    /**
     * Function to create the form
     *
     * @public
     * @noinspection PhpUnusedParameterInspection
     */
    public function create_form($form_action, $list)
    {
        Console::logSpeed('Time taken to get to line ' . __LINE__);
        $processed = $this->getProcessed();

        $id = $processed['id'];
        $atmenu = $processed['atmenu'];
        $stitle = $processed['stitle'];
        $ratesort = $processed['ratesort'];
        $sub_action = $processed['sub_action'];

        switch ($form_action) {
            case "add-content":
                $this->perform_add_content($sub_action, $id);
                require(__DIR__ . "/../../templates/EditForm.inc.php");
                break;

            case "del-content":
                $this->perform_del_content($sub_action, $id);
                require(__DIR__ . "/../../templates/EditForm.inc.php");
                break;

            case "ask-edit":
            case "edit":
                require(__DIR__ . "/../../templates/EditForm.inc.php");
                break;

            case "ask-add":
                require(__DIR__ . "/../../templates/AddForm.inc.php");
                break;

            case "ask-delete":
            case "ask-del-confirm":
                if (strlen($this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE) > 0) {
                    $res_funct = call_user_func([FormBO::class, $this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE]);
                }
                require(__DIR__ . "/../../templates/DelForm.inc.php");
                break;

            case "list":
                require(__DIR__ . "/../../templates/ViewHandler.inc.php");
                break;

            case "delete":
            case "add":
                $this->create_actionfinish($form_action);
                break;

            default:
                $this->create_custom($form_action);
        }
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function show_search($processed, $list)
    {
        $id = $processed['id'];
        $atmenu = $processed['atmenu'];
        $stitle = $processed['stitle'];
        $ratesort = $processed['ratesort'];
        $sub_action = $processed['sub_action'];

        require(__DIR__ . "/../../templates/SearchHandler.inc.php");
    }

    /**
     * Do multi-page navigation.  Displays the prev, next and page options.
     *
     * @param int $page the page currently viewed
     * @param int $pages the maximum number of pages
     * @param string $url the url to refer to with the page number inserted
     * @param int $max_width the number of pages to make available at any one time (default = 20)
     */
    public function printPages(int $page, int $pages, string $url, int $max_width = 20)
    {
        Console::logSpeed('Time taken to get to line ' . __LINE__);
        $window = 8;

        if ($page < 0 || $page > $pages) {
            return;
        }
        if ($pages < 0) {
            return;
        }
        if ($max_width <= 0) {
            return;
        }

        if ($pages > 1) {
            if ($page != 1) {
                $temp = str_replace('%s', 0, $url);
                echo "<a class=\"pagenav\" href=\"$temp\">{$this->lang['strfirst']}</a>\n";
                $temp = str_replace('%s', $page - 2, $url);
                echo "<a class=\"pagenav\" href=\"$temp\">{$this->lang['strprev']}</a>\n";
            }

            if ($page <= $window) {
                $min_page = 1;
                $max_page = min(2 * $window, $pages);
            } elseif ($pages >= $page + $window) {
                $min_page = ($page - $window) + 1;
                $max_page = $page + $window;
            } else {
                $min_page = ($page - (2 * $window - ($pages - $page))) + 1;
                $max_page = $pages;
            }

            // Make sure min_page is always at least 1
            // and max_page is never greater than $pages
            $min_page = max($min_page, 1);
            $max_page = min($max_page, $pages);

            for ($i = $min_page; $i <= $max_page; $i++) {
                $temp = str_replace('%s', $i - 1, $url);
                if ($i != $page) {
                    echo "<a class=\"pagenav\" href=\"$temp\">$i</a>\n";
                } else {
                    echo "$i\n";
                }
            }
            if ($page != $pages) {
                $temp = str_replace('%s', $page, $url);
                echo "<a class=\"pagenav\" href=\"$temp\">{$this->lang['strnext']}</a>\n";
                $temp = str_replace('%s', $pages - 1, $url);
                echo "<a class=\"pagenav\" href=\"$temp\">{$this->lang['strlast']}</a>\n";
            }
        }
    }
}