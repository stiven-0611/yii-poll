<?php

/**
 * This is the model class for table "{{poll_vote}}".
 *
 * The followings are the available columns in table '{{poll_vote}}':
 * @property string $id
 * @property string $choice_id
 * @property string $poll_id
 * @property string $user_id
 * @property string $ip_address
 * @property string $timestamp
 *
 * The followings are the available model relations:
 * @property User $user
 * @property PollChoice $choice
 * @property Poll $poll
 */
class PollVote extends CActiveRecord
{
  /**
   * Returns the static model of the specified AR class.
   * @return PollVote the static model class
   */
  public static function model($className=__CLASS__)
  {
    return parent::model($className);
  }

  /**
   * @return string the associated database table name
   */
  public function tableName()
  {
    return '{{poll_vote}}';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('choice_id, poll_id', 'required'),
      array('choice_id, poll_id, user_id, timestamp', 'length', 'max'=>11),
      array('ip_address', 'length', 'max'=>16),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
      'user' => array(self::BELONGS_TO, 'User', 'user_id'),
      'choice' => array(self::BELONGS_TO, 'PollChoice', 'choice_id'),
      'poll' => array(self::BELONGS_TO, 'Poll', 'poll_id'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'choice_id' => 'Choice',
    );
  }

  /**
   * Before a PollVote is saved.
   */
  public function beforeSave()
  {
    $this->ip_address = $_SERVER['REMOTE_ADDR'];
    $this->timestamp = time();
    $this->user_id = Yii::app()->user->id;

    return parent::beforeSave(); 
  }

  /**
   * After a PollVote is saved.
   */
  public function afterSave()
  {
    if (Yii::app()->getModule('poll')->guestCookies === TRUE && Yii::app()->user->isGuest) {
      $cookieName = 'Poll_'. $this->poll->id;
      $cookie = new CHttpCookie($cookieName, $this->id);
      $cookie->expire = time() + 60 * 60 * 24 * 365;
      Yii::app()->request->cookies[$cookieName] = $cookie;
    }
  }

  /**
   * Before a PollVote is deleted.
   */
  public function beforeDelete()
  {
    if (!$this->poll->userCanCancelVote($this)) {
      return FALSE;
    }

    // Delete cookie if needed
    if (isset(Yii::app()->request->cookies['Poll_'. $this->poll->id]))
      unset(Yii::app()->request->cookies['Poll_'. $this->poll->id]);

    return parent::beforeDelete();
  }

}
