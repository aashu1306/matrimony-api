<?php //echo "i m here";pr($socialTagArr);exit;?><?php
$userId = $participantId = $userType = $participantType = $projectId = NULL;
$roleClass = '';
if ($this->Session->check('User')) {
    $userId = $this->Session->read('User.id');
    $userType = $this->Session->read('User.type');
    $checkUser = 1;
}
elseif ($this->Session->check('Participant')) {
    $participantId = $this->Session->read('Participant.id');
    $participantType = $this->Session->read('Participant.type');
    $projectId = $this->Session->read('Participant.project_id');
    $checkUser = 0;
}?>
<div>
<?php //echo $this->Html->image('loading.gif', array('alt'=>'loding','id'=>'spinner')); ?>
</div>
<?php if (!$this->request->is('ajax')) { ?>
<div class="block-header">
	<h2>
		<?php echo __('Welcome to Journey Social!!'); ?> 
	</h2>
</div>

<?php } ?>
<!-- Widgets -->
<div class="row clearfix main-jou-soci-sec">
	<?php //if (!$this->Session->read('Participant.type') == 'participant' && !$this->Session->read('Participant.type') == 'reporting_manager' ) {?>
	<!-- <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12"> -->
	<?php //} else { ?>
	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
	<?php //} ?>
    	<?php if (!$this->request->is('ajax')) { ?>
    		<div class="alert alert-info lighter-alert alert-dismissable"  role="alert">
        		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
       			<?php echo __('<p>We invite you to use this Social Media platform to connect and collaborate
                              with your peers!!</p><p>Share your ideas/action plans, seek feedback and suggestions and 
    also share stories of implementation. Use <strong>Upvote</strong> and <strong>Comment</strong> to recognize and encourage
     your peers!!</p><p>Facilitators and Great Place To Work&#xae; Team will give star rating, recommend good Action Plans, share
     articles & suggestions from time to time and also answer queries and help in any way possible.</p>');?>
     		</div>
    		<?php echo $this->Session->flash('feed'); ?>
    		<!-- Filter section -->
    		
    		<?php if ($userType == 'admin') { ?>
    		<a href="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'feed',1)); ?>">
    			<?php echo $this->Form->button(__('View Shared Post'), array('class' => 'btn btn-info pull-right input')) ?>
    		</a>
    		<?php } ?>
    		<?php echo $this->Form->button(__('Create Post'), array('class' => 'btn btn-info waves-effect pull-right','id' => 'c_post','data-toggle' => 'collapse', 'data-target'=>'#create-post')); ?>
    		<div class="clearfix"></div>
            <hr>
            <?php echo $this->Form->create('Post', array('url' => array('action' => 'feed'),'class' =>"form-inline")); ?>
            <div class="row">
    			<?php if ($this->Session->check('User')) { ?>
    			    <div class="col-sm-6 col-xs-12">
        			    <div class="form-group">
            			    <?php echo $this->Form->input('project_id', array('options'=>$projectList,'empty' => __('Project'),'class' => 'form-control show-tick', 'data-live-search' => true, 'multiple' => true,'required' =>false,'label' => false, 'div'=>false)); ?>
            			</div>
            		</div> 
    			<?php } ?>
    			<?php if ($this->Session->check('User')) { ?>
    				<div class="col-sm-6 col-xs-12">
        			    <div class="form-group">
            			    <?php echo $this->Form->input('batch_id', array('options'=>$batchList,'empty' => __('Batch'),'class' => ' form-control show-tick', 'data-live-search' => true, 'multiple' => true,'required' =>false,'label' => false, 'div'=>false)); ?>
    					</div>
    				</div>
    			<?php } ?>
    			<div class="col-sm-4 col-xs-12">
                    <div class="form-group">
                        <div class="form-line">		
    						<?php echo $this->Form->input('keyword', array('class' => 'form-control', 'placeholder' => __('   Enter keyword'), 'label' => false, 'required' =>false, 'div'=>false)); ?>		
    					</div>
    				</div>
    			</div>
    			<div class="col-sm-4 col-xs-12">
                    <div class="form-group">
    					<?php echo $this->Form->input('practice_area', array('class' => 'form-control show-tick', 'data-live-search' => true, 'options' => $practiceAreaData, 'empty' => __('Select Practice Area'),'label' => false, 'required' =>false, 'div'=>false)); ?>
    				</div>
    			</div>
    			<div class="col-sm-4 col-xs-12">
                    <div class="form-group">
    					<?php echo $this->Form->input('ugift', array('class' => 'form-control show-tick', 'data-live-search' => true, 'options' =>  $ugiftData, 'empty' => __('Select UGIFT'),'label' => false, 'required' =>false, 'div'=>false)); ?>
    				</div>
    			</div>
    			<div class="col-sm-4 col-xs-12 m-t-10 pull-right">
    				<?php echo $this->Form->button('<i class="material-icons">search</i>', array('type' => 'submit', 'div' => false, 'class' => 'btn btn-info waves-effect')); ?>
    				<?php echo $this->Form->input('sort_by',array('type' => 'hidden')) ?>
        			<div id="sort_by" class="pull-right dropdown">
            			<button type="button" class="pull-right btn btn-info waves-effect user-helper-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            			<!-- <a href="javascript:void(0);" title="<?php //echo __('Sort by'); ?>" data-toggle="dropdown" class="dropdown-toggle" aria-expanded="true"> -->
            				<i class="material-icons">swap_vert</i><?php echo __('Sort by'); ?>
            			<!-- </a> -->
            			</button>
            			<ul class="dropdown-menu pull-right">
            				<li><a href="javascript:void(0);" rel="created" onclick="sortFeed(this.rel);" class=" waves-effect waves-block"><?php echo __('Most Recent'); ?></a>
            				</li>
            				<li><a href="javascript:void(0);" rel="upvoted" onclick="sortFeed(this.rel);" class=" waves-effect waves-block"><?php echo __('Most Upvotes'); ?></a>
            				</li>
            				<li><a href="javascript:void(0);" rel="commented" onclick="sortFeed(this.rel);" class=" waves-effect waves-block"><?php echo __('Most Comments'); ?></a>
            				</li>
            				<li><a href="javascript:void(0);" rel="rating" onclick="sortFeed(this.rel);" class=" waves-effect waves-block"><?php echo __('Star Ratings'); ?></a>
            				</li>
            			</ul>
                        <!-- /.dropdown-user -->
            		</div>
            	</div>
            </div>
        	<div class="row clearfix">    
                <div class="col-xs-12 chk-btn-reco-shr">
                    <div class="form-group">
                        <div class="demo-checkbox">
                            <div class="col-sm-4 col-xs-12 padding-0">
        						<?php echo $this->Form->input('recommended', array('class' => 'filled-in chk-col-light-blue' , 'type' =>  'checkbox', 'label' => __('Recommended by GPTW'), 'required' =>false,'onchange' => "$('#PostFeedForm').submit();")); ?>
    						</div>
                            <div class="col-sm-4 col-xs-12 padding-0">
    							<?php echo $this->Form->input('shared_by_me', array('class' => 'filled-in chk-col-light-blue' , 'type' =>  'checkbox', 'label' => __('Shared by Me'), 'required' =>false,'onchange' => "$('#PostFeedForm').submit();")); ?>
    						</div>
                        </div>
                    </div>
                </div>
            </div>
    		<?php echo $this->Form->end(); ?>
    <!-- Start of Create Post panel -->
    		<hr class="collapse-hr">
    		<?php if ($userType == 'admin' || $userType == 'sec_coordinator' || $userType == 'pri_coordinator' || $userType == 'batch_anchor' || $userType == 'facilitator' || $userType == 'jury_member' || $participantType == 'participant' || $participantType == 'reporting_manager') { ?>
    			<!-- Add Post -->
    			<div id='create-post' class="collapse create-pst" aria-expanded="false" style="height: 0px;">
    				<div class="well">
    
    					<?php echo $this->Form->create('Post', array('url' => array('action'=>'add')));
    
    					if ($userType == 'admin' || $userType == 'sec_coordinator' || $userType == 'pri_coordinator' || $userType == 'batch_anchor' || $userType == 'facilitator' || $userType == 'jury_member' ) {
                        ?>
    					<div class="form-group">
                           <!--  <div class="form-line"> -->
    					    	<?php echo $this->Form->input('project_id', array('options'=>$projectList,'placeholder' => __('Project'),'class' => 'form-control', 'empty' => __('Select Project'),'id' => 'projectlist' , 'required' => true, 'div' => array('style' => 'width : 50%;margin-left:0;'),'label' => __('Share with'))); ?>
    						<!-- </div> -->
    					</div>
    					<?php } ?>
    					<div class="form-group">
                            <div class="form-line">
    							<?php echo $this->Form->input('Post.name',array('type'=>'text','class' => 'form-control', 'placeholder' => 'Type in your Title', 'div' => array('style' => 'margin-left:0;'), 'label' => false, 'required' => true)); ?>
    						</div>	
    					</div>
    					<div id="tags_input">
                            <!-- <div class="form-group"> -->
                                <!-- <div class="form-line"> -->
            						<?php
                                        echo $this->Form->input('Post.tagsText', array('id'=> 'demo_input','type'=>'select', 'multiple'=>true,'options' =>[],'class' => 'form-control','placeholder'=>'Tag project Participant','div' => array('style' => 'margin-left:0;'),'id'=>'demo_input','label' => false/*, 'style' => 'width : 50%;margin-left:0;'*/));
                                        echo $this->Form->input('Post.tagsId', array('id'=> 'tags_id','type'=>'hidden'/*,'options' =>[]*/));
            						?>
                                <!-- </div> -->
                            <!-- </div> -->

                            
    					</div>
    					<div class = "clear"></div>
    					<div class="body">
    						<?php echo $this->Form->textarea('Post.description',array('class' => 'form-control postRichText', 'placeholder'=> __('Share your thoughts...'), 'required' => true)); ?>
    					</div>
    					<?php  echo $this->Form->submit(__('Post New Message'), array('class' => 'btn btn-info waves-effect pull-right post-msg-btn')); ?>
    					<?php echo $this->Form->end(); ?>
    					<div class="clearfix"></div>
    				</div>
    			</div>
    		<?php } ?>
    
    		<div class="clear"></div>
    	<?php } ?>
    <!-- End of Create Post panel -->
		<div class="main-content-sec">
    	<?php foreach ($postData as $key=>$post) { ?>
    		<div class="main-post">
    			<div class="row">
    				<div class="col-xs-2 post-img">
    					<?php if (!empty($post['User']['id'])) {
    						$href = $this->Html->url('/img/gptw-logo.jpg',true); 
    					}
    					else {
    						$href = $this->requestAction(array('controller' => 'participants', 'action' => 'photo_url'), array('pass' => array($post['Participant']['id']))); 
    					}?>
    					<img width="100%" class="img-responsive center-block" alt="<?php echo __('No Pic'); ?>" src="<?php echo $href; ?>">
    				</div>
    
    				<div class="col-xs-10 main-post-right-content">
    					<div class="main-post-profile-name">
    						<p>
        						<a class="font-bold post-profile-name" href="<?php echo (!empty($post['User']['id']))?$this->Html->url(array('controller'=>'users','action'=>'profile',$post['User']['id'])):$this->Html->url(array('controller'=>'participants','action'=>'profile',$post['Participant']['id'])); ?>">
                					<?php echo (!empty($post['User']['name']))?$post['User']['name']:$post['Participant']['name']; ?>
                				</a>
        						
            						<?php if (!empty($post['ActionPlan']['id'])) {
            						 	echo __('shared Action plan.');
            						}elseif (!empty($post['ImplementationStory']['id'])) {
            						 	echo __('shared Implmentation for action plan.');
            						}elseif (!empty($post['Post']['name']) || !empty($post['Post']['description'])) {
            							 echo __('shared Post.');
            						} ?>
            					
            					<br>
            					<span class="profile-hr">
            						<i class="fa fa-clock-o" aria-hidden="true"></i>
            						<?php echo $this->Time->timeAgoInWords($post['Post']['created'],array('format' => 'F jS, Y','accuracy' => array('hours' => 'hours'),'end' => 'today')); ?>
            					</span>
        					</p>
        					<div class='coment-star rating-stars text-center'>
        						<?php if (!empty($post['ActionPlan']['id']) && $participantType == 'participant' && $projectId == $post['Post']['project_id'] && $participantId != $post['ActionPlan']['participant_id']) { ?>
        							
        							<a class="action-copy" href="<?php echo $this->Html->url(array('controller'=>'action_plans','action'=>'borrow',$post['ActionPlan']['id'])); ?>" title="<?php echo __('Borrow Action Plan'); ?>">
        								<i class="material-icons">content_copy</i>
        							</a>

        						<?php } 
        						if (($userType == 'admin') || ($userType == 'facilitator')) { ?>
        							
        							<a title="<?php echo __('Remove Post'); ?>" onclick="return false;" data-href="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'delete',$post['Post']['id'])); ?>" data-toggle="modal" data-target="#deleteModal" data-body="<?php echo __('Are you sure you want to remove this post?')?>">
        								<i class="material-icons">delete</i>
        							</a>
        						<?php } ?>
                                <span class="pull-right">
        						<?php 
        						if (!empty($post['ActionPlan']['id']) || !empty($post['ImplementationStory']['id'])) { ?>
        							<?php if ($post['Post']['recommended_count'] > 0) { ?>
        								<span title="<?php echo __('Recommeneded'); ?>" >
        									<i class="fa fa-trophy fa-2x " style="color:gold;"></i>
        								</span>
        							<?php } ?>
    								<?php
    								if (isset($socialTagArr[$post['Post']['id']]) && !empty($socialTagArr[$post['Post']['id']])) {
    						            foreach ($socialTagArr[$post['Post']['id']] as $type  => $userDetails) {
    										if ($type == 'participant') {
    											foreach ($userDetails as $partiid => $roleId) {
    								
    												if ($roleArr[$roleId] == 'reporting_manager' ) {
    													$roleClass = 'label label-info-new';
    												}else if ($roleArr[$roleId] == 'participant' ) {
    													$roleClass = 'label label-primary';
    												}?>
    												<span class= "<?php echo $roleClass?>">
    													<?php echo $partiList[$partiid];?>
    												</span>
    											<?php }
    										}
                        					if ($type == 'user') {
    											foreach ($userDetails as $userIId => $roleid) {
    
    												if ($roleArr[$roleid] == 'sec_coordinator' ) {
    													$roleClass = 'label label-warn-new label-important';
    													//echo $userList[$userIId];
    												}
                                                    if ($roleArr[$roleid] == 'pri_coordinator' ) {
    													$roleClass = 'label label-new-warning  label-important';
    													//echo $userList[$userIId];
    												}
    												if ($roleArr[$roleid] == 'facilitator' ) {
    													$roleClass = 'label label-success-new';
    													//echo $userList[$userIId];
    												}
    												if ($roleArr[$roleid] == 'batch_anchor' ) {
    													$roleClass = 'label label-new';
    													//echo $userList[$userIId];
    												}
    												if ($roleArr[$roleid] == 'jury_member' ) {
    													$roleClass = 'label label-default-new';
    													//echo $userList[$userIId];
    												}
    												if ($roleArr[$roleid] == 'admin' ) {
    													$roleClass = 'label label-danger';
    													//echo $userList[$userIId];
    												}?>
    												<span class= "<?php echo $roleClass?>"><?php echo $userList[$userIId];?></span>
    			  							<?php }
                    						}
    									}
    								}?> </span>
        							<?php if (($userType == 'admin') || ($userType == 'facilitator')) { ?>
        								<a href="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'rate',$post['Post']['id']));?>" title="<?php echo __('Rate Post'); ?>">
        									<span style="" class="display-star" title="<?php echo __('Ratings'); ?>">
        										<?php echo $post['Post']['avg_rating']; ?>
        									</span>
        								</a>
        							<?php } 
        							else { ?>
        								<span class="display-star" title="<?php echo __('Ratings'); ?>">
        									<?php echo $post['Post']['avg_rating']; ?>
        								</span>
        								
        							<?php } ?>
        						<?php } ?>
        					</div>
        				</div>
                        <p>
                            <?php 
                            if (isset($socialTagArr[$post['Post']['id']]) && !empty($socialTagArr[$post['Post']['id']])) {
                                foreach ($socialTagArr[$post['Post']['id']] as $type  => $userDetails) {
                                    if ($type == 'participant') {
                                        foreach ($userDetails as $partiid => $roleId) {
                                            
                                            if ($roleArr[$roleId] == 'reporting_manager' ) {
                                                $roleClass = 'label label-info-new';
                                                
                                            }elseif ($roleArr[$roleId] == 'participant' ) {
                                                $roleClass = 'label label-primary';
                                                
                                            }?>
                                            <span class= "<?php echo $roleClass?>"><?php echo $partiList[$partiid];?></span>
                                        <?php }
                                    }
                                    
                                    if ($type == 'user') {
                                        foreach ($userDetails as $userIId => $roleid) {

                                            if ($roleArr[$roleid] == 'sec_coordinator' ) {
                                                $roleClass = 'label label-warn-new label-important';
                                                //echo $userList[$userIId];
                                            }

                                            if ($roleArr[$roleid] == 'pri_coordinator' ) {
                                                $roleClass = 'label label-new-warning  label-important';
                                                //echo $userList[$userIId];
                                            }
                                            if ($roleArr[$roleid] == 'facilitator' ) {
                                                $roleClass = 'label label-success-new';
                                                //echo $userList[$userIId];
                                            }
                                            if ($roleArr[$roleid] == 'batch_anchor' ) {
                                                $roleClass = 'label label-new';
                                                //echo $userList[$userIId];
                                            }
                                            if ($roleArr[$roleid] == 'jury_member' ) {
                                                $roleClass = 'label label-default-new';
                                                //echo $userList[$userIId];
                                            }
                                            if ($roleArr[$roleid] == 'admin' ) {
                                                $roleClass = 'label label-danger';
                                                //echo $userList[$userIId];
                                            }?>
                                            <span class= "<?php echo $roleClass?>"><?php echo $userList[$userIId];?></span>
                                        <?php }
                                        
                                    }
                                
                                }
                            }?>
                        </p>
        				<div class="coment-area">
        					<?php if (!empty($post['ActionPlan']['id'])) { ?>
        						<p><h4><?php echo h($post['ActionPlan']['name']); ?></h4></p>
        						<p>
        							<?php echo substr(h($post['ActionPlan']['description']), 0,200); ?>
        							<a href="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'detail',$post['Post']['id'])); ?>">
            							<?php echo __('Read more'); ?>
            						</a>
        						</p>
        					<?php }
        					else if (!empty($post['ImplementationStory']['id'])) { ?>
        						<p><h4><?php echo h($post['ImplementationAction']['name']); ?></h4></p>
        						<p>
            						<?php echo substr(h($post['ImplementationStory']['implementation_story']), 0,200); ?>
            						<a href="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'detail',$post['Post']['id'])); ?>">
            							<?php echo __('Read more'); ?>
            						</a>
            					</p>
        						<?php if (count($post['Attachment']) > 0) { 
        							$urlCount = $this->requestAction(array('controller'=>'implementation_stories','action'=>'get_url_count',$post['ImplementationStory']['id']));
        							$uploadCount = $this->requestAction(array('controller'=>'implementation_stories','action'=>'get_upload_count',$post['ImplementationStory']['id']));?>
        							<br/>
        							<div class="clear"></div>
        							<?php 
        							if ($uploadCount > 0) {
        								$uploadData = $this->requestAction(array('controller' => 'implementation_stories', 'action' => 'get_upload_data'), array('pass' => array($post['ImplementationStory']['id'])));
        								foreach ($uploadData as $key =>$attachment) { ?>
        									<div class="col-lg-3 col-md-4 col-xs-6 thumb">
        										<a class="livepreview thumbnail" href="<?php echo $this->Html->url(array('controller' => 'implementation_stories', 'action' => 'download_attachment',$attachment['ImplementationStoryAttachment']['id'])); ?>">
    												<?php $file_ext = strtolower($attachment['ImplementationStoryAttachment']['file_ext']);
    												if (in_array($file_ext, array('tif','tiff','jpg','jpeg','png','gif'))) {
    													$href = $this->requestAction(array('controller' => 'implementation_stories', 'action' => 'authenticated_url'), array('pass' => array($attachment['ImplementationStoryAttachment']['id'])));
    												} 
    												else {
    													$href = $this->Html->url('/img/custom_icons/file.png',true);
    												} ?>
    												<img src="<?php echo $href; ?>" alt="<?php echo $attachment['ImplementationStoryAttachment']['file_name']; ?>" title="<?php echo $attachment['ImplementationStoryAttachment']['file_name']; ?>">
        										</a>
        									</div>
        							<?php } 
        							}
                                    if($urlCount > 0){
        								$urlData = $this->requestAction(array('controller' => 'implementation_stories', 'action' => 'get_url_data'), array('pass' => array($post['ImplementationStory']['id'])));
        								foreach ($urlData as $key =>$attachment) { ?>
        									<div class="col-lg-3 col-md-4 col-xs-6 thumb">
        										<!-- 	<i class="fa fa-external-link" aria-hidden="true"></i> -->
        										<?php $url = $attachment['ImplementationStoryAttachment']['file_name'];
        										$imgurl = "https://www.google.com/s2/favicons?domain=" . $url; ?>
        										<a  class="livepreview thumbnail"  target="_blank" title = '<?php echo $attachment['ImplementationStoryAttachment']['file_name'] ?>' href = '<?php echo  $attachment['ImplementationStoryAttachment']['file_name']; ?>'>
        											<?php echo '<img src="' . $imgurl . '" width="20" height="20" />';?>
        										</a>
        									</div>
        							<?php }
        							} ?>
        							<div class="clear"></div> <?php	
        						}
        					} 
        					else if (!empty($post['Post']['name']) || !empty($post['Post']['description'])) { ?>
        						<?php echo (($post['Post']['name'] != '')?'<p><h4>'.$post['Post']['name'].'</h4><p>':''); ?>
        						<p><?php echo substr(strip_tags($post['Post']['description']), 0,200); ?>
            						<br/>
            						<a href="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'detail',$post['Post']['id'])); ?>">
            							<?php echo __('Read more'); ?>
            						</a>
        						</p>
        					<?php } ?>
        				</div>
        				
        				<div class="like-shr-sec">
                            <p>
            					<a class="<?php echo (!empty($post['UpvoteSelf']['id']))?'upvote-thumb-active':'upvote-thumb-inactive'?>" href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" data-title="Like" data-target="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'upvote',$post['Post']['id'])); ?>">
            						<i class="fa fa-thumbs-up" aria-hidden="true"></i>
            						<span class="upvote_count"> 
                                        <?php echo ($post['Post']['upvote_count'] > 1)?$post['Post']['upvote_count'].'&nbsp;'.__('Upvotes'):$post['Post']['upvote_count'].'&nbsp;'.__('Upvote'); ?>
                                    </span>
            					</a>
            					<a>
                					<i class="fa fa-comments-o" aria-hidden="true"></i>
                					<span>
                					<?php echo ($post['Post']['comment_count'] > 1)?$post['Post']['comment_count'].__(' Comments'):$post['Post']['comment_count'].__(' Comment');$post['Post']['comment_count']; ?>
                					</span>
            					</a>
                            </p>
        				</div>
    					<div class="clear"></div>
        				<div class="comment-box">
        					<div class="status-upload">
        					<?php if ($post['Post']['comment_count'] > 0) { ?>
        						<!-- View Comments -->
        						<div class="main-show-comment collapsed view-all-comments">
        							<a class="view-comments" style="text-decoration:none;" href="javascript:void(0);" data-target="<?php echo $this->Html->url(array('controller'=>'posts','action'=>'comment_list',$post['Post']['id'])); ?>">
        								<span class="font-light"><i class="fa fa-comments-o" aria-hidden="true"></i>
        									<?php echo __('View all comments'); ?>
        								</span>
        							</a>
        							<br>
        						</div>
        					<?php } ?>
        					<?php if (!empty($commentData[$post['Post']['id']])) {
        						$dp = $commentData[$post['Post']['id']]['display_name'];?>
        						<div class="row comments">
        							<div class="col-xs-4 col-sm-4 col-md-1">
        								<?php foreach ($commentData[$post['Post']['id']] as $field => $data) {
        								
        								?>
        										<?php
        											if ($field == 'href_flag' ) {
        												if ($data == 'user') {
        													$href1 = $this->Html->url('/img/gptw-logo.jpg',true);
        												}
        												else{
        													$href = $data;
        												}?>
        												<img width="100%" class="img-responsive img-thumbnail center-block" alt="<?php echo $dp; ?>" src="<?php echo $href; ?>"> <?php 
        											}
        										?>
        									
        
        								<?php }
        								$commentedBy =  $dp; ?>
        							</div>
        							<div class="col-xs-10 col-sm-10 col-md-10">
        								<p>
        									<?php echo nl2br(h($commentData[$post['Post']['id']]['comment'])); ?>
        									<?php
        									if (isset($socialTagArr_Comment[$commentData[$post['Post']['id']]['comment_id']]) && !empty($socialTagArr_Comment[$commentData[$post['Post']['id']]['comment_id']])) {
        										foreach ($socialTagArr_Comment[$commentData[$post['Post']['id']]['comment_id']] as $type  => $userDetails) {
        											if ($type == 'participant') {
        												foreach ($userDetails as $partiid => $roleId) {
        							
        												if ($roleArr_Comment[$roleId] == 'reporting_manager' ) {
        													$roleClass = 'label label-info-new';
        												} 
        												else if ($roleArr_Comment[$roleId] == 'participant' ) {
        													$roleClass = 'label label-primary';
        												}?>
        												<small><span class= "<?php echo $roleClass?>">
        													<?php echo $partiList_Comment[$partiid];?>
        												</span></small>
        											<?php }
        											}
        											if ($type == 'user') {
        												foreach ($userDetails as $userIId => $roleid) {
        
        												if ($roleArr_Comment[$roleid] == 'sec_coordinator' ) {
        													$roleClass = 'label label-warn-new label-important';
        													//echo $userList[$userIId];
        												}
        
        												if ($roleArr_Comment[$roleid] == 'pri_coordinator' ) {
        													$roleClass = 'label label-new-warning  label-important';
        													//echo $userList[$userIId];
        												}
        												if ($roleArr_Comment[$roleid] == 'facilitator' ) {
        													$roleClass = 'label label-success-new';
        													//echo $userList[$userIId];
        												}
        												if ($roleArr_Comment[$roleid] == 'batch_anchor' ) {
        													$roleClass = 'label label-new';
        													//echo $userList[$userIId];
        												}
        												if ($roleArr_Comment[$roleid] == 'jury_member' ) {
        													$roleClass = 'label label-default-new';
        													//echo $userList[$userIId];
        												}
        												if ($roleArr_Comment[$roleid] == 'admin' ) {
        													$roleClass = 'label label-danger';
        													//echo $userList[$userIId];
        												}?>
        												<small><span class= "<?php echo $roleClass?>"><?php echo $userList_Comment[$userIId];?></span></small>
        				  							<?php }
        						
        											}					
        										}
        									}?>
        									
        									<br><small class="text-muted"><i class="fa fa-clock-o fa-fw"></i><?php echo $dp." commented  ".$this->Time->timeAgoInWords($commentData[$post['Post']['id']]['created'],array('format' => 'F jS, Y','accuracy' => array('hours' => 'hours'),'end' => 'today')); ?></small>
        								</p>
        							</div>
        						</div>
        					<?php } ?>
        			
        						<div>
        							<?php echo $this->Form->create('Post', array('class' => '', 'url' => array('action' => 'comment')));
        							
        							echo $this->Form->input('Post.post_id', array('type' => 'hidden','id'=>"post_$key", 'value' => $post['Post']['id']));
        							echo $this->Form->input('Post.project_id', array('type' => 'hidden', 'id'=>"project_$key",'value' => $post['Post']['project_id']));
        							?>
        							<div class="form-line">
        								<?php echo $this->Form->textarea('Post.comment',array(/* 'type' => 'text', */'id'=>"comment_$key",'class'=>'mentions form-control',/* 'placeholder'=> __('Write Comment...'), */'label' => false, 'data-emojiable' => 'true' , 'data-emoji-input'=>"unicode")); ?>
        							</div>
        							<div class="clearfix"></div>
        							<a title="Tag a user" id="<?php echo $key; ?>" data-toggle='modal'  onclick="openModel('<?php echo $key; ?>'); return false;"> 
        								<?php echo $this->Form->button('<i class="material-icons">comment</i>&nbsp;'.__('Comment'), array('class'=>'btn bg-green waves-effect pull-right comment-btn')); ?>
        							</a>
        							<div class="clearfix"></div>
        							<?php echo $this->Form->end(); ?>
        						</div>
        					</div><!-- Status Upload  -->
        				</div><!-- Comment Box -->
            		</div><!-- Main Post Right Content -->
        		</div><!-- End of Row -->
        	</div><!-- End of Main post -->
    	<?php } ?>
    	</div><!-- End of Main Content Sec  -->
    	<div class="row top-margin">
			<div class="col-xs-12">
				<?php echo $this->Paginator->next('Load More', array('tag' => false,'onclick'=>'return false;','class'=>'btn btn-primary load-more pull-right'), null, array('class'=>'disabled hide')); ?>
			</div>	
		</div>
    </div>
    <?php  
    if ($this->Session->read('Participant.type') == 'participant' || $this->Session->read('Participant.type') == 'reporting_manager') {?>
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 main-pin-file-sec hidden-xs">
	<?php 
	if (!empty($pinnedFiles) and !$this->request->is('ajax')) {?>
		<div class="alert alert-info lighter-alert pin-file-sec">
            <a href="#" id="closeFiles" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<p><i class="fa fa-paperclip fa-fw"></i> Pinned Files</p>
            <hr class="pin-hr">
			<?php 
			foreach ($pinnedFiles as $key => $pin) {
				$detail_url = array('controller'=>'social_repositories','action'=>'download_document',$pin['SocialRepository']['id']);?>
				<ul>
					<li class = 'xyz'>
						<a href="<?php echo $this->Html->url($detail_url);?>"  >
							<?php echo $pin['SocialRepository']['description']; ?>
						</a>
					</li>
				</ul>
				<hr style="margin-bottom: 5px; margin-top: 5px; border-top: dotted 1px;">
			<?php } ?>
		</div>
	<?php }?>	
	</div>
	<?php if (!empty($pinnedFiles) and !$this->request->is('ajax')) {?>
		<button type="button" class="btn btn-info waves-effect pin-clip-mob visible-xs"><i class="fa fa-paperclip fa-fw"></i></button>
    <?php }
    } else{ ?>
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 main-pin-file-sec hidden-xs">
	<?php 
	 if (!empty($pinnedFiles)  and !$this->request->is('ajax')) {?>
		<div class="alert alert-info lighter-alert pin-file-sec">
            <a href="#" id="closeFiles" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<p><i class="fa fa-paperclip fa-fw"></i> Pinned Files</p>
            <hr class="pin-hr">
			<?php foreach ($pinnedFiles as $key => $pin) {
			$detail_url = array('controller'=>'social_repositories','action'=>'download_document',$pin['SocialRepository']['id']);?>
			<ul>
				<li class = 'xyz'>
					<a class="pinnLinks" style="color:#000" href="<?php echo $this->Html->url($detail_url);?>"  >
						<?php echo $pin['SocialRepository']['description']; ?>
					</a>
				</li>
			</ul>
			<hr style="margin-bottom: 5px; margin-top: 5px; border-top: dotted 1px;">
			<?php } ?>
		</div>
	<?php }?>	
	</div>
	<?php //if (!empty($pinnedFiles) and !$this->request->is('ajax')) {?>
		<button type="button" class="btn btn-info waves-effect pin-clip-mob visible-xs"><i class="fa fa-paperclip fa-fw"></i></button>
<?php // }
    } ?>
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<?php 
			echo $this->Form->create('Post', array('onkeypress'=>"return event.keyCode != 13",'class' => 'ajaxModalForm', 'url' => array('controller' => 'posts','action' => 'comment','inputDefaults' => array('label'=>false,'autocomplete'=>false))));
			?>
			<div class="modal-header">
				<h4 class="modal-title">Tag someone</h4>
			</div>
			<div class="modal-body">
	
    	 		<div class="control-group">
	   				<?php echo $this->Form->input('Post.CheckTag', array('type'=>'checkbox','label' => __('Do you wish to tag someone?'),'id'=>'WantToAddTag' ,'class' => 'filled-in chk-col-light-blue','div' => array('class' => 'input checkbox social-check'))); ?>
	
        			<div class="controls">
                      
            			<?php echo $this->Form->input('Post.add_tag', array('id'=> 'tag_input','type'=>'select', 'multiple'=>true,'options' =>[],'class' => 'form-control','placeholder'=>'Tag someone','id'=>'tag_input','label' => false)); ?>

            			<?php	echo $this->Form->input('Post.tagsCmtId', array('id'=> 'tagsCmt_id','type'=>'hidden'/*,'options' =>[]*/));
            	
            				echo $this->Form->input('Post.post_id',array('id'=>'postId','type'=>'hidden')); 
             				echo $this->Form->input('Post.project_id', array('id'=> 'pojectId','type'=>'hidden'/*,'options' =>[]*/)); 
             				echo $this->Form->hidden('Post.comment',array('id'=>'cmtTag')); 
             				 
            			?>
	   				</div>
    			</div>
			</div>
			<div class="modal-footer">
    			<?php echo $this->Form->button('Cancel',array('class'=>'btn btn-link waves-effect','type'=>'button','id'=>'cancel','data-dismiss'=>'modal'));?>
      			<?php echo $this->Form->button('<i class="fa fa-comments-o" aria-hidden="true"></i>&nbsp;'.__('Comment'), array('class'=>'btn btn-link waves-effect','type'=>'submit','id'=>'submitForm')); ?>

			</div>
    	<?php echo $this->Form->end();?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	modalConfig();
    $("#tags_input").find("button").remove();
	$('#submitForm').click(function(){
		$('#myModal').modal('hide');
		setTimeout(function(){
			$("#spinner").show(); 
		    location.reload();
		},2000)
	});
});
function openModel(key){
	$("#WantToAddTag").prop("checked", false);
	$(".controls").hide();
	$("#WantToAddTag").click(function() {
        if($("#WantToAddTag").prop('checked')==true) {
        $(".controls").show();
        $(".bootstrap-select").find('button').remove();
        } else {
            $(".controls").hide();
        }
        Commenttagg();
    });

    var CommentData = $(".mentions").val();
	$("#cmtTag").val(CommentData);
	$(".controls").hide();
	
    function Commenttagg(){
        $("#tag_input").tagsinput('refresh');
        var prjectId = $('#pojectId').val();
        var citynames =  new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: '<?php echo $this->Html->url(array('controller'=>'posts','action'=>'getTags'))?>' + '/' + prjectId + '?x=' + Date(),
        });
        $("#tag_input").tagsinput('destroy');
        citynames.initialize();
        var itemArr = []; var itemString = '';
        $("#tag_input").on('itemAdded', function(event) {
            itemArr.push(event.item.pid+':'+event.item.type);
            itemString = itemArr.join();
            $("#tagsCmt_id").val(itemString);
        });
        $('#tag_input').on('itemRemoved', function(event) {
            itemArr.reverse().shift();
        });
        $("#tag_input").tagsinput({
            tagClass: function(item) {
                $("#tagsCmt_type").val(item.type);
                $("#tagsCmt_id").val(item.pid);
                switch (item.type) {
                    case 'participant'   : return 'label label-primary';
                    case 'reporting_manager'  : return 'label label-info-new';
                    case 'sec_coordinator'  : return 'label label-warn-new label-important';
                    case 'pri_coordinator'  : return 'label label-new-warning  label-important';
                    case 'facilitator'  : return 'label label-success-new';
                    case 'batch_anchor'  : return 'label label-new';
                    case 'jury_member'  : return 'label label-default-new';
                    case 'admin'  : return 'label label-danger';
                }
            },
            itemValue: "pid",
            itemText: "text",
            typeaheadjs: {
                name: 'citynames',
                displayKey: 'text',
                source: citynames.ttAdapter()
            }
        });
    }
	var prjectId = $('#project_'+key).val();
	var postId = $('#post_'+key).val();
	var commentTest = $('#comment_'+key).val();
	$('#postId').val(postId);
	$('#pojectId').val(prjectId);
	$('#cmtTag').val(commentTest);
	$('#myModal').modal('show');
	$('#comment_'+key).val('');
}
</script>
<!-- Modal content-->
<script type="text/javascript">
	$(document).ready(function(){
		var userCheck = '<?php echo $checkUser;?>';
		if (userCheck == 1) {
			$('#c_post').on('click', function() {
				$("#tags_input").hide();
			});
		}
		// Like Button ajax call
		like();
		
		// Comment ajax Call
		comment();

		// View All Comments
		view_comments();
		
		// Load Posts
		load_more();

		// Delete Modal
		post_delete();

		$('span.display-star').stars();

		// Load Emojis
		loadEmojiPicker();

		
	});
</script>
<script>

	$(document).ready(function() {
		
		var userCheck = '<?php echo $checkUser;?>';
		tagg();
	
		if (userCheck == 0) {
				tagg();
		}
		$('#projectlist').on('change', function () {
			tagg();				
	    });

		function tagg(){
			$("#demo_input").tagsinput('refresh');
			var userCheck = '<?php echo $checkUser;?>';
				if (userCheck == 1) {
					$('#c_post').on('click', function() {
						$("#tags_input").hide();
					});
				}
			var userCheck1 = '<?php echo $checkUser;?>';
			if(userCheck1== 1) {
				selectedId = $('#projectlist').val();
			}
			else{
				selectedId = '<?php echo $projectId;?>';
			}
		
			if(selectedId !== '') {
				$("#tags_input").show();
			} else {
				$("#tags_input").hide();
			}
			var citynames =  new Bloodhound({
	        	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
	        	queryTokenizer: Bloodhound.tokenizers.whitespace,
	        	prefetch: '<?php echo $this->Html->url(array('controller'=>'posts','action'=>'getTags'))?>' + '/' + selectedId + '?x=' + Date(),
		    });
	    	$("#demo_input").tagsinput('destroy');
	    	citynames.initialize();
		   	var itemArr = []; var itemString = '';
	    	$("#demo_input").on('itemAdded', function(event) {
	    		itemArr.push(event.item.pid+':'+event.item.type);
	    		itemString = itemArr.join();
				$("#tags_id").val(itemString);
	     	});
	     	$('#demo_input').on('itemRemoved', function(event) {
	     		itemArr.reverse().shift();
			});
			$("#demo_input").tagsinput({
	    		tagClass: function(item) {
	    			$("#tags_type").val(item.type);
	    			$("#tags_id").val(item.pid);
			   	 	switch (item.type) {
			      		case 'participant'   : return 'label label-primary';
			      		case 'reporting_manager'  : return 'label label-info-new';
			      		case 'sec_coordinator'  : return 'label label-warn-new label-important';
			      		case 'pri_coordinator'  : return 'label label-new-warning  label-important';
			      		case 'facilitator'  : return 'label label-success-new';
			      		case 'batch_anchor'  : return 'label label-new';
			      		case 'jury_member'  : return 'label label-default-new';
			      		case 'admin'  : return 'label label-danger';
			    	}
				},
		    	itemValue: "pid",
		    	itemText: "text",
		    	typeaheadjs: {
				    name: 'citynames',
				    displayKey: 'text',
				    source: citynames.ttAdapter()
				}
			});
		}
	});
</script>

<script type="text/javascript">
	
	/* $(document).ready(function() {
		$('.plcholder div').find("textarea").each(function(ev)
        {
	        
            if(!$(this).val()) { 
                //console.log($(this));
//                 console.log("I'm here in placehoder");
//             	$(this).prop("placeholder", "Type your answer here :-)");
        	}
        });
	}); */

</script>

<style type="text/css">
.xyz {
    display: block;
}
/*.xyz span { position: relative; left: -10px; }*/
.xyz:before {
    /*Using a Bootstrap glyphicon as the bullet point*/
    content: "\e146";
    font-family: 'Glyphicons Halflings';
    font-size: 11px;
    width: 10px;
    float: left;
    margin-top: 4px;
    margin-left: -20px;
    color: #B0252D;
    transform: scaleX(-1);
    -moz-transform: scaleX(-1);
    -webkit-transform: scaleX(-1);
    -ms-transform: scaleX(-1);
}

.pinn {
    display: block;
    padding: 4px;
    margin-bottom: 20px;
    line-height: 1.42857143;
    background-color:  #D2EAF5;;
    border: 1px solid #ddd;
    border-radius: 4px;
    -webkit-transition: border .2s ease-in-out;
    -o-transition: border .2s ease-in-out;
    transition: border .2s ease-in-out;
}


.pinnLinks:link {
    color: #000;
}

.pinnLinks:hover {
   
    font-family: monospace;
}

/*.pinnLinks:visited {
    color:  #000;
}*/
#wrapper_1{   
    padding:5px;
    width:100%;
    max-height:400px;
    overflow-y:auto;
}


</style>
<style type="text/css">
	.bootstrap-tagsinput {
		width: 963px;
	}
	.label-new {
  		background-color: #d74442;
	}
	.label-new-warning {
  		background-color: #eb9114;
	}
	.label-default-new {
		background-color: #248f8f; 
	}
	.label-success-new {
		background-color: #2f6a31; 
	}
	.label-info-new {
		background-color: #8585e0; 
	}
	.label-warn-new {
		background-color: #993366; 
	}
	.mypin {
		padding-top: 12px;
	}
</style>
<style type="text/css">
	.loadings {
	  display: none;
	  position: absolute;
	  left: 100%;
	  top: 100%;
	}
	span {
	    margin-right: 2px;
	}
</style>