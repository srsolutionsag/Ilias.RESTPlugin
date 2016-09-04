/**
 *
 */
protected static function GetAllowedRoles($refId) {
  // Fetch current user object, eg. (local) admin
  global $ilUser;

  // Fetch assignanle roles
  $objectRoles    = $rbacreview->getRoleListByObject(self::ROLE_FOLDER_ID);
  $assignedRoles  = $rbacreview->assignedRoles($ilUser->getId());

  // Extract assignable roles in refID object
  $allowedRoles   = array();
  foreach ($objectRoles as $role) {
    // Extract role-data
    $roleId = $role['obj_id'];

    // Creating a local user as non-admin?
    if ($refId != self::USER_FOLDER_ID && !in_array(self::SYSTEM_ROLE_ID, $assignedRoles)) {
      // Filter out roles not assigned to user
      include_once './Services/AccessControl/classes/class.ilObjRole.php';
      if(!ilObjRole::_getAssignUsersStatus($roleId))
        continue;
    }

    // Skip anonymous role and admin-role for non-admin accounts
    if ($roleId != self::ANONYMOUS_ROLE_ID && ($roleId != self::SYSTEM_ROLE_ID || in_array(self::SYSTEM_ROLE_ID, $assignedRoles)))
      array_push($allowedRoles, $roleId);
  }

  // No assignable role
  if (count($allowedRoles) == 0)
    throw new LibExceptions\RBAC(
      self::MSG_NO_GLOBAL_ROLE,
      self::ID_NO_GLOBAL_ROLE
    );

  // Return list of allowed roles
  return $allowedRoles;


  // Alternative
  /*
  if ($_POST["role_assign"])
  {
    $global_roles = $rbacreview->getGlobalRoles();
    $roles_of_user = $rbacreview->assignedRoles($ilUser->getId());
    foreach ($_POST["role_assign"] as $role_id)
    {
      if ($role_id != "")
      {
        if (in_array($role_id, $global_roles))
        {
          if(!in_array(SYSTEM_ROLE_ID,$roles_of_user))
          {
            if ($role_id == SYSTEM_ROLE_ID && ! in_array(SYSTEM_ROLE_ID,$roles_of_user)
            || ($this->object->getRefId() != USER_FOLDER_ID
              && ! ilObjRole::_getAssignUsersStatus($role_id))
            )
            {
              ilUtil::delDir($import_dir);
              $this->ilias->raiseError($this->lng->txt("usrimport_with_specified_role_not_permitted"),
                $this->ilias->error_obj->MESSAGE);
            }
          }
        }
        else
        {
          $rolf = $rbacreview->getFoldersAssignedToRole($role_id,true);
          if ($rbacreview->isDeleted($rolf[0])
            || ! $rbacsystem->checkAccess('write',$rolf[0]))
          {
            ilUtil::delDir($import_dir);
            $this->ilias->raiseError($this->lng->txt("usrimport_with_specified_role_not_permitted"),
              $this->ilias->error_obj->MESSAGE);
            return;
          }
        }
      }
    }
  }
  */
}



// DoIt: Use this for validation
public static function _MISC_VALIDATION_() {
  // List of allowed auth-modes
  $allowedModes = ilAuthUtils::_getActiveAuthModes();
  $allowedModes = array_keys($allowedModes);


  //
  include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
  if (ilAuthUtils::_isExternalAccountEnabled()) {
    // Check !!!
  }


  //
  require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
  if (ilDiskQuotaActivationChecker::_isActive()) {
    // Check !!!
  }
  if (ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive()) {
    // Check !!!
  }


  //
  include_once './Services/User/classes/class.ilUserDefinedFields.php';
  $udf = ilUserDefinedFields::_getInstance();
  if ($refId == USER_FOLDER_ID)
    $allowedDefinitions = $udf->getDefinitions();
  else
    $allowedDefinitions = $udf->getChangeableLocalUserAdministrationDefinitions();


  //
  $allowedLanguages = $lng->getInstalledLanguages();
  $defaultLanguage  = $ilSetting->get("language");


  $templates = $styleDefinition->getAllTemplates();
  include_once("./Services/Style/classes/class.ilObjStyleSettings.php");
  $options = array();
  if (count($templates) > 0 && is_array ($templates))
    foreach ($templates as $template) {
      $styleDef =& new ilStyleDefinition($template["id"]);
      $styleDef->startParsing();
      $styles = $styleDef->getStyles();
      foreach ($styles as $style)
      {
        if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
        {
          continue;
        }
        $options[$template["id"].":".$style["id"]] =
          $styleDef->getTemplateName()." / ".$style["name"];
      }
    }
}

// TextArea
function checkInput()
{
  global $lng;
  include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");

  if($this->usePurifier() && $this->getPurifier())
  {
    $_POST[$this->getPostVar()] = ilUtil::stripOnlySlashes($_POST[$this->getPostVar()]);
      $_POST[$this->getPostVar()] = $this->getPurifier()->purify($_POST[$this->getPostVar()]);
  }
  else
  {
    $allowed = $this->getRteTagString();
    if ($this->plugins["latex"] == "latex" && !is_int(strpos($allowed, "<span>")))
    {
      $allowed.= "<span>";
    }
    $_POST[$this->getPostVar()] = ($this->getUseRte() || !$this->getUseTagsForRteOnly())
      ? ilUtil::stripSlashes($_POST[$this->getPostVar()], true, $allowed)
      : ilUtil::stripSlashes($_POST[$this->getPostVar()]);
  }

  if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
  {
    $this->setAlert($lng->txt("msg_input_is_required"));

    return false;
  }
  return $this->checkSubItemsInput();
}



// TextInput
function checkInput()
{
  global $lng;

  if(!$this->getMulti())
  {
    $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
    if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
    {
      $this->setAlert($lng->txt("msg_input_is_required"));

      return false;
    }
    else if (strlen($this->getValidationRegexp()))
    {
      if (!preg_match($this->getValidationRegexp(), $_POST[$this->getPostVar()]))
      {
        $this->setAlert(
          $this->getValidationFailureMessage() ?
          $this->getValidationFailureMessage() :
          $lng->txt('msg_wrong_format')
        );
        return FALSE;
      }
    }
  }
  else
  {
    foreach($_POST[$this->getPostVar()] as $idx => $value)
    {
      $_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
    }
    $_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);

    if ($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()])))
    {
      $this->setAlert($lng->txt("msg_input_is_required"));

      return false;
    }
    else if (strlen($this->getValidationRegexp()))
    {
      $reg_valid = true;
      foreach($_POST[$this->getPostVar()] as $value)
      {
        if (!preg_match($this->getValidationRegexp(), $value))
        {
          $reg_valid = false;
          break;
        }
      }
      if(!$reg_valid)
      {
        $this->setAlert(
          $this->getValidationFailureMessage() ?
          $this->getValidationFailureMessage() :
          $lng->txt('msg_wrong_format')
        );
        return false;
      }
    }
  }

  return $this->checkSubItemsInput();
}


// Email input
function checkInput()
{
  global $lng;

  $_POST[$this->getPostVar()]             = ilUtil::stripSlashes($_POST[$this->getPostVar()], !(bool)$this->allowRFC822);
  $_POST[$this->getPostVar() . '_retype'] = ilUtil::stripSlashes($_POST[$this->getPostVar() . '_retype'], !(bool)$this->allowRFC822);
  if($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
  {
    $this->setAlert($lng->txt("msg_input_is_required"));

    return false;
  }
  if($this->getRetype() && ($_POST[$this->getPostVar()] != $_POST[$this->getPostVar() . '_retype']))
  {
    $this->setAlert($lng->txt('email_not_match'));

    return false;
  }
  if(!ilUtil::is_email($_POST[$this->getPostVar()]) &&
    trim($_POST[$this->getPostVar()]) != ""
  )
  {
    $this->setAlert($lng->txt("email_not_valid"));

    return false;
  }


  return true;
}

// Birthday input
function checkInput()
{
  global $lng,$ilUser;

  $ok = true;

  $_POST[$this->getPostVar()]["date"]["y"] =
    ilUtil::stripSlashes($_POST[$this->getPostVar()]["date"]["y"]);
  $_POST[$this->getPostVar()]["date"]["m"] =
    ilUtil::stripSlashes($_POST[$this->getPostVar()]["date"]["m"]);
  $_POST[$this->getPostVar()]["date"]["d"] =
    ilUtil::stripSlashes($_POST[$this->getPostVar()]["date"]["d"]);
  $_POST[$this->getPostVar()]["time"]["h"] =
    ilUtil::stripSlashes($_POST[$this->getPostVar()]["time"]["h"]);
  $_POST[$this->getPostVar()]["time"]["m"] =
    ilUtil::stripSlashes($_POST[$this->getPostVar()]["time"]["m"]);
  $_POST[$this->getPostVar()]["time"]["s"] =
    ilUtil::stripSlashes($_POST[$this->getPostVar()]["time"]["s"]);

  // verify date

  $dt['year'] = (int) $_POST[$this->getPostVar()]['date']['y'];
  $dt['mon'] = (int) $_POST[$this->getPostVar()]['date']['m'];
  $dt['mday'] = (int) $_POST[$this->getPostVar()]['date']['d'];
  $dt['hours'] = (int) $_POST[$this->getPostVar()]['time']['h'];
  $dt['minutes'] = (int) $_POST[$this->getPostVar()]['time']['m'];
  $dt['seconds'] = (int) $_POST[$this->getPostVar()]['time']['s'];
  if ($dt['year'] == 0 && $dt['mon'] == 0 && $dt['mday'] == 0 && $this->getRequired())
  {
    $this->date = null;
    $this->setAlert($lng->txt("msg_input_is_required"));
    return false;
  }
  else if ($dt['year'] == 0 && $dt['mon'] == 0 && $dt['mday'] == 0)
  {
    $this->date = null;
    $_POST[$this->getPostVar()]['date'] = ""; // #10413
  }
  else
  {
    if (!checkdate((int)$dt['mon'], (int)$dt['mday'], (int)$dt['year']))
    {
      $this->date = null;
      $this->setAlert($lng->txt("exc_date_not_valid"));
      return false;
    }
    $date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
    $_POST[$this->getPostVar()]['date'] = $date->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
    $this->setDate($date);
  }
  return true;
}

// Date input
function checkInput()
{
  global $ilUser, $lng;

  if ($this->getDisabled())
  {
    return true;
  }

  $post = $_POST[$this->getPostVar()];

  // empty date valid with input field
  if(!$this->getRequired() && $this->getMode() == self::MODE_INPUT && $post["date"] == "")
  {
    return true;
  }

  if($this->getMode() == self::MODE_SELECT)
  {
    $post["date"]["y"] = ilUtil::stripSlashes($post["date"]["y"]);
    $post["date"]["m"] = ilUtil::stripSlashes($post["date"]["m"]);
    $post["date"]["d"] = ilUtil::stripSlashes($post["date"]["d"]);
    $dt['year'] = (int) $post['date']['y'];
    $dt['mon'] = (int) $post['date']['m'];
    $dt['mday'] = (int) $post['date']['d'];

    if($this->getShowTime())
    {
      $post["time"]["h"] = ilUtil::stripSlashes($post["time"]["h"]);
      $post["time"]["m"] = ilUtil::stripSlashes($post["time"]["m"]);
      $post["time"]["s"] = ilUtil::stripSlashes($post["time"]["s"]);
      $dt['hours'] = (int) $post['time']['h'];
      $dt['minutes'] = (int) $post['time']['m'];
      $dt['seconds'] = (int) $post['time']['s'];
    }
  }
  else
  {
    $post["date"] = ilUtil::stripSlashes($post["date"]);
    $post["time"] = ilUtil::stripSlashes($post["time"]);

    if($post["date"])
    {
      switch($ilUser->getDateFormat())
      {
        case ilCalendarSettings::DATE_FORMAT_DMY:
          $date = explode(".", $post["date"]);
          $dt['mday'] = (int)$date[0];
          $dt['mon'] = (int)$date[1];
          $dt['year'] = (int)$date[2];
          break;

        case ilCalendarSettings::DATE_FORMAT_YMD:
          $date = explode("-", $post["date"]);
          $dt['mday'] = (int)$date[2];
          $dt['mon'] = (int)$date[1];
          $dt['year'] = (int)$date[0];
          break;

        case ilCalendarSettings::DATE_FORMAT_MDY:
          $date = explode("/", $post["date"]);
          $dt['mday'] = (int)$date[1];
          $dt['mon'] = (int)$date[0];
          $dt['year'] = (int)$date[2];
          break;
      }

      if($this->getShowTime())
      {
        if($ilUser->getTimeFormat() == ilCalendarSettings::TIME_FORMAT_12)
        {
          $seconds = "";
          if($this->getShowSeconds())
          {
            $seconds = ":\s*([0-9]{1,2})\s*";
          }
          if(preg_match("/([0-9]{1,2})\s*:\s*([0-9]{1,2})\s*".$seconds."(am|pm)/", trim(strtolower($post["time"])), $matches))
          {
            $dt['hours'] = (int)$matches[1];
            $dt['minutes'] = (int)$matches[2];
            if($seconds)
            {
              $dt['seconds'] = (int)$time[2];
              $ampm = $matches[4];
            }
            else
            {
              $dt['seconds'] = 0;
              $ampm = $matches[3];
            }
            if($dt['hours'] == 12)
            {
              if($ampm == "am")
              {
                $dt['hours'] = 0;
              }
            }
            else if($ampm == "pm")
            {
              $dt['hours'] += 12;
            }
          }
        }
        else
        {
          $time = explode(":", $post["time"]);
          $dt['hours'] = (int)$time[0];
          $dt['minutes'] = (int)$time[1];
          $dt['seconds'] = (int)$time[2];
        }
      }
    }
  }

  // very basic validation
  if($dt['mday'] == 0 || $dt['mon'] == 0 || $dt['year'] == 0 || $dt['mday'] > 31 || $dt['mon'] > 12)
  {
    $dt = false;
  }
  else if($this->getShowTime() && ($dt['hours'] > 23 || $dt['minutes'] > 59 || $dt['seconds'] > 59))
  {
    $dt = false;
  }

  // #11847
  if(!checkdate($dt['mon'], $dt['mday'], $dt['year']))
  {
    $this->invalid_input = $_POST[$this->getPostVar()]['date'];
    $this->setAlert($lng->txt("exc_date_not_valid"));
    $dt = false;
  }

  $date = new ilDateTime($dt, IL_CAL_FKT_GETDATE, $ilUser->getTimeZone());
  $this->setDate($date);

  // post values used to be overwritten anyways - cannot change behaviour
  $_POST[$this->getPostVar()]['date'] = $date->get(IL_CAL_FKT_DATE, 'Y-m-d', $ilUser->getTimeZone());
  $_POST[$this->getPostVar()]['time'] = $date->get(IL_CAL_FKT_DATE, 'H:i:s', $ilUser->getTimeZone());

  return (bool)$dt;
}


// Password input
function checkInput()
{
  global $lng;

  $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
  $_POST[$this->getPostVar()."_retype"] = ilUtil::stripSlashes($_POST[$this->getPostVar()."_retype"]);
  if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
  {
    $this->setAlert($lng->txt("msg_input_is_required"));

    return false;
  }
  if ($this->getValidateAuthPost() != "")
  {
    $auth = ilAuthUtils::_getAuthMode($_POST[$this->getValidateAuthPost()]);

    // check, if password is required dependent on auth mode
    if ($this->getRequiredOnAuth() && ilAuthUtils::_allowPasswordModificationByAuthMode($auth)
      && trim($_POST[$this->getPostVar()]) == "")
    {
      $this->setAlert($lng->txt("form_password_required_for_auth"));

      return false;
    }

    // check, if password is allowed to be set for given auth mode
    if (trim($_POST[$this->getPostVar()]) != "" &&
      !ilAuthUtils::_allowPasswordModificationByAuthMode($auth))
    {
      $this->setAlert($lng->txt("form_password_not_allowed_for_auth"));

      return false;
    }
  }
  if ($this->getRetype() && !$this->getPreSelection() &&
    ($_POST[$this->getPostVar()] != $_POST[$this->getPostVar()."_retype"]))
  {
    $this->setAlert($lng->txt("passwd_not_match"));

    return false;
  }
  if (!$this->getSkipSyntaxCheck() &&
    !ilUtil::isPassword($_POST[$this->getPostVar()],$custom_error) &&
    $_POST[$this->getPostVar()] != "")
  {
    if($custom_error != '') $this->setAlert($custom_error);
    else $this->setAlert($lng->txt("passwd_invalid"));

    return false;
  }

  return $this->checkSubItemsInput();
}
