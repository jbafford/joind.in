<?php
/*
here's what we need
	$event_detail
	$attemd
*/

if ($event_detail->event_cfp_start>=time()) {
    $cfp_status = "pending";
} elseif ($event_detail->event_cfp_start <= time() && time() <= $event_detail->event_cfp_end) {
    $cfp_status = "open";
} else {
    $cfp_status = "closed";
}

?>

<div class="detail">
	
	<div class="header">
        <?php $this->load->view('event/_event-icon',array('event'=>$event_detail)); ?>
    
    	<div class="title">
        	<div class="head">
				<input type="hidden" name="eid" id="eid" value="<?php echo $event_detail->ID; ?>"/>
            	<h1><?php echo escape($event_detail->event_name)?> <?php echo (($event_detail->pending==1) ? '(Pending)':'')?></h1>
            	<p class="info">
					<strong><?php echo $this->timezone->formattedEventDatetimeFromUnixtime($event_detail->event_start, $event_detail->event_tz_cont.'/'.$event_detail->event_tz_place, 'M j, Y'); ?></strong>
                    <?php if ($event_detail->event_start+86399 != $event_detail->event_end) { ?>
                        - <strong><?php echo $this->timezone->formattedEventDatetimeFromUnixtime($event_detail->event_end, $event_detail->event_tz_cont.'/'.$event_detail->event_tz_place, 'M j, Y'); ?></strong>
                    <?php } ?>
            		<br/> 
            		<strong><?php echo escape($event_detail->event_loc); ?></strong>
					<?php if($event_detail->private==1): ?>
						<br/><strong>Private Event</strong>
					<?php endif; ?>
            	</p>
            	
            	<p class="opts">
            	<?php 
            	/*
            	if its set, but the event was in the past, just show the text "I was there!"
            	if its set, but the event is in the future, show a link for "I'll be there!"
            	if its not set show the "I'll be there/I was there" based on time
            	*/
            	if($attend && user_is_auth()){
            		if($event_detail->event_end<time()){
            			$link_txt="I attended"; $showt=1;
            		}else{ $link_txt="I'm attending"; $showt=2; }
            	}else{
            		if($event_detail->event_end<time()){
            			$link_txt="I attended"; $showt=3; 
            		}else{ $link_txt="I'm attending"; $showt=4; }
            	}
            	//if they're not logged in, show the questions
            	if(!user_is_auth()){ $attend=false; }
            	?>
            		
            		<a class="btn<?php echo $attend ? ' btn-success' : ''; ?>" id="mark-attending" href="javascript:void(0);" onclick="return markAttending(this,<?php echo $event_detail->ID?>,<?php echo $event_detail->event_end<time() ? 'true' : 'false'; ?>);"><?php echo $link_txt?></a>
            		<span class="attending"><strong><span class="event-attend-count-<?php echo $event_detail->ID; ?>"><?php echo (int)$attend_ct; ?></span> people</strong> <?php echo (time()<=$event_detail->event_end) ? ' attending so far':' said they attended'; ?>. <a href="javascript:void(0);" id="toggle-attendees" class="show">Show &raquo;</a></span>
            	</p>
            </div>
        	<div class="clear"></div>

        </div>
        <div class="clear"></div>
	</div>

	<div class="desc">
		<?php echo auto_p(auto_link(escape($event_detail->event_desc))); ?>
		<hr/>
		
	<b>Your host(s):</b><br/>
	<table cellpadding="5" cellspacing="0" border="0">
	<tr>
	<?php
	foreach($admins as $admin_user){
		echo '<td style="padding-right:5px;font-size:11px">';
		echo '<a href="/user/view/'.$admin_user->ID.'">'.$admin_user->full_name.'</a></td>';
	}
	?>
	</tr>
	</table>

	<?php if(!empty($event_detail->event_href) || !empty($event_detail->event_hastag) || !empty($event_detail->event_stub)){ ?>
		<div class="related">
		<?php if(!empty($event_detail->event_href)){ ?>
		<?php $hrefs = array_map('trim', explode(',',$event_detail->event_href)); ?>
        	<div class="links">
        		<h2 class="h4">Event Link<?php if (count($hrefs) != 1): ?>s<?php endif; ?></h2>
    			<ul>
    			<?php foreach ($hrefs as $href): ?>
    				<li><a href="<?php echo escape($href); ?>" rel="external"><?php echo escape($href); ?></a></li>
    			<?php endforeach; ?>
                </ul>
        	</div>
        <?php } ?>
        <?php if(!empty($event_detail->event_hashtag)){ ?>
        <?php $hashtags = array_map('trim', explode(',',$event_detail->event_hashtag)); ?>
        	<div class="hashtags">
        		<h2 class="h4">Hashtag<?php if (count($hashtags) != 1): ?>s<?php endif; ?></h2>
    			<ul>
    			<?php foreach ($hashtags as $hashtag): ?>
    				<?php $hashtag = str_replace('#', '', $hashtag); ?>
    				<li>#<a href="http://hashtags.org/<?php echo escape($hashtag); ?>" rel="external"><?php echo escape($hashtag); ?></a></li>
    			<?php endforeach; ?>
                </ul>
        	</div>
        <?php } ?>
		<?php if(!empty($event_detail->event_stub)){ ?>
			<div class="links">
        		<h2 class="h4">Quicklink</h2>
    			<ul>
					<li>
					<a href="/event/<?php echo $event_detail->event_stub; ?>"><?php echo $this->config->site_url(); ?>event/<?php echo $event_detail->event_stub;?></a>
					</li>
                </ul>
        	</div>
		<?php } ?>
        	<div class="clear"></div>
    	</div>
    <?php } ?>
			<?php 
			// If there's a Call for Papers open for the event, let them know
			if(!empty($event_detail->event_cfp_start) || !empty($event_detail->event_cfp_end)){
			?>
			<div class="links">
				<b>Call for Papers Status:
				<?php 
			switch ($cfp_status) {
                case "open" :
                    echo '<span style="color:#00BE02">Open!</span>';
                    echo '<br/>(ends '.date('m.d.Y',$event_detail->event_cfp_end).')';
					if (!empty($event_detail->event_cfp_url)) {
						echo '<br/>More information: <a href="'.$event_detail->event_cfp_url.'">'.$event_detail->event_cfp_url.'</a>';
					}
                    break;
                case "pending" :
                    echo '<span style="color:#BEBE02">Not open yet</span>';
                    echo '<br/>(opens at '.date('m.d.Y',$event_detail->event_cfp_start).')';
                    break;
                case "closed" :
                default :
                    echo '<span style="color:#BE0002">Closed</span>';
                    break;
            }
				?> </b> 
			</div>
			<div class="clear"></div>
			<?php } ?>
	</div>
</div>
