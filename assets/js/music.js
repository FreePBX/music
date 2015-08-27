function addstream_onsubmit() {
	var theForm = document.formstream;

	defaultEmptyOK = false;
	if (!isAlphanumeric(theForm.category.value))
		return warnInvalid(theForm.category, _('Please enter a valid Category Name'));
	if (theForm.category.value == "default" || theForm.category.value == "none" || theForm.category.value == ".nomusic_reserved")
		return warnInvalid(theForm.category, _('Categories: "none" and "default" are reserved names. Please enter a different name'));
	if (isEmpty(theForm.stream.value))
		return warnInvalid(theForm.stream, _('Please enter a streaming application command and arguments'));

	return true;
}


function editstream_onsubmit() {
	var theForm = document.formstream;

	defaultEmptyOK = false;
	if (isEmpty(theForm.stream.value))
		return warnInvalid(theForm.stream, _('Please enter a streaming application command and arguments'));

	return true;
}

function addcategory_onsubmit() {
	var theForm = document.localcategory;

	defaultEmptyOK = false;
	if (!isAlphanumeric(theForm.category.value)) {
		return warnInvalid(theForm.category, _('Please enter a valid Category Name'));
	}
	if(theForm.category.value.toLowerCase() == "general") {
		return warnInvalid(theForm.category, _("General is not a valid category name"));
	}
	if (theForm.category.value == "default" || theForm.category.value == "none" || theForm.category.value == ".nomusic_reserved") {
		return warnInvalid(theForm.category, _('Categories: "none" and "default" are reserved names. Please enter a different name'));
	}

	return true;
}

function linkFormat(value){
	var action = 'edit';
	if(value['type'] == 'streaming'){
		action = 'editstream'
	}
	var html = '<a href="?display=music&view=form&category='+value['category']+'&action='+action+'"><i class="fa fa-pencil"></i></a>';
	if(value['category'] !== 'default'){
		html += '&nbsp;<a href="?display=music&action=delete&category='+value['category']+'" class="delAction"><i class="fa fa-trash"></i></a>';
	}
	return html;
}

function playFormatter(val,row){
	return '<div id="jquery_jplayer_'+row.id+'" class="jp-jplayer" data-filename="'+row.basename+'" data-category="'+row.category+'" data-container="#jp_container_'+row.id+'" data-year="'+row.year+'" data-month="'+row.month+'" data-day="'+row.day+'" data-file="'+row.file+'"></div><div id="jp_container_'+row.id+'" data-player="jquery_jplayer_'+row.id+'" class="jp-audio-freepbx" role="application" aria-label="media player">'+
		'<div class="jp-type-single">'+
			'<div class="jp-gui jp-interface">'+
				'<div class="jp-controls">'+
					'<i class="fa fa-play jp-play"></i>'+
					'<i class="fa fa-repeat jp-repeat"></i>'+
				'</div>'+
				'<div class="jp-progress">'+
					'<div class="jp-seek-bar progress">'+
						'<div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>'+
						'<div class="progress-bar progress-bar-striped active" style="width: 100%;"></div>'+
						'<div class="jp-play-bar progress-bar"></div>'+
						'<div class="jp-play-bar">'+
							'<div class="jp-ball"></div>'+
						'</div>'+
						'<div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>'+
					'</div>'+
				'</div>'+
				'<div class="jp-volume-controls">'+
					'<i class="fa fa-volume-up jp-mute"></i>'+
					'<i class="fa fa-volume-off jp-unmute"></i>'+
				'</div>'+
			'</div>'+
			'<div class="jp-no-solution">'+
				'<span>Update Required</span>'+
				'To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.'+
			'</div>'+
		'</div>'+
	'</div>';
}

function musicFormat(value){
	html = '<a href="?display=music&action=deletefile&view=form&filename='+value['filename']+'&category='+value['category']+'" class="delAction"><i class="fa fa-trash"></i></a>';
	return html;
}

$('#musicgrid').on("post-body.bs.table", function () {
	bindPlayers();
});

function bindPlayers() {
	$(".jp-jplayer").each(function() {
		var container = $(this).data("container"),
				player = $(this),
				file = player.data("filename"),
				category = player.data("category");
		$(this).jPlayer({
			ready: function() {
				$(container + " .jp-play").click(function() {
					if(!player.data("jPlayer").status.srcSet) {
						$(container).addClass("jp-state-loading");
						$.ajax({
							type: 'POST',
							url: "ajax.php",
							data: {module: "music", command: "gethtml5", file: file, category: category},
							dataType: 'json',
							timeout: 30000,
							success: function(data) {
								if(data.status) {
									player.on($.jPlayer.event.error, function(event) {
										$(container).removeClass("jp-state-loading");
										console.log(event);
									});
									player.one($.jPlayer.event.canplay, function(event) {
										$(container).removeClass("jp-state-loading");
										player.jPlayer("play");
									});
									player.jPlayer( "setMedia", data.files);
								} else {
									alert(data.message);
									$(container).removeClass("jp-state-loading");
								}
							}
						});
					}
				});
			},
			timeupdate: function(event) {
				$(container).find(".jp-ball").css("left",event.jPlayer.status.currentPercentAbsolute + "%");
			},
			ended: function(event) {
				$(container).find(".jp-ball").css("left","0%");
			},
			swfPath: "/js",
			supplied: "wav",
			cssSelectorAncestor: container,
			wmode: "window",
			useStateClassSkin: true,
			autoBlur: false,
			keyEnabled: true,
			remainingDuration: true,
			toggleDuration: true
		});
		$(this).on($.jPlayer.event.play, function(event) {
			$(this).jPlayer("pauseOthers");
		});
	});

	var acontainer = null;
	$('.jp-play-bar').mousedown(function (e) {
		acontainer = $(this).parents(".jp-audio-freepbx");
		updatebar(e.pageX);
	});
	$(document).mouseup(function (e) {
		if (acontainer) {
			updatebar(e.pageX);
			acontainer = null;
		}
	});
	$(document).mousemove(function (e) {
		if (acontainer) {
			updatebar(e.pageX);
		}
	});

	//update Progress Bar control
	var updatebar = function (x) {
		var player = $("#" + acontainer.data("player")),
				progress = acontainer.find('.jp-progress'),
				maxduration = player.data("jPlayer").status.duration,
				position = x - progress.offset().left,
				percentage = 100 * position / progress.width();

		//Check within range
		if (percentage > 100) {
			percentage = 100;
		}
		if (percentage < 0) {
			percentage = 0;
		}

		player.jPlayer("playHead", percentage);

		//Update progress bar and video currenttime
		acontainer.find('.jp-ball').css('left', percentage+'%');
		acontainer.find('.jp-play-bar').css('width', percentage + '%');
		player.jPlayer.currentTime = maxduration * percentage / 100;
	};
}
