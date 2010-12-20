<?php
/* $Id: cookie.auth.lib.php 9991 2007-02-14 21:18:38Z lem9 $ */
// vim: expandtab sw=4 ts=4 sts=4:

// +--------------------------------------------------------------------------+
// | Set of functions used to run cookie based authentication.                |
// | Thanks to Piotr Roszatycki <d3xter at users.sourceforge.net> and         |
// | Dan Wilson who built this patch for the Debian package.                  |
// +--------------------------------------------------------------------------+


if (!isset($coming_from_common)) {
   exit;
}

// timestamp for login timeout
$current_time  = time();

// Uses faster mcrypt library if available
// (Note: mcrypt.lib.php needs $cookie_path and $is_https)
if (function_exists('mcrypt_encrypt') || PMA_dl('mcrypt')) {
    require_once './libraries/mcrypt.lib.php';
} else {
    require_once './libraries/blowfish.php';
    // for main.php:
    define('PMA_WARN_FOR_MCRYPT',1);
}


/**
 * Displays authentication form
 *
 * @global  string    the font face to use
 * @global  string    the default font size to use
 * @global  string    the big font size to use
 * @global  array     the list of servers settings
 * @global  array     the list of available translations
 * @global  string    the current language
 * @global  integer   the current server id
 * @global  string    the currect charset for MySQL
 * @global  array     the array of cookie variables if register_globals is
 *                    off
 *
 * @return  boolean   always true (no return indeed)
 *
 * @access  public
 */
function PMA_auth()
{
    global $cfg, $lang, $server, $convcharset, $conn_error;

    /* Perform logout to custom URL */
    if (!empty($_REQUEST['old_usr']) && !empty($GLOBALS['cfg']['Server']['LogoutURL'])) {
        PMA_sendHeaderLocation($GLOBALS['cfg']['Server']['LogoutURL']);
        exit;
    }

    // Tries to get the username from cookie whatever are the values of the
    // 'register_globals' and the 'variables_order' directives if last login
    // should be recalled, else skip the IE autocomplete feature.
    if ($cfg['LoginCookieRecall'] && !empty($GLOBALS['cfg']['blowfish_secret'])) {
        // username
        // do not try to use pma_cookie_username as it was encoded differently
        // in previous versions and would produce an undefined offset in blowfish
        if (!empty($_COOKIE) && isset($_COOKIE['pma_cookie_username-' . $server])) {
            $default_user = $_COOKIE['pma_cookie_username-' . $server];
        }
        $decrypted_user = isset($default_user) ? PMA_blowfish_decrypt($default_user, $GLOBALS['cfg']['blowfish_secret']) : '';
        if (!empty($decrypted_user)) {
            $pos = strrpos($decrypted_user, ':');
            $default_user = substr($decrypted_user, 0, $pos);
        } else {
            $default_user = '';
        }
        // server name
        if (!empty($GLOBALS['pma_cookie_servername'])) {
            $default_server = $GLOBALS['pma_cookie_servername'];
        } elseif (!empty($_COOKIE) && isset($_COOKIE['pma_cookie_servername-' . $server])) {
            $default_server = $_COOKIE['pma_cookie_servername-' . $server];
        }

        $autocomplete     = '';
    } else {
        $default_user     = '';
        $autocomplete     = ' autocomplete="off"';
    }

    $cell_align = ($GLOBALS['text_dir'] == 'ltr') ? 'left' : 'right';

    // Defines the charset to be used
    header('Content-Type: text/html; charset=' . $GLOBALS['charset']);
    // Defines the "item" image depending on text direction
    $item_img = $GLOBALS['pmaThemeImage'] . 'item_ltr.png';

    /* HTML header */
    $page_title = 'phpMyAdmin ' . PMA_VERSION;
    require './libraries/header_meta_style.inc.php';
    ?>
<script type="text/javascript" language="javascript">
//<![CDATA[
// show login form in top frame
if (top != self) {
    window.top.location.href=location;
}
//]]>
</script>
</head>

<body class="loginform">

<?php if (file_exists('./config.header.inc.php')) {
          require('./config.header.inc.php');
      } 
?>

<div class="container">
<a href="http://www.phpmyadmin.net" target="_blank" class="logo"><?php
    $logo_image = $GLOBALS['pmaThemeImage'] . 'logo_right.png';
    if (@file_exists($logo_image)) {
        echo '<img src="' . $logo_image . '" id="imLogo" name="imLogo" alt="phpMyAdmin" border="0" />';
    } else {
        echo '<img name="imLogo" id="imLogo" src="' . $GLOBALS['pmaThemeImage'] . 'pma_logo.png' . '" '
           . 'border="0" width="88" height="31" alt="phpMyAdmin" />';
    }
?></a>
<h1>
<?php
echo sprintf( $GLOBALS['strWelcome'],
    '<bdo dir="ltr" xml:lang="en">phpMyAdmin ' . PMA_VERSION . '</bdo>');
?>
</h1>
    <?php

    // Show error message
    if ( !empty($conn_error)) {
        echo '<div class="error"><h1>' . $GLOBALS['strError'] . '</h1>' . "\n";
        echo $conn_error . '</div>' . "\n";
    }

    // Displays the languages form
    if (empty($cfg['Lang'])) {
        echo "\n";
        require_once './libraries/display_select_lang.lib.php';
        PMA_select_language(true);
    }
    echo "\n\n";

    // Displays the warning message and the login form

    if (empty($GLOBALS['cfg']['blowfish_secret'])) {
    ?>
        <div class="error"><h1><?php echo $GLOBALS['strError']; ?></h1>
            <?php echo $GLOBALS['strSecretRequired']; ?>
        </div>
<?php
        echo '</div>' . "\n";
        if (file_exists('./config.footer.inc.php')) {
            require('./config.footer.inc.php');
        }

        echo '    </body>' . "\n"
           . '</html>';
        exit();
    }
?>
<br />
<!-- Login form -->
<form method="post" action="index.php" name="login_form"<?php echo $autocomplete; ?> target="_top" class="login">
    <fieldset>
        <legend><?php echo $GLOBALS['strLogin']; ?></legend>

<?php if ($GLOBALS['cfg']['AllowArbitraryServer']) { ?>
        <div class="item">
            <label for="input_servername"><?php echo $GLOBALS['strLogServer']; ?></label>
            <input type="text" name="pma_servername" id="input_servername" value="<?php echo (isset($default_server) ? htmlspecialchars($default_server) : ''); ?>" size="24" class="textfield" />
        </div>
<?php } ?>
        <div class="item">
            <label for="input_username"><?php echo $GLOBALS['strLogUsername']; ?></label>
            <input type="text" name="pma_username" id="input_username" value="<?php echo (isset($default_user) ? htmlspecialchars($default_user) : ''); ?>" size="24" class="textfield" />
        </div>
        <div class="item">
            <label for="input_password"><?php echo $GLOBALS['strLogPassword']; ?></label>
            <input type="password" name="pma_password" id="input_password" value="" size="24" class="textfield" />
        </div>
    <?php
    if (count($cfg['Servers']) > 1) {
        echo "\n";
        ?>
        <div class="item">
            <label for="select_server"><?php echo $GLOBALS['strServerChoice']; ?>:</label>
            <select name="server" id="select_server"
            <?php
            if ($GLOBALS['cfg']['AllowArbitraryServer']) {
                echo ' onchange="document.forms[\'login_form\'].elements[\'pma_servername\'].value = \'\'" ';
            }
            ?>
            >
        <?php
        require_once './libraries/select_server.lib.php';
        PMA_select_server(false, false);
        ?>
            </select>
        </div>
    <?php
    } else {
        echo '    <input type="hidden" name="server" value="' . $server . '" />';
    } // end if (server choice)
    ?>
    </fieldset>
    <fieldset class="tblFooters">
        <input value="<?php echo $GLOBALS['strGo']; ?>" type="submit" />
        <input type="hidden" name="lang" value="<?php echo $lang; ?>" />
        <input type="hidden" name="convcharset" value="<?php echo $convcharset; ?>" />
    <?php
    if (!empty($GLOBALS['target'])) {
        echo '            <input type="hidden" name="target" value="' . htmlspecialchars($GLOBALS['target']) . '" />' . "\n";
    }
    if (!empty($GLOBALS['db'])) {
        echo '            <input type="hidden" name="db" value="' . htmlspecialchars($GLOBALS['db']) . '" />' . "\n";
    }
    if (!empty($GLOBALS['table'])) {
        echo '            <input type="hidden" name="table" value="' . htmlspecialchars($GLOBALS['table']) . '" />' . "\n";
    }
    ?>
    </fieldset>
</form>

<?php
// show the "Cookies required" message only if cookies are disabled
// (we previously tried to set some cookies)
if (empty($_COOKIE)) {
    echo '<div class="notice">' . $GLOBALS['strCookiesRequired'] . '</div>' . "\n";
}
if ( ! empty( $GLOBALS['PMA_errors'] ) && is_array( $GLOBALS['PMA_errors'] ) ) {
    foreach ( $GLOBALS['PMA_errors'] as $error ) {
        echo '<div class="error">' . $error . '</div>' . "\n";
    }
}
?>

<script type="text/javascript" language="javascript">
<!--
var uname = document.forms['login_form'].elements['pma_username'];
var pword = document.forms['login_form'].elements['pma_password'];
if (uname.value == '') {
    uname.focus();
} else {
    pword.focus();
}
//-->
</script>
</div>

<?php if (file_exists('./config.footer.inc.php')) {
         require('./config.footer.inc.php');
      }
 ?>

</body>

</html>
    <?php
    exit();

    return true;
} // end of the 'PMA_auth()' function


/**
 * Gets advanced authentication settings
 *
 * @global  string    the username if register_globals is on
 * @global  string    the password if register_globals is on
 * @global  array     the array of cookie variables if register_globals is
 *                    off
 * @global  string    the servername sent by the login form
 * @global  string    the username sent by the login form
 * @global  string    the password sent by the login form
 * @global  string    the username of the user who logs out
 * @global  boolean   whether the login/password pair is grabbed from a
 *                    cookie or not
 *
 * @return  boolean   whether we get authentication settings or not
 *
 * @access  public
 */
function PMA_auth_check()
{
    global $PHP_AUTH_USER, $PHP_AUTH_PW, $pma_auth_server;
    global $pma_servername, $pma_username, $pma_password, $old_usr, $server;
    global $from_cookie;

    // avoid an error in mcrypt
    if (empty($GLOBALS['cfg']['blowfish_secret'])) {
        return false;
    }

    // Initialization
    $PHP_AUTH_USER = $PHP_AUTH_PW = '';
    $from_cookie   = false;
    $from_form     = false;

    // The user wants to be logged out -> delete password cookie(s)
    if (!empty($old_usr)) {
        if ($GLOBALS['cfg']['LoginCookieDeleteAll']) {
            foreach($GLOBALS['cfg']['Servers'] as $key => $val) {
                PMA_removeCookie('pma_cookie_password-' . $key);
            }
        } else {
            PMA_removeCookie('pma_cookie_password-' . $server);
        }
    }

    // The user just logged in
    elseif (!empty($pma_username)) {
        $PHP_AUTH_USER = $pma_username;
        $PHP_AUTH_PW   = (empty($pma_password)) ? '' : $pma_password;
        if ($GLOBALS['cfg']['AllowArbitraryServer']) {
            $pma_auth_server = $pma_servername;
        }
        $from_form     = true;
    }

    // At the end, try to set the $PHP_AUTH_USER & $PHP_AUTH_PW variables
    // from cookies whatever are the values of the 'register_globals' and
    // the 'variables_order' directives
    else {
        if ($GLOBALS['cfg']['AllowArbitraryServer']) {
            // servername
            if (!empty($pma_cookie_servername)) {
                $pma_auth_server = $pma_cookie_servername;
                $from_cookie   = true;
            } elseif (!empty($_COOKIE) && isset($_COOKIE['pma_cookie_servername-' . $server])) {
                $pma_auth_server = $_COOKIE['pma_cookie_servername-' . $server];
                $from_cookie   = true;
            }
        }

        // username
        if (!empty($_COOKIE) && isset($_COOKIE['pma_cookie_username-' . $server])) {
            $PHP_AUTH_USER = $_COOKIE['pma_cookie_username-' . $server];
            $from_cookie   = true;
        }
        $decrypted_user = PMA_blowfish_decrypt($PHP_AUTH_USER, $GLOBALS['cfg']['blowfish_secret']);
        if (!empty($decrypted_user)) {
            $pos = strrpos($decrypted_user, ':');
            $PHP_AUTH_USER = substr($decrypted_user, 0, $pos);
            $decrypted_time = (int)substr($decrypted_user, $pos + 1);
        } else {
            $decrypted_time = 0;
        }

        // User inactive too long
        if ($decrypted_time > 0 && $decrypted_time < $GLOBALS['current_time'] - $GLOBALS['cfg']['LoginCookieValidity']) {
            // Display an error message only if the inactivity has lasted
            // less than 4 times the timeout value. This is to avoid
            // alerting users with a error after "much" time has passed,
            // for example next morning.
            if ($decrypted_time > $GLOBALS['current_time'] - ($GLOBALS['cfg']['LoginCookieValidity'] * 4)) {
                $GLOBALS['no_activity'] = true;
                PMA_auth_fails();
            }
            return false;
        }

        // password
        if (!empty($pma_cookie_password)) {
            $PHP_AUTH_PW   = $pma_cookie_password;
        } elseif (!empty($_COOKIE) && isset($_COOKIE['pma_cookie_password-' . $server])) {
            $PHP_AUTH_PW   = $_COOKIE['pma_cookie_password-' . $server];
        } else {
            $from_cookie   = false;
        }
        $PHP_AUTH_PW = PMA_blowfish_decrypt($PHP_AUTH_PW, $GLOBALS['cfg']['blowfish_secret'] . $decrypted_time);

        if ($PHP_AUTH_PW == "\xff(blank)") {
            $PHP_AUTH_PW   = '';
        }
    }

    // Returns whether we get authentication settings or not
    if (!$from_cookie && !$from_form) {
        return false;
    } elseif ($from_cookie) {
        return true;
    } else {
        // we don't need to strip here, it is done in grab_globals
        return true;
    }
} // end of the 'PMA_auth_check()' function


/**
 * Set the user and password after last checkings if required
 *
 * @global  array     the valid servers settings
 * @global  integer   the id of the current server
 * @global  array     the current server settings
 * @global  string    the current username
 * @global  string    the current password
 * @global  boolean   whether the login/password pair has been grabbed from
 *                    a cookie or not
 *
 * @return  boolean   always true
 *
 * @access  public
 */
function PMA_auth_set_user()
{
    global $cfg, $server;
    global $PHP_AUTH_USER, $PHP_AUTH_PW, $pma_auth_server;
    global $from_cookie;

    // Ensures valid authentication mode, 'only_db', bookmark database and
    // table names and relation table name are used
    if ($cfg['Server']['user'] != $PHP_AUTH_USER) {
        foreach ($cfg['Servers'] as $idx => $current) {
            if ($current['host'] == $cfg['Server']['host'] 
                    && $current['port'] == $cfg['Server']['port'] 
                    && $current['socket'] == $cfg['Server']['socket'] 
                    && $current['ssl'] == $cfg['Server']['ssl'] 
                    && $current['connect_type'] == $cfg['Server']['connect_type'] 
                    && $current['user'] == $PHP_AUTH_USER) {
                $server        = $idx;
                $cfg['Server'] = $current;
                break;
            }
        } // end foreach
    } // end if

    $pma_server_changed = false;
    if ($GLOBALS['cfg']['AllowArbitraryServer']
            && isset($pma_auth_server) && !empty($pma_auth_server)
            && ($cfg['Server']['host'] != $pma_auth_server)
            ) {
        $cfg['Server']['host'] = $pma_auth_server;
        $pma_server_changed = true;
    }
    $cfg['Server']['user']     = $PHP_AUTH_USER;
    $cfg['Server']['password'] = $PHP_AUTH_PW;

    // Name and password cookies needs to be refreshed each time
    // Duration = one month for username
    PMA_setCookie('pma_cookie_username-' . $server, PMA_blowfish_encrypt($cfg['Server']['user'] . ':' . $GLOBALS['current_time'], $GLOBALS['cfg']['blowfish_secret']));

    // Duration = as configured
    PMA_setCookie('pma_cookie_password-' . $server,
        PMA_blowfish_encrypt(!empty($cfg['Server']['password']) ? $cfg['Server']['password'] : "\xff(blank)",
            $GLOBALS['cfg']['blowfish_secret'] . $GLOBALS['current_time']),
        null,
        $GLOBALS['cfg']['LoginCookieStore']);

    // Set server cookies if required (once per session) and, in this case, force
    // reload to ensure the client accepts cookies
    if (!$from_cookie) {
        if ($GLOBALS['cfg']['AllowArbitraryServer']) {
            if (isset($pma_auth_server) && !empty($pma_auth_server) && $pma_server_changed) {
                // Duration = one month for serverrname
                PMA_setCookie('pma_cookie_servername-' . $server, $cfg['Server']['host']);
            } else {
                // Delete servername cookie
                PMA_removeCookie('pma_cookie_servername-' . $server);
            }
        }

        // URL where to go:
        $redirect_url = $cfg['PmaAbsoluteUri'] . 'index.php';

        // any parameters to pass?
        $url_params = array();
        if ( isset($GLOBALS['db']) && strlen($GLOBALS['db']) ) {
            $url_params['db'] = $GLOBALS['db'];
        }
        if ( isset($GLOBALS['table']) && strlen($GLOBALS['table']) ) {
            $url_params['table'] = $GLOBALS['table'];
        }
        // Language change from the login panel needs to be remembered
        if ( ! empty($GLOBALS['lang']) ) {
            $url_params['lang'] = $GLOBALS['lang'];
        }
        // any target to pass?
        if ( ! empty($GLOBALS['target']) && $GLOBALS['target'] != 'index.php' ) {
            $url_params['target'] = $GLOBALS['target'];
        }

        define('PMA_COMING_FROM_COOKIE_LOGIN',1);
        PMA_sendHeaderLocation( $redirect_url . PMA_generate_common_url( $url_params, '&' ) );
        exit();
    } // end if

    return true;
} // end of the 'PMA_auth_set_user()' function


/**
 * User is not allowed to login to MySQL -> authentication failed
 *
 * @return  boolean   always true (no return indeed)
 *
 * @access  public
 */
function PMA_auth_fails()
{
    global $conn_error, $server;

    // Deletes password cookie and displays the login form
    PMA_removeCookie('pma_cookie_password-' . $server);

    if (isset($GLOBALS['allowDeny_forbidden']) && $GLOBALS['allowDeny_forbidden']) {
        $conn_error = $GLOBALS['strAccessDenied'];
    } elseif (isset($GLOBALS['no_activity']) && $GLOBALS['no_activity']) {
        $conn_error = sprintf($GLOBALS['strNoActivity'], $GLOBALS['cfg']['LoginCookieValidity']);
        // Remember where we got timeout to return on same place
        if (PMA_getenv('SCRIPT_NAME')) {
            $GLOBALS['target'] = basename(PMA_getenv('SCRIPT_NAME'));
        }
    } elseif (PMA_DBI_getError()) {
        $conn_error = PMA_sanitize(PMA_DBI_getError());
    } elseif (isset($php_errormsg)) {
        $conn_error = $php_errormsg;
    } else {
        $conn_error = $GLOBALS['strCannotLogin'];
    }

    PMA_auth();

    return true;
} // end of the 'PMA_auth_fails()' function

?>
