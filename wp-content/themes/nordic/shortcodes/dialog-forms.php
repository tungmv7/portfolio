<?php
add_action('wp_ajax_umb_tabs_wizard', 'umb_tabs_wizard');
function umb_tabs_wizard(){
    ?>
    <style>

        #TB_window {
            overflow: auto;
        }

        #TB_ajaxContent {
            height: auto !important;
            padding: 2px 15px 22px 15px;
        }
        #um_tabs_form {
            width: 100%;
            height: auto;
        }
        table.um_tabs tr td{
            border: 1px solid black;
        }
        table.um_tabs {
            padding: 20px;
            table-layout:fixed;
            width: 100%;
        }

        table.um_tabs tr td {
            border: none;
            background: #f3f3f3;
            padding: 5px 10px;
            vertical-align: top;
        }

        table.um_tabs tr td input[type=text] {
            width: 100%;
        }

        table.um_tabs tr td textarea {
            max-width: 100%;
            min-width: 100%;
            height: 111px;
            margin: 1px;
            width: 162px;
        }

        table.um_tabs tr td a.um_remove_row {
            background: #df7e7e;
            color: #fff !important;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            width: 100%;
            display: block;
            padding: 10px;
            box-sizing: border-box;
            text-align: center;
        }

        table.um_tabs .head_title > td {
            background: #40a965;
            border: none;
            padding: 6px;
            font-size: 12px;
            color: #fff;
            text-transform: uppercase;
            font-weight: 700;
            text-align: center;
        }

        #um_add_row, button#aes-submit {
            background: #40a965;
            color: #fff !important;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            padding: 10px;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            -ms-box-sizing: border-box;
            box-sizing: border-box;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        #um_add_row {
            margin-left: 22px;
        }

        #TB_ajaxContent {
            width: 100% !important;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            -ms-box-sizing: border-box;
            box-sizing: border-box;
        }
    </style>
    <script type="text/javascript">
        jQuery('#aes-submit').click(function () {
            var shortcode = "[<?php echo $_REQUEST["shortcode1"]; ?>]";
            jQuery("table.um_tabs tr:gt(0)").each(function () {
                var title = jQuery(this).find("input:text").val();
                var content = jQuery(this).find("textarea").val();
                var tmpshortcode = '[<?php echo $_REQUEST["shortcode2"]; ?> title="' + title + '"]' + content + '[/<?php echo $_REQUEST["shortcode2"]; ?>]';
                shortcode += tmpshortcode;
            });
            shortcode += "[/<?php echo $_REQUEST["shortcode1"]; ?>]";
            umb_active_tiny_mce.selection.setContent(shortcode);
            tb_remove();
        })
        jQuery(document).ready(function ($) {
            jQuery("a#um_add_row").click(function (e) {
                e.preventDefault();
                var tablerow = "<tr><td><input type='text'/></td><td><textarea></textarea></td><td><a href='#' class='um_remove_row'>Remove</a></td></tr>";
                $("table.um_tabs").append(tablerow);
            });
            $("a.um_remove_row").live("click", function (e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        });
    </script>
    <div id="um_tabs_form">
        <table class="um_tabs">
            <tr class="head_title">
                <td><?php _e("Title","um_lang"); ?></td> <td><?php _e("Content","um_lang"); ?></td> <td></td>
            </tr>
        </table>
        <a href="#" id="um_add_row"><?php _e("Add Row","um_lang"); ?></a>
        <button id="aes-submit"><?php _e("Get Shortcode","um_lang"); ?></button>
    </div>
    <?php
    die;
}
add_action('wp_ajax_umb_notification_wizard', 'umb_notification_wizard');
function umb_notification_wizard(){
    ?>
    <script>
        jQuery(document).ready(function ($) {
            $("#aes-submit").click(function () {
                var type = $("#um_notification").val();
                var title = $("#um_notify_title1").val();
                var content = $("#um_notify_content").val();
                umb_active_tiny_mce.selection.setContent('[notification type="' + type + '" title="' + title + '"]' + content + '[/notification]');
                tb_remove();
            });
        });
    </script>
    <style type="text/css">
        #TB_ajaxContent {
            color: #40a965;
            text-transform:uppercase;
            padding-top: 20px;
            vertical-align: middle;
            overflow: hidden;
        }

        #TB_ajaxContent input, #TB_ajaxContent textarea, #TB_ajaxContent button, #TB_ajaxContent #um_notification {
            border: none;
            background: #f3f3f3;
            border-radius: 0px;
            color: #8e8e8e;
            padding: 2px 5px;
        }

        #TB_ajaxContent textarea {
            width: 100%;
            height: 60px;
        }

        #TB_ajaxContent #aes-submit {
            background: #40a965;
            color: #fff !important;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            padding: 10px;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            -ms-box-sizing: border-box;
            box-sizing: border-box;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        #um_notify_title1 {
            width: 161px;
            padding: 4px !important;
        }

    </style>
    <?php _e("Type","um_lang"); ?>
    <select id="um_notification">
        <option value="info_alert"><?php _e("Info","um_lang"); ?></option>
        <option value="success_alert"><?php _e("Success","um_lang"); ?></option>
        <option value="warning_alert"><?php _e("Warning","um_lang"); ?></option>
        <option value="error_alert"><?php _e("Error","um_lang"); ?></option>
    </select><br><br>
    <?php _e("Title","um_lang"); ?>
<input type="text" name="um_notify_title" id="um_notify_title1"/><br><br>
    <?php _e("Message","um_lang"); ?><br>
    <textarea id="um_notify_content"></textarea><br><br>
    <button id="aes-submit"><?php _e("Get Shortcode","um_lang"); ?></button>
    <?php
    die;
}

add_action('wp_ajax_umb_buttons_wizard', 'umb_buttons_wizard');

function umb_buttons_wizard(){

    ?>
    <script>
        jQuery(document).ready(function ($) {
            $("#aes-submit").click(function () {
                var URL = $("#URL").val();
                var color = $("#color").val();
                umb_active_tiny_mce.selection.setContent('[button url="'+URL+'" color="'+color+'"]'+umb_active_tiny_mce.selection.getContent()+'[/button]');
                tb_remove();
            });
        });
    </script>
    <style>
        #TB_ajaxContent {
            width: auto !important;
            height: auto !important;
            padding-top: 15px;
        }

        #TB_ajaxContent #aes-submit {
            padding: 5px 40px;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 300;
            color: #ffffff;
            display: inline-block;
            background-color: #0656c9;
            border: 0px;
        }

        #TB_ajaxContent #aes-submit:hover {
            cursor: pointer;
        }

        #TB_ajaxContent input[type=text] {
            background-color: transparent;
            border-radius: 0px;
            border: 1px solid #9098a3;
            box-shadow: none;
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
        }
    </style>
    <input type="text" id="URL"/>
    <select id="color">
        <option value="white">White</option>
        <option value="dark-grey">Dark Grey</option>
        <option value="green">Green</option>
    </select>
    <button id="aes-submit"><?php _e("Get Shortcode","um_lang"); ?></button>
    <?php
    die;
}

add_action('wp_ajax_umb_video_wizard', 'umb_video_wizard');

function umb_video_wizard(){

    ?>
    <script>
        var custom_uploader;
        var callback = function(){};

        jQuery(document).ready(function($){

            function initMedia(){

                if (custom_uploader) {
                    custom_uploader.on('select',function(){
                        callback();
                    });
                    custom_uploader.open();
                    return;
                }

                custom_uploader = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: false
                });
                custom_uploader.on('select',function(){
                    callback();
                });
                custom_uploader.open();
            }

            $("#poster").click(function(){
                callback = function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $("#for_poster").val(attachment.url);
                };
                initMedia();
            });

            $("#video_mp4").click(function(){
                callback = function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $("#for_video_mp4").val(attachment.url);
                };
                initMedia();
            });

            $("#video_vp8").click(function(){
                callback = function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $("#for_video_vp8").val(attachment.url);
                };
                initMedia();
            });

            $("#video_ogg").click(function(){
                callback = function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $("#for_video_ogg").val(attachment.url);
                };
                initMedia();
            });

            $("#submit").click(function(){
                var poster = $("#for_poster").val();
                var video_mp4 = $("#for_video_mp4").val();
                var video_vp8 = $("#for_video_vp8").val();
                var video_ogg = $("#for_video_ogg").val();
                umb_active_tiny_mce.selection.setContent('[video poster="'+poster+'" videomp4="'+video_mp4+'" videovp8="'+video_vp8+'" video_ogg="'+video_ogg+'"][/video]');
                tb_remove();
            });
        });
    </script>

    <style>
        #TB_ajaxContent {
            width: auto !important;
            height: auto !important;
            padding-top: 15px;
        }

        #TB_ajaxContent > div + div {
            margin-top: 10px;
        }

        #TB_ajaxContent > div > * {
            width: 32.3%;
            display: inline-block;
        }

        #TB_ajaxContent input[type=button] {
            padding: 4px 40px;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 300;
            color: #ffffff;
            display: inline-block;
            background-color: #0656c9;
            border: 0px;
            margin-left: -5px;
        }

        #TB_ajaxContent input[type=button]#submit {
            margin-top: 20px;
        }

        #TB_ajaxContent input[type=button]:hover {
            cursor: pointer;
        }

        #TB_ajaxContent input[type=text][readonly=readonly] {
            background-color: #eaeaea;
            border-radius: 0px;
            border: 1px solid #9098a3;
            box-shadow: none;
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
        }
    </style>

    <div>
        <label for="for_poster">Chose Poster :</label>
        <input type="text" id="for_poster" readonly="readonly"/>
        <input type="button" id="poster" value="Poster"/>
    </div>

    <div>
        <label for="for_video_mp4">Chose Video MP4 :</label>
        <input type="text" id="for_video_mp4" readonly="readonly"/>
        <input type="button" id="video_mp4" value="Poster"/>
    </div>

    <div>
        <label>Chose Video WebM/VP8 :</label>
        <input type="text" id="for_video_vp8" readonly="readonly"/>
        <input type="button" id="video_vp8" value="Poster"/>
    </div>

    <div>
        <label>Chose Video OGG :</label>
        <input type="text" id="for_video_ogg" readonly="readonly"/>
        <input type="button" id="video_ogg" value="Poster"/>
    </div>

    <input type="button" value="Get Shortcode" id="submit"/>

    <?php
    die;
}
?>