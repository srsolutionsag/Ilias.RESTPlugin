<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer, T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\core\oauth2_v2;

// This allows us to use shortcuts instead of full quantifier
use \RESTController\libs            as Libs;
use \RESTController\libs\Exceptions as LibExceptions;


/**
 * Class:
 *
 */
class UserAdmin extends Libs\RESTModel {
  // Allow to re-use status messages and codes
  const MSG_RBAC_CREATE_DENIED  = 'Permission to create/modify user-account denied by RBAC-System.';
  const ID_RBAC_CREATE_DENIED   = 'RESTController\\extensions\\users_v2\\Admin::ID_RBAC_CREATE_DENIED';
  const MSG_RBAC_READ_DENIED    = 'Permission to read user-account denied by RBAC-System.';
  const ID_RBAC_READ_DENIED     = 'RESTController\\extensions\\users_v2\\Admin::ID_RBAC_READ_DENIED';
  const MSG_NO_GLOBAL_ROLE      = 'Access-token user has no global role that could be inherited by new users.';
  const ID_NO_GLOBAL_ROLE       = 'RESTController\\extensions\\users_v2\\Admin::ID_NO_GLOBAL_ROLE';
  const MSG_USER_PICTURE_VIRUS  = 'User picture was rejected by Virus-Scanner.';
  const ID_USER_PICTURE_VIRUS   = 'RESTController\\extensions\\users_v2\\Admin::ID_USER_PICTURE_VIRUS';
  const MSG_USER_PICTURE_EMPTY  = 'User picture does not contain any base64-encoded data.';
  const ID_USER_PICTURE_EMPTY   = 'RESTController\\extensions\\users_v2\\Admin::ID_USER_PICTURE_EMPTY';




  //
  const DEFAULT_ROLE_ID     = 4;
  const SYSTEM_ROLE_ID      = SYSTEM_ROLE_ID;
  const ANONYMOUS_ROLE_ID   = ANONYMOUS_ROLE_ID;
  const ROLE_FOLDER_ID      = ROLE_FOLDER_ID;
  const USER_FOLDER_ID      = USER_FOLDER_ID;


  /**
   * refID is either self::USER_FOLDER_ID, which in the context of ILIAS means the Admin-GUI
   * or the Reference-ID of a categorie or organisational-unit for local administration.
   */
  public static function CreateUser($userData, $refId = self::USER_FOLDER_ID) {
    /* DoIt:
     *  Check required fields (all fields, before setting)
     *  Check max length
     *  Check default values
     *  Check invalid values
     *  View differences in 5.1 (ilObjUserGUI)
     */


    // Will contain return values
    $result = array();

    // Check of user is allowd to create user globally or in given category/org-unit
    if (!$rbacsystem->checkAccess('create_usr', $refId) && !$ilAccess->checkAccess('cat_administrate_users', '', $refId))
      throw new LibExceptions\RBAC(
        self::MSG_RBAC_CREATE_DENIED,
        self::ID_RBAC_CREATE_DENIED
      );

    // Fetch ILIAS settings for checking changability
    $settings = $ilSetting->getAll();

    // Create new user object
    $userObj = new ilObjUser();

    // Set user-values which can have change-restrictions
    if (self::IsChangeable($refId, $settings, 'language'))
      $userObj->setLanguage($userData['language']);
    if (self::IsChangeable($refId, $settings, 'birthday'))
      $userObj->setBirthday(array_key_exists('birthday', $userData) ? $userData['birthday'] : null);
		if (self::IsChangeable($refId, $settings, 'gender'))
			$userObj->setGender($userData['gender']);
    if (self::IsChangeable($refId, $settings, 'title'))
      $userObj->setUTitle($userData['title']);
		if (self::IsChangeable($refId, $settings, 'firstname'))
			$userObj->setFirstname($userData['firstname']);
    if (self::IsChangeable($refId, $settings, 'lastname'))
			$userObj->setLastname($userData['lastname']);
    if (self::IsChangeable($refId, $settings, 'institution'))
			$userObj->setInstitution($userData['institution']);
		if (self::IsChangeable($refId, $settings, 'department'))
			$userObj->setDepartment($userData['department']);
		if (self::IsChangeable($refId, $settings, 'street'))
			$userObj->setStreet($userData['street']);
		if (self::IsChangeable($refId, $settings, 'city'))
			$userObj->setCity($userData['city']);
		if (self::IsChangeable($refId, $settings, 'zipcode'))
			$userObj->setZipcode($userData['zipcode']);
		if (self::IsChangeable($refId, $settings, 'country'))
			$userObj->setCountry($userData['country']);
		if (self::IsChangeable($refId, $settings, 'sel_country'))
			$userObj->setSelectedCountry($userData['sel_country']);
		if (self::IsChangeable($refId, $settings, 'phone_office'))
			$userObj->setPhoneOffice($userData['phone_office']);
		if (self::IsChangeable($refId, $settings, 'phone_home'))
			$userObj->setPhoneHome($userData['phone_home']);
		if (self::IsChangeable($refId, $settings, 'phone_mobile'))
			$userObj->setPhoneMobile($userData['phone_mobile']);
		if (self::IsChangeable($refId, $settings, 'fax'))
			$userObj->setFax($userData['fax']);
		if (self::IsChangeable($refId, $settings, 'matriculation'))
			$userObj->setMatriculation($userData['matriculation']);
		if (self::IsChangeable($refId, $settings, 'email'))
			$userObj->setEmail($userData['email']);
		if (self::IsChangeable($refId, $settings, 'hobby'))
			$userObj->setHobby($userData['hobby']);
		if (self::IsChangeable($refId, $settings, 'referral_comment'))
			$userObj->setComment($userData['referral_comment']);
    if (self::IsChangeable($refId, $settings, 'delicious'))
      $userObj->setDelicious($userData['delicious']);
    if (self::IsChangeable($refId, $settings, 'instant_messengers')) {
      $userObj->setInstantMessengerId('icq',    $userData['im_icq']);
      $userObj->setInstantMessengerId('yahoo',  $userData['im_yahoo']);
      $userObj->setInstantMessengerId('msn',    $userData['im_msn']);
      $userObj->setInstantMessengerId('aim',    $userData['im_aim']);
      $userObj->setInstantMessengerId('skype',  $userData['im_skype']);
      $userObj->setInstantMessengerId('jabber', $userData['im_jabber']);
      $userObj->setInstantMessengerId('voip',   $userData['im_voip']);
    }

    // Set user-preferences which can have change-restrictions
    if (self::IsChangeable($refId, $settings, 'hits_per_page'))
      $userObj->setPref('hits_per_page', $userData['hits_per_page']);
    if (self::IsChangeable($refId, $settings, 'show_users_online'))
      $userObj->setPref('show_users_online', $userData['show_users_online']);
    if (self::IsChangeable($refId, $settings, 'hide_own_online_status'))
      $userObj->setPref('hide_own_online_status', $userData['hide_own_online_status'] ? 'y' : 'n');
    if (self::IsChangeable($refId, $settings, 'skin_style')) {
      $userObj->setPref('skin',  $userData['skin']);
      $userObj->setPref('style', $userData['style']);
    }

    // Set session reminder
    if ($settings['session_reminder_enabled'])
      $userObj->setPref('session_reminder_enabled', (int) $userData['session_reminder_enabled']);

    // Set user-values without change-restrictions
 		$userObj->setLogin($userData['login']);
    $userObj->setPasswd($userData['passwd'], IL_PASSWD_PLAIN);
    $userObj->setLastPasswordChangeTS(time());
		$userObj->setAuthMode($userData['auth_mode']);
		$userObj->setClientIP($userData['client_ip']);
    $userObj->setActive($userData['active'], $ilUser->getId());
    $userObj->setFullname();
    $userObj->setTitle($userObj->getFullname());
    $userObj->setDescription($userObj->getEmail());
 		$userObj->setTimeLimitFrom(self::GetTimeValue($userData['time_limit_from']);
 		$userObj->setTimeLimitUntil(self::GetTimeValue($userData['time_limit_until']);
 		$userObj->setTimeLimitUnlimited(array_key_exists('time_limit_unlimited', $userData) ? $userData['time_limit_unlimited'] : true);
    $userObj->setTimeLimitOwner($refId);
    $userObj->setGeneralInterests($userData['interests_general']);
    $userObj->setOfferingHelp($userData['interests_help_offered']);
    $userObj->setLookingForHelp($userData['interests_help_looking']);
    $userObj->setLatitude($userData['latitude']);
    $userObj->setLongitude($userData['longitude']);
    $userObj->setLocationZoom($userData['loc_zoom']);
    $userObj->setUserDefinedData($userData['udf']);

    // Set attached external account if enabled
    include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
    if (ilAuthUtils::_isExternalAccountEnabled())
      $userObj->setExternalAccount($userData['ext_account']);

    // Set disk quotas (overall abd workspace)
    require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
    if (ilDiskQuotaActivationChecker::_isActive())
      $userObj->setPref('disk_quota',     $userData['disk_quota']     * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());
    if (ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
      $userObj->setPref("wsp_disk_quota", $userData['wsp_disk_quota'] * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());

    // Create and save user account data
    $userObj->create();
    $userObj->saveAsNew();
    $userObj->writePrefs();

    // Create profile-picture from attached based64 encoded image
    if (self::IsChangeable($refId, $settings, 'upload'))
      self::ProcessUserPicture($refId, $userObj, $userData);

    // Check wether profile is still incomplete
    include_once 'Services/User/classes/class.ilUserProfile.php';
    if (ilUserProfile::isProfileIncomplete($userObj)) {
      // Mark as incomplete
      $userObj->setProfileIncomplete(true);
      $userObj->update();

      // Add to return value
      $result['incomplete'] = true;
    }

    // Assign user to given role
    $rbacadmin->assignUser($userData['default_role'], $userObj->getId(), true);

    // Send email?
    if ($userData['send_mail'] == 'y') {
      // Create new eamil object
      include_once('Services/Mail/classes/class.ilAccountMail.php');
      $mail = new ilAccountMail();
      $mail->useLangVariablesAsFallback(true);
      $mail->setUserPassword($userData['passwd']);
      $mail->setUser($userObj);

      // Send email (and  throw on error)
      if (!$mail->send())
        $result['email'] = false;
    }

    // Return on success with some additional information
    $result['user'] = $userObj;
    return $result
  }


  /**
   *
   */
  protected static function GetTimeValue($data) {
    // Return current time as fallback
    if (!isset($data))
      return time();
    // Unix-time given
    elseif (is_int($data))
      return $data;
    // Date and time given, convert to uni-time
    elseif (array_key_exists('date', $data) && array_key_exists('time', $data)) {
      $time = new ilDateTime(sprintf('%s %s', $data['date'], $data['time'])
      return $time->get(IL_CAL_UNIX);
    }
    // Unix-time given as array-key
    elseif (array_key_exists('unix', $data))
      return $data['unix'];
    // Absolute fallback-case (should only happen on wrong input)
    else
      return time();
  }


  /**
   *
   */
  protected function IsChangeable($refId, $settings, $setting) {
    // All settings can be changed via the admin-panel / for global accounts
    if ($refId == USER_FOLDER_ID)
      return true;

    // Check wether setting is marked as changeable
    return (bool) $settings[sprintf('usr_settings_changeable_lua_%s', $setting)];
  }


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
  }


  /**
   *
   */
  protected static function GetDefaultRole($allowedRoles) {
    // Select default role ('User'-Role if available)
    if (in_array(self::DEFAULT_ROLE_ID, $allowedRoles))
      return self::DEFAULT_ROLE_ID;
    // Select role, that is not admin-role
    elseif (count($allowedRoles) > 1 and in_array(self::SYSTEM_ROLE_ID, $allowedRoles)) {
      for ($allowedRoles as $role)
        if ($role != self::SYSTEM_ROLE_ID)
          return $role;
    }
    // Just select some random role
    else
      return array_shift($systemRoleKey);
  }


  /**
   *
   */
  protected static function ProcessUserPicture($refId, $userObj, $userData) {
    // Import the RBAC-System instance
    global $rbacsystem;

    // Check for Admin-GUI access-rights to users
    if ($refId == USER_FOLDER_ID && !$rbacsystem->checkAccess('visible,read', $refId))
      throw new LibExceptions\RBAC(
        self::MSG_RBAC_READ_DENIED,
        self::ID_RBAC_READ_DENIED
      );

    // Check for local administration access-rights (Note: getTimeLimitOwner() should be $refId for new users)
    if ($refId != USER_FOLDER_ID && !$rbacsystem->checkAccess('cat_administrate_users', $userObj->getTimeLimitOwner()))
      throw new LibExceptions\RBAC(
        self::MSG_RBAC_CREATE_DENIED,
        self::ID_RBAC_CREATE_DENIED
      );

    // Delete user picture files
    $userFile = $userData['userfile'];
    if (!isset($userFile))
      $userObj->removeUserPicture();

    // Create user pciture files (profile-pricutre and thumbnails)
    else {
      // Extract user-id only once
      $userId = $userObj->getId();

      // Build upload path
      //  Note: ilObjUserGUI uses wrong upload path, which is never cleaned-up by $userObj->removeUserPicture()
      $directory  = sprintf('%s/usr_images',   ilUtil::getWebspaceDir());
      $uploaded   = sprintf('%s/upload_%d', $directory, $userId);

      // Extract base64 encoded image data
      $encodedData = preg_replace('#^data:image/\w+;base64,#i', '', $userFile);
      if (!isset($encodedData) || strlen($encodedData) == 0 || strcmp($userFile, $encodedData) == 0)
        throw new LibExceptions\Parameter(
          self::MSG_USER_PICTURE_EMPTY,
          self::ID_USER_PICTURE_EMPTY
        );

      // Store decoded image data to file
      //  Note: ilObjUserGUI sets chmod to 0770; beats me why one would enabled execution bit on an UPLOADED file...
      file_put_contents($uploaded, base64_decode($encodedData));
      chmod($uploaded, 0664);

      // Check uploaded file for virus and delete + fail if one was detected
      $scanResult = ilUtil::virusHandling($uploaded, sprintf('Profile-Picutre [User: %s]', $userObj->getLogin()));
      if (!$scanResult[0]) {
        // Delete file
        unlink($uploaded);

        // Throw exception
        throw new LibExceptions\Parameter(
          self::MSG_USER_PICTURE_VIRUS,
          self::ID_USER_PICTURE_VIRUS,
          array(
            'message' => $scanResult[1]
          )
        );
      }

      // Generate actual user profile picture as well as thumbnails
      //  Note: We could use $userObj->_uploadPersonalPicture(...), but ilObjUserGUI does neither
      //        and $userObj->_uploadPersonalPicture does write directly to DB without updating $userObj.
      $uploaded = ilUtil::escapeShellArg($uploaded);
      $stored   = array(
        'show'    => ilUtil::escapeShellArg(sprintf('%s/usr_%d.jpg',         $directory, $userId)),
        'thumb'   => ilUtil::escapeShellArg(sprintf('%s/usr_%d_small.jpg',   $directory, $userId)),
        'xthumb'  => ilUtil::escapeShellArg(sprintf('%s/usr_%d_xsmall.jpg',  $directory, $userId)),
        'xxthumb' => ilUtil::escapeShellArg(sprintf('%s/usr_%d_xxsmall.jpg', $directory, $userId)),
      )

      // Actually convert uploaded files
      if (ilUtil::isConvertVersionAtLeast("6.3.8-3")) {
        ilUtil::execConvert(sprintf('%s[0] -geometry 200x200^ -gravity center -extent 200x200 -quality 100 JPEG:%s', $uploaded, $stored['show']));
        ilUtil::execConvert(sprintf('%s[0] -geometry 100x100^ -gravity center -extent 100x100 -quality 100 JPEG:%s', $uploaded, $stored['thumb']));
        ilUtil::execConvert(sprintf('%s[0] -geometry  75x75^  -gravity center -extent  75x75  -quality 100 JPEG:%s', $uploaded, $stored['xthumb']));
        ilUtil::execConvert(sprintf('%s[0] -geometry  30x30^  -gravity center -extent  30x30  -quality 100 JPEG:%s', $uploaded, $stored['xxthumb']));
      }
      else {
        ilUtil::execConvert(sprintf('%s[0] -geometry 200x200 -quality 100 JPEG:%s', $uploaded, $stored['show']));
        ilUtil::execConvert(sprintf('%s[0] -geometry 100x100 -quality 100 JPEG:%s', $uploaded, $stored['thumb']));
        ilUtil::execConvert(sprintf('%s[0] -geometry  75x75  -quality 100 JPEG:%s', $uploaded, $stored['xthumb']));
        ilUtil::execConvert(sprintf('%s[0] -geometry  30x30  -quality 100 JPEG:%s', $uploaded, $stored['xxthumb']));
      }

      // Finally set profile picture and update user object
      $userObj->setPref('profile_image', sprintf('usr_%d.jpg', $userId));
      $userObj->update();
    }
  }


  // DoIt: Use this for validation
  public static function _NOT_IMPLEMENTED() {
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


    // Login: Required
    // Passwort: Required, Max 32
    // Ext-Account: Max 250
    // Gender: Required, Value 'f' / 'm'
    // Vorname: Max 32, Required
    // Nachname: Max 32, Required
    // Title: Max 32, (Required)
    // Email: (Required)
    // Rolle: Required
    // Birthdate: (Required)
    // institution: Max 80
    // department: Max 80
    // street: Max 40
    // city: Max 40
    // zipcode: Max 10
    // country: Max 40
    // phone_office: Max 30
    // phone_home: Max 30
    // phone_mobile: Max 30
    // fax: Max 30
    // Hobby: Max ~120
    // interests_general: Max 40, (Required)
    // interests_help_offered: Max 40, (Required)
    // interests_help_looking: Max 40, (Required)
    // "icq", "yahoo", "msn", "aim", "skype", "jabber", "voip" -> Max 40
    // Matriculation: Max 40, (Required)
    // Delicious: Max 40, (Required)
    // Client-IP: Max 255
    // UDF_TYPE_TEXT: Max 40, (Required)
    // show_users_online: 'y'/'n'
    // Send-Email Button, Value $ilUser->getPref('send_info_mails') == 'y')
    // Ignore-Required-Fields: 1 / 0
    // Language: Value $ilSetting->get("language")
    // Skin: Value $ilClientIniFile->readVariable("layout","skin"). ":".$ilClientIniFile->readVariable("layout","style")
    // HitsPerPage: Value $ilSetting->get("hits_per_page")
    // User-Online: Value $ilSetting->get("show_users_online")
  }
}
