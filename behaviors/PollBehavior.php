<?php

/**
 * PollBehavior class file.
 */
class PollBehavior extends CBehavior
{
  /**
   * Returns the PollChoice model based on primary key or a new PollChoice instance.
   * @param Poll the Poll model 
   * @param integer the ID of the PollChoice to be loaded
   */
  public function loadChoice($poll, $choice_id)
  {
    if ($choice_id) {
      foreach ($poll->choices as $choice) {
        if ($choice->id == $choice_id) return $choice;
      }
    }

    return new PollChoice;
  }

  /**
   * Returns the PollVote model based on primary key or a new PollVote instance.
   * @param object the Poll model 
   */
  public function loadVote($poll)
  {
    $module = Yii::app()->getModule('poll');
    $userId = (int) Yii::app()->user->id;
    $isGuest = Yii::app()->user->isGuest;
    $cookie = isset(Yii::app()->request->cookies['Poll_'. $poll->id])
      ? Yii::app()->request->cookies['Poll_'. $poll->id] 
      : NULL;

    foreach ($poll->votes as $vote) {
      if ($vote->user_id == $userId) {
        if (($isGuest && $module->guestCookies && ($cookie === NULL || $vote->id != $cookie->value)) ||
            ($isGuest && $module->ipRestrict && $vote->ip_address != $_SERVER['REMOTE_ADDR'])) {
          continue;
        }
        else
          return $vote;
      }
    }

    return new PollVote;
  }

}
