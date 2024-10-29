jQuery(document).ready(function(){

	jQuery("#del_post").click(function(){
		jQuery("#guest_passwd_del").fadeIn();
		jQuery("#guest_passwd_edit").val('');
		jQuery("#guest_passwd_edit").hide();
	});

	jQuery("#edit_post").click(function(){
		jQuery("#guest_passwd_edit").fadeIn();
		jQuery("#guest_passwd_del").val('');
		jQuery("#guest_passwd_del").hide();
	});

	jQuery("#insert_btn").click(function(){
		if(jQuery("#check_sbj").val() == ''){
			alert('제목을 입력해 주세요.');
			jQuery("#check_sbj").focus();
			return false;
		}
		
		if(jQuery("#check_nm").val() == ''){
			alert('이름을 입력해 주세요.');
			jQuery("#check_nm").focus();
			return false;
		}

		if(jQuery("#check_msg").val() == ''){
			alert('내용을 입력해 주세요.');
			jQuery("#check_msg").focus();
			return false;
		}

		if(jQuery("#check_pw").val() == ''){
			alert('비밀번호를 입력해 주세요.');
			jQuery("#check_pw").focus();
			return false;
		}

		jQuery("form[name=add_post]").submit();

		
	});

	jQuery("#edit_post_btn").click(function(){
		
		if(jQuery("#check_sbj").val() == ''){
			alert('제목을 입력해 주세요.');
			jQuery("#check_sbj").focus();
			return false;
		}

		if(jQuery("#check_text").val() == ''){
			alert('내용을 입력해 주세요.');
			jQuery("#check_text").focus();
			return false;
		}

		jQuery("form[name=edit_post]").submit();

		
	});

	jQuery("#edit_reply_btn").click(function(){
		
		if(jQuery("#check_sbj").val() == ''){
			alert('제목을 입력해 주세요.');
			jQuery("#check_sbj").focus();
			return false;
		}

		if(jQuery("#check_text").val() == ''){
			alert('내용을 입력해 주세요.');
			jQuery("#check_text").focus();
			return false;
		}

		jQuery("form[name=add_post]").submit();

		
	});

	jQuery("#amumu_board_search_submit").click(function(){
		var keyword = jQuery("#amumu_board_search").val();
		var permal_link = jQuery("#amumu_permal_link").val();
		var href = jQuery(this).attr("href")+permal_link+"keyword="+keyword;
		jQuery(this).attr("href",href);
	});

	jQuery(".amumu-board-reply-edit").live('click',function(){
		var tmp_text;
		var reply_id = jQuery(this).attr("value");
		tmp_text = jQuery("#amumu-board-reply-text-"+reply_id).html();
		jQuery("#amumu-board-reply-text-p-"+reply_id).hide();
		jQuery("#amumu-board-reply-text-"+reply_id).fadeIn();
		jQuery("#amumu-board-reply-text-"+reply_id).removeAttr("disabled");
		jQuery("#amumu-board-reply-"+reply_id).find(".reply_menu").hide();
		jQuery("#amumu-board-reply-"+reply_id).find(".reply_tool").fadeIn();
		jQuery("#amumu-board-reply-text-"+reply_id).focus();
	});

	jQuery(".esc_reply").live('click',function(){
		var tmp_text;
		var reply_id = jQuery(this).attr("value");
		tmp_text = jQuery("#amumu-board-reply-text-"+reply_id).html();
		jQuery("#amumu-board-reply-text-"+reply_id).hide();
		jQuery("#amumu-board-reply-text-p-"+reply_id).fadeIn();
		jQuery("#amumu-board-reply-text-"+reply_id).attr("disabled","disabled");
		jQuery("#amumu-board-reply-text-"+reply_id).css("outline","none");
		jQuery("#amumu-board-reply-text-"+reply_id).val(tmp_text);
		jQuery("#amumu-board-reply-"+reply_id).find(".reply_tool").hide();
		jQuery("#amumu-board-reply-"+reply_id).find(".reply_menu").fadeIn();
	});

	jQuery(".mod_reply").live('click',function(){
		var reply_id = jQuery(this).attr("value");
		jQuery("#amumu-board-reply-text-p-"+reply_id).html(jQuery("#amumu-board-reply-text-"+reply_id).val());
		jQuery("#amumu-board-reply-text-p-"+reply_id).fadeIn();
		jQuery("#amumu-board-reply-text-"+reply_id).hide();
		jQuery("#amumu-board-reply-text-"+reply_id).attr("disabled","disabled");
		jQuery("#amumu-board-reply-text-"+reply_id).css("outline","none");
		jQuery("#amumu-board-reply-"+reply_id).find(".reply_tool").hide();
		jQuery("#amumu-board-reply-"+reply_id).find(".reply_menu").fadeIn();
		var mod_text = strip_tags(jQuery("#amumu-board-reply-text-"+reply_id).val());

		if(mod_text == ''){
			alert('덧글을 입력해 주세요.');
			return false;
		}

		var data = {
				action : 'amumu_board_update_comment',
				text : mod_text,
				reply_id : reply_id
			};
			jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
				beforeSend: function()
				{
					//show_loading();		
				},
				success: function(result){
					//hide_loading();
					if(result.result == "1"){
						
					}else if(result.result == "0"){
						alert('DataBass Error');
					}
				}
			});

	});


	jQuery(".amumu-board-reply-del").live('click',function(){

		if(confirm('댓글을 삭제 하시겠습니까?')){
			var comment_ID = jQuery(this).attr('value');
			var data = {
					action     : 'amumu_board_delete_comment',
					comment_ID : comment_ID
				};
				jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
					beforeSend: function()
					{
						//show_loading();		
					},
					success: function(result){
						//hide_loading();
						if(result.result == "1"){
							//alert('삭제성공');
							jQuery(".amumu-board-reply-reply-"+comment_ID).fadeOut();
							jQuery(".amumu-board-reply-reply-"+comment_ID).detach();
							jQuery("#amumu-board-reply-"+comment_ID).fadeOut();
							jQuery("#amumu-board-reply-"+comment_ID).detach();
							//location.reload();
						}else if(result.result == "0"){
							alert('삭제실패');
						}
					}
				});
		}
		return false;
	});

	jQuery(".ins-reply-reply").live('click',function(){
		jQuery("#amumu-board-reply-"+jQuery(this).attr('rel')).append(jQuery(".amumu-board-reply-reply-ins"));
		jQuery(".amumu-board-reply-reply-ins").fadeIn();
		jQuery("#parent_comment_ID").val(jQuery(this).attr('rel'));
		jQuery("#reply_reply_massage").focus();
	});

	jQuery("#ins-reply").click(function(){
		var text = strip_tags(jQuery('#reply_massage').val());
		if(text == ''){
			alert('내용을 입력 하시기 바랍니다.');
			jQuery('#reply_massage').focus();
			return false;
		}
		var author_id =  jQuery('#reply_author_id').val();
		var author_name =  jQuery('#reply_author_name').val();
		var author_pic =  jQuery('#reply_author_pic').val();
		var author_email =  jQuery('#reply_author_email').val();
		var author_email_hash =  jQuery('#reply_author_email_hash').val();
		
		var parent_id =  jQuery('#parent_ID').val();
		var parent_comment_id = jQuery("#parent_comment_ID").val();
		//var comment_ID =  jQuery('#post_ID').val();

		var data = {
				action : 'amumu_board_insert_comment',
				text : text,
				author_id : author_id,
				author_name : author_name,
				author_pic : author_pic,
				author_email : author_email,
				parent_id : parent_id
			};
			jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
				beforeSend: function()
				{
					//show_loading();		
				},
				success: function(result){
					//hide_loading();
					if(result.result == "1"){
						tmp_html = "<div id='amumu-board-reply-"+result.reply_id+"'>";
						tmp_html += "<div class='amumu-board-reply-wrap-one'>";

						if( author_pic != "" ){
							tmp_html +="<span class='reply-author'><img src ='"+author_pic+"' /> "+author_name+"</span>";
						}else{
							tmp_html += "<span class='reply-author'><img src ='http://www.gravatar.com/avatar/"+author_email_hash+"?s=48' /> "+author_name+"</span>";
						}

						tmp_html += "<span class='reply-date'>"+result.date+"</span>";
						tmp_html += " <img src='/wp-content/plugins/amumu-board/image/re.png' style='box-shadow:none;'><span class='ins-reply-reply' rel='"+result.reply_id+"'>답글</span>";
						tmp_html += "<span class='reply_menu'><span class='amumu-board-reply-edit' value='"+result.reply_id+"'>수정</span><span class='amumu-board-reply-del' value='"+result.reply_id+"'>삭제</span></span>";
						tmp_html += "<span class='reply_tool' style='display:none;'><span class='mod_reply' value='"+result.reply_id+"'>확인</span><span ></span><span class='esc_reply' value='"+result.reply_id+"'>취소</span></span>";
						tmp_html += "</div>";
						tmp_html += "<div class='amumu-board-reply-wrap-two'>";
						tmp_html += "<p class='amumu-baord-reply-p' id='amumu-board-reply-text-p-"+result.reply_id+"'>"+text+"</p>";
						tmp_html += "<textarea id='amumu-board-reply-text-"+result.reply_id+"' class='amumu-board-reply-text' disabled='disabled'>"+text+"</textarea>";
						tmp_html += "<input type='hidden' name='reply_id' value='"+result.reply_id+"' />";
						tmp_html += "</div>";
						tmp_html += "</div>";
						
						jQuery("#amumu-board-reply").append(tmp_html);

						jQuery('#reply_massage').val('');
						
					}else if(result.result == "0"){
						alert('DataBass Error');
					}
				}
			});
	});

	jQuery("#ins-reply-reply").click(function(){
		var text = strip_tags(jQuery('#reply_reply_massage').val());
		if(text == ''){
			alert('내용을 입력 하시기 바랍니다.');
			jQuery('#reply_reply_massage').focus();
			return false;
		}
		var author_id =  jQuery('#reply_reply_author_id').val();
		var author_name =  jQuery('#reply_reply_author_name').val();
		var author_pic =  jQuery('#reply_reply_author_pic').val();
		var author_email =  jQuery('#reply_reply_author_email').val();
		var author_email_hash =  jQuery('#reply_reply_author_email_hash').val();
		
		var parent_id =  jQuery('#reply_parent_ID').val();
		var parent_comment_id = jQuery("#parent_comment_ID").val();

		var data = {
				action : 'amumu_board_insert_comment',
				text : text,
				author_id : author_id,
				author_name : author_name,
				author_pic : author_pic,
				author_email : author_email,
				parent_id : parent_id,
				parent_comment_id : parent_comment_id
			};
			jQuery.ajax({ type: 'POST', url: ajaxurl, data: data, dataType: "json",
				beforeSend: function()
				{
					//show_loading();		
				},
				success: function(result){
					//hide_loading();
					if(result.result == "1"){
						tmp_html = "<div id='amumu-board-reply-"+result.reply_id+"' class='amumu-board-indent1'>";
						tmp_html += "<div class='amumu-board-reply-wrap-one'>";

						if( author_pic != "" ){
							tmp_html +="<span class='reply-author'><img src ='"+author_pic+"' /> "+author_name+"</span>";
						}else{
							tmp_html += "<span class='reply-author'><img src ='http://www.gravatar.com/avatar/"+author_email_hash+"?s=48' /> "+author_name+"</span>";
						}
						tmp_html += "<span class='reply-date'>"+result.date+"</span>";

						tmp_html += "<span class='reply_menu'><span class='amumu-board-reply-edit' value='"+result.reply_id+"'>수정</span><span class='amumu-board-reply-del' value='"+result.reply_id+"'>삭제</span></span>";
						tmp_html += "<span class='reply_tool' style='display:none;'><span class='mod_reply' value='"+result.reply_id+"'>확인</span><span ></span><span class='esc_reply' value='"+result.reply_id+"'>취소</span></span>";
						tmp_html += "</div>";
						tmp_html += "<div class='amumu-board-reply-wrap-two'>";
						tmp_html += "<p class='amumu-baord-reply-p' id='amumu-board-reply-text-p-"+result.reply_id+"'>"+text+"</p>";
						tmp_html += "<textarea id='amumu-board-reply-text-"+result.reply_id+"' class='amumu-board-reply-text' disabled='disabled'>"+text+"</textarea>";
						tmp_html += "<input type='hidden' name='reply_id' value='"+result.reply_id+"' />";
						tmp_html += "</div>";
						tmp_html += "</div>";

						jQuery("#amumu-board-reply-"+result.parent_comment_id).append(tmp_html);

						jQuery(".amumu-board-reply-reply-ins").hide();
						jQuery("#parent_comment_ID").val('');
						jQuery("#reply_reply_massage").val('');
						
					}else if(result.result == "0"){
						alert('DataBass Error');
					}
				}
			});
	});

	jQuery("#cancel-reply-reply").live('click',function(){
		jQuery(".amumu-board-reply-reply-ins").hide();
		jQuery("#parent_comment_ID").val('');
		jQuery("#reply_reply_massage").val('');
	});

	jQuery(function(){
		jQuery(window).resize(function(){
			var width = parseInt(jQuery(this).width());
			//jQuery("#wrapper").text(width);
			if ( width < 480) {
				jQuery('.amumu-board-title').attr('colspan', 4);
				jQuery('.amumu-board-title-th').attr('colspan', 4);
				jQuery('.amumu-board-mobile-title-th').attr('colspan',3);
				jQuery('.amumu-board-mobile-author').show();
				jQuery('.amumu-board-mobile-author').css('display','block');
			}else{
				jQuery('.amumu-board-title').attr('colspan', 1);
				jQuery('.amumu-board-title-th').attr('colspan', 1);
				jQuery('.amumu-board-mobile-title-th').attr('colspan',1);
				jQuery('.amumu-board-mobile-author').hide();
			}
		}).resize();
	});

});

function strip_tags (input, allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}