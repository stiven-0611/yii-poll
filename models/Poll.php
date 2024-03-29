<?php

/**
 * This is the model class for table "{{poll}}".
 *
 * The followings are the available columns in table '{{poll}}':
 * @property string $id
 * @property string $title
 * @property string $description
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property PollChoice[] $choices
 * @property PollVote[] $votes
 * @property integer $totalVotes
 */
class Poll extends CActiveRecord
{
  /**
   * @var integer representing a closed poll status
   */
  const STATUS_CLOSED = 0;

  /**
   * @var integer representing an open poll status
   */
  const STATUS_OPEN = 1;

  /**
   * Returns the static model of the specified AR class.
   * @return Poll the static model class
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
    return '{{poll}}';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      array('title', 'required'),
      array('status', 'numerical', 'integerOnly'=>true),
      array('title', 'length', 'max'=>255),
      array('description', 'safe'),
      array('title, description, status', 'safe', 'on'=>'search'),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
      'choices' => array(self::HAS_MANY, 'PollChoice', 'poll_id'),
      'votes' => array(self::HAS_MANY, 'PollVote', 'poll_id'),
      'totalVotes' => array(self::STAT, 'PollVote', 'poll_id'),
    );
  }

  /**
   * @return array default scope.
   */
  public function defaultScope()
  {
    $t = $this->tableName();

    return array(
      'alias' => $t,
    );
  }

  /** 
   * @return array additional query scopes
   */
  public function scopes()
  {
    $t = $this->tableName();

    return array(
      'open' => array(
        'alias' => $t,
        'condition' => "$t.status=". self::STATUS_OPEN,
       ),  
      'closed' => array(
        'alias' => $t,
        'condition' => "$t.status=". self::STATUS_CLOSED,
       ),  
      'latest' => array(
        'alias' => $t,
        'order' => "$t.id DESC",
      ),  
    );  
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'id' => 'ID',
      'title' => 'Title',
      'description' => 'Description',
      'status' => 'Status',
    );
  }

  /**
   * @return array customized status labels
   */
  public function statusLabels()
  {
    return array(
      self::STATUS_CLOSED => 'Closed',
      self::STATUS_OPEN   => 'Open',
    );
  }

  /**
   * Returns the text label for the specified status.
   */
  public function getStatusLabel($status)
  {
    $labels = self::statusLabels();

    if (isset($labels[$status])) {
      return $labels[$status];
    }

    return $status;
  }

  /**
   * Retrieves a list of models based on the current search/filter conditions.
   * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
   */
  public function search()
  {
    $criteria=new CDbCriteria;

    $criteria->compare('title',$this->title,true);
    $criteria->compare('description',$this->description,true);
    $criteria->compare('status',$this->status);

    return new CActiveDataProvider($this, array(
      'criteria'=>$criteria,
    ));
  }

  /**
   * Determine if a user can vote on a Poll.
   */
  public function userCanVote()
  {
    if ($this->status == self::STATUS_CLOSED) return FALSE;

    // Setup global query attributes
    $where = array('and', 'poll_id=:poll_id', 'user_id=:user_id');
    $params = array(':poll_id' => $this->id, ':user_id' => (int) Yii::app()->user->id);

    if (Yii::app()->user->isGuest) {
      $module = Yii::app()->getModule('poll');

      // Check for choice cookie
      if ($module->guestCookies === TRUE && isset(Yii::app()->request->cookies['Poll_'. $this->id])) {
        return FALSE;
      }

      // Add IP restricted attributes if needed
      if ($module->ipRestrict === TRUE) {
        $where[] = 'ip_address=:ip_address';
        $params[':ip_address'] = $_SERVER['REMOTE_ADDR'];
      }
      else {
        // No cookie and no IP restriction, so go for it
        return TRUE;
      }
    }

    // Retrieve true/false if a vote exists on poll by user
    $result = (bool) Yii::app()->db->createCommand(array(
      'select' => 'id',
      'from'   => '{{poll_vote}}',
      'where'  => $where,
      'params' => $params,
    ))->queryRow();

    return !$result;
  }

  /**
   * Determine if a user can cancel their vote.
   * @param PollVote instance
   */
  public function userCanCancelVote($pollVote)
  {
    if (!$pollVote->id || $this->status == self::STATUS_CLOSED) {
      return FALSE;
    }

    $module = Yii::app()->getModule('poll');
    $isGuest = Yii::app()->user->isGuest;
    $hasCookie = $isGuest && $module->guestCookies && isset(Yii::app()->request->cookies['Poll_'. $this->id]);
    $guestCanCancel = $isGuest && $module->allowGuestCancel && ($module->ipRestrict || $hasCookie);

    if (!$isGuest || ($guestCanCancel)) {
      return TRUE;
    }

    return FALSE;
  }
}
