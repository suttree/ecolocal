<?php
/*
 * Code for displaying server selection written by nijel
 * $Id: select_server.lib.php 9438 2006-09-21 14:28:46Z cybot_tm $
 */

/**
 * display server selection in list or selectbox form, or option tags only
 *
 * @globals $lang
 * @globals $convcharset
 * @uses    $GLOBALS['cfg']['DisplayServersList']
 * @uses    $GLOBALS['strServer']
 * @uses    $GLOBALS['cfg']['Servers']
 * @uses    $GLOBALS['strGo']
 * @uses    implode()
 * @uses    htmlspecialchars()
 * @param   boolean $not_only_options   whether to include form tags or not
 * @param   boolean $ommit_fieldset     whether to ommit fieldset tag or not
 */
function PMA_select_server($not_only_options, $ommit_fieldset)
{
    global $lang, $convcharset;

    // Show as list?
    if ($not_only_options) {
        $list = $GLOBALS['cfg']['DisplayServersList'];
        $not_only_options =! $list;
    } else {
        $list = false;
    }

    if ($not_only_options) {
        echo '<form method="post" action="index.php" target="_parent">';

        if (! $ommit_fieldset) {
            echo '<fieldset>';
        }
        echo '<label for="select_server">' . $GLOBALS['strServer'] . ':</label> ';

        echo '<select name="server" id="select_server"'
            . ' onchange="if (this.value != \'\') this.form.submit();">';
        echo '<option value="">(' . $GLOBALS['strServers'] . ') ...</option>' . "\n";
    } elseif ($list) {
        echo $GLOBALS['strServer'] . ':<br />';
        echo '<ul id="list_server">';
    }

    foreach ($GLOBALS['cfg']['Servers'] as $key => $server) {
        if (empty($server['host'])) {
            continue;
        }

        if (!empty($GLOBALS['server']) && (int) $GLOBALS['server'] === (int) $key) {
            $selected = 1;
        } else {
            $selected = 0;
        }

        if (!empty($server['verbose'])) {
            $label = $server['verbose'];
        } else {
            $label = $server['host'];
            if (!empty($server['port'])) {
                $label .= ':' . $server['port'];
            }
        }
        // loic1: if 'only_db' is an array and there is more than one
        //        value, displaying such informations may not be a so good
        //        idea
        if (!empty($server['only_db'])) {
            /**
             * @todo this can become a really big/long/wide selectbox ...
             */
            $label .= ' - ' . (is_array($server['only_db']) ? implode(', ', $server['only_db']) : $server['only_db']);
        }
        if (!empty($server['user']) && $server['auth_type'] == 'config') {
            $label .= '  (' . $server['user'] . ')';
        }

        if ($list) {
            echo '<li>';
            if ($selected && !$ommit_fieldset) {
                echo '<b>' . htmlspecialchars($label) . '</b>';
            } else {
                echo '<a class="item" href="index.php?server=' . $key . '&amp;lang=' . $lang . '&amp;convcharset=' . $convcharset . '" target="_top">' . htmlspecialchars($label) . '</a>';
            }
            echo '</li>';
        } else {
            echo '            <option value="' . $key . '" ' . ($selected ? ' selected="selected"' : '') . '>' . htmlspecialchars($label) . '</option>' . "\n";
        }
    } // end while

    if ($not_only_options) {
        echo '</select>';
        ?>
        <input type="hidden" name="lang" value="<?php echo $lang; ?>" />
        <input type="hidden" name="convcharset" value="<?php echo $convcharset; ?>" />
        <?php
        // Show submit button if we have just one server (this happens with no default)
        echo '<noscript>';
        echo '<input type="submit" value="' . $GLOBALS['strGo'] . '" />';
        echo '</noscript>';
        if (! $ommit_fieldset) {
            echo '</fieldset>';
        }
        echo '</form>';
    } elseif ($list) {
        echo '</ul>';
    }
}
?>
