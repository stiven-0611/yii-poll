<?php

/**
 * PollModule class file.
 *
 * @author Matt Kelliher
 * @license New BSD License
 * @version 0.9.3
 */

/**
 * The Poll extension allows you to create polls for users to vote on.
 * Votes can be restricted by user ID, cookie, and/or IP address.
 *
 * Installation:
 *   In order for this to work properly, you must have a User class
 *   where Yii::app()->user->id returns an integer id for the user,
 *   as well as an entry in the User table with an id of 0 to
 *   represent guests.
 *   Also, you must configure/install the schema file located in:
 *     /data/poll.sql
 *   and adjust the tables & PollVote user_id foreign key as needed.
 *
 * Configuration:
 *    Be sure to configure the 'db' component of your app
 *    to use a table prefix, otherwise you must update
 *    the models' table names.
 *
 * <pre>
 * return array(
 *    ...
 *    'import' => array(
 *      'application.modules.poll.models.*',
 *      'application.modules.poll.components.*',
 *    ),
 *    'modules' => array(
 *      'poll' => array(
 *        // Force users to vote before seeing results
 *        'forceVote' => TRUE,
 *        // Track anonymous votes with cookies
 *        'guestCookies' => TRUE,
 *        // Restrict anonymous votes by IP address,
 *        // otherwise it's tied only to user_id 
 *        'ipRestrict' => FALSE,
 *        // Allow guests to cancel their votes
 *        // if ipRestrict or guestCookies are enabled.
 *        'allowGuestCancel' => FALSE,
 *      ),
 *    ),
 * );
 * </pre>
 *
 * Usage:
 *
 * The Poll extension has the basic Gii-created CRUD functionality,
 * as well as a portlet to load elsewhere.
 *
 * To load the latest poll:
 * <pre>
 * $this->widget('EPoll');
 * </pre>
 *
 * To load a specific poll:
 * <pre>
 * $this->widget('EPoll', array('poll_id' => 1));
 * </pre>
 */

class PollModule extends CWebModule
{
  /**
   * @var string the ID of the default controller for this module.
   */
  public $defaultController = 'poll';
  /**
   * @var boolean Force users to vote before seeing results.
   */
  public $forceVote = TRUE;
  /**
   * @var boolean Track anonymous votes with cookies.
   */
  public $guestCookies = TRUE;
  /**
   * @var boolean Restrict anonymous votes by IP address,
   * otherwise it's tied only to the user's ID.
   */
  public $ipRestrict = FALSE;
  /**
   * @var boolean Allow guests to cancel their votes
   * if $ipRestrict is enabled.
   */
  public $allowGuestCancel = FALSE;

  private $_assetsUrl;

  /**
   * Initializes the module.
   */
  public function init()
  {
    parent::init();

    $this->setImport(array(
      'poll.components.*',
      'poll.models.*',
    ));

    Yii::app()->clientScript->registerCssFile($this->assetsUrl .'/poll.css');
  }

  /**
   * @return string the base URL that contains all published asset files of poll.
   */
  public function getAssetsUrl()
  {
    if ($this->_assetsUrl === NULL)
      $this->_assetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('poll.assets'));
    return $this->_assetsUrl;
  }

  /**
   * @param string $value the base URL that contains all published asset files of poll.
   */
  public function setAssetsUrl($value)
  {
    $this->_assetsUrl = $value;
  }

  /**
   * @return string the version of this module.
   */
  public function getVersion()
  {
    return '0.9.3';
  }
}
