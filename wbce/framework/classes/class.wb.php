<?php
/**
 * WebsiteBaker Community Edition (WBCE)
 * Way Better Content Editing.
 * Visit http://wbce.org to learn more and to join the community.
 *
 * @copyright Ryan Djurovich (2004-2009)
 * @copyright WebsiteBaker Org. e.V. (2009-2015)
 * @copyright WBCE Project (2015-)
 * @license GNU GPL2 (or any later version)
 */

//no direct file access
if(count(get_included_files())==1) die(header("Location: ../index.php",TRUE,301));

class wb extends SecureForm
{

    public $sDirectOutput="";

    public $password_chars = 'a-zA-Z0-9\_\-\!\#\*\+\@\$\&\:'; // General initialization function
                                                              // performed when frontend or backend is loaded.

    public function __construct($mode = SecureForm::FRONTEND)
    {
        parent::__construct($mode);
    }

/**
    @brief  for easy output of JSON strings XML for ajax...... 
*/    
    
    public function DirectOutput($sContent=false) {
        if (is_string($sContent)){
            $this->sDirectOutput.=$sContent;
        }
        
        if (empty ($this->sDirectOutput)) return;

        // kill all output buffering
        while (ob_get_level())
        {
            ob_end_clean ();
        }
        
        echo $this->sDirectOutput;
        exit;
    }
    
    
    
    
    
    
    
    
    
    
/* ****************
 * check if one or more group_ids are in both group_lists
 *
 * @access public
 * @param mixed $groups_list1: an array or a coma seperated list of group-ids
 * @param mixed $groups_list2: an array or a coma seperated list of group-ids
 * @param array &$matches: an array-var whitch will return possible matches
 * @return bool: true there is a match, otherwise false
 */
    public function is_group_match($groups_list1 = '', $groups_list2 = '', &$matches = null)
    {
        if ($groups_list1 == '') {return false;}
        if ($groups_list2 == '') {return false;}
        if (!is_array($groups_list1)) {
            $groups_list1 = explode(',', $groups_list1);
        }
        if (!is_array($groups_list2)) {
            $groups_list2 = explode(',', $groups_list2);
        }
        $matches = array_intersect($groups_list1, $groups_list2);
        return (sizeof($matches) != 0);
    }
/* ****************
 * check if current user is member of at least one of given groups
 * ADMIN (uid=1) always is treated like a member of any groups
 *
 * @access public
 * @param mixed $groups_list: an array or a coma seperated list of group-ids
 * @return bool: true if current user is member of one of this groups, otherwise false
 */
    public function ami_group_member($groups_list = '')
    {
        if ($this->get_group_id() == 1) {return true;}
        return $this->is_group_match($groups_list, $this->get_groups_id());
    }

    // Check whether a page is visible or not.
    // This will check page-visibility and user- and group-rights.
    /* page_is_visible() returns
    false: if page-visibility is 'none' or 'deleted', or page-vis. is 'registered' or 'private' and user isn't allowed to see the page.
    true: if page-visibility is 'public' or 'hidden', or page-vis. is 'registered' or 'private' and user _is_ allowed to see the page.
     */
    public function page_is_visible($page)
    {
        $show_it = false; // shall we show the page?
        $page_id = $page['page_id'];
        $visibility = $page['visibility'];
        $viewing_groups = $page['viewing_groups'];
        $viewing_users = $page['viewing_users'];

        // First check if visibility is 'none', 'deleted'
        if ($visibility == 'none') {
            return (false);
        } elseif ($visibility == 'deleted') {
            return (false);
        }

        // Now check if visibility is 'hidden', 'private' or 'registered'
        if ($visibility == 'hidden') {
            // hidden: hide the menu-link, but show the page
            $show_it = true;
        } elseif ($visibility == 'private' || $visibility == 'registered') {
            // Check if the user is logged in
            if ($this->is_authenticated() == true) {
                // Now check if the user has perms to view the page
                $in_group = false;
                foreach ($this->get_groups_id() as $cur_gid) {
                    if (in_array($cur_gid, explode(',', $viewing_groups))) {
                        $in_group = true;
                    }
                }
                if ($in_group || in_array($this->get_user_id(), explode(',', $viewing_users))) {
                    $show_it = true;
                } else {
                    $show_it = false;
                }
            } else {
                $show_it = false;
            }
        } elseif ($visibility == 'public') {
            $show_it = true;
        } else {
            $show_it = false;
        }
        return ($show_it);
    }
    // Check if there is at least one active section on this page
    public function page_is_active($page)
    {
        global $database;
        $has_active_sections = false;
        $page_id = $page['page_id'];
        $now = time();
        $sql = 'SELECT `publ_start`, `publ_end` ';
        $sql .= 'FROM `' . TABLE_PREFIX . 'sections` WHERE `page_id`=' . (int) $page_id;
        $query_sections = $database->query($sql);
        if ($query_sections->numRows() != 0) {
            while ($section = $query_sections->fetchRow()) {
                if ($now < $section['publ_end'] &&
                    ($now > $section['publ_start'] || $section['publ_start'] == 0) ||
                    $now > $section['publ_start'] && $section['publ_end'] == 0) {
                    $has_active_sections = true;
                    break;
                }
            }
        }
        return ($has_active_sections);
    }

    // Check whether we should show a page or not (for front-end)
    public function show_page($page)
    {
        $retval = ($this->page_is_visible($page) && $this->page_is_active($page));
        return $retval;
    }

    // Check if the user is already authenticated or not
    public function is_authenticated()
    {
        $retval = (isset($_SESSION['USER_ID']) and
            $_SESSION['USER_ID'] != "" and
            is_numeric($_SESSION['USER_ID']));
        return $retval;
    }

    // Modified addslashes function which takes into account magic_quotes
    public function add_slashes($input)
    {
        if (get_magic_quotes_gpc() || (!is_string($input))) {
            return $input;
        }
        return addslashes($input);
    }

    // Ditto for stripslashes
    // Attn: this is _not_ the counterpart to $this->add_slashes() !
    // Use stripslashes() to undo a preliminarily done $this->add_slashes()
    // The purpose of $this->strip_slashes() is to undo the effects of magic_quotes_gpc==On
    public function strip_slashes($input)
    {
        if (!get_magic_quotes_gpc() || (!is_string($input))) {
            return $input;
        }
        return stripslashes($input);
    }


    /**
    Strip values from magic quotes if magic quotes is on. 
    */
    function strip_magic($input) {
    if (get_magic_quotes_gpc() and is_string($input)) {
            return stripslashes($input);
        }
        return $input;
    }



    // Escape backslashes for use with mySQL LIKE strings
    public function escape_backslashes($input)
    {
        return str_replace("\\", "\\\\", $input);
    }

    public function page_link($link)
    {
        // Check for [wblink
        if (substr($link, 0, 7) == '[wblink') {
            return $link;
        }
        
        // Check for :// in the link (used in URL's) as well as mailto:
        if (strstr($link, '://') == '' and substr($link, 0, 7) != 'mailto:') {
            return WB_URL . PAGES_DIRECTORY . $link . PAGE_EXTENSION;
        }
        return $link;
        
    }

    // Get POST data
    public function get_post($field)
    {
        return (isset($_POST[$field]) ? $_POST[$field] : null);
    }

    // Get POST data and escape it
    public function get_post_escaped($field)
    {
        $result = $this->get_post($field);
        return (is_null($result)) ? null : $this->add_slashes($result);
    }

    // Get GET data
    public function get_get($field)
    {
        return (isset($_GET[$field]) ? $_GET[$field] : null);
    }

    // Get SESSION data
    public function get_session($field)
    {
        return (isset($_SESSION[$field]) ? $_SESSION[$field] : null);
    }

    // Get SERVER data
    public function get_server($field)
    {
        return (isset($_SERVER[$field]) ? $_SERVER[$field] : null);
    }

    // Get the current users id
    public function get_user_id()
    {
        return $this->get_session('USER_ID');
    }

    // Get the current users group id
    public function get_group_id()
    {
        return $this->get_session('GROUP_ID');
    }

    // Get the current users group ids
    public function get_groups_id()
    {
        return explode(",", $this->get_session('GROUPS_ID'));
    }

    // Get the current users group name
    public function get_group_name()
    {
        return implode(",", $this->get_session('GROUP_NAME'));
    }

    // Get the current users group name
    public function get_groups_name()
    {
        return $this->get_session('GROUP_NAME');
    }

    // Get the current users username
    public function get_username()
    {
        return $this->get_session('USERNAME');
    }

    // Get the current users display name
    public function get_display_name()
    {
        return $this->get_session('DISPLAY_NAME');
    }

    // Get the current users email address
    public function get_email()
    {
        return $this->get_session('EMAIL');
    }

    // Get the current users home folder
    public function get_home_folder()
    {
        return $this->get_session('HOME_FOLDER');
    }

    // Get the current users timezone
    public function get_timezone()
    {
        return (isset($_SESSION['USE_DEFAULT_TIMEZONE']) ? '-72000' : $_SESSION['TIMEZONE']);
    }

        // Return a system permission
    public function get_permission($name, $type = 'system')
    {
        // Append to permission type
        $type .= '_permissions';
        // Check if we have a section to check for
        if ($name == 'start') {
            return true;
        } else {
            // Set system permissions var
            $system_permissions = $this->get_session('SYSTEM_PERMISSIONS');
            // Set module permissions var
            $module_permissions = $this->get_session('MODULE_PERMISSIONS');
            // Set template permissions var
            $template_permissions = $this->get_session('TEMPLATE_PERMISSIONS');
            // Return true if system perm = 1
            if (isset($$type) && is_array($$type) && is_numeric(array_search($name, $$type))) {
                if ($type == 'system_permissions') {
                    return true;
                } else {
                    return false;
                }
            } else {
                if ($type == 'system_permissions') {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    public function get_user_details($user_id)
    {
        global $database;
        $retval = array('username' => 'unknown', 'display_name' => 'Unknown', 'email' => '');
        $sql = 'SELECT `username`,`display_name`,`email` ';
        $sql .= 'FROM `' . TABLE_PREFIX . 'users` ';
        $sql .= 'WHERE `user_id`=' . (int) $user_id;
        if (($resUsers = $database->query($sql))) {
            if (($recUser = $resUsers->fetchRow(MYSQLI_ASSOC))) {
                $retval = $recUser;
            }
        }
        return $retval;
    }





    // Validate supplied email address
    public function validate_email($email)
    {
        if (function_exists('idn_to_ascii')) {
            /* use pear if available */
            $email = idn_to_ascii($email);
        } else {
            require_once WB_PATH . '/include/idna_convert/idna_convert.class.php';
            $IDN = new idna_convert();
            $email = $IDN->encode($email);
            unset($IDN);
        }
        // regex from NorHei 2011-01-11
        $retval = preg_match("/^((([!#$%&'*+\\-\/\=?^_`{|}~\w])|([!#$%&'*+\\-\/\=?^_`{|}~\w][!#$%&'*+\\-\/\=?^_`{|}~\.\w]{0,}[!#$%&'*+\\-\/\=?^_`{|}~\w]))[@]\w+(([-.]|\-\-)\w+)*\.\w+(([-.]|\-\-)\w+)*)$/", $email);
        return ($retval != false);
    }

/* ****************
 * set one or more bit in a integer value
 *
 * @access public
 * @param int $value: reference to the integer, containing the value
 * @param int $bits2set: the bitmask witch shall be added to value
 * @return void
 */
    public function bit_set(&$value, $bits2set)
    {
        $value |= $bits2set;
    }

/* ****************
 * reset one or more bit from a integer value
 *
 * @access public
 * @param int $value: reference to the integer, containing the value
 * @param int $bits2reset: the bitmask witch shall be removed from value
 * @return void
 */
    public function bit_reset(&$value, $bits2reset)
    {
        $value &= ~$bits2reset;
    }

/* ****************
 * check if one or more bit in a integer value are set
 *
 * @access public
 * @param int $value: reference to the integer, containing the value
 * @param int $bits2set: the bitmask witch shall be added to value
 * @return void
 */
    public function bit_isset($value, $bits2test)
    {
        return (($value & $bits2test) == $bits2test);
    }

    // Print a success message which then automatically redirects the user to another page
    public function print_success($message, $redirect = 'index.php')
    {
        global $TEXT;
        if (is_array($message)) {
            $message = implode('<br />', $message);
        }
        // fetch redirect timer for sucess messages from settings table
        $redirect_timer = ((defined('REDIRECT_TIMER')) && (REDIRECT_TIMER <= 10000)) ? REDIRECT_TIMER : 0;
        // add template variables
        // Setup template object, parse vars to it, then parse it
        $tpl = new Template(dirname($this->correct_theme_source('success.htt')));
        $tpl->set_file('page', 'success.htt');
        $tpl->set_block('page', 'main_block', 'main');
        $tpl->set_block('main_block', 'show_redirect_block', 'show_redirect');
        $tpl->set_var('MESSAGE', $message);
        $tpl->set_var('REDIRECT', $redirect);
        $tpl->set_var('REDIRECT_TIMER', $redirect_timer);
        $tpl->set_var('NEXT', $TEXT['NEXT']);
        $tpl->set_var('BACK', $TEXT['BACK']);
        if ($redirect_timer == -1) {
            $tpl->set_block('show_redirect', '');
        } else {
            $tpl->parse('show_redirect', 'show_redirect_block', true);
        }
        $tpl->parse('main', 'main_block', false);
        $tpl->pparse('output', 'page');
    }

    // Print an error message
    public function print_error($message, $link = 'index.php', $auto_footer = true)
    {
        global $TEXT;
        if (is_array($message)) {
            $message = implode('<br />', $message);
        }
        // Setup template object, parse vars to it, then parse it
        $success_template = new Template(dirname($this->correct_theme_source('error.htt')));
        $success_template->set_file('page', 'error.htt');
        $success_template->set_block('page', 'main_block', 'main');
        $success_template->set_var('MESSAGE', $message);
        $success_template->set_var('LINK', $link);
        $success_template->set_var('BACK', $TEXT['BACK']);
        $success_template->parse('main', 'main_block', false);
        $success_template->pparse('output', 'page');
        if ($auto_footer == true) {
            if (method_exists($this, "print_footer")) {
                $this->print_footer();
            }
            exit();
        }
        
    }

    // Validate send email
    public function mail($fromaddress, $toaddress, $subject, $message, $fromname = '')
    {
/*
INTEGRATED OPEN SOURCE PHPMAILER CLASS FOR SMTP SUPPORT AND MORE
SOME SERVICE PROVIDERS DO NOT SUPPORT SENDING MAIL VIA PHP AS IT DOES NOT PROVIDE SMTP AUTHENTICATION
NEW WBMAILER CLASS IS ABLE TO SEND OUT MESSAGES USING SMTP WHICH RESOLVE THESE ISSUE (C. Sommer)

NOTE:
To use SMTP for sending out mails, you have to specify the SMTP host of your domain
via the Settings panel in the backend of Website Baker
 */

        $fromaddress = preg_replace('/[\r\n]/', '', $fromaddress);
        $toaddress = preg_replace('/[\r\n]/', '', $toaddress);
        $subject = preg_replace('/[\r\n]/', '', $subject);
        // $message_alt = $message;
        // $message = preg_replace('/[\r\n]/', '<br \>', $message);

        // create PHPMailer object and define default settings
        $myMail = new wbmailer();
        // set user defined from address
        if ($fromaddress != '') {
            if ($fromname != '') {
                $myMail->FromName = $fromname;
            }
                                               // FROM-NAME
            $myMail->From = $fromaddress;      // FROM:
            $myMail->AddReplyTo($fromaddress); // REPLY TO:
        }
                                                 // define recepient and information to send out
        $myMail->AddAddress($toaddress);         // TO:
        $myMail->Subject = $subject;             // SUBJECT
        $myMail->Body = nl2br($message);         // CONTENT (HTML)
        $myMail->AltBody = strip_tags($message); // CONTENT (TEXT)
                                                 // check if there are any send mail errors, otherwise say successful
        if (!$myMail->Send()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * checks if there is an alternative Theme template
     *
     * @param string $sThemeFile set the template.htt
     * @return string the relative theme path
     *
     */
    public function correct_theme_source($sThemeFile = 'start.htt')
    {
        $sRetval = $sThemeFile;
        if (file_exists(THEME_PATH . '/templates/' . $sThemeFile)) {
            $sRetval = THEME_PATH . '/templates/' . $sThemeFile;
        } 
        elseif (file_exists(WB_PATH."/templates/default_theme/templates/" . $sThemeFile)) {
            $sRetval = WB_PATH."/templates/default_theme/templates/" . $sThemeFile;
        } else {
            if (file_exists(ADMIN_PATH . '/themes/templates/' . $sThemeFile)) {
                $sRetval = ADMIN_PATH . '/themes/templates/' . $sThemeFile;
            } else {
                throw new InvalidArgumentException('missing template file ' . $sThemeFile);
            }
        }
        return $sRetval;
    }

    /**
     * Check if a foldername doesn't have invalid characters
     *
     * @param String $str to check
     * @return Bool
     */
    public function checkFolderName($str)
    {
        return !(preg_match('#\^|\\\|\/|\.|\?|\*|"|\'|\<|\>|\:|\|#i', $str) ? true : false);
    }

    /**
     * Check the given path to make sure current path is within given basedir
     * normally document root
     *
     * @param String $sCurrentPath
     * @param String $sBaseDir
     * @return $sCurrentPath or FALSE
     */
    public function checkpath($sCurrentPath, $sBaseDir = WB_PATH)
    {
        // Clean the cuurent path
        $sCurrentPath = rawurldecode($sCurrentPath);
        $sCurrentPath = realpath($sCurrentPath);
        $sBaseDir = realpath($sBaseDir);
        // $sBaseDir needs to exist in the $sCurrentPath
        $pos = stripos($sCurrentPath, $sBaseDir);

        if ($pos === false) {
            return false;
        } elseif ($pos == 0) {
            return $sCurrentPath;
        } else {
            return false;
        }
    }
    
    /*
 * replace all "[wblink{page_id}]" with real links
 * @param string &$content : reference to global $content
 * @return void
 * @history 100216 17:00:00 optimise errorhandling, speed, SQL-strict
 */
    public function preprocess(&$content)
    {
        global $database;
        $replace_list = array();
        $pattern = '/\[wblink([0-9]+)\]/isU';
        if (preg_match_all($pattern, $content, $ids)) {
            foreach ($ids[1] as $key => $page_id) {
                $replace_list[$page_id] = $ids[0][$key];
            }
            foreach ($replace_list as $page_id => $tag) {
                $sql = 'SELECT `link` FROM `' . TABLE_PREFIX . 'pages` WHERE `page_id` = ' . (int) $page_id;
                $link = $database->get_one($sql);
                if (!is_null($link)) {
                    $link = $this->page_link($link);
                    $content = str_replace($tag, $link, $content);
                }
            }
        }
    }

 

}
