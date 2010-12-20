<?php
/* $Id: pmd_general.php 9929 2007-02-06 09:56:31Z cybot_tm $ */
// vim: expandtab sw=4 ts=4 sts=4:

/*
@author  Ivan A Kirillov (Ivan.A.Kirillov@gmail.com)
*/
include_once "./pmd_common.php";

$tab_column       = get_tab_info();
$script_tabs      = get_script_tabs();
$script_contr     = get_script_contr();
$tab_pos          = get_tab_pos();
$tables_pk_or_unique_keys = get_pk_or_unique_keys();
$tables_all_keys  = get_all_keys();
$hidden           = "hidden";

?>
<html xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<link rel="SHORTCUT ICON" href="pmd/images/favicon.ico" />
<?php if(0){ ?>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link rel="stylesheet" type="text/css" href="pmd/styles/default/style1.css">
<?php } ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>" />
<link rel="stylesheet" type="text/css" href="pmd/styles/<?php echo $GLOBALS['PMD']['STYLE'] ?>/style1.css">

<title>Designer</title>
</head>

<?php 
echo '
<script>
var db = "' . $db . '";
var token = "' . $token . '";
var LangSelectReferencedKey = "' . $strSelectReferencedKey . '";
var LangSelectForeignKey = "' . $strSelectForeignKey . '";
var LangPleaseSelectPrimaryOrUniqueKey = "' . $strPleaseSelectPrimaryOrUniqueKey . '";
var LangIEnotSupport = "' . $strIEUnsupported . '";
var LangChangeDisplay = "' . $strChangeDisplay . '";   

var strLang = Array();
strLang["strModifications"] = "' . $strModifications . '";
strLang["strRelationDeleted"] = "' . $strRelationDeleted . '";
strLang["strInnoDBRelationAdded"] = "' . $strInnoDBRelationAdded . '";
strLang["strGeneralRelationFeat:strDisabled"] = "' . $strGeneralRelationFeat . ' : ' . $strDisabled . '";
strLang["strInternalRelationAdded"] = "' . $strInternalRelationAdded . '";
strLang["strErrorRelationAdded"] = "' . $strErrorRelationAdded . '";   
strLang["strErrorRelationExists"] = "' . $strErrorRelationExists . '";   
strLang["strErrorSaveTable"] = "' . $strErrorSaveTable . '";                   
</script>';  
?>
<script language=javascript src="pmd/scripts/ajax.js" type=text/javascript></script>
<script language=javascript src="pmd/scripts/move.js" type=text/javascript></script>
<!--[if IE]><script type="text/javascript" src="pmd/scripts/iecanvas.js"></script><![endif]-->

<?php
echo $script_tabs . $script_contr . $script_display_field;
?>
<body onLoad="Main()" class="general_body" marginheight="0" marginwidth="0">
  <ul class="header" id="top_menu"><table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="10%" align="left"><a 
          href="javascript:Show_left_menu(document.getElementById('key_Show_left_menu'));" onMouseDown="return false;" class="M_butt" target="_self"><img id='key_Show_left_menu' title="<?php echo $strShowHideLeftMenu; ?>" alt="v" src="pmd/images/downarrow2_m.png" ></a></td>
        <td width="80%" align="center" nowrap><a
          href="javascript:Save2();" onMouseDown="return false;" class="M_butt" target="_self"><img  title="<?php echo $strSavePosition ?>" src="pmd/images/save.png"></a><a 
          href="javascript:Start_table_new();" onMouseDown="return false;" class="M_butt" target="_self"><img title="<?php echo $strCreateTable ?>"     src="pmd/images/table.png"></a><a 
          href="javascript:Start_relation();" onMouseDown="return false;" class="M_butt" id="rel_button" target="_self"><img title="<?php echo $strCreateRelation ?>"  src="pmd/images/relation.png"></a><a 
          href="javascript:Start_display_field();" onMouseDown="return false;" class="M_butt" id="display_field_button" target="_self"><img title="<?php echo $strChangeDisplay ?>"  src="pmd/images/display_field.png"></a><a 
          href="javascript:location.reload();" onMouseDown="return false;" class="M_butt" target="_self"><img title="<?php echo $strReload; ?>" src="pmd/images/reload.png"></a><a 
          href="javascript:Help();" onMouseDown="return false;" class="M_butt" target="_self"><img title="<?php echo $strHelp; ?>" src="pmd/images/help.png"></a><img class="M_bord" src="pmd/images/bord.png"><a 
          href="javascript:Angular_direct();" onMouseDown="return false;" class="M_butt" id="angular_direct_button" target="_self"><img title="<?php echo $strAngularLinks.' / '.$strDirectLinks; ?>"  src="pmd/images/ang_direct.png"></a><a 
          href="javascript:Grid();" onMouseDown="return false;" class="M_butt" id="grid_button" target="_self"><img title="<?php echo $strSnapToGrid ?>"  src="pmd/images/grid.png"></a><img class="M_bord" src="pmd/images/bord.png"><a 
          href="javascript:Small_tab_all(document.getElementById('key_SB_all'));" onMouseDown="return false;" class="M_butt" target="_self"><img id='key_SB_all' title="<?php echo $strSmallBigAll; ?>" alt="v" src="pmd/images/downarrow1.png" ></a><a 
          href="javascript:Small_tab_invert();" onMouseDown="return false;" class="M_butt" target="_self"><img title="<?php echo $strToggleSmallBig; ?>" alt="key" src="pmd/images/bottom.png" ></a><img class="M_bord" src="pmd/images/bord.png"><a
          href="javascript:PDF_save();" onMouseDown="return false;" class="M_butt" target="_self"><img src="pmd/images/pdf.png" alt="key" width="20" height="20" title="<?php echo $strImportExportCoords; ?>"></a></td>
        <td width="10%" align="right"><a
          href="javascript:Top_menu_right(document.getElementById('key_Left_Right'));" onMouseDown="return false;" class="M_butt" target="_self"><img src="pmd/images/2rightarrow_m.png" id="key_Left_Right" alt=">" title="<?php echo $strMoveMenu; ?>"></a></td>
      </tr>
    </table></ul>
  
<div id="osn_tab">
  <CANVAS id="canvas" width="100" height="100" onClick="Canvas_click(this)"></CANVAS>    
</div>

<form action="" method="post" name="form1">
<div id="layer_menu" style="left:0px; top:28px; width:150px; visibility:<?PHP echo $hidden ?>;  position:fixed; z-index:1000; background-color:#EAEEF0; border:#999999 solid 1px;">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td height="1px">

  <div align="center" style="padding-top:5px">
      <a 
  href="javascript:Hide_tab_all(document.getElementById('key_HS_all'));" onMouseDown="return false;" class="M_butt" target="_self"><img title="<?php echo $strHideShowAll; ?>" alt="v" src="pmd/images/downarrow1.png" id='key_HS_all' ></a><a 
  href="javascript:No_have_constr(document.getElementById('key_HS'));" onMouseDown="return false;" class="M_butt" target="_self"><img title="<?php echo $strHideShowNoRelation; ?>" alt="v" src="pmd/images/downarrow2.png" id='key_HS'></a>      
  </div>
  </td>
</tr>
<tr>
  <td>
    <div id="id_scroll_tab" class="scroll_tab">
        <table width="100%" style="padding-left:3px;">
          <?php
        for ( $i=0; $i < sizeof( $GLOBALS['PMD']['TABLE_NAME'] ); $i++ ) 
        {
        ?>
          <tr>
      <td title="<?php echo $strStructure; ?>" width="1px" onMouseOver="this.className='L_butt2_2'" onMouseOut="this.className='L_butt2_1'" ><img onClick="Start_tab_upd('<?php echo $GLOBALS['PMD_URL']["TABLE_NAME_SMALL"][$i]; ?>');" src="pmd/images/exec.png"></td>
            <td width="1px"><input onClick="VisibleTab(this,'<?php echo $GLOBALS['PMD_URL']["TABLE_NAME"][$i]; ?>')" title="<?php echo $strHide ?>" id="check_vis_<?php echo $GLOBALS['PMD_URL']["TABLE_NAME"][$i]; ?>" style="margin:0px;" type="checkbox" value="<?php echo $GLOBALS['PMD_URL']["TABLE_NAME"][$i]; ?>"  <?php if( isset($tab_pos[$GLOBALS['PMD']["TABLE_NAME"][$i]]) ) echo $tab_pos[$GLOBALS['PMD']["TABLE_NAME"][$i]]["H"]?"checked":""; else echo "checked"; ?>></td>
            <td class="Tabs" onMouseOver="this.className='Tabs2'" onMouseOut="this.className='Tabs'" onClick="Select_tab('<?php echo $GLOBALS['PMD_URL']["TABLE_NAME"][$i]; ?>');"><?php echo $GLOBALS['PMD_OUT']["TABLE_NAME"][$i]; ?></td>
          </tr>
          <?php 
        }
        ?>
        </table>
  </div>
  </td> 
</tr>
<tr>
  <td height="1px">
  <div align="center"><?php echo $strNumberOfTables ?>: <?php echo sizeof( $GLOBALS['PMD']['TABLE_NAME'] ) ?></div>
  <div align="right"><div style=" background-image:url(pmd/images/resize.png); cursor:nw-resize; width:16px; height:16px; " onMouseDown="layer_menu_cur_click=1" onMouseUp="layer_menu_cur_click=0"></div></div>
  </td>
</tr>
</table>
</div>
<?php
for ( $i=0; $i < sizeof( $GLOBALS['PMD']["TABLE_NAME"] ); $i++ ) 
{
  $t_n = $GLOBALS['PMD']["TABLE_NAME"][$i];
  $t_n_url = $GLOBALS['PMD_URL']["TABLE_NAME"][$i];
?>
<input name="t_x[<?php echo $t_n_url ?>]" type="hidden" id="t_x[<?php echo $t_n_url ?>]">
<input name="t_y[<?php echo $t_n_url ?>]" type="hidden" id="t_y[<?php echo $t_n_url ?>]">
<input name="t_v[<?php echo $t_n_url ?>]" type="hidden" id="t_v[<?php echo $t_n_url ?>]">
<input name="t_h[<?php echo $t_n_url ?>]" type="hidden" id="t_h[<?php echo $t_n_url ?>]">

<table id="<?php echo $t_n_url ?>" cellpadding="0" cellspacing="0" class="tab" 
       style="position:absolute; 
              left: <?php if( isset($tab_pos[$t_n]) ) echo $tab_pos[$t_n]["X"]; else echo rand(180,800); ?>; 
              top:  <?php if( isset($tab_pos[$t_n]) ) echo $tab_pos[$t_n]["Y"]; else echo rand(30,500); ?>;
              visibility:  <?php if( isset($tab_pos[$t_n]) ) echo $tab_pos[$t_n]["H"]?"visible":"hidden"; ?>;
             ">
  <tr>
    <td class="small_tab" onMouseOver="this.className='small_tab2';" onMouseOut="this.className='small_tab';" id="_|_hide_tbody_<?php echo $t_n_url ?>" onClick="Small_tab('<?php echo $t_n_url ?>',1)"><?php if( isset($tab_pos[$t_n]) ) echo $tab_pos[$t_n]["V"]?"v":">";else echo "v"; ?></td>
    <td class="small_tab_pref" onMouseOver="this.className='small_tab_pref2';" onMouseOut="this.className='small_tab_pref';" onClick="Start_tab_upd('<?php echo $GLOBALS['PMD_URL']["TABLE_NAME_SMALL"][$i]; ?>');"><img src="pmd/images/exec_small.png"></td>
    <td nowrap id="_|_zag_<?php echo $t_n_url ?>" class="tab_zag" onMouseDown="cur_click=document.getElementById('<?php echo $t_n_url ?>');"
    onMouseOver="this.className = 'tab_zag_2'" onMouseOut="this.className = 'tab_zag'"
    ><?php echo "<span class='owner'>".strtolower($GLOBALS['PMD_OUT']["OWNER"][$i]).".</span>".$GLOBALS['PMD_OUT']["TABLE_NAME_SMALL"][$i]; ?></td>
  </tr>
  <tbody id="_|_tbody_<?php echo $t_n_url ?>" 
         style="display:<?php if( isset($tab_pos[$t_n]) ) echo $tab_pos[$t_n]["V"]?"":"none"; ?>;">
  <?php
  $display_field = PMA_getDisplayField($db, $GLOBALS['PMD']["TABLE_NAME_SMALL"][$i]);
  for ( $j=0; $j < sizeof( $tab_column[$t_n]["COLUMN_ID"] ); $j++ ) 
  {
  ?>
    <tr id = "_|_tr_<?php echo $GLOBALS['PMD_URL']["TABLE_NAME_SMALL"][$i].'.'.urlencode($tab_column[$t_n]["COLUMN_NAME"][$j]) ?>"
    <?php if($display_field == $tab_column[$t_n]["COLUMN_NAME"][$j]) echo ' class="tab_field_3" '; else  echo ' class="tab_field" '; ?>
    onMouseOver="old_class = this.className; this.className = 'tab_field_2';" onMouseOut="this.className = old_class;" 
    onMouseDown="Click_field('<?php
        echo $GLOBALS['PMD_URL']["TABLE_NAME_SMALL"][$i]."','".urlencode($tab_column[$t_n]["COLUMN_NAME"][$j])."',";
        if ($GLOBALS['PMD']['TABLE_TYPE'][$i] != 'INNODB') {
            echo (isset( $tables_pk_or_unique_keys[ $t_n.".".$tab_column[$t_n]["COLUMN_NAME"][$j] ] ) ? 1 : 0);
        } else {
      // if this is an InnoDB table, it's not necessary that the
      // index is a primary key
            echo (isset( $tables_all_keys[ $t_n.".".$tab_column[$t_n]["COLUMN_NAME"][$j] ] ) ? 1 : 0);
        }
?>)">
        <td width="10px" colspan="3" id="<?php echo $t_n_url.".".urlencode($tab_column[$t_n]["COLUMN_NAME"][$j]) ?>"
        ><div style="white-space:nowrap">
      <?php 
      if(isset($tables_pk_or_unique_keys[$t_n.".".$tab_column[$t_n]["COLUMN_NAME"][$j]])) {
      ?>
      <img src="pmd/styles/<?php echo $GLOBALS['PMD']['STYLE']
      ?>/images/FieldKey_small.png" alt="*"><?php 
      } else {
      ?><img src="pmd/styles/<?php echo $GLOBALS['PMD']['STYLE']?>/images/Field_small<?php
        if(strstr($tab_column[$t_n]["TYPE"][$j],'char')
        || strstr($tab_column[$t_n]["TYPE"][$j],'text')  ) echo '_char';
        elseif(strstr($tab_column[$t_n]["TYPE"][$j],'int')
            || strstr($tab_column[$t_n]["TYPE"][$j],'float')
            || strstr($tab_column[$t_n]["TYPE"][$j],'double')
            || strstr($tab_column[$t_n]["TYPE"][$j],'decimal') ) echo '_int';
        elseif(strstr($tab_column[$t_n]["TYPE"][$j],'date')
            || strstr($tab_column[$t_n]["TYPE"][$j],'time')
            || strstr($tab_column[$t_n]["TYPE"][$j],'year') ) echo '_date';
       ?>.png" alt="*"><?php } ?> 
      <?php echo htmlspecialchars($tab_column[$t_n]["COLUMN_NAME"][$j]." : ".$tab_column[$t_n]["TYPE"][$j], ENT_QUOTES); ?>
        </div>
       </td>
    </tr>
    <?php
  }
  ?>
  </tbody>
</table>
<?php
}
?>
</form>
<div id="hint"></div>
<div id='layer_action' style="position:absolute; left:638px; top:52px; z-index:1000; 
                              visibility:<?PHP echo $hidden ?>; background-color:#CCFF99; padding:3px; border:#009933 solid 1px; white-space:nowrap; font-weight:bold">Load...</div>

  <table id="layer_new_relation" style="visibility:<?PHP echo $hidden ?>; position:absolute; left:636px; top:85px; z-index:1000; width: 153px;" width="5%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td id="frams1" width="10px"></td>
      <td width="99%" id="frams5"></td>
      <td width="10px" id="frams2"><div class="bor"></div></td>
    </tr>
    <tr>
      <td id="frams8"></td>
      <td class="input_tab"><table width="168" border="0" align="center" cellpadding="2" cellspacing="0">
          <tr>
      <td colspan="2" align="center" nowrap><b><?php echo $strCreateRelation; ?></b></td>
          </tr>
          <tbody id="InnoDB_relation">
          <tr>
            <td colspan="2" align="center" nowrap><b>InnoDB</b></td>
            </tr>
          <tr>
            <td width="58" nowrap>on delete</td>
            <td width="102"><select name="on_delete" id="on_delete">
                <option value="nix" selected="selected">--</option>
                <option value="CASCADE">CASCADE</option>
                <option value="SET NULL">SET NULL</option>
                <option value="NO ACTION">NO ACTION</option>
                <option value="RESTRICT">RESTRICT</option>
              </select>            </td>
          </tr>
          <tr>
            <td nowrap>on update</td>
            <td><select name="on_update" id="on_update">
                <option value="nix" selected="selected">--</option>
                <option value="CASCADE">CASCADE</option>
                <option value="SET NULL">SET NULL</option>
                <option value="NO ACTION">NO ACTION</option>
                <option value="RESTRICT">RESTRICT</option>
              </select>            </td>
          </tr>
          </tbody>
          <tr>
            <td colspan="2" align="center" nowrap>
        <input type="button" id="butt" name="Button" value="<?php echo $strOK; ?>" onClick="New_relation()" > 
          <input type="button" id="butt" name="Button" value="<?php echo $strCancel; ?>" onClick="document.getElementById('layer_new_relation').style.visibility = 'hidden';">
            </td>
          </tr>
      </table></td>
      <td id="frams6"></td>
    </tr>
    <tr>
      <td id="frams4"><div class="bor"></div></td>
      <td id="frams7"></td>
      <td id="frams3"></td>
    </tr>
</table>
  <table id="layer_upd_relation" style="visibility:<?PHP echo $hidden ?>; position:absolute; left:637px; top:224px; z-index:1000;" width="5%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td id="frams1" width="10px"></td>
      <td width="99%" id="frams5"></td>
      <td width="10px" id="frams2"><div class="bor"></div></td>
    </tr>
    <tr>
      <td id="frams8"></td>
      <td class="input_tab"><table width="100%" border="0" align="center" cellpadding="2" cellspacing="0">
          <tr>
      <td colspan="3" align="center" nowrap><b><?php echo $strDeleteRelation; ?></b></td>
          </tr>
          <tr>
            <td colspan="3" align="center" nowrap>
        <input name="Button" type="button" id="butt" onClick="Upd_relation()" value="<?php echo $strDelete; ?>" >
          <input type="button" id="butt" name="Button" value="<?php echo $strCancel; ?>" onClick="document.getElementById('layer_upd_relation').style.visibility = 'hidden'; Re_load();">
            </td>
          </tr>
      </table></td>
      <td id="frams6"></td>
    </tr>
    <tr>
      <td id="frams4"><div class="bor"></div></td>
      <td id="frams7"></td>
      <td id="frams3"></td>
    </tr>
</table>
<!-- cache images -->
<img src="pmd/images/2leftarrow_m.png" width="0" height="0">
<img src="pmd/images/rightarrow1.png" width="0" height="0">
<img src="pmd/images/rightarrow2.png" width="0" height="0">
<img src="pmd/images/uparrow2_m.png" width="0" height="0">
</body>
</html>
