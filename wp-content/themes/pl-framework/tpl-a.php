<?php
/*
 * Template Name: Framework A
 */


global $pl_controller; 

pl_add_static_settings( $pl_controller->content->section_opts() );

$pl_controller->content->get_loop(); 