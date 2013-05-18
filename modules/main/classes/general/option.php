<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

global $MAIN_OPTIONS;
$MAIN_OPTIONS = array();
class CAllOption
{
	public static function err_mess()
	{
		return "<br>Class: CAllOption<br>File: ".__FILE__;
	}

	
	/**
	 * <p>Возвращает строковое значение параметра <i>option_id</i>, принадлежащего модулю <i>module_id</i>. Если не установлен параметр <i>site_id</i> то делается попытка найти числовой параметр <i>option_id</i>, принадлежащий модулю <i>module_id</i> для текущего сайта. Если такого параметра нет, возвращается параметр, общий для всех сайтов.</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $option_id  Идентификатор параметра.
	 *
	 *
	 *
	 * @param mixed $default_value = false Значение по умолчанию.<br>Если <i>default_value</i> не задан, то значение для
	 * <i>default_value</i> будет браться из массива с именем ${<i>module_id</i>."_default_option"}
	 * заданного в файле <b>/bitrix/modules/</b><i>module_id</i><b>/default_option.php</b>.
	 *
	 *
	 *
	 * @param string $site_id = false Идентификатор сайта для которого будут возвращены параметры.
	 * Необязательный. По умолчанию - false (для текущего сайта или если не
	 * установлены то общие для всех сайтов)
	 *
	 *
	 *
	 * @return string 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * // получим поле "При регистрации добавлять в группу" 
	 * // из настроек главного модуля
	 * $default_group = <b>COption::GetOptionString</b>("main", "new_user_registration_def_group", "2");
	 * if($default_group!="")
	 *     $arrGroups = explode(",",$default_group);
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89621]Параметры модуля[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/settings.php">Настройки главного модуля</a> </li> <li>
	 * <a href="http://dev.1c-bitrix.ruapi_help/main/reference/coption/getoptionint.php">COption::GetOptionInt</a> </li>
	 * </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/coption/getoptionstring.php
	 * @author Bitrix
	 */
	public static function GetOptionString($module_id, $name, $def="", $site=false, $bExactSite=false)
	{
		global $DB, $MAIN_OPTIONS;

		if($site===false)
			$site = SITE_ID;

		if($site == "")
			$site_id = '-';
		else
			$site_id = $site;

		if(CACHED_b_option===false)
		{
			if(!isset($MAIN_OPTIONS[$site_id][$module_id]))
			{
				$MAIN_OPTIONS[$site_id][$module_id] = array();
				$res = $DB->Query(
					"SELECT SITE_ID, NAME, VALUE ".
					"FROM b_option ".
					"WHERE (SITE_ID='".$DB->ForSql($site, 2)."' OR SITE_ID IS NULL)".
					"	AND MODULE_ID='".$DB->ForSql($module_id)."'"
				);

				while($ar = $res->Fetch())
					$MAIN_OPTIONS[strlen($ar["SITE_ID"])>0?$ar["SITE_ID"]:"-"][$module_id][$ar["NAME"]]=$ar["VALUE"];
			}
		}
		else
		{
			if(empty($MAIN_OPTIONS))
			{
				global $CACHE_MANAGER;
				if($CACHE_MANAGER->Read(CACHED_b_option, "b_option"))
				{
					$MAIN_OPTIONS = $CACHE_MANAGER->Get("b_option");
				}
				else
				{
					$res = $DB->Query("SELECT o.SITE_ID, o.MODULE_ID, o.NAME, o.VALUE FROM b_option o");
					while($ar = $res->Fetch())
						$MAIN_OPTIONS[strlen($ar["SITE_ID"])>0?$ar["SITE_ID"]:"-"][$ar["MODULE_ID"]][$ar["NAME"]]=$ar["VALUE"];
					$CACHE_MANAGER->Set("b_option", $MAIN_OPTIONS);
				}
			}
		}

		if(isset($MAIN_OPTIONS[$site_id][$module_id][$name]))
			return $MAIN_OPTIONS[$site_id][$module_id][$name];

		if($bExactSite && !isset($MAIN_OPTIONS[$site_id][$module_id][$name]))
			return false;

		if($site_id != "-" && isset($MAIN_OPTIONS["-"][$module_id][$name]))
			return $MAIN_OPTIONS["-"][$module_id][$name];

		if($def == "")
		{
			$module_id = _normalizePath($module_id);
			if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module_id."/default_option.php"))
			{
				$var = str_replace(".", "_", $module_id)."_default_option";
				global $$var;
				include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/".$module_id."/default_option.php");
				$arrDefault = $$var;
				if(is_array($arrDefault))
					return $arrDefault[$name];
			}
		}

		return $def;
	}

	
	/**
	 * <p>Устанавливает строковое значение параметра <i>option_id</i> для модуля <i>module_id</i>. Если указан <i>site_id</i>, параметр установится только для этого сайта и не будет влиять на аналогичный параметр другого сайта. Возвращает <i>true</i>, если операция прошла успешна, в противном случае - <i>false</i>.</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $option_id  Идентификатор параметра.
	 *
	 *
	 *
	 * @param string $value = "" Значение параметра.<br>Необязательный. По умолчанию - "".
	 *
	 *
	 *
	 * @param mixed $description = false Описание параметра.<br>Необязательный. По умолчанию - "false"
	 * (описание отсутствует).
	 *
	 *
	 *
	 * @param string $site_id = false Идентификатор сайта, для которого устанавливается параметр.
	 * Необязательный. По умолчанию - <i>false</i> (общий для всех сайтов
	 * параметр).
	 *
	 *
	 *
	 * @return bool 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * // установим значение для поля 
	 * // "E-Mail администратора сайта (отправитель по умолчанию)" 
	 * // из настроек главного модуля
	 * <b>COption::SetOptionString</b>("main","email_from","admin@site.com");
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89621]Параметры модуля[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/settings.php">Настройки главного модуля</a> </li> <li>
	 * <a href="http://dev.1c-bitrix.ruapi_help/main/reference/coption/setoptionint.php">COption::SetOptionInt</a> </li>
	 * </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/coption/setoptionstring.php
	 * @author Bitrix
	 */
	public static function SetOptionString($module_id, $name, $value="", $desc=false, $site="")
	{
		global $DB,$CACHE_MANAGER;
		if(CACHED_b_option!==false) $CACHE_MANAGER->Clean("b_option");

		if($site === false)
			$site = SITE_ID;

		$strSqlWhere = " SITE_ID".($site==""?" IS NULL":"='".$DB->ForSql($site, 2)."'")." ";

		$name = $DB->ForSql($name, 50);
		$res = $DB->Query(
			"SELECT 'x' ".
			"FROM b_option ".
			"WHERE ".$strSqlWhere.
			"	AND MODULE_ID='".$DB->ForSql($module_id)."' ".
			"	AND NAME='".$name."'"
			);

		if($res_array = $res->Fetch())
		{
			$DB->Query(
				"UPDATE b_option SET ".
				"	VALUE='".$DB->ForSql($value, 2000)."'".
				($desc!==false?", DESCRIPTION='".$DB->ForSql($desc, 255)."'":"")." ".
				"WHERE ".$strSqlWhere.
				"	AND MODULE_ID='".$DB->ForSql($module_id)."' ".
				"	AND NAME='".$name."'"
				);
		}
		else
		{
			$DB->Query(
				"INSERT INTO b_option(SITE_ID, MODULE_ID, NAME, VALUE, DESCRIPTION) ".
				"VALUES(".($site==""?"NULL":"'".$DB->ForSQL($site, 2)."'").", ".
				"'".$DB->ForSql($module_id, 50)."', '".$name."', ".
				"'".$DB->ForSql($value, 2000)."', '".$DB->ForSql($desc, 255)."') "
				);
		}

		if($site == "")
			$site = '-';

		global $MAIN_OPTIONS;
		$MAIN_OPTIONS[$site][$module_id][$name] = $value;

		$module_id = _normalizePath($module_id);
		$fname = $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$module_id.'/option_triggers.php';
		if(file_exists($fname))
			include_once($fname);

		$events = GetModuleEvents("main", "OnAfterSetOption_".$name);
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($value));

		return true;
	}

	
	/**
	 * <p>Удаляет значение одного (<i>option_id</i>) или всех параметров модуля <i>module_id</i> из базы. Если не установлен параметр <i>site_id</i> то делается попытка найти числовой параметр <i>option_id</i>, принадлежащий модулю <i>module_id</i> для текущего сайта. Если такого параметра нет, возвращается параметр, общий для всех сайтов.</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $option_id = "" Идентификатор параметра.<br>Необязательный. По умолчанию - ""
	 * (удалить все значения параметров модуля).
	 *
	 *
	 *
	 * @param string $site_id = false Идентификатор сайта для которого будут возвращены параметры.
	 * Необязательный. По умолчанию - false (для текущего сайта или если не
	 * установлены то общие для всех сайтов)
	 *
	 *
	 *
	 * @return bool 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * // удалим значение параметра "Количество результатов на одной странице" 
	 * // для модуля "Веб-формы" из базы
	 * <b>COption::RemoveOption</b>("form", "RESULTS_PAGEN");
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89621]Параметры модуля[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/settings.php">Настройки главного модуля</a> </li>
	 * </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/coption/removeoption.php
	 * @author Bitrix
	 */
	public static function RemoveOption($module_id, $name="", $site=false)
	{
		global $MAIN_OPTIONS, $DB, $CACHE_MANAGER;

		if ($module_id == "main")
			$DB->Query("
				DELETE FROM b_option
				WHERE MODULE_ID = 'main'
					AND NAME NOT LIKE '~%'
					AND NAME NOT IN ('crc_code', 'admin_passwordh', 'server_uniq_id','PARAM_MAX_SITES', 'PARAM_MAX_USERS')
					".(strlen($name) > 0? " AND NAME = '".$DB->forSql($name)."' ": "")."
					".(strlen($site) > 0? " AND SITE_ID = '".$DB->forSql($site)."' ": "")."
			");
		else
			$DB->Query("
				DELETE FROM b_option
				WHERE MODULE_ID = '".$DB->ForSql($module_id)."'
					AND NAME NOT IN ('~bsm_stop_date')
					".(strlen($name) > 0? " AND NAME = '".$DB->forSql($name)."' ": "")."
					".(strlen($site) > 0? " AND SITE_ID = '".$DB->forSql($site)."' ": "")."
			");

		if ($site === false)
		{
			foreach ($MAIN_OPTIONS as $site => $temp)
			{
				if ($name == "")
					unset($MAIN_OPTIONS[$site][$module_id]);
				else
					unset($MAIN_OPTIONS[$site][$module_id][$name]);
			}
		}
		else
		{
			if ($name == "")
				unset($MAIN_OPTIONS[$site][$module_id]);
			else
				unset($MAIN_OPTIONS[$site][$module_id][$name]);
		}

		if (CACHED_b_option !== false)
			$CACHE_MANAGER->clean("b_option");
	}

	
	/**
	 * <p>Возвращает числовое значение параметра <i>option_id</i>, принадлежащего модулю <i>module_id</i>. Если не установлен параметр <i>site_id</i> то делается попытка найти числовой параметр <i>option_id</i>, принадлежащий модулю <i>module_id</i> для текущего сайта. Если такого параметра нет, возвращается параметр, общий для всех сайтов.</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $option_id  Идентификатор параметра.
	 *
	 *
	 *
	 * @param mixed $default_value = false Значение по умолчанию.<br>Если <i>default_value</i> не задан, то значение для
	 * <i>default_value</i> будет браться из массива с именем ${<i>module_id</i>."_default_option"}
	 * заданного в файле <b>/bitrix/modules/</b><i>module_id</i><b>/default_option.php</b>.
	 *
	 *
	 *
	 * @param string $site_id = false Идентификатор сайта для которого будут возвращены параметры.
	 * Необязательный. По умолчанию - false (для текущего сайта или если не
	 * установлены то общие для всех сайтов)
	 *
	 *
	 *
	 * @return int 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * // получим поле "Ответственный по умолчанию" 
	 * // из настроек модуля "Техподдержка"
	 * $RESPONSIBLE_USER_ID = <b>COption::GetOptionInt</b>("support", "DEFAULT_RESPONSIBLE_ID");
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89621]Параметры модуля[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/settings.php">Настройки главного модуля</a> </li> <li>
	 * <a href="http://dev.1c-bitrix.ruapi_help/main/reference/coption/getoptionstring.php">COption::GetOptionString</a> </li>
	 * </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/coption/getoptionint.php
	 * @author Bitrix
	 */
	public static function GetOptionInt($module_id, $name, $def="", $site=false)
	{
		return COption::GetOptionString($module_id, $name, $def, $site);
	}

	
	/**
	 * <p>Устанавливает числовое значение параметра <i>option_id</i> для модуля <i>module_id</i>. Если указан <i>site_id</i>, параметр установится только для этого сайта и не будет влиять на аналогичный параметр другого сайта. Возвращает "true", если операция прошла успешна, в противном случае - "false".</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $option_id  Идентификатор параметра.
	 *
	 *
	 *
	 * @param mixed $value = "" Значение параметра.<br>Необязательный. По умолчанию - "".
	 *
	 *
	 *
	 * @param mixed $description = false Описание параметра.<br>Необязательный. По умолчанию - "false"
	 * (описание отсутствует).
	 *
	 *
	 *
	 * @param string $site_id = false Идентификатор сайта, для которого устанавливается параметр.
	 * Необязательный. По умолчанию - false (общий для всех сайтов
	 * параметр).
	 *
	 *
	 *
	 * @return bool 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * // установим значение для поля 
	 * // "Количество дополнительных параметров меню" 
	 * // из настроек модуля "Управление структурой сайта"
	 * <b>COption::SetOptionInt</b>("fileman", "num_menu_param", 2);
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89621]Параметры модуля[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/settings.php">Настройки главного модуля</a> </li> <li>
	 * <a href="http://dev.1c-bitrix.ruapi_help/main/reference/coption/setoptionstring.php">COption::SetOptionString</a> </li>
	 * </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/coption/setoptionint.php
	 * @author Bitrix
	 */
	public static function SetOptionInt($module_id, $name, $value="", $desc="", $site="")
	{
		return COption::SetOptionString($module_id, $name, IntVal($value), $desc, $site);
	}
}

global $MAIN_PAGE_OPTIONS;
$MAIN_PAGE_OPTIONS = array();
class CAllPageOption
{
	
	/**
	 * <p>Возвращает строковое значение параметра <i>page_option_id</i>, принадлежащего модулю <i>module_id</i>.</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $page_option_id  Произвольный идентификатор параметра страницы.
	 *
	 *
	 *
	 * @param mixed $default_value = false Значение по умолчанию.
	 *
	 *
	 *
	 * @return string 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * $my_parameter = <b>CPageOption::GetOptionString</b>("main", "MY_PARAMETER", "Y");
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89636]Параметры страницы[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/reference/cpageoption/getoptionint.php">CPageOption::GetOptionInt</a> </li>
	 * </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/cpageoption/getoptionstring.php
	 * @author Bitrix
	 */
	public static function GetOptionString($module_id, $name, $def="", $site=false)
	{
		global $MAIN_PAGE_OPTIONS;

		if($site===false)
			$site = SITE_ID;

		if(isset($MAIN_PAGE_OPTIONS[$site][$module_id][$name]))
			return $MAIN_PAGE_OPTIONS[$site][$module_id][$name];
		elseif(isset($MAIN_PAGE_OPTIONS["-"][$module_id][$name]))
			return $MAIN_PAGE_OPTIONS["-"][$module_id][$name];
		return $def;
	}

	
	/**
	 * <p>Устанавливает строковое значение параметра <i>page_option_id</i> для модуля <i>module_id</i>. Возвращает "true", если операция прошла успешна, в противном случае - "false".</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $page_option_id  Произвольный идентификатор параметра страницы.
	 *
	 *
	 *
	 * @param string $value = "" Значение параметра.<br>Необязательный. По умолчанию - "".
	 *
	 *
	 *
	 * @return bool 
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89636]Параметры страницы[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/reference/cpageoption/setoptionint.php">CPageOption::SetOptionInt</a> </li>
	 * </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/cpageoption/setoptionstring.php
	 * @author Bitrix
	 */
	public static function SetOptionString($module_id, $name, $value="", $desc=false, $site="")
	{
		global $MAIN_PAGE_OPTIONS;

		if($site===false)
			$site = SITE_ID;
		if(strlen($site)<=0)
			$site = "-";

		$MAIN_PAGE_OPTIONS[$site][$module_id][$name] = $value;
		return true;
	}

	
	/**
	 * <p>Удаляет значение одного (<i>page_option_id</i>) или всех параметров модуля <i>module_id</i> для данной страницы.</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $page_option_id = "" Произвольный идентификатор параметра
	 * страницы.<br>Необязательный. По умолчанию - "" (удалить все значения
	 * параметров страницы для модуля <i>module_id</i>).
	 *
	 *
	 *
	 * @return bool 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * // удалим значение параметра MY_PARAMETER для текущей страницы
	 * <b>CPageOption::RemoveOption</b>("main", "MY_PARAMETER");
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul><li>[link=89636]Параметры страницы[/link] </li></ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/cpageoption/removeoption.php
	 * @author Bitrix
	 */
	public static function RemoveOption($module_id, $name="", $site=false)
	{
		global $MAIN_PAGE_OPTIONS;

		if ($site === false)
		{
			foreach ($MAIN_PAGE_OPTIONS as $site => $temp)
			{
				if ($name == "")
					unset($MAIN_PAGE_OPTIONS[$site][$module_id]);
				else
					unset($MAIN_PAGE_OPTIONS[$site][$module_id][$name]);
			}
		}
		else
		{
			if ($name == "")
				unset($MAIN_PAGE_OPTIONS[$site][$module_id]);
			else
				unset($MAIN_PAGE_OPTIONS[$site][$module_id][$name]);
		}
	}

	
	/**
	 * <p>Возвращает числовое значение параметра <i>page_option_id</i>, принадлежащего модулю <i>module_id</i>.</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $page_option_id  Произвольный идентификатор параметра страницы.
	 *
	 *
	 *
	 * @param mixed $default_value = false Значение по умолчанию.
	 *
	 *
	 *
	 * @return int 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * $my_parameter = <b>CPageOption::GetOptionInt</b>("main", "MY_PARAMETER", 21);
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89636]Параметры страницы[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/reference/cpageoption/getoptionstring.php">CPageOption::GetOptionString</a>
	 * </li> </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/cpageoption/getoptionint.php
	 * @author Bitrix
	 */
	public static function GetOptionInt($module_id, $name, $def="", $site=false)
	{
		return CPageOption::GetOptionString($module_id, $name, $def, $site);
	}

	
	/**
	 * <p>Устанавливает числовое значение параметра <i>page_option_id</i> для модуля <i>module_id</i>. Возвращает "true", если операция прошла успешна, в противном случае - "false".</p>
	 *
	 *
	 *
	 *
	 * @param string $module_id  <a href="http://dev.1c-bitrix.ruapi_help/main/general/identifiers.php">Идентификатор модуля</a>.
	 *
	 *
	 *
	 * @param string $page_option_id  Произвольный идентификатор параметра страницы.
	 *
	 *
	 *
	 * @param mixed $value = "" Значение параметра.<br>Необязательный. По умолчанию - "".
	 *
	 *
	 *
	 * @return bool 
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * <b>CPageOption::SetOptionInt</b>("main", "MY_PARAMETER", 2);
	 * ?&gt;
	 * </pre>
	 *
	 *
	 *
	 * <h4>See Also</h4> 
	 * <ul> <li>[link=89636]Параметры страницы[/link] </li> <li> <a
	 * href="http://dev.1c-bitrix.ruapi_help/main/reference/cpageoption/setoptionstring.php">CPageOption::SetOptionString</a>
	 * </li> </ul><a name="examples"></a>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/main/reference/cpageoption/setoptionint.php
	 * @author Bitrix
	 */
	public static function SetOptionInt($module_id, $name, $value="", $desc="", $site="")
	{
		return CPageOption::SetOptionString($module_id, $name, IntVal($value), $desc, $site);
	}
}
?>