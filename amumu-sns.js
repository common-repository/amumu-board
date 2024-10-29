jQuery(document).ready(function(){

	jQuery('#amumu-sns-text').bind('click',function(){
		if(jQuery('#amumu-sns-text').val() == '로그인 후 작성 가능합니다.'){
			jQuery('#amumu-sns-text').val('');
		}
	});

	jQuery('#amumu-sns-text').bind('keyup',function(){
		textCounter(jQuery('#amumu-sns-text'), jQuery('#amumu-sns-count'));
	});

	jQuery('#amumu-sns-text').bind('keydown',function(){
		textCounter(jQuery('#amumu-sns-text'), jQuery('#amumu-sns-count'));
	});

	jQuery('#amumu-sns-logout').bind('click',function(){
		if(confirm('로그아웃 하시겠습니까?')){
			if(jQuery('#amumu-sns-facebook-login').attr('class') == 'on'){
				FB.logout();
			}
			//jQuery('#amumu-sns-title').html('로그인 후 작성가능합니다.');
			amumu_sns_logout();

		}else{
			alert('취소하였습니다.');
		}
	});

	jQuery('.replreport').bind('click',function(){
		if(confirm('댓글을 삭제 하시겠습니까?')){
			var comment_ID = jQuery(this).attr('value');
			var data = {
					action     : 'amumu_sns_delete_comment',
					comment_ID : comment_ID
				};
				jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
					beforeSend: function()
					{
						show_loading();		
					},
					success: function(result){
						hide_loading();
						if(result.result == "1"){
							alert('삭제성공');
							location.reload();
						}else if(result.result == "0"){
							alert('삭제실패');
						}
					}
				});
		}else {
			alert('취소하였습니다.');
		}
	});

	jQuery('#amumu-sns-insert').bind('click',function(){

		var comment = strip_tags(jQuery('#amumu-sns-text').val());
		if(comment == ''){
			alert('덧글을 입력해 주세요.');
			jQuery('#amumu-sns-text').focus();
			return false;
		}

		if(jQuery('#amumu-sns-facebook-login').attr('class') == 'off'){
			alert('페이스북 로그인 후에 이용 가능합니다.');
			return false;
		}

		if(jQuery('#amumu-sns-selected').val() == "facebook"){
			var name = jQuery('#amumu-sns-facebook-name').val();
			var profile_link = jQuery('#amumu-sns-facebook-profile-link').val();
			var pic = jQuery('#amumu-sns-facebook-pic').val();
			var comment_type = 'facebook';
			var sns_type = 'facebook';
		}

		var page_ID = jQuery('#amumu-sns-page-ID').val();
		var link_url = location.protocol + '//' + location.host +'/?page_id='+page_ID;
		var comment_author = jQuery('#amumu-sns-comment-author').val();

		var data = {
				action     : 'amumu_sns_insert_comment',
				comment : comment,
				name : name,
				profile_link : profile_link,
				pic : pic,
				page_ID : page_ID,
				comment_type : comment_type,
				comment_author : comment_author
				//amumu_sns_ID : amumu_sns_return
			};
			jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
				beforeSend: function()
				{
					show_loading();		
				},
				success: function(result){
					hide_loading();
					amumu_sns_post(sns_type,link_url);
					var output = '<div class="viewarea"><span class="uimg"><a href="' + profile_link + '" target="_blank"><img src="' + pic + '" alt="썸네일" /></a></span>';
					output += '<div class="conwrap"><div class="contop"><div class="topright"></div></div>';
					output += '<div class="snscontents"><img src="'+ result.plugins_url +'/image/ico_mark.png" class="mark" alt="" />';
					output += '<div class="uinfo"><img src="'+ result.plugins_url +'/image/'+comment_type+'.gif" alt="f" /><a href="' + profile_link + '" target="_blank">'+ name +'</a><span>' + result.date + '</span>';
					output += '<a class="replreport" value="'+ result.comment_id +'"><img src="'+ result.plugins_url +'/image/ico_del.png" alt="" />삭제</a>';
					output += '</ul></div><p>';
					output += comment;
					output += '</p></div><div class="conbottom"><div class="bottomleft"></div></div></div></div>';
					jQuery( "div.snsappend" ).prepend( output );

					jQuery('.replreport').bind('click',function(){
						if(confirm('댓글을 삭제 하시겠습니까?')){
							var comment_ID = jQuery(this).attr('value');
							var data = {
									action     : 'amumu_sns_delete_comment',
									comment_ID : comment_ID
								};
								jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
									beforeSend: function()
									{
										show_loading();		
									},
									success: function(result){
										hide_loading();
										if(result.result == "1"){
											alert('삭제성공');
											location.reload();
										}else if(result.result == "0"){
											alert('삭제실패');
										}
									}
								});
						}else {
							alert('취소하였습니다.');
						}
					});
				}
			});

	});

	jQuery('#amumu-sns-more').bind('click',function(){

		var page_ID = jQuery('#amumu-sns-page-ID').val();
		var page_offset = parseInt(jQuery('#amumu-sns-offset').val()) + 10;
		
		var data = {
				action     : 'amumu_sns_more_comment',
				page_ID : page_ID,
				page_offset : page_offset
			};
			jQuery.ajax({ type: 'POST', url: ajaxurl, data: data,
				beforeSend: function()
				{
					show_loading();		
				},
				success: function(result){
					hide_loading();
					if(result.trim() == "nohave"){
						alert('더이상의 댓글이 없습니다.');
					}else{
						jQuery( "div.snsview" ).append( result );
						jQuery('#amumu-sns-offset').val(parseInt(jQuery('#amumu-sns-offset').val()) + 10);
						jQuery('.replreport').bind('click',function(){
							var comment_ID = jQuery(this).attr('value');
							var data = {
									action     : 'amumu_sns_delete_comment',
									comment_ID : comment_ID
								};
								jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
									beforeSend: function()
									{
										show_loading();		
									},
									success: function(result){
										hide_loading();
										if(result.result == "1"){
											alert('삭제성공');
											location.reload();
										}else if(result.result == "0"){
											alert('삭제실패');
										}
									}
								});
						});
					}
				}
			});

	});

	// respond to clicks on the login and logout links
	jQuery('#amumu-sns-facebook-login').bind('click',function(){
		if(jQuery('#amumu-sns-facebook-login').attr('class') == 'on'){

			alert('페이스북이 대표 계정으로 설정되었습니다.');
			jQuery('#amumu-sns-comment-author').val(jQuery('#amumu-sns-facebook-authorId').val());
			jQuery('#amumu-sns-pic').css('background','url('+jQuery('#amumu-sns-facebook-pic').val()+')');
			jQuery('#amumu-sns-facebook-name').val(jQuery('#amumu-sns-facebook-name').val());
			jQuery('#amumu-sns-facebook-profile-link').val(jQuery('#amumu-sns-facebook-profile-link').val());
			jQuery('#amumu-sns-facebook-pic').val(jQuery('#amumu-sns-facebook-pic').val());
			jQuery('#amumu-sns-selected').val('facebook');
		}else{
			FB.login(function(response) {
				if (response.authResponse) {
					//console.log('Welcome!  Fetching your information.... ');
					FB.api('/me', function(response) {
					//console.log('Good to see you, ' + response.name + '.');
					});
				} else {
					//console.log('User cancelled login or did not fully authorize.');
				}
			},{scope: 'email,publish_actions'});
		}
	});
});

function  amumu_sns_login(type,id,name,email,pic,url) {
	var data = {
			action : 'amumu_sns_login',
			amumu_sns_type : type,
			amumu_sns_id : id,
			amumu_sns_name : name,
			amumu_sns_email : email,
			amumu_sns_pic : pic,
			amumu_sns_url : url
		};
	jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
		beforeSend: function()
		{
			show_loading();		
		},
		success: function(result){
			hide_loading();
			if(result.result == "1"){
				jQuery('#amumu-sns-logout').fadeIn();
			}else if(result.result == "0"){
				
			}
		}
	});
}

function amumu_sns_logout() {
	var data = {
			action     : 'amumu_sns_logout'
		};
	jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
		beforeSend: function()
		{
			show_loading();		
		},
		success: function(result){
			hide_loading();
			if(result.result == "1"){
				jQuery('#amumu-sns-pic').css('background','url("'+result.url+'")');
				jQuery('#amumu-sns-logout').fadeOut();
			}else if(result.result == "0"){
				
			}
		}
	});
}

function textCounter(field, countField){
	var count = field.val().length;
	if ( count > 140 ){
		alert('140자 까지만 입력하실 수 있습니다.');
		field.val( field.val().substring(0, 140) );
		count = field.val().length;
	}
	
	var available = 140 - count;
	if(available <= 20 && available >= 0){
		field.addClass('warning');
	} else {
		field.removeClass('warning');
	}
	if(available < 0){
		field.addClass('exceeded');
	} else {
		field.removeClass('exceeded');
	}
	
	countField.html('' + available +' / 140');
}

function show_loading(){
	var winH = jQuery('#amumu-sns-more').height();
	var winW = jQuery('#amumu-sns-more').width();
	jQuery( 'div.loading-indicator' ).css('top',  winH/2-jQuery('div.loading-indicator').height()/2+jQuery('#amumu-sns-more').scrollTop());
	jQuery( 'div.loading-indicator' ).css('left', winW/2-jQuery('div.loading-indicator').width()/2);
	jQuery( 'div.loading-indicator' ).fadeIn(300);
}

function hide_loading(){
	jQuery( 'div.loading-indicator' ).fadeOut(300);
}

function amumu_sns_post(type,link_url) {
	var content = strip_tags(jQuery('#amumu-sns-text').val());

	var limit_length = 140;
	var link_length = link_url.length;
	content = content.substring( 0, limit_length - link_length - 5 );

	if(type == "facebook"){
		var path = '/me/feed'; 
		var message = strip_tags(jQuery('#amumu-sns-text').val());
		var url = link_url;
		var title = jQuery('title').html();
		var desc = 'Amumu SNS Service';
		var capt = jQuery('#amumu-sns-excerpt').val();
		var picture = jQuery('#amumu_sns_thumb').attr("src");

		FB.api(path, 'Post', {
			message:message,
			link : url,
			name : title,
			caption: capt,
			description : desc,
			picture : picture
			}, function(response) {
				if (!response || response.error) {
					//alert(response.error);
				} else {
					//alert(response.id);
				}
			});
	}
}

function strip_tags (input, allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}