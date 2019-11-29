<?php
/**
 * Разработано в комании Prodvigaeff.ru
 * Date: 06.03.2017
 * Time: 10:20
 * @copyright Prodvigaeff.ru <info@prodvigaeff.ru>
 * @author Большагин Вячеслав <gbublik@gmail.com>
 * @version 1.0
 */
if(!check_bitrix_sessid()) return;

echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>

