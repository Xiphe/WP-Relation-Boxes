jQuery(document).ready(function($) {
$('body').addClass('js');
$('.rb_addexisting').removeAttr('multiple').css({'height' : 'auto'});
// $('.rb_removescript').remove();
$.each( $('.rb_addexisting'), function() {
	if( $(this).find( '.rb_type' ) == 'n' ) {
		$(this).find( '.rb_addexisting_select option[value="null"]' ).remove();
	}
});
// $('.rb_addexisting_select option[value="null"]').remove();
$('.rb_addexisting_select option[value="null"]').val('');
$('#publish').after('<input type="hidden" name="rb_js" value="on" />');
	
	/** Sortable **/
	if(typeof $('.rb_list').sortable == 'function') {
		$('.rb_list').sortable({
			'handle' : '.rb_drag_handler',
			'update' : function(event, ui) {
				var serial = $(this).sortable('serialize'),
					$serial = $(this).closest('.rb_list_wrap').find('.rb_serial');
				$serial.attr('value', serial);
				
				colorList($(this), 500, { alt: '#f3f3f3', def: '#fafafa'});
			}
		});
	}
	function colorList(list, speed, colors) {
		var i = -1;
		$.each($(list).children('li'), function(key, value) {
			var c = i++/2 == Math.floor(i/2) ? colors.alt : colors.def;
			$(this).animate({'background-color' : c}, speed);
		});
	}
	
	/** TypeOne Options **/
	$.each($('.rb_addexisting'), function() {
		$(this).attr('data-selected', $(this).children('option[selected="selected"]').attr('value'));
		$(this).children('option').click(function() {
			if($(this).attr('value') != $(this).parent('select').attr('data-selected')) {
				$(this).closest('.inside').find('.rb_typeoneentry .rb_options').addClass('hidden');
				$(this).closest('.inside').find('.rb_typeoneentry .rb_message').html(rb_text.typeOneRefresh).removeClass('hidden');
			} else {
				$(this).closest('.inside').find('.rb_typeoneentry .rb_options').removeClass('hidden');
				$(this).closest('.inside').find('.rb_typeoneentry .rb_message').addClass('hidden');
			}
		});
	});

	/** Add Existing **/
	$( '.rb_addexisting_select' ).combobox({
		select: function( e, ui ) {
			var option = $( ui.item.option ),
				$addBtn = option.closest('.inside').find('.rb_add');
			if( $( ui.item.option ).closest( '.rb_addexisting' ).find( '.rb_type' ).html() == 1 ) {
				var	original = option.closest( '.rb_addexisting' ).find( '.rb_original' ).html();
				option.closest( '.rb_addexisting' ).find( 'option[selected="selected"]' ).removeAttr('selected');
				if( option.attr('value') != original ) {
					option.closest( '.inside' ).find( '.rb_options' ).fadeOut( 'fast', function() {
						option.closest( '.inside' ).find( '.rb_unsaved' ).fadeIn( 'fast' );
					});
				} else {
					option.closest( '.inside' ).find( '.rb_unsaved' ).fadeOut( 'fast', function() {
						option.closest( '.inside' ).find( '.rb_options' ).fadeIn( 'fast' );
					});
				}
				option.attr('selected', 'selected');
			}
			if( option.val() != 'null' ) {
				$addBtn.removeAttr('disabled');
			} else {
				$addBtn.attr('disabled', 'disabled');
			}
		}
	});

	$('.rb_add').removeClass('hidden');
	// $('.ui-combobox_wrap').after('<button class="button rb_add" disabled="disabled">'+rb_text.addButton+'</button>');
	$('.rb_addexisting .ui-autocomplete-input').keypress(function(e) {
		var addButton = $(this).closest('.rb_addexisting').find('.rb_add');
		var list = $('ul.ui-autocomplete[style*="display: block"]')[0];
		var select = $(this).closest('.rb_addexisting').find('.rb_addexisting_select');
		
		if( e.keyCode == 13 ) {
			e.preventDefault();
			if(addButton.attr('disabled') == 'disabled' && typeof list != 'undefined') {
				$(this).attr('value', $(list).children('li').first().text());
				addButton.removeAttr('disabled');
				$(list).html('').css({'display' : 'none'});
			} else if(addButton.attr('disabled') != 'disabled') {
				rb_addexistingCall(
					select.children('option[data-inner="'+$(this).attr('value').toLowerCase()+'"]')
				);
			}
		}
	}).keyup(function() {
		var thiz = this;
		var list = $('ul.ui-autocomplete[style*="display: block"]')[0];
		var select = $(this).closest('.rb_addexisting').find('.rb_addexisting_select');
		var addButton = $(this).closest('.rb_addexisting').find('.rb_add');
		var isComplete = false;
		var value = $(this).attr('value');
		
		if(typeof list != 'undefined' && $(list).attr('data-clickinit') != 'true') {
			$(list).click(function() {
				$('.ui-combobox_wrap.current').next('.rb_add').removeAttr('disabled');
			}).attr('data-clickinit', 'true');
		}
		
		if(typeof select.children('option[data-inner="'+$(this).attr('value').toLowerCase()+'"]')[0] != 'undefined') {
			isComplete = true;
		}
		
		if(isComplete) {
			addButton.removeAttr('disabled');
		} else {
			addButton.attr('disabled', 'disabled');
		}
	});
	$('.ui-combobox_wrap .ui-autocomplete-input').add('.ui-combobox_wrap .ui-button').click(function() {
		$('.ui-combobox_wrap.current').removeClass('current');
		$(this).parent('.ui-combobox_wrap').addClass('current');
		$('.ui-autocomplete li').click(function() {
			$('.ui-combobox_wrap.current').next('.rb_add').removeAttr('disabled');
		}).attr('data-clickinit', 'true');
	});
	$('button.rb_add').click(function(e) {
		e.preventDefault();
		var base = $(this).closest('.rb_addexisting');
		var targetString = base.find('.ui-autocomplete-input').attr('value').toLowerCase();
		var target = base.find('.rb_addexisting_select').children('option[data-inner="'+targetString+'"]');
		
		if(typeof target[0] != 'undefined') {
			rb_addexistingCall(target);
		}
	});
	
	// Gets a list elem from server - returns true if type = 1
	var rb_addexistingCall = function( $target ) {
		var $prt = $target.closest('.inside'),
			$list = $prt.find('.rb_list'),
			type = $prt.find('.rb_type').text(),
			$cBInput = $prt.find('.ui-combobox_wrap .ui-autocomplete-input'),
			$sel = $target.closest('select'),
			$serial = $prt.find('.rb_serial');
			$btn = $prt.find('button.rb_add');

		$btn.attr('disabled', 'disabled');

		$.post(ajaxurl, {
			action: 'rb_getLi',
			nonce: $target.attr('data-nonce'),
			ID: $('#post_ID').attr('value'),
			type: $('#post_type').attr('value'),
			relID: $target.attr('value'),
			relType: type
		}, function( r ) {
			if( r !== '' ) {
				r = eval('(' + r + ')');
					if( typeof r === 'object' ) {
					$cBInput.val('');
					$target.attr('disabled', 'disabled');

					$list.append( r.content );
					colorList($list, 500, { alt: '#f3f3f3', def: '#fafafa'});

					$serial.val($list.sortable('serialize'));

					initUnsavedReladtionDelete();
				}
			}
		});
	};

	// Release of unsaved relations.
	var initUnsavedReladtionDelete = function() {
		$('.rb_submitdelete.unsaved').click(function(e) {
			e.preventDefault();

			var $entry = $(this).closest('.rb_listentry'),
				$prt = $entry.closest('.inside'),
				$serial = $prt.find('.rb_serial'),
				$list = $prt.find('.rb_list'),
				id = $entry.attr('data-id');

			$('option[value="' + id + '"]').removeAttr('disabled');
			$entry.remove();

			$serial.val($list.sortable('serialize'));
		});
	};
});