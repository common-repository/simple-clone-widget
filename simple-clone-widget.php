<?php
/*
Plugin Name: Simple clone widget
Plugin URI: http://yaroslava.me/simple-clone-widget
Description: Simple clone widget with button "Clone this!"
Version: 1.0
Author: Yaroslava Kotova
Author URI: http://yaroslava.me/

Copyright 2016 Yaroslava Kotova  (email: roboyaroslava@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/* Include css file */
function add_simple_clone_stylesheet() 
{
    wp_enqueue_style( 'myCSS', plugins_url( '/css/simple-clone-widget.css', __FILE__ ) );
}

add_action('init', 'add_simple_clone_stylesheet');


class Simple_Clone_Widgets {
	function __construct() {
		add_filter( 'admin_head', array( $this, 'clone_script'  )  );
	}
	function clone_script() {
		global $pagenow;
		if( $pagenow != 'widgets.php' )
			return;?>
<script>
(function($) {
    if(!window.Simple) window.Simple = {};
    Simple.CloneWidgets = {
        init: function() {
            $(document.body).bind('click.widgets-clone', function(e) {
                var $target = $(e.target);
                if($target.closest('.clone-widget-active').length && !$target.parents('#available-widgets').length) {
                    e.stopPropagation();
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    Simple.CloneWidgets.Clone($target.parents('.widget'));
                }
            });
            Simple.CloneWidgets.Add();
        },
        Add: function() {
            $('#widgets-right').off('DOMSubtreeModified', Simple.CloneWidgets.Add);
            $('#widgets-right .widget:not(.Simple-cloneable)').each(function() {
                var $widget = $(this)
                    , $clone = $('<a class="clone-widget" title="Clone widget now!">')
                ;
                $widget.addClass('Simple-cloneable')
                    .find('.widget-top')
                    .prepend($('<div class="clone-widget-active widget-title-action">').append($clone))
                ;
                $widget.addClass('Simple-cloneable');
            });
            $('#widgets-right').on('DOMSubtreeModified', Simple.CloneWidgets.Add);
        },
        Clone: function($original) {
            var $widget = $original.clone();
            // Find this widget ID in base. Find number and duplicate.
            var idbase = $widget.find('input.id_base').val()
                , $source = $('#available-widgets').find('.id_base[value="' + idbase + '"]').parents('.widget')
                , widgetId = $source.find('.widget-id').val()
                , multi = parseInt($source.find('.multi_number').val())
                , number = parseInt($widget.find('.widget_number').val())
                , newNum = number + 1
            ;
            $widget.find('.widget-content').find('input,select,textarea').each(function() {
                $(['name', 'id']).each(function(i, attr) {
                    var val = $(this).attr(attr);
                    if(val) {
                        $(this).attr(attr, val.replace(new RegExp('([-\\[])' + number + '([-\\]]?|$)'), '$1' + newNum + '$2'));
                    }
                });
            });
            // Unique id new widget:
            var newid = 0;
            $('.widget').each(function() {
                var match = this.id.match(/^widget-(\d+)/);
                if(match && parseInt(match[1]) > newid)
                    newid = parseInt(match[1]);
            });
            newid++;
            // Figure out value of add_new from the source widget:
            var add = $source.find('.add_new').val();
            // Calculate new widget ID and multi number
            if ('multi' === add) {
                multi++;
                $widget.attr( 'id', 'widget-' + newid + '_' + widgetId.replace('__i__', multi));
                $source.find('input.multi_number').val(multi);
                $widget.find('.multi_number').val(multi);
                $widget.find('input.widget-id').val(idbase + '-' + multi)
            } else if ( 'single' === add ) {
                $widget.attr('id', 'new-' + widgetId);
                $widget.find('input.widget-id').val(idbase);
            }
            $widget.find('input.add_new').val(add);
            $widget.find('input.widget_number').val(newNum);
            $widget.hide();
            $original.after($widget);
            $original.removeClass('open').find('.widget-inside').hide();
            $widget.addClass('open').find('.widget-inside').show();
            $widget.fadeIn(300).fadeOut(300).fadeIn(300);
            wpWidgets.save($widget, 0, 0, 1);
        }
    }
    $(Simple.CloneWidgets.init);
})(jQuery);
</script>
<?php
    }
}
$GLOBALS['Simple_Clone_Widgets'] = new Simple_Clone_Widgets();