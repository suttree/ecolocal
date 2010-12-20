<?php
/* $Id: common.lib.php 10318 2007-04-24 03:54:30Z lem9 $ */
// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Misc stuff and functions used by almost all the scripts.
 * Among other things, it contains the advanced authentication work.
 */

/**
 * Order of sections for common.lib.php:
 *
 * the include of libraries/defines_mysql.lib.php must be after the connection
 * to db to get the MySql version
 *
 * the authentication libraries must be before the connection to db
 *
 * ... so the required order is:
 *
 * LABEL_definition_of_functions
 *  - definition of functions
 * LABEL_variables_init
 *  - init some variables always needed
 * LABEL_parsing_config_file
 *  - parsing of the config file
 * LABEL_loading_language_file
 *  - loading language file
 * LABEL_theme_setup
 *  - setting up themes
 *
 * - load of mysql extension (if necessary) label_loading_mysql
 * - loading of an authentication library label_
 * - db connection
 * - authentication work
 * - load of the libraries/defines_mysql.lib.php library to get the MySQL
 *   release number
 */

/**
 * For now, avoid warnings of E_STRICT mode
 * (this must be done before function definitions)
 */

if (defined('E_STRICT')) {
    $old_error_reporting = error_reporting(0);
    if ($old_error_reporting & E_STRICT) {
        error_reporting($old_error_reporting ^ E_STRICT);
    } else {
        error_reporting($old_error_reporting);
    }
    unset($old_error_reporting);
}

/**
 * Avoid object cloning errors
 */

@ini_set('zend.ze1_compatibility_mode',false);

/**
 * Avoid problems with magic_quotes_runtime
 */

@ini_set('magic_quotes_runtime',false);


/******************************************************************************/
/* definition of functions         LABEL_definition_of_functions              */
/**
 * Removes insecure parts in a path; used before include() or
 * require() when a part of the path comes from an insecure source
 * like a cookie or form.
 *
 * @param    string  The path to check
 *
 * @return   string  The secured path
 *
 * @access  public
 * @author  Marc Delisle (lem9@users.sourceforge.net)
 */
function PMA_securePath($path)
{
    // change .. to .
    $path = preg_replace('@\.\.*@', '.', $path);

    return $path;
} // end function

/**
 * returns count of tables in given db
 *
 * @param   string  $db database to count tables for
 * @return  integer count of tables in $db
 */
function PMA_getTableCount($db)
{
    $tables = PMA_DBI_try_query(
        'SHOW TABLES FROM ' . PMA_backquote($db) . ';',
        null, PMA_DBI_QUERY_STORE);
    if ($tables) {
        $num_tables = PMA_DBI_num_rows($tables);
        PMA_DBI_free_result($tables);
    } else {
        $num_tables = 0;
    }

    return $num_tables;
}

/**
 * Converts numbers like 10M into bytes
 * Used with permission from Moodle (http://moodle.org) by Martin Dougiamas
 * (renamed with PMA prefix to avoid double definition when embedded
 * in Moodle)
 *
 * @param   string  $size
 * @return  integer $size
 */
function PMA_get_real_size($size = 0)
{
    if (!$size) {
        return 0;
    }
    $scan['MB'] = 1048576;
    $scan['Mb'] = 1048576;
    $scan['M']  = 1048576;
    $scan['m']  = 1048576;
    $scan['KB'] =    1024;
    $scan['Kb'] =    1024;
    $scan['K']  =    1024;
    $scan['k']  =    1024;

    while (list($key) = each($scan)) {
        if ((strlen($size) > strlen($key))
          && (substr($size, strlen($size) - strlen($key)) == $key)) {
            $size = substr($size, 0, strlen($size) - strlen($key)) * $scan[$key];
            break;
        }
    }
    return $size;
} // end function PMA_get_real_size()

/**
 * loads php module
 *
 * @uses    PHP_OS
 * @uses    extension_loaded()
 * @uses    ini_get()
 * @uses    function_exists()
 * @uses    ob_start()
 * @uses    phpinfo()
 * @uses    strip_tags()
 * @uses    ob_get_contents()
 * @uses    ob_end_clean()
 * @uses    preg_match()
 * @uses    strtoupper()
 * @uses    substr()
 * @uses    dl()
 * @param   string  $module name if module to load
 * @return  boolean success loading module
 */
function PMA_dl($module)
{
    static $dl_allowed = null;

    if (extension_loaded($module)) {
        return true;
    }

    if (null === $dl_allowed) {
        if (!@ini_get('safe_mode')
          && @ini_get('enable_dl')
          && @function_exists('dl')) {
            ob_start();
            phpinfo(INFO_GENERAL); /* Only general info */
            $a = strip_tags(ob_get_contents());
            ob_end_clean();
            if (preg_match('@Thread Safety[[:space:]]*enabled@', $a)) {
                if (preg_match('@Server API[[:space:]]*\(CGI\|CLI\)@', $a)) {
                    $dl_allowed = true;
                } else {
                    $dl_allowed = false;
                }
            } else {
                $dl_allowed = true;
            }
        } else {
            $dl_allowed = false;
        }
    }

    if (!$dl_allowed) {
        return false;
    }

    /* Once we require PHP >= 4.3, we might use PHP_SHLIB_SUFFIX here */
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $module_file = 'php_' . $module . '.dll';
    } elseif (PHP_OS=='HP-UX') {
        $module_file = $module . '.sl';
    } else {
        $module_file = $module . '.so';
    }

    return @dl($module_file);
}

/**
 * merges array recursive like array_merge_recursive() but keyed-values are
 * always overwritten.
 *
 * array PMA_array_merge_recursive(array $array1[, array $array2[, array ...]])
 *
 * @see     http://php.net/array_merge
 * @see     http://php.net/array_merge_recursive
 * @uses    func_num_args()
 * @uses    func_get_arg()
 * @uses    is_array()
 * @uses    call_user_func_array()
 * @param   array   array to merge
 * @param   array   array to merge
 * @param   array   ...
 * @return  array   merged array
 */
function PMA_array_merge_recursive()
{
    switch(func_num_args()) {
        case 0 :
            return false;
            break;
        case 1 :
            // when does that happen?
            return func_get_arg(0);
            break;
        case 2 :
            $args = func_get_args();
            if (!is_array($args[0]) || !is_array($args[1])) {
                return $args[1];
            }
            foreach ($args[1] as $key2 => $value2) {
                if (isset($args[0][$key2]) && !is_int($key2)) {
                    $args[0][$key2] = PMA_array_merge_recursive($args[0][$key2],
                        $value2);
                } else {
                    // we erase the parent array, otherwise we cannot override a directive that
                    // contains array elements, like this:
                    // (in config.default.php) $cfg['ForeignKeyDropdownOrder'] = array('id-content','content-id');
                    // (in config.inc.php) $cfg['ForeignKeyDropdownOrder'] = array('content-id');
                    if (is_int($key2) && $key2 == 0) {
                        unset($args[0]);
                    }
                    $args[0][$key2] = $value2;
                }
            }
            return $args[0];
            break;
        default :
            $args = func_get_args();
            $args[1] = PMA_array_merge_recursive($args[0], $args[1]);
            array_shift($args);
            return call_user_func_array('PMA_array_merge_recursive', $args);
            break;
    }
}

/**
 * calls $function vor every element in $array recursively
 *
 * @param   array   $array      array to walk
 * @param   string  $function   function to call for every array element
 */
function PMA_arrayWalkRecursive(&$array, $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            PMA_arrayWalkRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter++;
}

/**
 * boolean phpMyAdmin.PMA_checkPageValidity(string &$page, array $whitelist)
 *
 * checks given given $page against given $whitelist and returns true if valid
 * it ignores optionaly query paramters in $page (script.php?ignored)
 *
 * @uses    in_array()
 * @uses    urldecode()
 * @uses    substr()
 * @uses    strpos()
 * @param   string  &$page      page to check
 * @param   array   $whitelist  whitelist to check page against
 * @return  boolean whether $page is valid or not (in $whitelist or not)
 */
function PMA_checkPageValidity(&$page, $whitelist)
{
    if (! isset($page) || !is_string($page)) {
        return false;
    }

    if (in_array($page, $whitelist)) {
        return true;
    } elseif (in_array(substr($page, 0, strpos($page . '?', '?')), $whitelist)) {
        return true;
    } else {
        $_page = urldecode($page);
        if (in_array(substr($_page, 0, strpos($_page . '?', '?')), $whitelist)) {
            return true;
        }
    }
    return false;
}

/**
 * trys to find the value for the given environment vriable name
 *
 * searchs in $_SERVER, $_ENV than trys getenv() and apache_getenv()
 * in this order
 *
 * @param   string  $var_name   variable name
 * @return  string  value of $var or empty string
 */
function PMA_getenv($var_name) {
    if (isset($_SERVER[$var_name])) {
        return $_SERVER[$var_name];
    } elseif (isset($_ENV[$var_name])) {
        return $_ENV[$var_name];
    } elseif (getenv($var_name)) {
        return getenv($var_name);
    } elseif (function_exists('apache_getenv')
     && apache_getenv($var_name, true)) {
        return apache_getenv($var_name, true);
    }

    return '';
}

/**
 * removes cookie
 *
 * @uses    PMA_Config::isHttps()
 * @uses    PMA_Config::getCookiePath()
 * @uses    setcookie()
 * @uses    time()
 * @param   string  $cookie     name of cookie to remove
 * @return  boolean result of setcookie()
 */
function PMA_removeCookie($cookie)
{
    return setcookie($cookie, '', time() - 3600,
        PMA_Config::getCookiePath(), '', PMA_Config::isHttps());
}

/**
 * sets cookie if value is different from current cokkie value,
 * or removes if value is equal to default
 *
 * @uses    PMA_Config::isHttps()
 * @uses    PMA_Config::getCookiePath()
 * @uses    $_COOKIE
 * @uses    PMA_removeCookie()
 * @uses    setcookie()
 * @uses    time()
 * @param   string  $cookie     name of cookie to remove
 * @param   mixed   $value      new cookie value
 * @param   string  $default    default value
 * @param   int     $validity   validity of cookie in seconds (default is one month)
 * @param   bool    $httponlt   whether cookie is only for HTTP (and not for scripts)
 * @return  boolean result of setcookie()
 */
function PMA_setCookie($cookie, $value, $default = null, $validity = null, $httponly = true)
{
    if ($validity == null) {
        $validity = 2592000;
    }
    if (strlen($value) && null !== $default && $value === $default
     && isset($_COOKIE[$cookie])) {
        // remove cookie, default value is used
        return PMA_removeCookie($cookie);
    }

    if (! strlen($value) && isset($_COOKIE[$cookie])) {
        // remove cookie, value is empty
        return PMA_removeCookie($cookie);
    }

    if (! isset($_COOKIE[$cookie]) || $_COOKIE[$cookie] !== $value) {
        // set cookie with new value
        /* Calculate cookie validity */
        if ($validity == 0) {
            $v = 0;
        } else {
            $v = time() + $validity;
        }
        /* Use native support for httponly cookies if available */
        if (version_compare(PHP_VERSION, '5.2.0', 'ge')) {
            return setcookie($cookie, $value, $v,
                PMA_Config::getCookiePath(), '', PMA_Config::isHttps(), $httponly);
        } else {
            return setcookie($cookie, $value, $v,
                PMA_Config::getCookiePath() . ($httponly ? '; HttpOnly' : ''), '', PMA_Config::isHttps());
        }
    }

    // cookie has already $value as value
    return true;
}

/**
 * include here only libraries which contain only function definitions
 * no code im main()!
 */
/**
 * Input sanitizing
 */
require_once './libraries/sanitizing.lib.php';
/**
 * the PMA_Theme class
 */
require_once './libraries/Theme.class.php';
/**
 * the PMA_Theme_Manager class
 */
require_once './libraries/Theme_Manager.class.php';
/**
 * the PMA_Config class
 */
require_once './libraries/Config.class.php';
/**
 * the PMA_Table class
 */
require_once './libraries/Table.class.php';


if (!defined('PMA_MINIMUM_COMMON')) {

    /**
     * Java script escaping.
     */
    require_once './libraries/js_escape.lib.php';

    /**
     * Exponential expression / raise number into power
     *
     * @uses    function_exists()
     * @uses    bcpow()
     * @uses    gmp_pow()
     * @uses    gmp_strval()
     * @uses    pow()
     * @param   number  $base
     * @param   number  $exp
     * @param   string  pow function use, or false for auto-detect
     * @return  mixed  string or float
     */
    function PMA_pow($base, $exp, $use_function = false)
    {
        static $pow_function = null;
        if (null == $pow_function) {
            if (function_exists('bcpow')) {
                // BCMath Arbitrary Precision Mathematics Function
                $pow_function = 'bcpow';
            } elseif (function_exists('gmp_pow')) {
                // GMP Function
                $pow_function = 'gmp_pow';
            } else {
                // PHP function
                $pow_function = 'pow';
            }
        }

        if (! $use_function) {
            $use_function = $pow_function;
        }

        switch ($use_function) {
            case 'bcpow' :
                $pow = bcpow($base, $exp);
                break;
            case 'gmp_pow' :
                $pow = gmp_strval(gmp_pow($base, $exp));
                break;
            case 'pow' :
                $base = (float) $base;
                $exp = (int) $exp;
                if ($exp < 0) {
                    return false;
                }
                $pow = pow($base, $exp);
                break;
            default:
                $pow = $use_function($base, $exp);
        }

        return $pow;
    }

    /**
     * string PMA_getIcon(string $icon)
     *
     * @uses    $GLOBALS['pmaThemeImage']
     * @param   $icon   name of icon
     * @return          html img tag
     */
    function PMA_getIcon($icon, $alternate = '')
    {
        if ($GLOBALS['cfg']['PropertiesIconic']) {
            return '<img src="' . $GLOBALS['pmaThemeImage'] . $icon . '"'
                . ' title="' . $alternate . '" alt="' . $alternate . '"'
                . ' class="icon" width="16" height="16" />';
        } else {
            return $alternate;
        }
    }

    /**
     * Displays the maximum size for an upload
     *
     * @param   integer  the size
     *
     * @return  string   the message
     *
     * @access  public
     */
    function PMA_displayMaximumUploadSize($max_upload_size)
    {
        list($max_size, $max_unit) = PMA_formatByteDown($max_upload_size);
        return '(' . sprintf($GLOBALS['strMaximumSize'], $max_size, $max_unit) . ')';
    }

    /**
     * Generates a hidden field which should indicate to the browser
     * the maximum size for upload
     *
     * @param   integer  the size
     *
     * @return  string   the INPUT field
     *
     * @access  public
     */
     function PMA_generateHiddenMaxFileSize($max_size)
     {
         return '<input type="hidden" name="MAX_FILE_SIZE" value="' .$max_size . '" />';
     }

    /**
     * Add slashes before "'" and "\" characters so a value containing them can
     * be used in a sql comparison.
     *
     * @param   string   the string to slash
     * @param   boolean  whether the string will be used in a 'LIKE' clause
     *                   (it then requires two more escaped sequences) or not
     * @param   boolean  whether to treat cr/lfs as escape-worthy entities
     *                   (converts \n to \\n, \r to \\r)
     *
     * @param   boolean  whether this function is used as part of the
     *                   "Create PHP code" dialog
     *
     * @return  string   the slashed string
     *
     * @access  public
     */
    function PMA_sqlAddslashes($a_string = '', $is_like = false, $crlf = false, $php_code = false)
    {
        if ($is_like) {
            $a_string = str_replace('\\', '\\\\\\\\', $a_string);
        } else {
            $a_string = str_replace('\\', '\\\\', $a_string);
        }

        if ($crlf) {
            $a_string = str_replace("\n", '\n', $a_string);
            $a_string = str_replace("\r", '\r', $a_string);
            $a_string = str_replace("\t", '\t', $a_string);
        }

        if ($php_code) {
            $a_string = str_replace('\'', '\\\'', $a_string);
        } else {
            $a_string = str_replace('\'', '\'\'', $a_string);
        }

        return $a_string;
    } // end of the 'PMA_sqlAddslashes()' function


    /**
     * Add slashes before "_" and "%" characters for using them in MySQL
     * database, table and field names.
     * Note: This function does not escape backslashes!
     *
     * @param   string   the string to escape
     *
     * @return  string   the escaped string
     *
     * @access  public
     */
    function PMA_escape_mysql_wildcards($name)
    {
        $name = str_replace('_', '\\_', $name);
        $name = str_replace('%', '\\%', $name);

        return $name;
    } // end of the 'PMA_escape_mysql_wildcards()' function

    /**
     * removes slashes before "_" and "%" characters
     * Note: This function does not unescape backslashes!
     *
     * @param   string   $name  the string to escape
     * @return  string   the escaped string
     * @access  public
     */
    function PMA_unescape_mysql_wildcards($name)
    {
        $name = str_replace('\\_', '_', $name);
        $name = str_replace('\\%', '%', $name);

        return $name;
    } // end of the 'PMA_unescape_mysql_wildcards()' function

    /**
     * removes quotes (',",`) from a quoted string
     *
     * checks if the sting is quoted and removes this quotes
     *
     * @param   string  $quoted_string  string to remove quotes from
     * @param   string  $quote          type of quote to remove
     * @return  string  unqoted string
     */
    function PMA_unQuote($quoted_string, $quote = null)
    {
        $quotes = array();

        if (null === $quote) {
            $quotes[] = '`';
            $quotes[] = '"';
            $quotes[] = "'";
        } else {
            $quotes[] = $quote;
        }

        foreach ($quotes as $quote) {
            if (substr($quoted_string, 0, 1) === $quote
             && substr($quoted_string, -1, 1) === $quote ) {
                 $unquoted_string = substr($quoted_string, 1, -1);
                 // replace escaped quotes
                 $unquoted_string = str_replace($quote . $quote, $quote, $unquoted_string);
                 return $unquoted_string;
             }
        }

        return $quoted_string;
    }

    /**
     * format sql strings
     *
     * @param   mixed    pre-parsed SQL structure
     *
     * @return  string   the formatted sql
     *
     * @global  array    the configuration array
     * @global  boolean  whether the current statement is a multiple one or not
     *
     * @access  public
     *
     * @author  Robin Johnson <robbat2@users.sourceforge.net>
     */
    function PMA_formatSql($parsed_sql, $unparsed_sql = '')
    {
        global $cfg;

        // Check that we actually have a valid set of parsed data
        // well, not quite
        // first check for the SQL parser having hit an error
        if (PMA_SQP_isError()) {
            return $parsed_sql;
        }
        // then check for an array
        if (!is_array($parsed_sql)) {
            // We don't so just return the input directly
            // This is intended to be used for when the SQL Parser is turned off
            $formatted_sql = '<pre>' . "\n"
                            . (($cfg['SQP']['fmtType'] == 'none' && $unparsed_sql != '') ? $unparsed_sql : $parsed_sql) . "\n"
                            . '</pre>';
            return $formatted_sql;
        }

        $formatted_sql        = '';

        switch ($cfg['SQP']['fmtType']) {
            case 'none':
                if ($unparsed_sql != '') {
                    $formatted_sql = "<pre>\n" . PMA_SQP_formatNone(array('raw' => $unparsed_sql)) . "\n</pre>";
                } else {
                    $formatted_sql = PMA_SQP_formatNone($parsed_sql);
                }
                break;
            case 'html':
                $formatted_sql = PMA_SQP_formatHtml($parsed_sql, 'color');
                break;
            case 'text':
                //$formatted_sql = PMA_SQP_formatText($parsed_sql);
                $formatted_sql = PMA_SQP_formatHtml($parsed_sql, 'text');
                break;
            default:
                break;
        } // end switch

        return $formatted_sql;
    } // end of the "PMA_formatSql()" function


    /**
     * Displays a link to the official MySQL documentation
     *
     * @param string  chapter of "HTML, one page per chapter" documentation
     * @param string  contains name of page/anchor that is being linked
     * @param bool    whether to use big icon (like in left frame)
     *
     * @return  string  the html link
     *
     * @access  public
     */
    function PMA_showMySQLDocu($chapter, $link, $big_icon = false)
    {
        global $cfg;

        if ($cfg['MySQLManualType'] == 'none' || empty($cfg['MySQLManualBase'])) {
            return '';
        }

        // Fixup for newly used names:
        $chapter = str_replace('_', '-', strtolower($chapter));
        $link = str_replace('_', '-', strtolower($link));

        switch ($cfg['MySQLManualType']) {
            case 'chapters':
                if (empty($chapter)) {
                    $chapter = 'index';
                }
                $url = $cfg['MySQLManualBase'] . '/' . $chapter . '.html#' . $link;
                break;
            case 'big':
                $url = $cfg['MySQLManualBase'] . '#' . $link;
                break;
            case 'searchable':
                if (empty($link)) {
                    $link = 'index';
                }
                $url = $cfg['MySQLManualBase'] . '/' . $link . '.html';
                break;
            case 'viewable':
            default:
                if (empty($link)) {
                    $link = 'index';
                }
                $mysql = '5.0';
                $lang = 'en';
                if (defined('PMA_MYSQL_INT_VERSION')) {
                    if (PMA_MYSQL_INT_VERSION < 50000) {
                        $mysql = '4.1';
                        if (!empty($GLOBALS['mysql_4_1_doc_lang'])) {
                            $lang = $GLOBALS['mysql_4_1_doc_lang'];
                        }
                    } elseif (PMA_MYSQL_INT_VERSION >= 50100) {
                        $mysql = '5.1';
                        if (!empty($GLOBALS['mysql_5_1_doc_lang'])) {
                            $lang = $GLOBALS['mysql_5_1_doc_lang'];
                        }
                    } elseif (PMA_MYSQL_INT_VERSION >= 50000) {
                        $mysql = '5.0';
                        if (!empty($GLOBALS['mysql_5_0_doc_lang'])) {
                            $lang = $GLOBALS['mysql_5_0_doc_lang'];
                        }
                    }
                }
                $url = $cfg['MySQLManualBase'] . '/' . $mysql . '/' . $lang . '/' . $link . '.html';
                break;
        }

        if ($big_icon) {
            return '<a href="' . $url . '" target="mysql_doc"><img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 'b_sqlhelp.png" width="16" height="16" alt="' . $GLOBALS['strDocu'] . '" title="' . $GLOBALS['strDocu'] . '" /></a>';
        } elseif ($GLOBALS['cfg']['ReplaceHelpImg']) {
            return '<a href="' . $url . '" target="mysql_doc"><img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 'b_help.png" width="11" height="11" alt="' . $GLOBALS['strDocu'] . '" title="' . $GLOBALS['strDocu'] . '" /></a>';
        } else {
            return '[<a href="' . $url . '" target="mysql_doc">' . $GLOBALS['strDocu'] . '</a>]';
        }
    } // end of the 'PMA_showMySQLDocu()' function

    /**
     * Displays a hint icon, on mouse over show the hint
     *
     * @param   string   the error message
     *
     * @access  public
     */
     function PMA_showHint($hint_message)
     {
         //return '<img class="lightbulb" src="' . $GLOBALS['pmaThemeImage'] . 'b_tipp.png" width="16" height="16" border="0" alt="' . $hint_message . '" title="' . $hint_message . '" align="middle" onclick="alert(\'' . PMA_jsFormat($hint_message, false) . '\');" />';
         return '<img class="lightbulb" src="' . $GLOBALS['pmaThemeImage'] . 'b_tipp.png" width="16" height="16" alt="Tip" title="Tip" onmouseover="pmaTooltip(\'' .  PMA_jsFormat($hint_message, false) . '\'); return false;" onmouseout="swapTooltip(\'default\'); return false;" />';
     }

    /**
     * Displays a MySQL error message in the right frame.
     *
     * @param   string   the error message
     * @param   string   the sql query that failed
     * @param   boolean  whether to show a "modify" link or not
     * @param   string   the "back" link url (full path is not required)
     * @param   boolean  EXIT the page?
     *
     * @global  array    the configuration array
     *
     * @access  public
     */
    function PMA_mysqlDie($error_message = '', $the_query = '',
                            $is_modify_link = true, $back_url = '',
                            $exit = true)
    {
        global $cfg, $table, $db, $sql_query;

        /**
         * start http output, display html headers
         */
        require_once './libraries/header.inc.php';

        if (!$error_message) {
            $error_message = PMA_DBI_getError();
        }
        if (!$the_query && !empty($GLOBALS['sql_query'])) {
            $the_query = $GLOBALS['sql_query'];
        }

        // --- Added to solve bug #641765
        // Robbat2 - 12 January 2003, 9:46PM
        // Revised, Robbat2 - 13 January 2003, 2:59PM
        if (!function_exists('PMA_SQP_isError') || PMA_SQP_isError()) {
            $formatted_sql = htmlspecialchars($the_query);
        } elseif (empty($the_query) || trim($the_query) == '') {
            $formatted_sql = '';
        } else {
            $formatted_sql = PMA_formatSql(PMA_SQP_parse($the_query), $the_query);
        }
        // ---
        echo "\n" . '<!-- PMA-SQL-ERROR -->' . "\n";
        echo '    <div class="error"><h1>' . $GLOBALS['strError'] . '</h1>' . "\n";
        // if the config password is wrong, or the MySQL server does not
        // respond, do not show the query that would reveal the
        // username/password
        if (!empty($the_query) && !strstr($the_query, 'connect')) {
            // --- Added to solve bug #641765
            // Robbat2 - 12 January 2003, 9:46PM
            // Revised, Robbat2 - 13 January 2003, 2:59PM
            if (function_exists('PMA_SQP_isError') && PMA_SQP_isError()) {
                echo PMA_SQP_getErrorString() . "\n";
                echo '<br />' . "\n";
            }
            // ---
            // modified to show me the help on sql errors (Michael Keck)
            echo '    <p><strong>' . $GLOBALS['strSQLQuery'] . ':</strong>' . "\n";
            if (strstr(strtolower($formatted_sql), 'select')) { // please show me help to the error on select
                echo PMA_showMySQLDocu('SQL-Syntax', 'SELECT');
            }
            if ($is_modify_link && isset($db)) {
                if (isset($table)) {
                    $doedit_goto = '<a href="tbl_sql.php?' . PMA_generate_common_url($db, $table) . '&amp;sql_query=' . urlencode($the_query) . '&amp;show_query=1">';
                } else {
                    $doedit_goto = '<a href="db_sql.php?' . PMA_generate_common_url($db) . '&amp;sql_query=' . urlencode($the_query) . '&amp;show_query=1">';
                }
                if ($GLOBALS['cfg']['PropertiesIconic']) {
                    echo $doedit_goto
                       . '<img class="icon" src=" '. $GLOBALS['pmaThemeImage'] . 'b_edit.png" width="16" height="16" alt="' . $GLOBALS['strEdit'] .'" />'
                       . '</a>';
                } else {
                    echo '    ['
                       . $doedit_goto . $GLOBALS['strEdit'] . '</a>'
                       . ']' . "\n";
                }
            } // end if
            echo '    </p>' . "\n"
                .'    <p>' . "\n"
                .'        ' . $formatted_sql . "\n"
                .'    </p>' . "\n";
        } // end if

        $tmp_mysql_error = ''; // for saving the original $error_message
        if (!empty($error_message)) {
            $tmp_mysql_error = strtolower($error_message); // save the original $error_message
            $error_message = htmlspecialchars($error_message);
            $error_message = preg_replace("@((\015\012)|(\015)|(\012)){3,}@", "\n\n", $error_message);
        }
        // modified to show me the help on error-returns (Michael Keck)
        // (now error-messages-server)
        echo '<p>' . "\n"
                . '    <strong>' . $GLOBALS['strMySQLSaid'] . '</strong>'
                . PMA_showMySQLDocu('Error-messages-server', 'Error-messages-server')
                . "\n"
                . '</p>' . "\n";

        // The error message will be displayed within a CODE segment.
        // To preserve original formatting, but allow wordwrapping, we do a couple of replacements

        // Replace all non-single blanks with their HTML-counterpart
        $error_message = str_replace('  ', '&nbsp;&nbsp;', $error_message);
        // Replace TAB-characters with their HTML-counterpart
        $error_message = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $error_message);
        // Replace linebreaks
        $error_message = nl2br($error_message);

        echo '<code>' . "\n"
            . $error_message . "\n"
            . '</code><br />' . "\n";

        // feature request #1036254:
        // Add a link by MySQL-Error #1062 - Duplicate entry
        // 2004-10-20 by mkkeck
        // 2005-01-17 modified by mkkeck bugfix
        if (substr($error_message, 1, 4) == '1062') {
            // get the duplicate entry

            // get table name
            /**
             * @todo what would be the best delimiter, while avoiding special
             * characters that can become high-ascii after editing, depending
             * upon which editor is used by the developer?
             */
            $error_table = array();
            if (preg_match('@ALTER\s*TABLE\s*\`([^\`]+)\`@iu', $the_query, $error_table)) {
                $error_table = $error_table[1];
            } elseif (preg_match('@INSERT\s*INTO\s*\`([^\`]+)\`@iu', $the_query, $error_table)) {
                $error_table = $error_table[1];
            } elseif (preg_match('@UPDATE\s*\`([^\`]+)\`@iu', $the_query, $error_table)) {
                $error_table = $error_table[1];
            } elseif (preg_match('@INSERT\s*\`([^\`]+)\`@iu', $the_query, $error_table)) {
                $error_table = $error_table[1];
            }

            // get fields
            $error_fields = array();
            if (preg_match('@\(([^\)]+)\)@i', $the_query, $error_fields)) {
                $error_fields = explode(',', $error_fields[1]);
            } elseif (preg_match('@(`[^`]+`)\s*=@i', $the_query, $error_fields)) {
                $error_fields = explode(',', $error_fields[1]);
            }
            if (is_array($error_table) || is_array($error_fields)) {

                // duplicate value
                $duplicate_value = array();
                preg_match('@\'([^\']+)\'@i', $tmp_mysql_error, $duplicate_value);
                $duplicate_value = $duplicate_value[1];

                $sql = '
                     SELECT *
                       FROM ' . PMA_backquote($error_table) . '
                      WHERE CONCAT_WS("-", ' . implode(', ', $error_fields) . ')
                            = "' . PMA_sqlAddslashes($duplicate_value) . '"
                   ORDER BY ' . implode(', ', $error_fields);
                unset($error_table, $error_fields, $duplicate_value);

                echo '        <form method="post" action="import.php" style="padding: 0; margin: 0">' ."\n"
                    .'            <input type="hidden" name="sql_query" value="' . htmlentities($sql) . '" />' . "\n"
                    .'            ' . PMA_generate_common_hidden_inputs($db, $table) . "\n"
                    .'            <input type="submit" name="submit" value="' . $GLOBALS['strBrowse'] . '" />' . "\n"
                    .'        </form>' . "\n";
                unset($sql);
            }
        } // end of show duplicate entry

        echo '</div>';
        echo '<fieldset class="tblFooters">';

        if (!empty($back_url) && $exit) {
            $goto_back_url='<a href="' . (strstr($back_url, '?') ? $back_url . '&amp;no_history=true' : $back_url . '?no_history=true') . '">';
            echo '[ ' . $goto_back_url . $GLOBALS['strBack'] . '</a> ]';
        }
        echo '    </fieldset>' . "\n\n";
        if ($exit) {
            /**
             * display footer and exit
             */
            require_once './libraries/footer.inc.php';
        }
    } // end of the 'PMA_mysqlDie()' function

    /**
     * Returns a string formatted with CONVERT ... USING
     * if MySQL supports it
     *
     * @param   string  the string itself
     * @param   string  the mode: quoted or unquoted (this one by default)
     *
     * @return  the formatted string
     *
     * @access  private
     */
    function PMA_convert_using($string, $mode='unquoted')
    {
        if ($mode == 'quoted') {
            $possible_quote = "'";
        } else {
            $possible_quote = "";
        }

        if (PMA_MYSQL_INT_VERSION >= 40100) {
            list($conn_charset) = explode('_', $GLOBALS['collation_connection']);
            $converted_string = "CONVERT(" . $possible_quote . $string . $possible_quote . " USING " . $conn_charset . ")";
        } else {
            $converted_string = $possible_quote . $string . $possible_quote;
        }
        return $converted_string;
    } // end function

    /**
     * Send HTTP header, taking IIS limits into account (600 seems ok)
     *
     * @param   string   $uri the header to send
     * @return  boolean  always true
     */
    function PMA_sendHeaderLocation($uri)
    {
        if (PMA_IS_IIS && strlen($uri) > 600) {

            echo '<html><head><title>- - -</title>' . "\n";
            echo '<meta http-equiv="expires" content="0">' . "\n";
            echo '<meta http-equiv="Pragma" content="no-cache">' . "\n";
            echo '<meta http-equiv="Cache-Control" content="no-cache">' . "\n";
            echo '<meta http-equiv="Refresh" content="0;url=' .$uri . '">' . "\n";
            echo '<script type="text/javascript" language="javascript">' . "\n";
            echo '//<![CDATA[' . "\n";
            echo 'setTimeout ("window.location = unescape(\'"' . $uri . '"\')",2000); </script>' . "\n";
            echo '//]]>' . "\n";
            echo '</head>' . "\n";
            echo '<body>' . "\n";
            echo '<script type="text/javascript" language="javascript">' . "\n";
            echo '//<![CDATA[' . "\n";
            echo 'document.write (\'<p><a href="' . $uri . '">' . $GLOBALS['strGo'] . '</a></p>\');' . "\n";
            echo '//]]>' . "\n";
            echo '</script></body></html>' . "\n";

        } else {
            if (SID) {
                if (strpos($uri, '?') === false) {
                    header('Location: ' . $uri . '?' . SID);
                } else {
                    $separator = PMA_get_arg_separator();
                    header('Location: ' . $uri . $separator . SID);
                }
            } else {
                session_write_close();
                if (headers_sent()) {
                    if (function_exists('debug_print_backtrace')) {
                        echo '<pre>';
                        debug_print_backtrace();
                        echo '</pre>';
                    }
                    trigger_error('PMA_sendHeaderLocation called when headers are already sent!', E_USER_ERROR);
                }
                // bug #1523784: IE6 does not like 'Refresh: 0', it
                // results in a blank page
                // but we need it when coming from the cookie login panel)
                if (PMA_IS_IIS && defined('PMA_COMING_FROM_COOKIE_LOGIN')) {
                    header('Refresh: 0; ' . $uri);
                } else {
                    header('Location: ' . $uri);
                }
            }
        }
    }

    /**
     * returns array with tables of given db with extended infomation and grouped
     *
     * @uses    $GLOBALS['cfg']['LeftFrameTableSeparator']
     * @uses    $GLOBALS['cfg']['LeftFrameTableLevel']
     * @uses    $GLOBALS['cfg']['ShowTooltipAliasTB']
     * @uses    $GLOBALS['cfg']['NaturalOrder']
     * @uses    PMA_backquote()
     * @uses    count()
     * @uses    array_merge
     * @uses    uksort()
     * @uses    strstr()
     * @uses    explode()
     * @param   string  $db     name of db
     * return   array   (rekursive) grouped table list
     */
    function PMA_getTableList($db, $tables = null)
    {
        $sep = $GLOBALS['cfg']['LeftFrameTableSeparator'];

        if ( null === $tables ) {
            $tables = PMA_DBI_get_tables_full($db);
            if ($GLOBALS['cfg']['NaturalOrder']) {
                uksort($tables, 'strnatcasecmp');
            }
        }

        if (count($tables) < 1) {
            return $tables;
        }

        $default = array(
            'Name'      => '',
            'Rows'      => 0,
            'Comment'   => '',
            'disp_name' => '',
        );

        $table_groups = array();

        foreach ($tables as $table_name => $table) {

            // check for correct row count
            if (null === $table['Rows']) {
                // Do not check exact row count here,
                // if row count is invalid possibly the table is defect
                // and this would break left frame;
                // but we can check row count if this is a view,
                // since PMA_Table::countRecords() returns a limited row count
                // in this case.

                // set this because PMA_Table::countRecords() can use it
                $tbl_is_view = PMA_Table::isView($db, $table['Name']);

                if ($tbl_is_view) {
                    $table['Rows'] = PMA_Table::countRecords($db, $table['Name'],
                        $return = true);
                }
            }

            // in $group we save the reference to the place in $table_groups
            // where to store the table info
            if ($GLOBALS['cfg']['LeftFrameDBTree']
                && $sep && strstr($table_name, $sep))
            {
                $parts = explode($sep, $table_name);

                $group =& $table_groups;
                $i = 0;
                $group_name_full = '';
                while ($i < count($parts) - 1
                  && $i < $GLOBALS['cfg']['LeftFrameTableLevel']) {
                    $group_name = $parts[$i] . $sep;
                    $group_name_full .= $group_name;

                    if (!isset($group[$group_name])) {
                        $group[$group_name] = array();
                        $group[$group_name]['is' . $sep . 'group'] = true;
                        $group[$group_name]['tab' . $sep . 'count'] = 1;
                        $group[$group_name]['tab' . $sep . 'group'] = $group_name_full;
                    } elseif (!isset($group[$group_name]['is' . $sep . 'group'])) {
                        $table = $group[$group_name];
                        $group[$group_name] = array();
                        $group[$group_name][$group_name] = $table;
                        unset($table);
                        $group[$group_name]['is' . $sep . 'group'] = true;
                        $group[$group_name]['tab' . $sep . 'count'] = 1;
                        $group[$group_name]['tab' . $sep . 'group'] = $group_name_full;
                    } else {
                        $group[$group_name]['tab' . $sep . 'count']++;
                    }
                    $group =& $group[$group_name];
                    $i++;
                }
            } else {
                if (!isset($table_groups[$table_name])) {
                    $table_groups[$table_name] = array();
                }
                $group =& $table_groups;
            }


            if ($GLOBALS['cfg']['ShowTooltipAliasTB']
              && $GLOBALS['cfg']['ShowTooltipAliasTB'] !== 'nested') {
                // switch tooltip and name
                $table['Comment'] = $table['Name'];
                $table['disp_name'] = $table['Comment'];
            } else {
                $table['disp_name'] = $table['Name'];
            }

            $group[$table_name] = array_merge($default, $table);
        }

        return $table_groups;
    }

    /* ----------------------- Set of misc functions ----------------------- */


    /**
     * Adds backquotes on both sides of a database, table or field name.
     * and escapes backquotes inside the name with another backquote
     *
     * <code>
     * echo PMA_backquote('owner`s db'); // `owner``s db`
     * </code>
     *
     * @param   mixed    $a_name    the database, table or field name to "backquote"
     *                              or array of it
     * @param   boolean  $do_it     a flag to bypass this function (used by dump
     *                              functions)
     * @return  mixed    the "backquoted" database, table or field name if the
     *                   current MySQL release is >= 3.23.6, the original one
     *                   else
     * @access  public
     */
    function PMA_backquote($a_name, $do_it = true)
    {
        if (! $do_it) {
            return $a_name;
        }

        if (is_array($a_name)) {
             $result = array();
             foreach ($a_name as $key => $val) {
                 $result[$key] = PMA_backquote($val);
             }
             return $result;
        }

        // '0' is also empty for php :-(
        if (strlen($a_name) && $a_name !== '*') {
            return '`' . str_replace('`', '``', $a_name) . '`';
        } else {
            return $a_name;
        }
    } // end of the 'PMA_backquote()' function


    /**
     * Defines the <CR><LF> value depending on the user OS.
     *
     * @return  string   the <CR><LF> value to use
     *
     * @access  public
     */
    function PMA_whichCrlf()
    {
        $the_crlf = "\n";

        // The 'PMA_USR_OS' constant is defined in "./libraries/defines.lib.php"
        // Win case
        if (PMA_USR_OS == 'Win') {
            $the_crlf = "\r\n";
        }
        // Others
        else {
            $the_crlf = "\n";
        }

        return $the_crlf;
    } // end of the 'PMA_whichCrlf()' function

    /**
     * Reloads navigation if needed.
     *
     * @global  mixed   configuration
     * @global  bool    whether to reload
     *
     * @access  public
     */
    function PMA_reloadNavigation()
    {
        global $cfg;

        // Reloads the navigation frame via JavaScript if required
        if (isset($GLOBALS['reload']) && $GLOBALS['reload']) {
            echo "\n";
            $reload_url = './navigation.php?' . PMA_generate_common_url((isset($GLOBALS['db']) ? $GLOBALS['db'] : ''), '', '&');
            ?>
<script type="text/javascript" language="javascript">
//<![CDATA[
if (typeof(window.parent) != 'undefined'
    && typeof(window.parent.frame_navigation) != 'undefined') {
    window.parent.goTo('<?php echo $reload_url; ?>');
}
//]]>
</script>
            <?php
            unset($GLOBALS['reload']);
        }
    }

    /**
     * displays the message and the query
     * usually the message is the result of the query executed
     *
     * @param   string  $message    the message to display
     * @param   string  $sql_query  the query to display
     * @global  array   the configuration array
     * @uses    $GLOBALS['cfg']
     * @access  public
     */
    function PMA_showMessage($message, $sql_query = null)
    {
        global $cfg;

        if (null === $sql_query) {
            if (! empty($GLOBALS['display_query'])) {
                $sql_query = $GLOBALS['display_query'];
            } elseif ($cfg['SQP']['fmtType'] == 'none' && ! empty($GLOBALS['unparsed_sql'])) {
                $sql_query = $GLOBALS['unparsed_sql'];
            // could be empty, for example export + save on server
            } elseif (! empty($GLOBALS['sql_query'])) {
                $sql_query = $GLOBALS['sql_query'];
            } else {
                $sql_query = '';
            }
        }

        // Corrects the tooltip text via JS if required
        // @todo this is REALLY the wrong place to do this - very unexpected here
        if ( isset($GLOBALS['table']) && strlen($GLOBALS['table']) && $cfg['ShowTooltip']) {
            $result = PMA_DBI_try_query('SHOW TABLE STATUS FROM ' . PMA_backquote($GLOBALS['db']) . ' LIKE \'' . PMA_sqlAddslashes($GLOBALS['table'], true) . '\'');
            if ($result) {
                $tbl_status = PMA_DBI_fetch_assoc($result);
                $tooltip    = (empty($tbl_status['Comment']))
                            ? ''
                            : $tbl_status['Comment'] . ' ';
                $tooltip .= '(' . PMA_formatNumber($tbl_status['Rows'], 0) . ' ' . $GLOBALS['strRows'] . ')';
                PMA_DBI_free_result($result);
                $uni_tbl = PMA_jsFormat($GLOBALS['db'] . '.' . $GLOBALS['table'], false);
                echo "\n";
                echo '<script type="text/javascript" language="javascript">' . "\n";
                echo '//<![CDATA[' . "\n";
                echo "window.parent.updateTableTitle('" . $uni_tbl . "', '" . PMA_jsFormat($tooltip, false) . "');" . "\n";
                echo '//]]>' . "\n";
                echo '</script>' . "\n";
            } // end if
        } // end if ... elseif

        // Checks if the table needs to be repaired after a TRUNCATE query.
        // @todo this should only be done if isset($GLOBALS['sql_query']), what about $GLOBALS['display_query']???
        // @todo this is REALLY the wrong place to do this - very unexpected here
        if (isset($GLOBALS['table']) && isset($GLOBALS['sql_query'])
            && $GLOBALS['sql_query'] == 'TRUNCATE TABLE ' . PMA_backquote($GLOBALS['table'])) {
            if (!isset($tbl_status)) {
                $result = @PMA_DBI_try_query('SHOW TABLE STATUS FROM ' . PMA_backquote($GLOBALS['db']) . ' LIKE \'' . PMA_sqlAddslashes($GLOBALS['table'], true) . '\'');
                if ($result) {
                    $tbl_status = PMA_DBI_fetch_assoc($result);
                    PMA_DBI_free_result($result);
                }
            }
            if (isset($tbl_status) && (int) $tbl_status['Index_length'] > 1024) {
                PMA_DBI_try_query('REPAIR TABLE ' . PMA_backquote($GLOBALS['table']));
            }
        }
        unset($tbl_status);
        echo '<br />' . "\n";

        echo '<div align="' . $GLOBALS['cell_align_left'] . '">' . "\n";
        if (!empty($GLOBALS['show_error_header'])) {
            echo '<div class="error">' . "\n";
            echo '<h1>' . $GLOBALS['strError'] . '</h1>' . "\n";
        }

        echo '<div class="notice">';
        echo PMA_sanitize($message);
        if (isset($GLOBALS['special_message'])) {
            echo PMA_sanitize($GLOBALS['special_message']);
            unset($GLOBALS['special_message']);
        }
        echo '</div>';

        if (!empty($GLOBALS['show_error_header'])) {
            echo '</div>';
        }

        if ($cfg['ShowSQL'] == true && ! empty($sql_query)) {
            // Basic url query part
            $url_qpart = '?' . PMA_generate_common_url(isset($GLOBALS['db']) ? $GLOBALS['db'] : '', isset($GLOBALS['table']) ? $GLOBALS['table'] : '');

            // Html format the query to be displayed
            // The nl2br function isn't used because its result isn't a valid
            // xhtml1.0 statement before php4.0.5 ("<br>" and not "<br />")
            // If we want to show some sql code it is easiest to create it here
             /* SQL-Parser-Analyzer */

            if (!empty($GLOBALS['show_as_php'])) {
                $new_line = '\'<br />' . "\n" . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;. \' ';
            }
            if (isset($new_line)) {
                 /* SQL-Parser-Analyzer */
                $query_base = PMA_sqlAddslashes(htmlspecialchars($sql_query), false, false, true);
                 /* SQL-Parser-Analyzer */
                $query_base = preg_replace("@((\015\012)|(\015)|(\012))+@", $new_line, $query_base);
            } else {
                $query_base = $sql_query;
            }

            $max_characters = 1000;
            if (strlen($query_base) > $max_characters) {
                define('PMA_QUERY_TOO_BIG',1);
            }

            // Parse SQL if needed
            // (here, use "! empty" because when deleting a bookmark,
            // $GLOBALS['parsed_sql'] is set but empty
            if (! empty($GLOBALS['parsed_sql']) && $query_base == $GLOBALS['parsed_sql']['raw']) {
                $parsed_sql = $GLOBALS['parsed_sql'];
            } else {
                // when the query is large (for example an INSERT of binary
                // data), the parser chokes; so avoid parsing the query
                if (! defined('PMA_QUERY_TOO_BIG')) {
                    $parsed_sql = PMA_SQP_parse($query_base);
                }
            }

            // Analyze it
            if (isset($parsed_sql)) {
                $analyzed_display_query = PMA_SQP_analyze($parsed_sql);
            }

            // Here we append the LIMIT added for navigation, to
            // enable its display. Adding it higher in the code
            // to $sql_query would create a problem when
            // using the Refresh or Edit links.

            // Only append it on SELECTs.

            /**
             * @todo what would be the best to do when someone hits Refresh:
             * use the current LIMITs ?
             */

            if (isset($analyzed_display_query[0]['queryflags']['select_from'])
             && isset($GLOBALS['sql_limit_to_append'])) {
                $query_base  = $analyzed_display_query[0]['section_before_limit'] . "\n" . $GLOBALS['sql_limit_to_append'] . $analyzed_display_query[0]['section_after_limit'];
                // Need to reparse query
                $parsed_sql = PMA_SQP_parse($query_base);
            }

            if (!empty($GLOBALS['show_as_php'])) {
                $query_base = '$sql  = \'' . $query_base;
            } elseif (!empty($GLOBALS['validatequery'])) {
                $query_base = PMA_validateSQL($query_base);
            } else {
                if (isset($parsed_sql)) {
                    $query_base = PMA_formatSql($parsed_sql, $query_base);
                }
            }

            // Prepares links that may be displayed to edit/explain the query
            // (don't go to default pages, we must go to the page
            // where the query box is available)

            $edit_target = isset($GLOBALS['db']) ? (isset($GLOBALS['table']) ? 'tbl_sql.php' : 'db_sql.php') : 'server_sql.php';

            if (isset($cfg['SQLQuery']['Edit'])
                && ($cfg['SQLQuery']['Edit'] == true)
                && (!empty($edit_target))
                && ! defined('PMA_QUERY_TOO_BIG')) {

                if ($cfg['EditInWindow'] == true) {
                    $onclick = 'window.parent.focus_querywindow(\'' . PMA_jsFormat($sql_query, false) . '\'); return false;';
                } else {
                    $onclick = '';
                }

                $edit_link = $edit_target
                           . $url_qpart
                           . '&amp;sql_query=' . urlencode($sql_query)
                           . '&amp;show_query=1#querybox';
                $edit_link = ' [' . PMA_linkOrButton($edit_link, $GLOBALS['strEdit'], array('onclick' => $onclick)) . ']';
            } else {
                $edit_link = '';
            }

            // Want to have the query explained (Mike Beck 2002-05-22)
            // but only explain a SELECT (that has not been explained)
            /* SQL-Parser-Analyzer */
            if (isset($cfg['SQLQuery']['Explain'])
                && $cfg['SQLQuery']['Explain'] == true
                && ! defined('PMA_QUERY_TOO_BIG')) {

                // Detect if we are validating as well
                // To preserve the validate uRL data
                if (!empty($GLOBALS['validatequery'])) {
                    $explain_link_validate = '&amp;validatequery=1';
                } else {
                    $explain_link_validate = '';
                }

                $explain_link = 'import.php'
                              . $url_qpart
                              . $explain_link_validate
                              . '&amp;sql_query=';

                if (preg_match('@^SELECT[[:space:]]+@i', $sql_query)) {
                    $explain_link .= urlencode('EXPLAIN ' . $sql_query);
                    $message = $GLOBALS['strExplain'];
                } elseif (preg_match('@^EXPLAIN[[:space:]]+SELECT[[:space:]]+@i', $sql_query)) {
                    $explain_link .= urlencode(substr($sql_query, 8));
                    $message = $GLOBALS['strNoExplain'];
                } else {
                    $explain_link = '';
                }
                if (!empty($explain_link)) {
                    $explain_link = ' [' . PMA_linkOrButton($explain_link, $message) . ']';
                }
            } else {
                $explain_link = '';
            } //show explain

            // Also we would like to get the SQL formed in some nice
            // php-code (Mike Beck 2002-05-22)
            if (isset($cfg['SQLQuery']['ShowAsPHP'])
                && $cfg['SQLQuery']['ShowAsPHP'] == true
                && ! defined('PMA_QUERY_TOO_BIG')) {
                $php_link = 'import.php'
                          . $url_qpart
                          . '&amp;show_query=1'
                          . '&amp;sql_query=' . urlencode($sql_query)
                          . '&amp;show_as_php=';

                if (!empty($GLOBALS['show_as_php'])) {
                    $php_link .= '0';
                    $message = $GLOBALS['strNoPhp'];
                } else {
                    $php_link .= '1';
                    $message = $GLOBALS['strPhp'];
                }
                $php_link = ' [' . PMA_linkOrButton($php_link, $message) . ']';

                if (isset($GLOBALS['show_as_php'])) {
                    $runquery_link
                         = 'import.php'
                         . $url_qpart
                         . '&amp;show_query=1'
                         . '&amp;sql_query=' . urlencode($sql_query);
                    $php_link .= ' [' . PMA_linkOrButton($runquery_link, $GLOBALS['strRunQuery']) . ']';
                }

            } else {
                $php_link = '';
            } //show as php

            // Refresh query
            if (isset($cfg['SQLQuery']['Refresh'])
                && $cfg['SQLQuery']['Refresh']
                && preg_match('@^(SELECT|SHOW)[[:space:]]+@i', $sql_query)) {

                $refresh_link = 'import.php'
                          . $url_qpart
                          . '&amp;show_query=1'
                          . (isset($_GET['pos']) ? '&amp;pos=' . $_GET['pos'] : '')
                          . '&amp;sql_query=' . urlencode($sql_query);
                $refresh_link = ' [' . PMA_linkOrButton($refresh_link, $GLOBALS['strRefresh']) . ']';
            } else {
                $refresh_link = '';
            } //show as php

            if (isset($cfg['SQLValidator']['use'])
                && $cfg['SQLValidator']['use'] == true
                && isset($cfg['SQLQuery']['Validate'])
                && $cfg['SQLQuery']['Validate'] == true) {
                $validate_link = 'import.php'
                               . $url_qpart
                               . '&amp;show_query=1'
                               . '&amp;sql_query=' . urlencode($sql_query)
                               . '&amp;validatequery=';
                if (!empty($GLOBALS['validatequery'])) {
                    $validate_link .= '0';
                    $validate_message = $GLOBALS['strNoValidateSQL'] ;
                } else {
                    $validate_link .= '1';
                    $validate_message = $GLOBALS['strValidateSQL'] ;
                }
                $validate_link = ' [' . PMA_linkOrButton($validate_link, $validate_message) . ']';
            } else {
                $validate_link = '';
            } //validator
            unset($sql_query);

            // Displays the message
            echo '<fieldset class="">' . "\n";
            echo '    <legend>' . $GLOBALS['strSQLQuery'] . ':</legend>';
            echo '    <div>';
            // when uploading a 700 Kio binary file into a LONGBLOB, 
            // I get a white page, strlen($query_base) is 2 x 700 Kio
            // so put a hard limit here (let's say 1000)
            if (defined('PMA_QUERY_TOO_BIG')) {
                echo '    ' . substr($query_base,0,$max_characters) . '[...]';
            } else {
                echo '    ' . $query_base;
            }

            //Clean up the end of the PHP
            if (!empty($GLOBALS['show_as_php'])) {
                echo '\';';
            }
            echo '    </div>';
            echo '</fieldset>' . "\n";

            if (!empty($edit_target)) {
                echo '<fieldset class="tblFooters">';
                echo $edit_link . $explain_link . $php_link . $refresh_link . $validate_link;
                echo '</fieldset>';
            }
        }
        echo '</div><br />' . "\n";
    } // end of the 'PMA_showMessage()' function


    /**
     * Formats $value to byte view
     *
     * @param    double   the value to format
     * @param    integer  the sensitiveness
     * @param    integer  the number of decimals to retain
     *
     * @return   array    the formatted value and its unit
     *
     * @access  public
     *
     * @author   staybyte
     * @version  1.2 - 18 July 2002
     */
    function PMA_formatByteDown($value, $limes = 6, $comma = 0)
    {
        $dh           = PMA_pow(10, $comma);
        $li           = PMA_pow(10, $limes);
        $return_value = $value;
        $unit         = $GLOBALS['byteUnits'][0];

        for ($d = 6, $ex = 15; $d >= 1; $d--, $ex-=3) {
            if (isset($GLOBALS['byteUnits'][$d]) && $value >= $li * PMA_pow(10, $ex)) {
                // use 1024.0 to avoid integer overflow on 64-bit machines
                $value = round($value / (PMA_pow(1024, $d) / $dh)) /$dh;
                $unit = $GLOBALS['byteUnits'][$d];
                break 1;
            } // end if
        } // end for

        if ($unit != $GLOBALS['byteUnits'][0]) {
            $return_value = number_format($value, $comma, $GLOBALS['number_decimal_separator'], $GLOBALS['number_thousands_separator']);
        } else {
            $return_value = number_format($value, 0, $GLOBALS['number_decimal_separator'], $GLOBALS['number_thousands_separator']);
        }

        return array($return_value, $unit);
    } // end of the 'PMA_formatByteDown' function

    /**
     * Formats $value to the given length and appends SI prefixes
     * $comma is not substracted from the length
     * with a $length of 0 no truncation occurs, number is only formated
     * to the current locale
     * <code>
     * echo PMA_formatNumber(123456789, 6);     // 123,457 k
     * echo PMA_formatNumber(-123456789, 4, 2); //    -123.46 M
     * echo PMA_formatNumber(-0.003, 6);        //      -3 m
     * echo PMA_formatNumber(0.003, 3, 3);      //       0.003
     * echo PMA_formatNumber(0.00003, 3, 2);    //       0.03 m
     * echo PMA_formatNumber(0, 6);             //       0
     * </code>
     * @param   double   $value     the value to format
     * @param   integer  $length    the max length
     * @param   integer  $comma     the number of decimals to retain
     * @param   boolean  $only_down do not reformat numbers below 1
     *
     * @return  string   the formatted value and its unit
     *
     * @access  public
     *
     * @author  staybyte, sebastian mendel
     * @version 1.1.0 - 2005-10-27
     */
    function PMA_formatNumber($value, $length = 3, $comma = 0, $only_down = false)
    {
        if ($length === 0) {
            return number_format($value,
                                $comma,
                                $GLOBALS['number_decimal_separator'],
                                $GLOBALS['number_thousands_separator']);
        }

        // this units needs no translation, ISO
        $units = array(
            -8 => 'y',
            -7 => 'z',
            -6 => 'a',
            -5 => 'f',
            -4 => 'p',
            -3 => 'n',
            -2 => '&micro;',
            -1 => 'm',
            0 => ' ',
            1 => 'k',
            2 => 'M',
            3 => 'G',
            4 => 'T',
            5 => 'P',
            6 => 'E',
            7 => 'Z',
            8 => 'Y'
       );

        // we need at least 3 digits to be displayed
        if (3 > $length + $comma) {
            $length = 3 - $comma;
        }

        // check for negativ value to retain sign
        if ($value < 0) {
            $sign = '-';
            $value = abs($value);
        } else {
            $sign = '';
        }

        $dh = PMA_pow(10, $comma);
        $li = PMA_pow(10, $length);
        $unit = $units[0];

        if ($value >= 1) {
            for ($d = 8; $d >= 0; $d--) {
                if (isset($units[$d]) && $value >= $li * PMA_pow(1000, $d-1)) {
                    $value = round($value / (PMA_pow(1000, $d) / $dh)) /$dh;
                    $unit = $units[$d];
                    break 1;
                } // end if
            } // end for
        } elseif (!$only_down && (float) $value !== 0.0) {
            for ($d = -8; $d <= 8; $d++) {
                if (isset($units[$d]) && $value <= $li * PMA_pow(1000, $d-1)) {
                    $value = round($value / (PMA_pow(1000, $d) / $dh)) /$dh;
                    $unit = $units[$d];
                    break 1;
                } // end if
            } // end for
        } // end if ($value >= 1) elseif (!$only_down && (float) $value !== 0.0)

        $value = number_format($value,
                                $comma,
                                $GLOBALS['number_decimal_separator'],
                                $GLOBALS['number_thousands_separator']);

        return $sign . $value . ' ' . $unit;
    } // end of the 'PMA_formatNumber' function

    /**
     * Extracts ENUM / SET options from a type definition string
     *
     * @param   string   The column type definition
     *
     * @return  array    The options or
     *          boolean  false in case of an error.
     *
     * @author  rabus
     */
    function PMA_getEnumSetOptions($type_def)
    {
        $open = strpos($type_def, '(');
        $close = strrpos($type_def, ')');
        if (!$open || !$close) {
            return false;
        }
        $options = substr($type_def, $open + 2, $close - $open - 3);
        $options = explode('\',\'', $options);
        return $options;
    } // end of the 'PMA_getEnumSetOptions' function

    /**
     * Writes localised date
     *
     * @param   string   the current timestamp
     *
     * @return  string   the formatted date
     *
     * @access  public
     */
    function PMA_localisedDate($timestamp = -1, $format = '')
    {
        global $datefmt, $month, $day_of_week;

        if ($format == '') {
            $format = $datefmt;
        }

        if ($timestamp == -1) {
            $timestamp = time();
        }

        $date = preg_replace('@%[aA]@', $day_of_week[(int)strftime('%w', $timestamp)], $format);
        $date = preg_replace('@%[bB]@', $month[(int)strftime('%m', $timestamp)-1], $date);

        return strftime($date, $timestamp);
    } // end of the 'PMA_localisedDate()' function


    /**
     * returns a tab for tabbed navigation.
     * If the variables $link and $args ar left empty, an inactive tab is created
     *
     * @uses    array_merge()
     * basename()
     * $GLOBALS['strEmpty']
     * $GLOBALS['strDrop']
     * $GLOBALS['active_page']
     * $GLOBALS['PHP_SELF']
     * htmlentities()
     * PMA_generate_common_url()
     * $GLOBALS['url_query']
     * urlencode()
     * $GLOBALS['cfg']['MainPageIconic']
     * $GLOBALS['pmaThemeImage']
     * sprintf()
     * trigger_error()
     * E_USER_NOTICE
     * @param   array   $tab    array with all options
     * @return  string  html code for one tab, a link if valid otherwise a span
     * @access  public
     */
    function PMA_getTab($tab)
    {
        // default values
        $defaults = array(
            'text'      => '',
            'class'     => '',
            'active'    => false,
            'link'      => '',
            'sep'       => '?',
            'attr'      => '',
            'args'      => '',
            'warning'   => '',
       );

        $tab = array_merge($defaults, $tab);

        // determine additionnal style-class
        if (empty($tab['class'])) {
            if ($tab['text'] == $GLOBALS['strEmpty']
                || $tab['text'] == $GLOBALS['strDrop']) {
                $tab['class'] = 'caution';
            } elseif (!empty($tab['active'])
              || (isset($GLOBALS['active_page'])
                   && $GLOBALS['active_page'] == $tab['link'])
              || basename(PMA_getenv('PHP_SELF')) == $tab['link'])
            {
                $tab['class'] = 'active';
            }
        }

        if (!empty($tab['warning'])) {
            $tab['class'] .= ' warning';
            $tab['attr'] .= ' title="' . htmlspecialchars($tab['warning']) . '"';
        }

        // build the link
        if (!empty($tab['link'])) {
            $tab['link'] = htmlentities($tab['link']);
            $tab['link'] = $tab['link'] . $tab['sep']
                .(empty($GLOBALS['url_query']) ?
                    PMA_generate_common_url() : $GLOBALS['url_query']);
            if (!empty($tab['args'])) {
                foreach ($tab['args'] as $param => $value) {
                    $tab['link'] .= '&amp;' . urlencode($param) . '='
                        . urlencode($value);
                }
            }
        }

        // display icon, even if iconic is disabled but the link-text is missing
        if (($GLOBALS['cfg']['MainPageIconic'] || empty($tab['text']))
            && isset($tab['icon'])) {
            $image = '<img class="icon" src="' . htmlentities($GLOBALS['pmaThemeImage'])
                .'%1$s" width="16" height="16" alt="%2$s" />%2$s';
            $tab['text'] = sprintf($image, htmlentities($tab['icon']), $tab['text']);
        }
        // check to not display an empty link-text
        elseif (empty($tab['text'])) {
            $tab['text'] = '?';
            trigger_error('empty linktext in function ' . __FUNCTION__ . '()',
                E_USER_NOTICE);
        }

        if (!empty($tab['link'])) {
            $out = '<a class="tab' . htmlentities($tab['class']) . '"'
                .' href="' . $tab['link'] . '" ' . $tab['attr'] . '>'
                . $tab['text'] . '</a>';
        } else {
            $out = '<span class="tab' . htmlentities($tab['class']) . '">'
                . $tab['text'] . '</span>';
        }

        return $out;
    } // end of the 'PMA_getTab()' function

    /**
     * returns html-code for a tab navigation
     *
     * @uses    PMA_getTab()
     * @uses    htmlentities()
     * @param   array   $tabs   one element per tab
     * @param   string  $tag_id id used for the html-tag
     * @return  string  html-code for tab-navigation
     */
    function PMA_getTabs($tabs, $tag_id = 'topmenu')
    {
        $tab_navigation =
             '<div id="' . htmlentities($tag_id) . 'container">' . "\n"
            .'<ul id="' . htmlentities($tag_id) . '">' . "\n";

        foreach ($tabs as $tab) {
            $tab_navigation .= '<li>' . PMA_getTab($tab) . '</li>' . "\n";
        }

        $tab_navigation .=
             '</ul>' . "\n"
            .'<div class="clearfloat"></div>'
            .'</div>' . "\n";

        return $tab_navigation;
    }


    /**
     * Displays a link, or a button if the link's URL is too large, to
     * accommodate some browsers' limitations
     *
     * @param  string  the URL
     * @param  string  the link message
     * @param  mixed   $tag_params  string: js confirmation
     *                              array: additional tag params (f.e. style="")
     * @param  boolean $new_form    we set this to false when we are already in
     *                              a  form, to avoid generating nested forms
     *
     * @return string  the results to be echoed or saved in an array
     */
    function PMA_linkOrButton($url, $message, $tag_params = array(),
        $new_form = true, $strip_img = false, $target = '')
    {
        if (! is_array($tag_params)) {
            $tmp = $tag_params;
            $tag_params = array();
            if (!empty($tmp)) {
                $tag_params['onclick'] = 'return confirmLink(this, \'' . $tmp . '\')';
            }
            unset($tmp);
        }
        if (! empty($target)) {
            $tag_params['target'] = htmlentities($target);
        }

        $tag_params_strings = array();
        foreach ($tag_params as $par_name => $par_value) {
            // htmlspecialchars() only on non javascript
            $par_value = substr($par_name, 0, 2) == 'on'
                ? $par_value
                : htmlspecialchars($par_value);
            $tag_params_strings[] = $par_name . '="' . $par_value . '"';
        }

        // previously the limit was set to 2047, it seems 1000 is better
        if (strlen($url) <= 1000) {
            // no whitespace within an <a> else Safari will make it part of the link
            $ret = "\n" . '<a href="' . $url . '" '
                . implode(' ', $tag_params_strings) . '>'
                . $message . '</a>' . "\n";
        } else {
            // no spaces (linebreaks) at all
            // or after the hidden fields
            // IE will display them all

            // add class=link to submit button
            if (empty($tag_params['class'])) {
                $tag_params['class'] = 'link';
            }

            // decode encoded url separators
            $separator   = PMA_get_arg_separator();
            // on most places separator is still hard coded ...
            if ($separator !== '&') {
                // ... so always replace & with $separator
                $url         = str_replace(htmlentities('&'), $separator, $url);
                $url         = str_replace('&', $separator, $url);
            }
            $url         = str_replace(htmlentities($separator), $separator, $url);
            // end decode

            $url_parts   = parse_url($url);
            $query_parts = explode($separator, $url_parts['query']);
            if ($new_form) {
                $ret = '<form action="' . $url_parts['path'] . '" class="link"'
                     . ' method="post"' . $target . ' style="display: inline;">';
                $subname_open   = '';
                $subname_close  = '';
                $submit_name    = '';
            } else {
                $query_parts[] = 'redirect=' . $url_parts['path'];
                if (empty($GLOBALS['subform_counter'])) {
                    $GLOBALS['subform_counter'] = 0;
                }
                $GLOBALS['subform_counter']++;
                $ret            = '';
                $subname_open   = 'subform[' . $GLOBALS['subform_counter'] . '][';
                $subname_close  = ']';
                $submit_name    = ' name="usesubform[' . $GLOBALS['subform_counter'] . ']"';
            }
            foreach ($query_parts as $query_pair) {
                list($eachvar, $eachval) = explode('=', $query_pair);
                $ret .= '<input type="hidden" name="' . $subname_open . $eachvar
                    . $subname_close . '" value="'
                    . htmlspecialchars(urldecode($eachval)) . '" />';
            } // end while

            if (stristr($message, '<img')) {
                if ($strip_img) {
                    $message = trim(strip_tags($message));
                    $ret .= '<input type="submit"' . $submit_name . ' '
                        . implode(' ', $tag_params_strings)
                        . ' value="' . htmlspecialchars($message) . '" />';
                } else {
                    $ret .= '<input type="image"' . $submit_name . ' '
                        . implode(' ', $tag_params_strings)
                        . ' src="' . preg_replace(
                            '/^.*\ssrc="([^"]*)".*$/si', '\1', $message) . '"'
                        . ' value="' . htmlspecialchars(
                            preg_replace('/^.*\salt="([^"]*)".*$/si', '\1',
                                $message))
                        . '" />';
                }
            } else {
                $message = trim(strip_tags($message));
                $ret .= '<input type="submit"' . $submit_name . ' '
                    . implode(' ', $tag_params_strings)
                    . ' value="' . htmlspecialchars($message) . '" />';
            }
            if ($new_form) {
                $ret .= '</form>';
            }
        } // end if... else...

            return $ret;
    } // end of the 'PMA_linkOrButton()' function


    /**
     * Returns a given timespan value in a readable format.
     *
     * @param  int     the timespan
     *
     * @return string  the formatted value
     */
    function PMA_timespanFormat($seconds)
    {
        $return_string = '';
        $days = floor($seconds / 86400);
        if ($days > 0) {
            $seconds -= $days * 86400;
        }
        $hours = floor($seconds / 3600);
        if ($days > 0 || $hours > 0) {
            $seconds -= $hours * 3600;
        }
        $minutes = floor($seconds / 60);
        if ($days > 0 || $hours > 0 || $minutes > 0) {
            $seconds -= $minutes * 60;
        }
        return sprintf($GLOBALS['timespanfmt'], (string)$days, (string)$hours, (string)$minutes, (string)$seconds);
    }

    /**
     * Takes a string and outputs each character on a line for itself. Used
     * mainly for horizontalflipped display mode.
     * Takes care of special html-characters.
     * Fulfills todo-item
     * http://sf.net/tracker/?func=detail&aid=544361&group_id=23067&atid=377411
     *
     * @param   string   The string
     * @param   string   The Separator (defaults to "<br />\n")
     *
     * @access  public
     * @author  Garvin Hicking <me@supergarv.de>
     * @return  string      The flipped string
     */
    function PMA_flipstring($string, $Separator = "<br />\n")
    {
        $format_string = '';
        $charbuff = false;

        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string{$i};
            $append = false;

            if ($char == '&') {
                $format_string .= $charbuff;
                $charbuff = $char;
                $append = true;
            } elseif (!empty($charbuff)) {
                $charbuff .= $char;
            } elseif ($char == ';' && !empty($charbuff)) {
                $format_string .= $charbuff;
                $charbuff = false;
                $append = true;
            } else {
                $format_string .= $char;
                $append = true;
            }

            if ($append && ($i != strlen($string))) {
                $format_string .= $Separator;
            }
        }

        return $format_string;
    }


    /**
     * Function added to avoid path disclosures.
     * Called by each script that needs parameters, it displays
     * an error message and, by default, stops the execution.
     *
     * Not sure we could use a strMissingParameter message here,
     * would have to check if the error message file is always available
     *
     * @param   array   The names of the parameters needed by the calling
     *                  script.
     * @param   boolean Stop the execution?
     *                  (Set this manually to false in the calling script
     *                   until you know all needed parameters to check).
     * @param   boolean Whether to include this list in checking for special params.
     * @global  string  path to current script
     * @global  boolean flag whether any special variable was required
     *
     * @access  public
     * @author  Marc Delisle (lem9@users.sourceforge.net)
     */
    function PMA_checkParameters($params, $die = true, $request = true)
    {
        global $PHP_SELF, $checked_special;

        if (!isset($checked_special)) {
            $checked_special = false;
        }

        $reported_script_name = basename($PHP_SELF);
        $found_error = false;
        $error_message = '';

        foreach ($params as $param) {
            if ($request && $param != 'db' && $param != 'table') {
                $checked_special = true;
            }

            if (!isset($GLOBALS[$param])) {
                $error_message .= $reported_script_name . ': Missing parameter: ' . $param . ' <a href="./Documentation.html#faqmissingparameters" target="documentation"> (FAQ 2.8)</a><br />';
                $found_error = true;
            }
        }
        if ($found_error) {
            /**
             * display html meta tags
             */
            require_once './libraries/header_meta_style.inc.php';
            echo '</head><body><p>' . $error_message . '</p></body></html>';
            if ($die) {
                exit();
            }
        }
    } // end function

    /**
     * Function to generate unique condition for specified row.
     *
     * @uses    PMA_MYSQL_INT_VERSION
     * @uses    $GLOBALS['analyzed_sql'][0]
     * @uses    PMA_DBI_field_flags()
     * @uses    PMA_backquote()
     * @uses    PMA_sqlAddslashes()
     * @uses    stristr()
     * @uses    bin2hex()
     * @uses    preg_replace()
     * @param   resource    $handle         current query result
     * @param   integer     $fields_cnt     number of fields
     * @param   array       $fields_meta    meta information about fields
     * @param   array       $row            current row
     *
     * @access  public
     * @author  Michal Cihar (michal@cihar.com)
     * @return  string      calculated condition
     */
    function PMA_getUniqueCondition($handle, $fields_cnt, $fields_meta, $row)
    {
        $primary_key          = '';
        $unique_key           = '';
        $nonprimary_condition = '';

        for ($i = 0; $i < $fields_cnt; ++$i) {
            $condition   = '';
            $field_flags = PMA_DBI_field_flags($handle, $i);
            $meta        = $fields_meta[$i];

            // do not use an alias in a condition
            if (! isset($meta->orgname) || ! strlen($meta->orgname)) {
                $meta->orgname = $meta->name;

                if (isset($GLOBALS['analyzed_sql'][0]['select_expr'])
                  && is_array($GLOBALS['analyzed_sql'][0]['select_expr'])) {
                    foreach ($GLOBALS['analyzed_sql'][0]['select_expr']
                      as $select_expr) {
                        // need (string) === (string)
                        // '' !== 0 but '' == 0
                        if ((string) $select_expr['alias'] === (string) $meta->name) {
                            $meta->orgname = $select_expr['column'];
                            break;
                        } // end if
                    } // end foreach
                }
            }


            // to fix the bug where float fields (primary or not)
            // can't be matched because of the imprecision of
            // floating comparison, use CONCAT
            // (also, the syntax "CONCAT(field) IS NULL"
            // that we need on the next "if" will work)
            if ($meta->type == 'real') {
                $condition = ' CONCAT(' . PMA_backquote($meta->table) . '.'
                    . PMA_backquote($meta->orgname) . ') ';
            } else {
                // string and blob fields have to be converted using
                // the system character set (always utf8) since
                // mysql4.1 can use different charset for fields.
                if (PMA_MYSQL_INT_VERSION >= 40100
                  && ($meta->type == 'string' || $meta->type == 'blob')) {
                    $condition = ' CONVERT(' . PMA_backquote($meta->table) . '.'
                        . PMA_backquote($meta->orgname) . ' USING utf8) ';
                } else {
                    $condition = ' ' . PMA_backquote($meta->table) . '.'
                        . PMA_backquote($meta->orgname) . ' ';
                }
            } // end if... else...

            if (!isset($row[$i]) || is_null($row[$i])) {
                $condition .= 'IS NULL AND';
            } else {
                // timestamp is numeric on some MySQL 4.1
                if ($meta->numeric && $meta->type != 'timestamp') {
                    $condition .= '= ' . $row[$i] . ' AND';
                } elseif ($meta->type == 'blob'
                    // hexify only if this is a true not empty BLOB
                     && stristr($field_flags, 'BINARY')
                     && !empty($row[$i])) {
                        // do not waste memory building a too big condition 
                        if (strlen($row[$i]) < 1000) {
                            if (PMA_MYSQL_INT_VERSION < 40002) {
                                $condition .= 'LIKE 0x' . bin2hex($row[$i]) . ' AND';
                            } else {
                                // use a CAST if possible, to avoid problems
                                // if the field contains wildcard characters % or _
                                $condition .= '= CAST(0x' . bin2hex($row[$i])
                                    . ' AS BINARY) AND';
                            }
                        } else {
                            // this blob won't be part of the final condition
                            $condition = '';
                        }
                } else {
                    $condition .= '= \''
                        . PMA_sqlAddslashes($row[$i], false, true) . '\' AND';
                }
            }
            if ($meta->primary_key > 0) {
                $primary_key .= $condition;
            } elseif ($meta->unique_key > 0) {
                $unique_key  .= $condition;
            }
            $nonprimary_condition .= $condition;
        } // end for

        // Correction University of Virginia 19991216:
        // prefer primary or unique keys for condition,
        // but use conjunction of all values if no primary key
        if ($primary_key) {
            $preferred_condition = $primary_key;
        } elseif ($unique_key) {
            $preferred_condition = $unique_key;
        } else {
            $preferred_condition = $nonprimary_condition;
        }

        return preg_replace('|\s?AND$|', '', $preferred_condition);
    } // end function

    /**
     * Generate a button or image tag
     *
     * @param   string      name of button element
     * @param   string      class of button element
     * @param   string      name of image element
     * @param   string      text to display
     * @param   string      image to display
     *
     * @access  public
     * @author  Michal Cihar (michal@cihar.com)
     */
    function PMA_buttonOrImage($button_name, $button_class, $image_name, $text,
        $image)
    {
        global $pmaThemeImage, $propicon;

        /* Opera has trouble with <input type="image"> */
        /* IE has trouble with <button> */
        if (PMA_USR_BROWSER_AGENT != 'IE') {
            echo '<button class="' . $button_class . '" type="submit"'
                .' name="' . $button_name . '" value="' . $text . '"'
                .' title="' . $text . '">' . "\n"
                .'<img class="icon" src="' . $pmaThemeImage . $image . '"'
                .' title="' . $text . '" alt="' . $text . '" width="16"'
                .' height="16" />'
                .($propicon == 'both' ? '&nbsp;' . $text : '') . "\n"
                .'</button>' . "\n";
        } else {
            echo '<input type="image" name="' . $image_name . '" value="'
                . $text . '" title="' . $text . '" src="' . $pmaThemeImage
                . $image . '" />'
                . ($propicon == 'both' ? '&nbsp;' . $text : '') . "\n";
        }
    } // end function

    /**
     * Generate a pagination selector for browsing resultsets
     *
     * @param   string      URL for the JavaScript
     * @param   string      Number of rows in the pagination set
     * @param   string      current page number
     * @param   string      number of total pages
     * @param   string      If the number of pages is lower than this
     *                      variable, no pages will be ommitted in
     *                      pagination
     * @param   string      How many rows at the beginning should always
     *                      be shown?
     * @param   string      How many rows at the end should always
     *                      be shown?
     * @param   string      Percentage of calculation page offsets to
     *                      hop to a next page
     * @param   string      Near the current page, how many pages should
     *                      be considered "nearby" and displayed as
     *                      well?
     *
     * @access  public
     * @author  Garvin Hicking (pma@supergarv.de)
     */
    function PMA_pageselector($url, $rows, $pageNow = 1, $nbTotalPage = 1,
        $showAll = 200, $sliceStart = 5, $sliceEnd = 5, $percent = 20,
        $range = 10)
    {
        $gotopage = $GLOBALS['strPageNumber']
                  . ' <select name="goToPage" onchange="goToUrl(this, \''
                  . $url . '\');">' . "\n";
        if ($nbTotalPage < $showAll) {
            $pages = range(1, $nbTotalPage);
        } else {
            $pages = array();

            // Always show first X pages
            for ($i = 1; $i <= $sliceStart; $i++) {
                $pages[] = $i;
            }

            // Always show last X pages
            for ($i = $nbTotalPage - $sliceEnd; $i <= $nbTotalPage; $i++) {
                $pages[] = $i;
            }

            // garvin: Based on the number of results we add the specified
            // $percent percentate to each page number,
            // so that we have a representing page number every now and then to
            // immideately jump to specific pages.
            // As soon as we get near our currently chosen page ($pageNow -
            // $range), every page number will be
            // shown.
            $i = $sliceStart;
            $x = $nbTotalPage - $sliceEnd;
            $met_boundary = false;
            while ($i <= $x) {
                if ($i >= ($pageNow - $range) && $i <= ($pageNow + $range)) {
                    // If our pageselector comes near the current page, we use 1
                    // counter increments
                    $i++;
                    $met_boundary = true;
                } else {
                    // We add the percentate increment to our current page to
                    // hop to the next one in range
                    $i = $i + floor($nbTotalPage / $percent);

                    // Make sure that we do not cross our boundaries.
                    if ($i > ($pageNow - $range) && !$met_boundary) {
                        $i = $pageNow - $range;
                    }
                }

                if ($i > 0 && $i <= $x) {
                    $pages[] = $i;
                }
            }

            // Since because of ellipsing of the current page some numbers may be double,
            // we unify our array:
            sort($pages);
            $pages = array_unique($pages);
        }

        foreach ($pages as $i) {
            if ($i == $pageNow) {
                $selected = 'selected="selected" style="font-weight: bold"';
            } else {
                $selected = '';
            }
            $gotopage .= '                <option ' . $selected . ' value="' . (($i - 1) * $rows) . '">' . $i . '</option>' . "\n";
        }

        $gotopage .= ' </select>';

        return $gotopage;
    } // end function

    /**
     * @todo add documentation
     */
    function PMA_userDir($dir)
    {
        global $cfg;

        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }

        return str_replace('%u', $cfg['Server']['user'], $dir);
    }

    /**
     * returns html code for db link to default db page
     *
     * @uses    $GLOBALS['cfg']['DefaultTabDatabase']
     * @uses    $GLOBALS['db']
     * @uses    $GLOBALS['strJumpToDB']
     * @uses    PMA_generate_common_url()
     * @param   string  $database
     * @return  string  html link to default db page
     */
    function PMA_getDbLink($database = null)
    {
        if (!strlen($database)) {
            if (!strlen($GLOBALS['db'])) {
                return '';
            }
            $database = $GLOBALS['db'];
        } else {
            $database = PMA_unescape_mysql_wildcards($database);
        }

        return '<a href="' . $GLOBALS['cfg']['DefaultTabDatabase'] . '?' . PMA_generate_common_url($database) . '"'
            .' title="' . sprintf($GLOBALS['strJumpToDB'], htmlspecialchars($database)) . '">'
            .htmlspecialchars($database) . '</a>';
    }

    /**
     * Displays a lightbulb hint explaining a known external bug
     * that affects a functionality
     *
     * @uses    PMA_showHint()
     * @param   string  $functionality localized message explaining the func.
     * @param   string  $component  'mysql' (eventually, 'php')
     * @param   string  $minimum_version of this component
     * @param   string  $bugref  bug reference for this component
     */
    function PMA_externalBug($functionality, $component, $minimum_version, $bugref) {
        if ($component == 'mysql' && PMA_MYSQL_INT_VERSION < $minimum_version) {
            echo PMA_showHint(sprintf($GLOBALS['strKnownExternalBug'], $functionality, 'http://bugs.mysql.com/' . $bugref));
        }
    }

    /**
     * include here only libraries which contain only function definitions
     * no code im main()!
     */
    /**
     * Include URL/hidden inputs generating.
     */
    require_once './libraries/url_generating.lib.php';

}


/******************************************************************************/
/* start procedural code                       label_start_procedural         */

/**
 * protect against older PHP versions' bug about GLOBALS overwrite
 * (no need to localize this message :))
 * but what if script.php?GLOBALS[admin]=1&GLOBALS[_REQUEST]=1 ???
 */
if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])
  || isset($_SERVER['GLOBALS']) || isset($_COOKIE['GLOBALS'])
  || isset($_ENV['GLOBALS'])) {
    die('GLOBALS overwrite attempt');
}

/**
 * protect against possible exploits - there is no need to have so much vars
 */
if (count($_REQUEST) > 1000) {
    die('possible exploit');
}

/**
 * Check for numeric keys
 * (if register_globals is on, numeric key can be found in $GLOBALS)
 */

foreach ($GLOBALS as $key => $dummy) {
    if (is_numeric($key)) {
        die('numeric key detected');
    }
}

/**
 * just to be sure there was no import (registering) before here
 * we empty the global space
 */
$variables_whitelist = array (
    'GLOBALS',
    '_SERVER',
    '_GET',
    '_POST',
    '_REQUEST',
    '_FILES',
    '_ENV',
    '_COOKIE',
    '_SESSION',
);

foreach (get_defined_vars() as $key => $value) {
    if (! in_array($key, $variables_whitelist)) {
        unset($$key);
    }
}
unset($key, $value, $variables_whitelist);


/**
 * Subforms - some functions need to be called by form, cause of the limited url
 * length, but if this functions inside another form you cannot just open a new
 * form - so phpMyAdmin uses 'arrays' inside this form
 *
 * <code>
 * <form ...>
 * ... main form elments ...
 * <intput type="hidden" name="subform[action1][id]" value="1" />
 * ... other subform data ...
 * <intput type="submit" name="usesubform[action1]" value="do action1" />
 * ... other subforms ...
 * <intput type="hidden" name="subform[actionX][id]" value="X" />
 * ... other subform data ...
 * <intput type="submit" name="usesubform[actionX]" value="do actionX" />
 * ... main form elments ...
 * <intput type="submit" name="main_action" value="submit form" />
 * </form>
 * </code
 *
 * so we now check if a subform is submitted
 */
$__redirect = null;
if (isset($_POST['usesubform'])) {
    // if a subform is present and should be used
    // the rest of the form is deprecated
    $subform_id = key($_POST['usesubform']);
    $subform    = $_POST['subform'][$subform_id];
    $_POST      = $subform;
    $_REQUEST   = $subform;
    /**
     * some subforms need another page than the main form, so we will just
     * include this page at the end of this script - we use $__redirect to
     * track this
     */
    if (isset($_POST['redirect'])
      && $_POST['redirect'] != basename(PMA_getenv('PHP_SELF'))) {
        $__redirect = $_POST['redirect'];
        unset($_POST['redirect']);
    }
    unset($subform_id, $subform);
}
// end check if a subform is submitted

// remove quotes added by php
if (get_magic_quotes_gpc()) {
    PMA_arrayWalkRecursive($_GET, 'stripslashes', true);
    PMA_arrayWalkRecursive($_POST, 'stripslashes', true);
    PMA_arrayWalkRecursive($_COOKIE, 'stripslashes', true);
    PMA_arrayWalkRecursive($_REQUEST, 'stripslashes', true);
}
/**
 * In some cases, this one is not set
 *
 */
if (! isset($_REQUEST['js_frame']) || ! is_string($_REQUEST['js_frame'])) {
    $_REQUEST['js_frame'] = '';
}

/**
 * clean cookies on new install or upgrade
 * when changing something with increment the cookie version
 */
$pma_cookie_version = 4;
if (isset($_COOKIE)
 && (! isset($_COOKIE['pmaCookieVer'])
  || $_COOKIE['pmaCookieVer'] < $pma_cookie_version)) {
    // delete all cookies
    foreach($_COOKIE as $cookie_name => $tmp) {
        PMA_removeCookie($cookie_name);
    }
    $_COOKIE = array();
    PMA_setCookie('pmaCookieVer', $pma_cookie_version);
}

/**
 * include deprecated grab_globals only if required
 */
if (empty($__redirect) && !defined('PMA_NO_VARIABLES_IMPORT')) {
    require './libraries/grab_globals.lib.php';
}

/**
 * include session handling after the globals, to prevent overwriting
 */
require_once './libraries/session.inc.php';

/**
 * init some variables LABEL_variables_init
 */

/**
 * holds errors
 * @global array $GLOBALS['PMA_errors']
 */
$GLOBALS['PMA_errors'] = array();

/**
 * holds params to be passed to next page
 * @global array $GLOBALS['url_params']
 */
$GLOBALS['url_params'] = array();

/**
 * the whitelist for $GLOBALS['goto']
 * @global array $goto_whitelist
 */
$goto_whitelist = array(
    //'browse_foreigners.php',
    //'calendar.php',
    //'changelog.php',
    //'chk_rel.php',
    'db_create.php',
    'db_datadict.php',
    'db_sql.php',
    'db_export.php',
    'db_importdocsql.php',
    'db_qbe.php',
    'db_structure.php',
    'db_import.php',
    'db_operations.php',
    'db_printview.php',
    'db_search.php',
    //'Documentation.html',
    //'error.php',
    'export.php',
    'import.php',
    //'index.php',
    //'navigation.php',
    //'license.php',
    'main.php',
    'pdf_pages.php',
    'pdf_schema.php',
    //'phpinfo.php',
    'querywindow.php',
    //'readme.php',
    'server_binlog.php',
    'server_collations.php',
    'server_databases.php',
    'server_engines.php',
    'server_export.php',
    'server_import.php',
    'server_privileges.php',
    'server_processlist.php',
    'server_sql.php',
    'server_status.php',
    'server_variables.php',
    'sql.php',
    'tbl_addfield.php',
    'tbl_alter.php',
    'tbl_change.php',
    'tbl_create.php',
    'tbl_import.php',
    'tbl_indexes.php',
    'tbl_move_copy.php',
    'tbl_printview.php',
    'tbl_sql.php',
    'tbl_export.php',
    'tbl_operations.php',
    'tbl_structure.php',
    'tbl_relation.php',
    'tbl_replace.php',
    'tbl_row_action.php',
    'tbl_select.php',
    //'themes.php',
    'transformation_overview.php',
    'transformation_wrapper.php',
    'translators.html',
    'user_password.php',
);

/**
 * check $__redirect against whitelist
 */
if (! PMA_checkPageValidity($__redirect, $goto_whitelist)) {
    $__redirect = null;
}

/**
 * holds page that should be displayed
 * @global string $GLOBALS['goto']
 */
$GLOBALS['goto'] = '';
// Security fix: disallow accessing serious server files via "?goto="
if (PMA_checkPageValidity($_REQUEST['goto'], $goto_whitelist)) {
    $GLOBALS['goto'] = $_REQUEST['goto'];
    $GLOBALS['url_params']['goto'] = $_REQUEST['goto'];
} else {
    unset($_REQUEST['goto'], $_GET['goto'], $_POST['goto'], $_COOKIE['goto']);
}

/**
 * returning page
 * @global string $GLOBALS['back']
 */
if (PMA_checkPageValidity($_REQUEST['back'], $goto_whitelist)) {
    $GLOBALS['back'] = $_REQUEST['back'];
} else {
    unset($_REQUEST['back'], $_GET['back'], $_POST['back'], $_COOKIE['back']);
}

/**
 * Check whether user supplied token is valid, if not remove any possibly
 * dangerous stuff from request.
 *
 * remember that some objects in the session with session_start and __wakeup()
 * could access this variables before we reach this point
 * f.e. PMA_Config: fontsize
 *
 * @todo variables should be handled by their respective owners (objects)
 * f.e. lang, server, convcharset, collation_connection in PMA_Config
 */
if ((isset($_REQUEST['token']) && !is_string($_REQUEST['token'])) || empty($_REQUEST['token']) || $_SESSION[' PMA_token '] != $_REQUEST['token']) {
    /**
     *  List of parameters which are allowed from unsafe source
     */
    $allow_list = array(
        'db', 'table', 'lang', 'server', 'convcharset', 'collation_connection', 'target',
        /* Session ID */
        'phpMyAdmin',
        /* Cookie preferences */
        'pma_lang', 'pma_charset', 'pma_collation_connection',
        /* Possible login form */
        'pma_servername', 'pma_username', 'pma_password',
    );
    /**
     * Require cleanup functions
     */
    require_once('./libraries/cleanup.lib.php');
    /**
     * Do actual cleanup
     */
    PMA_remove_request_vars($allow_list);

}


/**
 * @global string $convcharset
 * @see select_lang.lib.php
 */
if (isset($_REQUEST['convcharset'])) {
    $convcharset = strip_tags($_REQUEST['convcharset']);
}

/**
 * current selected database
 * @global string $GLOBALS['db']
 */
$GLOBALS['db'] = '';
if (isset($_REQUEST['db']) && is_string($_REQUEST['db'])) {
    // can we strip tags from this?
    // only \ and / is not allowed in db names for MySQL
    $GLOBALS['db'] = $_REQUEST['db'];
    $GLOBALS['url_params']['db'] = $GLOBALS['db'];
}

/**
 * current selected table
 * @global string $GLOBALS['table']
 */
$GLOBALS['table'] = '';
if (isset($_REQUEST['table']) && is_string($_REQUEST['table'])) {
    // can we strip tags from this?
    // only \ and / is not allowed in table names for MySQL
    $GLOBALS['table'] = $_REQUEST['table'];
    $GLOBALS['url_params']['table'] = $GLOBALS['table'];
}

/**
 * sql query to be executed
 * @global string $GLOBALS['sql_query']
 */
if (isset($_REQUEST['sql_query']) && is_string($_REQUEST['sql_query'])) {
    $GLOBALS['sql_query'] = $_REQUEST['sql_query'];
}

//$_REQUEST['set_theme'] // checked later in this file LABEL_theme_setup
//$_REQUEST['server']; // checked later in this file
//$_REQUEST['lang'];   // checked by LABEL_loading_language_file



/******************************************************************************/
/* parsing config file                         LABEL_parsing_config_file      */

if (empty($_SESSION['PMA_Config'])) {
    /**
     * We really need this one!
     */
    if (!function_exists('preg_replace')) {
        header('Location: error.php'
            . '?lang='  . urlencode($available_languages[$lang][2])
            . '&dir='   . urlencode($text_dir)
            . '&type='  . urlencode($strError)
            . '&error=' . urlencode(
                strtr(sprintf($strCantLoad, 'pcre'),
                    array('<br />' => '[br]')))
            . '&' . SID
            );
        exit();
    }

    /**
     * @global PMA_Config $_SESSION['PMA_Config']
     */
    $_SESSION['PMA_Config'] = new PMA_Config('./config.inc.php');

} elseif (version_compare(phpversion(), '5', 'lt')) {
    /**
     * @todo move all __wakeup() functionality into session.inc.php
     */
    $_SESSION['PMA_Config']->__wakeup();
}

if (!defined('PMA_MINIMUM_COMMON')) {
    $_SESSION['PMA_Config']->checkPmaAbsoluteUri();
}

/**
 * BC - enable backward compatibility
 * exports all config settings into $GLOBALS ($GLOBALS['cfg'])
 */
$_SESSION['PMA_Config']->enableBc();


/**
 * check https connection
 */
if ($_SESSION['PMA_Config']->get('ForceSSL')
  && !$_SESSION['PMA_Config']->get('is_https')) {
    PMA_sendHeaderLocation(
        preg_replace('/^http/', 'https',
            $_SESSION['PMA_Config']->get('PmaAbsoluteUri'))
        . PMA_generate_common_url($_GET));
    exit;
}


/******************************************************************************/
/* loading language file                       LABEL_loading_language_file    */

/**
 * Added messages while developing:
 */
if (file_exists('./lang/added_messages.php')) {
    include './lang/added_messages.php';
}

/**
 * Includes the language file if it hasn't been included yet
 */
require './libraries/language.lib.php';


/**
 * check for errors occured while loading config
 * this check is done here after loading lang files to present errors in locale
 */
if ($_SESSION['PMA_Config']->error_config_file) {
    $GLOBALS['PMA_errors'][] = $strConfigFileError
        . '<br /><br />'
        . ($_SESSION['PMA_Config']->getSource() == './config.inc.php' ?
        '<a href="show_config_errors.php"'
        .' target="_blank">' . $_SESSION['PMA_Config']->getSource() . '</a>'
        :
        '<a href="' . $_SESSION['PMA_Config']->getSource() . '"'
        .' target="_blank">' . $_SESSION['PMA_Config']->getSource() . '</a>');
}
if ($_SESSION['PMA_Config']->error_config_default_file) {
    $GLOBALS['PMA_errors'][] = sprintf($strConfigDefaultFileError,
        $_SESSION['PMA_Config']->default_source);
}
if ($_SESSION['PMA_Config']->error_pma_uri) {
    $GLOBALS['PMA_errors'][] = sprintf($strPmaUriError);
}

/**
 * current server
 * @global integer $GLOBALS['server']
 */
$GLOBALS['server'] = 0;

/**
 * Servers array fixups.
 * $default_server comes from PMA_Config::enableBc()
 * @todo merge into PMA_Config
 */
// Do we have some server?
if (!isset($cfg['Servers']) || count($cfg['Servers']) == 0) {
    // No server => create one with defaults
    $cfg['Servers'] = array(1 => $default_server);
} else {
    // We have server(s) => apply default config
    $new_servers = array();

    foreach ($cfg['Servers'] as $server_index => $each_server) {

        // Detect wrong configuration
        if (!is_int($server_index) || $server_index < 1) {
            $GLOBALS['PMA_errors'][] = sprintf($strInvalidServerIndex, $server_index);
        }

        $each_server = array_merge($default_server, $each_server);

        // Don't use servers with no hostname
        if ($each_server['connect_type'] == 'tcp' && empty($each_server['host'])) {
            $GLOBALS['PMA_errors'][] = sprintf($strInvalidServerHostname, $server_index);
        }

        // Final solution to bug #582890
        // If we are using a socket connection
        // and there is nothing in the verbose server name
        // or the host field, then generate a name for the server
        // in the form of "Server 2", localized of course!
        if ($each_server['connect_type'] == 'socket' && empty($each_server['host']) && empty($each_server['verbose'])) {
            $each_server['verbose'] = $GLOBALS['strServer'] . $server_index;
        }

        $new_servers[$server_index] = $each_server;
    }
    $cfg['Servers'] = $new_servers;
    unset($new_servers, $server_index, $each_server);
}

// Cleanup
unset($default_server);


/******************************************************************************/
/* setup themes                                          LABEL_theme_setup    */

/**
 * @global PMA_Theme_Manager $_SESSION['PMA_Theme_Manager']
 */
if (! isset($_SESSION['PMA_Theme_Manager'])) {
    $_SESSION['PMA_Theme_Manager'] = new PMA_Theme_Manager;
} else {
    /**
     * @todo move all __wakeup() functionality into session.inc.php
     */
    $_SESSION['PMA_Theme_Manager']->checkConfig();
}

// for the theme per server feature
if (isset($_REQUEST['server']) && !isset($_REQUEST['set_theme'])) {
    $GLOBALS['server'] = $_REQUEST['server'];
    $tmp = $_SESSION['PMA_Theme_Manager']->getThemeCookie();
    if (empty($tmp)) {
        $tmp = $_SESSION['PMA_Theme_Manager']->theme_default;
    }
    $_SESSION['PMA_Theme_Manager']->setActiveTheme($tmp);
    unset($tmp);
}
/**
 * @todo move into PMA_Theme_Manager::__wakeup()
 */
if (isset($_REQUEST['set_theme'])) {
    // if user selected a theme
    $_SESSION['PMA_Theme_Manager']->setActiveTheme($_REQUEST['set_theme']);
}

/**
 * the theme object
 * @global PMA_Theme $_SESSION['PMA_Theme']
 */
$_SESSION['PMA_Theme'] = $_SESSION['PMA_Theme_Manager']->theme;

// BC
/**
 * the active theme
 * @global string $GLOBALS['theme']
 */
$GLOBALS['theme']           = $_SESSION['PMA_Theme']->getName();
/**
 * the theme path
 * @global string $GLOBALS['pmaThemePath']
 */
$GLOBALS['pmaThemePath']    = $_SESSION['PMA_Theme']->getPath();
/**
 * the theme image path
 * @global string $GLOBALS['pmaThemeImage']
 */
$GLOBALS['pmaThemeImage']   = $_SESSION['PMA_Theme']->getImgPath();

/**
 * load layout file if exists
 */
if (@file_exists($_SESSION['PMA_Theme']->getLayoutFile())) {
    include $_SESSION['PMA_Theme']->getLayoutFile();
    /**
     * @todo remove if all themes are update use Navi instead of Left as frame name
     */
    if (! isset($GLOBALS['cfg']['NaviWidth'])
     && isset($GLOBALS['cfg']['LeftWidth'])) {
        $GLOBALS['cfg']['NaviWidth'] = $GLOBALS['cfg']['LeftWidth'];
    }
}

if (! defined('PMA_MINIMUM_COMMON')) {
    /**
     * Charset conversion.
     */
    require_once './libraries/charset_conversion.lib.php';

    /**
     * String handling
     */
    require_once './libraries/string.lib.php';

    /**
     * Lookup server by name
     * by Arnold - Helder Hosting
     * (see FAQ 4.8)
     */
    if (! empty($_REQUEST['server']) && is_string($_REQUEST['server'])
     && ! is_numeric($_REQUEST['server'])) {
        foreach ($cfg['Servers'] as $i => $server) {
            if ($server['host'] == $_REQUEST['server']) {
                $_REQUEST['server'] = $i;
                break;
            }
        }
        if (is_string($_REQUEST['server'])) {
            unset($_REQUEST['server']);
        }
        unset($i);
    }

    /**
     * If no server is selected, make sure that $cfg['Server'] is empty (so
     * that nothing will work), and skip server authentication.
     * We do NOT exit here, but continue on without logging into any server.
     * This way, the welcome page will still come up (with no server info) and
     * present a choice of servers in the case that there are multiple servers
     * and '$cfg['ServerDefault'] = 0' is set.
     */

    if (isset($_REQUEST['server']) && (is_string($_REQUEST['server']) || is_numeric($_REQUEST['server'])) && ! empty($_REQUEST['server']) && ! empty($cfg['Servers'][$_REQUEST['server']])) {
        $GLOBALS['server'] = $_REQUEST['server'];
        $cfg['Server'] = $cfg['Servers'][$GLOBALS['server']];
    } else {
        if (!empty($cfg['Servers'][$cfg['ServerDefault']])) {
            $GLOBALS['server'] = $cfg['ServerDefault'];
            $cfg['Server'] = $cfg['Servers'][$GLOBALS['server']];
        } else {
            $GLOBALS['server'] = 0;
            $cfg['Server'] = array();
        }
    }
    $GLOBALS['url_params']['server'] = $GLOBALS['server'];


    if (! empty($cfg['Server'])) {

        /**
         * Loads the proper database interface for this server
         */
        require_once './libraries/database_interface.lib.php';

        // Gets the authentication library that fits the $cfg['Server'] settings
        // and run authentication

        // (for a quick check of path disclosure in auth/cookies:)
        $coming_from_common = true;

        // to allow HTTP or http
        $cfg['Server']['auth_type'] = strtolower($cfg['Server']['auth_type']);
        if (!file_exists('./libraries/auth/' . $cfg['Server']['auth_type'] . '.auth.lib.php')) {
            header('Location: error.php'
                    . '?lang='  . urlencode($available_languages[$lang][2])
                    . '&dir='   . urlencode($text_dir)
                    . '&type='  . urlencode($strError)
                    . '&error=' . urlencode(
                        $strInvalidAuthMethod . ' '
                        . $cfg['Server']['auth_type'])
                    . '&' . SID
                    );
            exit();
        }
        /**
         * the required auth type plugin
         */
        require_once './libraries/auth/' . $cfg['Server']['auth_type'] . '.auth.lib.php';

        if (!PMA_auth_check()) {
            PMA_auth();
        } else {
            PMA_auth_set_user();
        }

        // Check IP-based Allow/Deny rules as soon as possible to reject the
        // user
        // Based on mod_access in Apache:
        // http://cvs.apache.org/viewcvs.cgi/httpd-2.0/modules/aaa/mod_access.c?rev=1.37&content-type=text/vnd.viewcvs-markup
        // Look at: "static int check_dir_access(request_rec *r)"
        // Robbat2 - May 10, 2002
        if (isset($cfg['Server']['AllowDeny'])
          && isset($cfg['Server']['AllowDeny']['order'])) {

            /**
             * ip based access library
             */
            require_once './libraries/ip_allow_deny.lib.php';

            $allowDeny_forbidden         = false; // default
            if ($cfg['Server']['AllowDeny']['order'] == 'allow,deny') {
                $allowDeny_forbidden     = true;
                if (PMA_allowDeny('allow')) {
                    $allowDeny_forbidden = false;
                }
                if (PMA_allowDeny('deny')) {
                    $allowDeny_forbidden = true;
                }
            } elseif ($cfg['Server']['AllowDeny']['order'] == 'deny,allow') {
                if (PMA_allowDeny('deny')) {
                    $allowDeny_forbidden = true;
                }
                if (PMA_allowDeny('allow')) {
                    $allowDeny_forbidden = false;
                }
            } elseif ($cfg['Server']['AllowDeny']['order'] == 'explicit') {
                if (PMA_allowDeny('allow')
                  && !PMA_allowDeny('deny')) {
                    $allowDeny_forbidden = false;
                } else {
                    $allowDeny_forbidden = true;
                }
            } // end if ... elseif ... elseif

            // Ejects the user if banished
            if ($allowDeny_forbidden) {
               PMA_auth_fails();
            }
            unset($allowDeny_forbidden); //Clean up after you!
        } // end if

        // is root allowed?
        if (!$cfg['Server']['AllowRoot'] && $cfg['Server']['user'] == 'root') {
            $allowDeny_forbidden = true;
            PMA_auth_fails();
            unset($allowDeny_forbidden); //Clean up after you!
        }

        $bkp_track_err = @ini_set('track_errors', 1);

        // Try to connect MySQL with the control user profile (will be used to
        // get the privileges list for the current user but the true user link
        // must be open after this one so it would be default one for all the
        // scripts)
        if ($cfg['Server']['controluser'] != '') {
            $controllink = PMA_DBI_connect($cfg['Server']['controluser'],
                $cfg['Server']['controlpass'], true);
        } else {
            $controllink = PMA_DBI_connect($cfg['Server']['user'],
                $cfg['Server']['password'], true);
        } // end if ... else

        // Pass #1 of DB-Config to read in master level DB-Config will go here
        // Robbat2 - May 11, 2002

        // Connects to the server (validates user's login)
        $userlink = PMA_DBI_connect($cfg['Server']['user'],
            $cfg['Server']['password'], false);

        // Pass #2 of DB-Config to read in user level DB-Config will go here
        // Robbat2 - May 11, 2002

        @ini_set('track_errors', $bkp_track_err);
        unset($bkp_track_err);

        /**
         * If we auto switched to utf-8 we need to reread messages here
         */
        if (defined('PMA_LANG_RELOAD')) {
            require './libraries/language.lib.php';
        }

        /**
         * SQL Parser code
         */
        require_once './libraries/sqlparser.lib.php';

        /**
         * SQL Validator interface code
         */
        require_once './libraries/sqlvalidator.lib.php';

        /**
         * the PMA_List_Database class
         */
        require_once './libraries/PMA_List_Database.class.php';
        $PMA_List_Database = new PMA_List_Database($userlink, $controllink);

    } // end server connecting

    /**
     * Kanji encoding convert feature appended by Y.Kawada (2002/2/20)
     */
    if (@function_exists('mb_convert_encoding')
        && strpos(' ' . $lang, 'ja-')
        && file_exists('./libraries/kanji-encoding.lib.php')) {
        require_once './libraries/kanji-encoding.lib.php';
        define('PMA_MULTIBYTE_ENCODING', 1);
    } // end if

    /**
     * save some settings in cookies
     * @todo should be done in PMA_Config
     */
    PMA_setCookie('pma_lang', $GLOBALS['lang']);
    PMA_setCookie('pma_charset', $GLOBALS['convcharset']);
    PMA_setCookie('pma_collation_connection', $GLOBALS['collation_connection']);

    $_SESSION['PMA_Theme_Manager']->setThemeCookie();
} // end if !defined('PMA_MINIMUM_COMMON')

if (!empty($__redirect) && in_array($__redirect, $goto_whitelist)) {
    // to handle bug #1388167
    if (isset($_GET['is_js_confirmed'])) {
        $is_js_confirmed = 1;
    }
    /**
     * include subform target page
     */
    require $__redirect;
    exit();
}
?>
