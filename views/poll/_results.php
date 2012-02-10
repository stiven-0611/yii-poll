<div class="poll-results">
<?php
  foreach ($model->choices as $choice) {
    $this->renderPartial('/pollchoice/_resultsChoice', array(
      'choice' => $choice,
      'percent' => $model->totalVotes > 0 ? 100 * round($choice->totalVotes / $model->totalVotes, 3) : 0,
      'voteCount' => $choice->totalVotes,
    ));
  }
?>
</div>
