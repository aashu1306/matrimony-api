<?php 
//print_r($projectugifts);
?>
<div class="page-header page-header-back">
	

	<a class="back-link" href="<?php echo $this->Html->url(array(
    "controller" => "action_plans","action" => "index")); ?>" onclick="return false;" ><?php echo $this->Form->button('<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> '.__('Back'), array('type' => 'button', 'class' => 'btn btn-default pull-right btn-back')); ?></a>

	<h3><?php if (isset($this->request->data['ActionPlan']['id'])) echo __('Edit Action Plan'); else echo __('Add Action Plan'); ?></h3>
</div>
<?php echo $this->Session->flash('add'); 
	$yesterday_stamp = YESTERDAY_STAMP; // Yesterday's stamp at current time, 
	$action_plan_due_date_stamp = strtotime($action_plan_due_date); 

	 if (!($yesterday_stamp < $action_plan_due_date_stamp)) { ?>
	<div class="alert alert-danger">
  		<?php echo __(CRITERIA_LVL1_CLOSE);?>
	</div>
	<?php } ?>
<div class="clear"></div>



	<?php 

	echo $this->Form->create('ActionPlan',array('url' => array('controller' => 'action_plans', 'action' => 'add'),'class' =>'ajaxForm', 'id'=>'form' ,'autocomplete' => false));

	/*echo $this->Form->create('ActionPlan', array('url' =>  array('controller' => 'action_plans', 'action' => 'add')), 'class'=> 'ajaxForm', 'autocomplete' => false);*/ ?>

	<?php 
		if (isset($this->request->data['ActionPlan']['id'])) {
			echo $this->Form->input('id', array('type'=>'hidden', 'value' => $this->request->data['ActionPlan']['id']));
		}
	?>

	<div class="col-xs-12 col-sm-6 col-md-6">


		<?php echo $this->Form->input('name', array('class' => 'form-control','id'=>'name', 'label' => 'Action Idea','placeholder' => __('Enter the title of your Action Plan'), 'error' => array('attributes' => array('wrap' => 'span','class' => 'label label-danger')),'onblur'=>'checkFields()')); ?>


		<?php echo $this->Form->input('project_practice_area_id', array('class' => 'form-control select2','label' => 'Practice Area', 'empty' => 'Select Practice','options'=>$projectpracticeareas, 'required' => true,'id'=>'project_practice_area_id','onchange'=>'checkFields()')); ?>
		<div id="desc"></div>
		<div id="TestId">
		<?php 
		// echo '<pre>';
		// print_r($projectimpactareas);
		// echo '</pre>';
			// echo "<b>Impact Area :</b>";
		echo $this->Form->label(__('Impact Areas'), null, array('class' => 'label-margin-left'));	
		foreach ($projectimpactareas as $projectimpactarea_id => $projectimpactarea) {
			echo $this->Form->input('project_impact_area_id_'.$projectimpactarea_id,array('type' => 'checkbox','div' => array('class' => 'input checkbox ap-impact-checkbox'), /*'value' => $projectimpactarea_id,*/ 'label' => $projectimpactarea/*, 'hiddenField' => false*/,'onclick'=>'checkFields()'));
		}
		// echo $this->Form->input('project_impact_area_id',array('options' => $projectimpactareas,'class'=>'form-control select2 dropdown-toggle','placeholder' => __('Impact Area'),'multiple'=>true,'label'=>'Impact Area','error' => array('attributes' => array('wrap' => 'span','class' => 'label label-danger')))); ?> 
		<br/>
		</div>
		<div id="SecTestId">
		<?php 
			echo $this->Form->label(__('UGIFT'), __('UGIFT'), array('class' => 'label-margin-left'));
			foreach ($projectugifts as $projectugift_id => $projectugift) {
				echo $this->Form->input('project_ugift_id_'.$projectugift_id,array('type' => 'checkbox','label' => $projectugift,'div' => array('class' => 'input checkbox ap-ugift-checkbox'),'onclick'=>'checkFields()'));
				
			}
		?></div>
		
	</div>
	<div class="col-xs-12 col-sm-6 col-md-6">
	<label style="padding-left:10px">
		Measure of Success

		<a title="<?php echo __('Examples'); ?>" data-toggle='modal' data-target='#formModalStd',  data-remote="<?php echo $this->Html->url(array("controller" => "action_plans","action" => "view_measures"));?>" onclick="return false;" >
				<?php echo '(Examples)'; ?>
		</a>

	</label>

                	
                	
                	
	<?php //echo $this->Form->label('Measure of Success', array('style' => 'padding-left:10px'));
		echo $this->Form->input('measure_of_success', array('class' => 'form-control','label'=>'Measure of success', 'label' => false, 'placeholder' => __('What do you look forward to accomplish by this initiative?'),'id'=>'measure_of_success','error' => array('attributes' => array('wrap' => 'span','class' => 'label label-danger')),'onblur'=>'checkFields()')); ?>
		
		<div class="input text">
			<?php 
			echo $this->Form->label(__('Description'));
			echo $this->Form->textarea('description', array('class' => 'form-control', 'id'=>'descriptions','col' => 8, 'row' => 12, 'placeholder' => __('Detail out the action steps of your Action Plan')/*, 'error' => array('attributes' => array('wrap' => 'span','class' => 'label label-danger'))*/,'onblur'=>'checkFields()')); ?>
		</div>
		
		<?php echo $this->Form->input('start_date', array('class' => 'form-control datepicker','id'=>'start_date' ,'type' => 'text', /*'label' => false,*/'label'=>'Start Date', 'placeholder' => __('When will you start implementing this Action Plan'),'error' => array('attributes' => array('wrap' => 'span','class' => 'label label-danger')),'onchange'=>'checkFields()')); ?>

		
	
		
		<?php //echo $this->Form->input('end_date', array('class' => 'form-control datepicker', 'type' => 'text', /*'label' => false,*/ 'label'=>'End Date','placeholder' => __('Date by when you will analyze the impact of this Action Plan'), 'error' => array('attributes' => array('wrap' => 'span','class' => 'label label-danger')))); 
		#@ 17/02/2016 By Vishal, Change End date to Frequency
		echo $this->Form->input('frequency_id', array('class' => 'form-control select2','id'=>'frequency_id','label' => 'Frequency','options'=>$frequencies,'empty' => __('Select Frequency'),'onchange'=>'checkFields()'));?>
		
	</div>
	
	
	<div class="col-xs-12 col-sm-12 col-md-12">
		<?php if($projectArr['ProjectSetting']['is_disclaimer'] == 0) { ?>
			<?php echo $this->Form->button('Submit',array('class'=>'btn btn-primary center-block','type'=>'submit','align'=>'right','id'=>'save'));
		} else if($projectArr['ProjectSetting']['is_disclaimer'] == 1) { ?>
			 <a data-toggle='modal', onclick="openModel();"> <?php echo $this->Form->button('Proceed', array('class'=>'btn btn-primary center-block','id'=>'save','type'=>'submit')); ?></a> 
		<?php } ?>

	</div>
	<?php echo $this->Form->end(); ?>
	
<div class="modal fade" id="confirm-submit" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
  		<div class="modal-content">
    		<div class="modal-header">
    			<h4 class="modal-title">Quality Checklist</h4>
    		</div>
    		<div class="modal-body">
    			<div class="div1">
    				<div class="row">
    					<div class="col-sm-10">
    						<p>I have thought about when I am going to start this activity and have put it on my calendar too so I don’t forget.</p>
    					</div>
    					<div class="col-sm-2">
    						<br><i class="em em-slightly_smiling_face"></i> <i class="em em-disappointed"></i>
    					</div>
    				</div>
				</div><br>
				<div class="div1">
					<div class="row">
    					<div class="col-sm-10">
							<p>I have not just revamped an existing organizational practice. I have really thought about how I am going to make it more GIFTLIKE – have put in more time, more effort, more concern.
						</div>
						<div class="col-sm-2">
							<br><br><i class="em em-slightly_smiling_face"></i> <i class="em em-disappointed"></i></p>
						</div>
					</div>
				</div><br>
				<div class="div1">
					<div class="row">
    					<div class="col-sm-10">
							<p>To make it successful, I have thought of some templates and frameworks that will help me execute the practice.
						</div>
						<div class="col-sm-2">
							<br><i class="em em-slightly_smiling_face"></i> <i class="em em-disappointed"></i></p>
						</div>
					</div>
				</div><br>
				<div class="div1">
					<div class="row">
    					<div class="col-sm-10">
							<p>I know how I am going to measure, if my action has been a success. I can think of 1 business metric it is likely to impact.
						</div>
						<div class="col-sm-2">
							<br><i class="em em-slightly_smiling_face"></i> <i class="em em-disappointed"></i></p>
						</div>
					</div>
				</div><br>
				<div class="div1">
					<div class="row">
    					<div class="col-sm-10">
							<p>I know I am going to be able to sustain this practice and it wont just be the flavour of the month.
						</div>
						<div class="col-sm-2">
							<br><i class="em em-slightly_smiling_face"></i> <i class="em em-disappointed"></i></p>
						</div>
					</div>
				</div><br>
				<p>Do you think you want to go back and make some minor edits to your plan?
				YES/NO <br><br>
				If YES - Click on Go Back Button.<br>
				If NO - Click on Submit Button,</p>

				<div class="modal-footer">
	    		<?php echo $this->Form->button('Go Back',array('class'=>'btn btn-default','type'=>'button','id'=>'cancel','data-dismiss'=>'modal'));?>
	      		<?php echo $this->Form->button('Submit',array('class'=>'btn btn-primary','type'=>'submit','id'=>'modalForm')); ?>
	      	</div>
    	</div>
    </div>
</div>

<script type="text/javascript">
  	$(document).ready(function() {
		modalConfig();
	    $('#modalForm').click(function(){
			$('#confirm-submit').modal('hide');
			$('#form').submit();
		});

  	});


  	function checkFields() {
  		if($('#name').val() == '' || $('#project_practice_area_id option').filter(':selected').val() == '' || $('#TestId').find('input[type=checkbox]:checked').length == 0 || $('#SecTestId').find('input[type=checkbox]:checked').length == 0 || $('#measure_of_success').val() == '' || $('#descriptions').val() == '' || $('#start_date').val() == '' || $('#frequency_id option').filter(':selected').val() == '') {
			$("#save").attr('disabled','disabled');
		} else {
			$("#save").attr('disabled',false);
		}
  	}


  	function openModel() {
  		$('#confirm-submit').modal('show');	
  	}
</script>
<script type="text/javascript">
	$(document).ready(function() {
		config();

		var description = <?php echo json_encode($projectpracticdescription); ?>;
		var practiceName = $("#project_practice_area_id :selected").val();
		  	if(practiceName in description) {
		  		$.each(description, function(key, value) {
		  			if(key == practiceName) {
		  				$("#description").remove();
		  				if(value != null && value.length != 0) {
		  					$("#desc").append('<p style="margin-left: 10px;" id="description"><?php echo $this->Html->tag('span', '',array('class' => 'glyphicon glyphicon-info-sign')); ?><b>Practice Description: </b><br><span><small><i>'+ value +'</i></small></span></p>');
		  				}
		  			} 
		  		});
		  	} else {
		  		$("#description").remove();
		  	}
		<?php if (!($yesterday_stamp < $action_plan_due_date_stamp)) { ?>
			disable_form();
		<?php } ?>

		//@ 27/02/2018 by Pooja Ganvir, REQ-4737
		
		$("#project_practice_area_id").on('change',function() {
			var practiceName = $("#project_practice_area_id :selected").val();
		  	if(practiceName in description) {
		  		$.each(description, function(key, value) {
		  			if(key == practiceName) {
		  				$("#description").remove();
		  				if(value != null && value.length != 0) {
		  					$("#desc").append('<p style="margin-left: 10px;" id="description"><?php echo $this->Html->tag('span', '',array('class' => 'glyphicon glyphicon-info-sign')); ?><b>Practice Description: </b><br><span><small><i>'+ value +'</i></small></span></p>');
		  				}
		  			} 
		  		});
		  	} else {
		  		$("#description").remove();
		  	}
		});
		$(function () {
			var val = $('#name, #project_practice_area_id option, #TestId, #SecTestId, #description, #start_date, #frequency_id option').val().trim();
		    if (val == '') {
	            $("#save").attr('disabled','disabled');
        	} else if (val.length > 0) {
				$("#save").attr('disabled',false);
			}
		});
	});
</script>	
<style type="text/css">
	.div1 {    
	    padding: 20px;
	    border: 1px;
	    background-color: lightblue;
	    border-radius: 6px;
	}
</style>

