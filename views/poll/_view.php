<div class="view poll-item">

  <strong><?php echo CHtml::link(CHtml::encode($data->title), array('view', 'id'=>$data->id)); ?></strong>

  <p class="description"><?php echo CHtml::encode($data->description); ?></p>

</div>
