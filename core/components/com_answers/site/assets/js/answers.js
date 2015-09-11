/**
 * @package     hubzero-cms
 * @file        components/com_answers/assets/js/answers.jquery.js
 * @copyright   Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

//----------------------------------------------------------
// Answers Scripts
//----------------------------------------------------------
if (!jq) {
	var jq = $;
}

jQuery(document).ready(function(jq){
	var $ = jq;

	$('.entries-menu').each(function(i, el){
		el = $(el);
		el.addClass('js');

		var select = $("<select />").on('change', function() {
			window.location = $(this).find("option:selected").val();
		});

		$("<option />", {
			"value"   : "",
			"text"    : el.attr('data-label')
		}).appendTo(select);

		el.find("a").each(function() {
			var elm = $(this),
				opts = {
					"value"   : elm.attr("href"),
					"text"    : elm.text()
				};

			if (elm.hasClass('active')) {
				opts.selected = 'selected';
			}
			$("<option />", opts).appendTo(select);
		});

		var li = $("<li />").addClass('option-select');

		select.appendTo(li);
		li.appendTo(el);
	});

	$('a.reply').on('click', function (e) {
		e.preventDefault();

		var frm = $('#' + $(this).attr('data-rel'));

		if (frm.hasClass('hide')) {
			frm.removeClass('hide');
			$(this)
				.addClass('active')
				.text($(this).attr('data-txt-active'));
		} else {
			frm.addClass('hide');
			$(this)
				.removeClass('active')
				.text($(this).attr('data-txt-inactive'));
		}
	});

	$('a.abuse').fancybox({
		type: 'ajax',
		width: 500,
		height: 'auto',
		autoSize: false,
		fitToView: false,
		titleShow: false,
		tpl: {
			wrap:'<div class="fancybox-wrap"><div class="fancybox-skin"><div class="fancybox-outer"><div id="sbox-content" class="fancybox-inner"></div></div></div></div>'
		},
		beforeLoad: function() {
			href = $(this).attr('href');
			$(this).attr('href', href.nohtml());
		},
		afterShow: function() {
			var frm = $('#hubForm-ajax'),
				self = $(this.element[0]);

			if (frm.length) {
				frm.on('submit', function(e) {
					e.preventDefault();
					$.post($(this).attr('action'), $(this).serialize(), function(data) {
						var response = jQuery.parseJSON(data);

						if (!response.success) {
							frm.prepend('<p class="error">' + response.message + '</p>');
							return;
						} else {
							$('#sbox-content').html('<p class="passed">' + response.message + '</p>');
							$('#' + (response.category == 'answer' ? 'a' : 'c') + response.id)
								.find('.comment-body')
								.first()
								.html('<p class="warning">' + self.attr('data-txt-flagged') + '</p>');
						}

						setTimeout(function(){
							$.fancybox.close();
						}, 2 * 1000);
					});
				});
			}
		}
	});
});
