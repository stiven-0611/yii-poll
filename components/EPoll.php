<?php

Yii::import('zii.widgets.CPortlet');

/**
 * EPoll portlet class file.
 */
class EPoll extends CPortlet
{
  /**
   * @var integer the poll ID to load
   * Defaults to 0, which loads the latest poll
   */
  public $poll_id = 0;
  /**
   * @var integer the counter for generating implicit IDs.
   */
  private static $_pollCounter = 0;
  /**
   * @var string id of the widget.
   */
  private $_id;
  /**
   * @var Poll
   */
  private $_poll;

  /**
   * Returns the ID of the widget or generates a new one if requested.
   * @param boolean $autoGenerate whether to generate an ID if it is not set previously
   * @return string id of the widget.
   */
  public function getId($autoGenerate = TRUE)
  {
    if ($this->_id !== NULL)
      return $this->_id;
    else if ($autoGenerate)
      return $this->_id = 'Poll_'. self::$_pollCounter++;
  }

  /**
   * Initializes the portlet.
   */
  public function init()
  {
    $this->attachBehavior('poll', 'poll.behaviors.PollBehavior');

    $assets = Yii::app()->assetManager->publish(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../assets');
    $clientScript = Yii::app()->clientScript;
    $clientScript->registerCssFile($assets .'/poll.css');

    $this->_poll = $this->poll_id == 0 
      ? Poll::model()->latest()->find()
      : Poll::model()->findByPk($this->poll_id);

    if ($this->_poll) {
      $this->title = $this->_poll->title;
    }

    parent::init();
  }

  /**
   * Renders the portlet content.
   */
  public function renderContent()
  {
    $model = $this->_poll;

    if ($model) {
      $userVote = $this->loadVote($model);
      $params = array('model' => $model, 'userVote' => $userVote);

      // Save a user's vote
      if (isset($_POST['PortletPollVote_choice_id'])) {
        $userVote->choice_id = $_POST['PortletPollVote_choice_id'];
        $userVote->poll_id = $model->id;
        if ($userVote->save()) {
          // Prevent submit on refresh
          $route = Yii::app()->controller->route;
          Yii::app()->controller->redirect(Yii::app()->createUrl($route));
        }
      }

      // Force user to vote if needed
      if (Yii::app()->getModule('poll')->forceVote && $model->userCanVote()) {
        $view = 'vote';

        // Convert choices to form options list
        $choices = array();
        foreach ($model->choices as $choice) {
          $choices[$choice->id] = CHtml::encode($choice->label);
        }

        $params['choices'] = $choices;
      }
      // Otherwise view the results
      else {
        $view = 'view';
        $userChoice = $this->loadChoice($model, $userVote->choice_id);

        $params += array(
          'userVote' => $userVote,
          'userChoice' => $userChoice,
          'userCanCancel' => $model->userCanCancelVote($userVote),
        );
      }

      $this->render($view, $params);
    }
  }

}
