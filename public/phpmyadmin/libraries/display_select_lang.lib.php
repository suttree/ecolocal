<?php
/*
 * Code for displaying language selection
 * $Id: display_select_lang.lib.php 9265 2006-08-09 21:19:33Z lem9 $
 */

/**
 * Sorts available languages by their true english names
 *
 * @param   array   the array to be sorted
 * @param   mixed   a required parameter
 * @return  the sorted array
 * @access  private
 */
function PMA_language_cmp( &$a, &$b ) {
    return (strcmp($a[1], $b[1]));
} // end of the 'PMA_language_cmp()' function

/**
 * Displays for for language selection
 *
 * @access  public
 */
function PMA_select_language($use_fieldset = FALSE) {
    global $cfg, $lang;
    ?>

<form method="post" action="index.php" target="_parent">
    <?php
    if (isset($GLOBALS['collation_connection'])) {
        echo '            <input type="hidden" name="collation_connection" value="'
            . htmlspecialchars($GLOBALS['collation_connection']) . '" />' . "\n";
    }
    if (isset($GLOBALS['convcharset'])) {
        echo '            <input type="hidden" name="convcharset" value="'
            . htmlspecialchars($GLOBALS['convcharset']) . '" />' . "\n";
    }
    if (isset($GLOBALS['db'])) {
        echo '            <input type="hidden" name="db" value="'
            . htmlspecialchars($GLOBALS['db']) . '" />' . "\n";
    }
    if (isset($GLOBALS['table'])) {
        echo '            <input type="hidden" name="table" value="'
            . htmlspecialchars($GLOBALS['table']) . '" />' . "\n";
    }
    if (isset($GLOBALS['server'])) {
        echo '            <input type="hidden" name="server" value="'
            . ((int)$GLOBALS['server']) . '" />' . "\n";
    }

    $language_title = $GLOBALS['strLanguage'] . ($GLOBALS['strLanguage'] != 'Language' ? ' - Language' : '') . ' <a href="./translators.html" target="documentation">' .
            ( $cfg['ReplaceHelpImg'] ? 
                '<img class="icon" src="' . $GLOBALS['pmaThemeImage'] . 'b_info.png" width="11" height="11" alt="Info" />' :
                '(*)' ) . '</a>';
    if ($use_fieldset) {
        echo '<fieldset><legend xml:lang="en" dir="ltr">' . $language_title . '</legend>';
    } else {
        echo '<bdo xml:lang="en" dir="ltr">' . $language_title . ':</bdo>';
    }
    ?>

    <select name="lang" onchange="this.form.submit();" xml:lang="en" dir="ltr">
    <?php

    uasort($GLOBALS['available_languages'], 'PMA_language_cmp');
    foreach ($GLOBALS['available_languages'] AS $id => $tmplang) {
        $lang_name = ucfirst(substr(strrchr($tmplang[0], '|'), 1));

        // Include native name if non empty
        if (!empty($tmplang[3])) {
            $lang_name = $tmplang[3] . ' - '
                . $lang_name;
        }

        // Include charset if it makes sense
        if (!defined('PMA_REMOVED_NON_UTF_8')) {
            $lang_name .= ' (' . substr($id, strpos($id, '-') + 1) . ')';
        }

        //Is current one active?
        if ($lang == $id) {
            $selected = ' selected="selected"';
        } else {
            $selected = '';
        }

        echo '        ';
        echo '<option value="' . $id . '"' . $selected . '>' . $lang_name
            . '</option>' . "\n";
    }
    ?>

    </select>
    <?php
    if ($use_fieldset) {
        echo '</fieldset>';
    }
    ?>

    <noscript>
    <?php
    if ($use_fieldset) {
        echo '<fieldset class="tblFooters">';
    }
    ?>

        <input type="submit" value="Go" />
    <?php
    if ($use_fieldset) {
        echo '</fieldset>';
    }
    ?>

    </noscript>
</form>
    <?php
} // End of function PMA_select_language
?>
