/**
 * JS Master of relationboxes Wordpress plugin
 * @author  Hannes Diercks <info@xiphe.net>
 * @license GPLv2
 */
/*global ajaxurl */
if(typeof xiphe==='undefined'){var xiphe={};}xiphe=jQuery.extend(true,{},xiphe,{relationboxes:{master:(function($){var
    
    /* PRIVATE VARS */
    self = this,
    _disabled = {}

    /* PUBLIC VARS */;



    /* PRIVATE METHODS */ var

    /**
     * Initiation
     *
     * @return {void}
     */
    _init = function() {
    },


    /**
     * Second initiation when the document is ready
     *
     * @return {void}
     */
    _ready = function() {
        _jsify();
        _initSortable();
        _initTypeOne();
        _enhanceCombobox();
    },

    _initTypeOne = function() {
        $.each($('.rb_addexisting'), function() {
            $(this).attr('data-selected', $(this).children('option[selected="selected"]').attr('value'));
            $(this).children('option').click(function() {
                if($(this).attr('value') !== $(this).parent('select').attr('data-selected')) {
                    $(this).closest('.inside').find('.rb_typeoneentry .rb_options').addClass('hidden');
                    $(this).closest('.inside').find('.rb_typeoneentry .rb_message').html(xiphe.relationboxes.text.typeOneRefresh).removeClass('hidden');
                } else {
                    $(this).closest('.inside').find('.rb_typeoneentry .rb_options').removeClass('hidden');
                    $(this).closest('.inside').find('.rb_typeoneentry .rb_message').addClass('hidden');
                }
            });
        });
    },

    _initSortable = function() {
        if(typeof $('.rb_list').sortable === 'function') {
            $('.rb_list').sortable({
                handle: '.rb_drag_handler',
                update: function() {
                            var serial = $(this).sortable('serialize'),
                                $serial = $(this).closest('.rb_list_wrap').find('.rb_serial');
                            $serial.attr('value', serial);
                            
                            self.colorList.call(this, 500, {alt: '#f3f3f3', def: '#fafafa'});
                        }
            });
        }
    },

    _jsify = function() {
        /*
         * Prepare the select box for combobox usage.
         */
        $('.rb_addexisting')
            .removeAttr('multiple')
            .css({'height' : 'auto'})
            .each(function() {
                if ($(this).find('.rb_type') === 'n') {
                    $(this).find('.rb_addexisting_select option[value="null"]').remove();
                }
            });

        /*
         * Ensure "null" as a value means null, nada, nothing!
         */
        $('.rb_addexisting_select option[value="null"]').val('');

        /*
         * Ensure the save action knows that combobox was active
         */
        $('#publish').after('<input type="hidden" name="rb_js" value="on" />');
    },

    _enhanceCombobox = function() {
        /*
         * Initiate the combobox
         */
        $('.rb_addexisting_select').combobox({
            select: function(e, ui) {
                var option = $(ui.item.option),
                    $addBtn = option.closest('.inside').find('.rb_add');

                if ($(ui.item.option).closest('.rb_addexisting').find('.rb_type').html() === 1) {
                    var original = option.closest( '.rb_addexisting' ).find( '.rb_original' ).html();

                    option.closest('.rb_addexisting')
                        .find('option[selected="selected"]')
                        .removeAttr('selected');

                    if (option.attr('value') !== original) {
                        option.closest('.inside').find('.rb_options').fadeOut(
                            'fast',
                            function() {
                                option.closest('.inside').find('.rb_unsaved').fadeIn('fast');
                            }
                        );
                    } else {
                        option.closest('.inside').find('.rb_unsaved').fadeOut(
                            'fast',
                            function() {
                                option.closest('.inside').find('.rb_options').fadeIn('fast');
                            }
                        );
                    }
                    option.attr('selected', 'selected');
                }

                if (option.val() !== 'null') {
                    $addBtn.removeAttr('disabled');
                } else {
                    $addBtn.attr('disabled', 'disabled');
                }
            }
        });
        
        /*
         * Activate the add-button
         */
        _initAddButton();

        /*
         * Activate adding through keyboard
         */
        $('.rb_addexisting .ui-autocomplete-input').keypress(function(e) {
            /*
             * Filter Enter-Keystrokes
             */
            if (e.keyCode === 13) {
                var addButton = $(this).closest('.rb_addexisting').find('.rb_add');
                var list = $('ul.ui-autocomplete[style*="display: block"]')[0];
                var select = $(this).closest('.rb_addexisting').find('.rb_addexisting_select');

                e.preventDefault();
                if (addButton.attr('disabled') === 'disabled' && typeof list !== 'undefined') {
                    /*
                     * Button was not enabled = we're selecting an entry
                     */
                    $(this).attr('value', $(list).children('li').first().text());
                    addButton.removeAttr('disabled');
                    $(list).html('').css({'display' : 'none'});
                } else if(addButton.attr('disabled') !== 'disabled') {
                    /*
                     * Button was enabled = we're adding an entry to the list
                     */
                    _addexistingCB.call(
                        select.children('option[data-inner="'+$(this).attr('value').toLowerCase()+'"]')
                    );
                }
            }
        }).keyup(function(e) {
            /*
             * activate the Add-Button if we found a unique entry
             */
            var list = $('ul.ui-autocomplete[style*="display: block"]')[0];
            var select = $(this).closest('.rb_addexisting').find('.rb_addexisting_select');
            var addButton = $(this).closest('.rb_addexisting').find('.rb_add');
            var isComplete = false;
            
            if (typeof list !== 'undefined' && $(list).attr('data-clickinit') !== 'true') {
                $(list).click(function() {
                    $('.ui-combobox_wrap.current').next('.rb_add').removeAttr('disabled');
                }).attr('data-clickinit', 'true');
            }
            
            if (typeof select.children('option[data-inner="'+$(this).attr('value').toLowerCase()+'"]')[0] !== 'undefined') {
                isComplete = true;
            }
            
            if (isComplete) {
                if (e.keyCode !== 13) {
                    addButton.removeAttr('disabled');
                }
            } else {
                addButton.attr('disabled', 'disabled');
            }
        });

        /*
         * Activate the Add button when a list-entry has been clicked.
         */
        $('.ui-combobox input').focus(_setActiveCB);

        $('.ui-autocomplete li').live('click', function() {
            $('.ui-combobox.current').siblings('.rb_add').removeAttr('disabled');
        }).attr('data-clickinit', 'true');
    },

    _setActiveCB = function() {
        $('.ui-combobox.current').removeClass('current');
        $(this).closest('.ui-combobox').addClass('current');
    },

    _initAddButton = function() {
        $('.rb_add')
            .removeClass('hidden')
            .click(function(e) {
                e.preventDefault();
                var $base = $(this).closest('.rb_addexisting'),
                    targetString = $base.find('.ui-autocomplete-input').attr('value').toLowerCase(),
                    $target = $base.find('.rb_addexisting_select').children('option[data-inner="'+targetString+'"]');
                
                if ($target.length) {
                    _addexistingCB.call($target);
                }
            });
    },


    _addexistingCB = function() {
        var thiz = this,
            $prt = this.closest('.inside'),
            $laoder = $prt.find('.rb_loader'),
            $list = $prt.find('.rb_list'),
            type = $prt.find('.rb_type').text(),
            $cBInput = $prt.find('.ui-combobox input'),
            $serial = $prt.find('.rb_serial'),
            $btn = $prt.find('button.rb_add');

        /*
         * Deactivate the button again.
         */
        $btn.attr('disabled', 'disabled');

        $laoder.addClass('loading');
        $.post(ajaxurl, {
            action: 'rb_getLi',
            nonce: this.attr('data-nonce'),
            ID: $('#post_ID').attr('value'),
            type: $('#post_type').attr('value'),
            relID: this.attr('value'),
            relType: type
        }, function( r ) {
            $laoder.removeClass('loading');
            if (r !== '') {
                r = eval('('+r+')');
                    if (typeof r === 'object') {
                    $cBInput.val('');


                    var $newLi = $(r.content),
                        relatedOption = thiz.detach();

                    $list.append($newLi);
                    self.colorList($list, 500, {alt: '#f3f3f3', def: '#fafafa'});

                    $serial.val($list.sortable('serialize'));

                    _initUnsavedReladtionDelete.call($newLi, relatedOption);
                }
            }
        });
    },

    _initUnsavedReladtionDelete = function(relatedOption) {
        this.find('.rb_submitdelete').click(function(e) {
            e.preventDefault();

            var $entry = $(this).closest('.rb_listentry'),
                $prt = $entry.closest('.inside'),
                $select = $prt.find('.rb_addexisting_select'),
                $serial = $prt.find('.rb_serial'),
                $list = $prt.find('.rb_list');

            $select.append(relatedOption);
            $entry.remove();

            $serial.val($list.sortable('serialize'));
        });
    }


    /* PUBLIC METHODS */;

    this.colorList = function(speed, colors) {
        var i = -1;
        $.each($(this).children('li'), function() {
            var c = (i++/2 === Math.floor(i/2) ? colors.alt : colors.def);
            $(this).animate({'background-color' : c}, speed);
        });
    }

/* initiation */
;(function(){_init();$(document).ready(_ready);})();return this;})(jQuery)}});