var umb_active_tiny_mce;
(function() {
    /*Accordion*/
	tinymce.PluginManager.add('accordion_btn', function( editor, url ) {
        editor.addButton('accordion_btn', {
			title : 'Accordion',
			image : url+'/img/toggle.png',
			onclick : function() {
				umb_active_tiny_mce = editor;
				tb_show('Accordion', 'admin-ajax.php?action=umb_tabs_wizard&shortcode1=accordiongroup&shortcode2=accordion');
			}
		});
    });
    /*Accordion*/

    /*Toggle*/
    tinymce.PluginManager.add('toggle_btn', function(ed,url){
		ed.addButton('toggle_btn', {
			title : 'Toggle',
			image : url+'/img/toggle.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				tb_show('Toggle', 'admin-ajax.php?action=umb_tabs_wizard&shortcode1=togglegroup&shortcode2=toggle');
			}
		});
	});
    /*Toggle*/

    /*Tabs*/
    tinymce.PluginManager.add('tab_btn', function(editor,url){
		editor.addButton('tab_btn', {
			title : 'Tabs',
			image : url+'/img/tab.png',
			onclick : function() {
				umb_active_tiny_mce = editor;
				tb_show('Tabs', 'admin-ajax.php?action=umb_tabs_wizard&shortcode1=tabgroup&shortcode2=tab');
			}
		});
	});
    /*Tabs*/

    /*Notification*/
    tinymce.PluginManager.add('alert_btn', function(editor,url){
		editor.addButton('alert_btn', {
			title : 'Notification',
			image : url+'/img/notification.png',
			onclick : function() {
				umb_active_tiny_mce = editor;
				tb_show('Notification', 'admin-ajax.php?action=umb_notification_wizard');
			}
		});
	});
    /*Notificaion*/

    /*Dropcap*/
    tinymce.PluginManager.add('dropcap1_btn', function(ed,url){
		ed.addButton('dropcap1_btn', {
			title : 'Dropcap 1',
			image : url+'/img/dropcap.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				umb_active_tiny_mce.selection.setContent('[dropcap1]'+ed.selection.getContent()+'[/dropcap1]');
			}
		});
	});

    tinymce.PluginManager.add('dropcap2_btn', function(ed,url){
		ed.addButton('dropcap2_btn', {
			title : 'Dropcap 2',
			image : url+'/img/dropcap.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				umb_active_tiny_mce.selection.setContent('[dropcap2]'+ed.selection.getContent()+'[/dropcap2]');
			}
		});
	});
    /*Dropcap*/

    /*Highlights*/
    tinymce.PluginManager.add('highlight_btn', function(ed,url){
		ed.addButton('highlight_btn', {
			title : 'Highlight',
			image : url+'/img/dropcap.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				umb_active_tiny_mce.selection.setContent('[highlight]'+ed.selection.getContent()+'[/highlight]');
			}
		});
	});
    /*Highlights*/

    /*Boxed Content*/
    tinymce.PluginManager.add('boxed_btn', function(ed,url){
		ed.addButton('boxed_btn', {
			title : 'Boxed',
			image : url+'/img/paragraph.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				umb_active_tiny_mce.selection.setContent('[boxed]'+ed.selection.getContent()+'[/boxed]');
			}
		});
	});

    tinymce.PluginManager.add('boxed2_btn', function(ed,url){
		ed.addButton('boxed2_btn', {
			title : 'Boxed 2',
			image : url+'/img/paragraph.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				umb_active_tiny_mce.selection.setContent('[boxed2]'+ed.selection.getContent()+'[/boxed2]');
			}
		});
	});
    /*Boxed Content*/

    /*Buttons*/
    tinymce.PluginManager.add('button_btn', function(ed,url){
		ed.addButton('button_btn', {
			title : 'Button',
			image : url+'/img/buttons.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				tb_show('Buttons', 'admin-ajax.php?action=umb_buttons_wizard');
			}
		});
	});
    /*Buttons*/

    /*Video*/
    tinymce.PluginManager.add('video_btn', function(ed,url){
		ed.addButton('video_btn', {
			title : 'Video',
			image : url+'/img/video.png',
			onclick : function() {
				umb_active_tiny_mce = ed;
				tb_show('Video', 'admin-ajax.php?action=umb_video_wizard');
			}
		});
	});
    /*Video*/

    /*tinymce.create('tinymce.plugins.layout', {
        createControl: function(n, cm) {
            switch (n) {
                case 'layout':
                    var mlb = cm.createListBox('layout', {
                        title : 'Layout',
                        onselect : function(v) {
                            switch(v){
                                case 'full_width' : tinyMCE.activeEditor.selection.setContent("[layout][layout_group][full_width]"+tinyMCE.activeEditor.selection.getContent() +"[/full_width][/layout_group][/layout]");break;
                                case 'half_width' : break;
                                case 'one_third' : break;
                                case 'one_fourth' : break;
                                case 'one_sixth' : break;
                            }

                        }
                    });

                    // Add some values to the list box
                    mlb.add('Full Width', 'full_width');
                    mlb.add('Half Width', 'half_width');
                    mlb.add('One Third', 'one_third');
                    mlb.add('One Fourth', 'one_fourth');
                    mlb.add('One Sixth', 'one_sixth');

                    // Return the new listbox instance
                    return mlb;
            }
            return null;
        }
    });
    tinymce.PluginManager.add('layout', tinymce.plugins.layout);*/
	tinymce.PluginManager.add('um_layout_btn', function(ed,url){
		ed.addButton('um_layout_btn', {
			title : 'Layout',
			type: 'menubutton',
			icon : 'icon dashicons dashicons-screenoptions',
			menu : [
				{
					text: 'Full Width',
					value: 'Full Width',
					onclick: function() {
						tinyMCE.activeEditor.selection.setContent("[layout][layout_group][full_width]"+tinyMCE.activeEditor.selection.getContent() +"[/full_width][/layout_group][/layout]");
					}
				},
				{
					text: 'Half Width',
					value: 'Half Width',
					onclick: function() {
						tinyMCE.activeEditor.selection.setContent("[layout][layout_group][half_width]"+tinyMCE.activeEditor.selection.getContent() +"[/half_width][half_width][/half_width][/layout_group][/layout]");
					}
				},
				{
					text: 'One Third',
					value: 'One Third',
					onclick: function() {
						tinyMCE.activeEditor.selection.setContent("[layout][layout_group][one_third]"+tinyMCE.activeEditor.selection.getContent() +"[/one_third][one_third][/one_third][one_third][/one_third][/layout_group][/layout]");
					}
				},
				{
					text: 'One Fourth',
					value: 'One Fourth',
					onclick: function() {
						tinyMCE.activeEditor.selection.setContent("[layout][layout_group][one_fourth]"+tinyMCE.activeEditor.selection.getContent() +"[/one_fourth][one_fourth][/one_fourth][one_fourth][/one_fourth][one_fourth][/one_fourth][/layout_group][/layout]");
					}
				},
				{
					text: 'One Sixth',
					value: 'One Sixth',
					onclick: function() {
						tinyMCE.activeEditor.selection.setContent("[layout][layout_group][one_sixth]"+tinyMCE.activeEditor.selection.getContent() +"[/one_sixth][one_sixth][/one_sixth][one_sixth][/one_sixth][one_sixth][/one_sixth][one_sixth][/one_sixth][one_sixth][/one_sixth][/layout_group][/layout]");
					}
				}
			]
		});
	});

})();