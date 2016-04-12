<?php
/**
 * i18n Strings Class
 *
 * This class adds translation strings to the main JSON blob via pl_workarea_json filter
 *
 * To make a translatable string in use function PLTranslate(key) in your js and make sure
 * there is a corresponding entry in the translate array.
 * (If there isnt you will trigger a warning in console logs)
 *
 * @class     Platform_i18n
 * @version   5.0.0
 * @package   PageLines/Classes
 * @category  Class
 * @author    PageLines
 */
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class Platform_i18n {

  function __construct() {
    add_filter( 'pl_workarea_json', array( $this, 'translate' ) );
  }

  /**
   * Return array of strings used by pl_workarea_json filter
   */
  function translate( $settings ) {

    $strings = array(
      'add_custom_section_name'     => __( 'Add a custom name for this section.', 'pl-platform' ),
      'add_sections_to_page'        => __( 'Add Sections To Page', 'pl-platform' ),
      'additional_section_classes'  => __( 'Additional Section Classes', 'pl-platform' ),
      'advanced'                    => __( 'Advanced', 'pl-platform' ),
      'all_of_type'                 => __( 'All of Type', 'pl-platform' ),
      'are_you_sure'                => __( 'Are You Sure?', 'pl-platform' ),
      'background_advanced'         => __( 'Background Advanced', 'pl-platform' ),
      'background_and_color'        => __( 'Background and Color', 'pl-platform' ),
      'background_color'            => __( 'Background Color', 'pl-platform' ),
      'background_cover'            => __( 'Background Cover', 'pl-platform' ),
      'background_image'            => __( 'Background Image', 'pl-platform' ),
      'background_overlay'          => __( 'Background Overlay', 'pl-platform' ),
      'background_position'         => __( 'Background Position', 'pl-platform' ),
      'background_size'             => __( 'Background Size', 'pl-platform' ),
      'background_tile'             => __( 'Background Tile', 'pl-platform' ),
      'background_video'            => __( 'Background Video', 'pl-platform' ),
      'basic'                       => __( 'Basic', 'pl-platform' ),
      'carousel'                    => __( 'Carousel', 'pl-platform' ),
      'center'                      => __( 'Center', 'pl-platform' ),
      'clone'                       => __( 'Clone', 'pl-platform' ),
      'cols'                        => __( 'Cols', 'pl-platform' ),
      'columns'                     => __( 'Columns', 'pl-platform' ),
      'column12'                    => __( 'of 12 Columns', 'pl-platform' ),
      'components'                  => __( 'Components', 'pl-platform' ),
      'contain'                     => __( 'Contain', 'pl-platform' ),
      'content_formats'             => __( 'Content Formats', 'pl-platform' ),
      'content_height_width'        => __( 'Content Height / Width', 'pl-platform' ),
      'cover'                       => __( 'Cover', 'pl-platform' ),
      'current_page_only'           => __( 'Current Page Only', 'pl-platform' ),
      'dark_text'                   => __( 'Dark Text', 'pl-platform' ),
      'default'                     => __( 'Default', 'pl-platform' ),
      'delete_section'              => __( 'Delete Section...', 'pl-platform' ),
      'delete'                      => __( 'Delete', 'pl-platform' ),
      'edit_sets'                   => __( 'Edit Sets', 'pl-platform' ),
      'edit'                        => __( 'Edit', 'pl-platform' ),
      'font_color'                  => __( 'Font Color', 'pl-platform' ),
      'font_size_and_alignment'     => __( 'Font Size and Alignment', 'pl-platform' ),
      'font_size'                   => __( 'Font Size', 'pl-platform' ),
      'gallery'                     => __( 'Gallery', 'pl-platform' ),
      'grid_and_sizing'             => __( 'Grid and Sizing', 'pl-platform' ),
      'grid_controls'               => __( 'Grid Controls', 'pl-platform' ),
      'height'                      => __( 'Height', 'pl-platform' ),
      'hide_on_pages'               => __( 'Hide On Page(s)', 'pl-platform' ),
      'hidden'                      => __( 'Hidden', 'pl-platform' ),
      'hide_with_comma'             => __( 'Hide this section on certain pages by adding their IDs here separated by a comma', 'pl-platform' ),
      'layout_containers'           => __( 'Layout / Containers', 'pl-platform' ),
      'left'                        => __( 'Left', 'pl-platform' ),
      'light_text'                  => __( 'Light Text', 'pl-platform' ),
      'margin'                      => __( 'Margin', 'pl-platform' ),
      'max_width'                   => __( 'Max Width (in px)', 'pl-platform' ),
      'min_height'                  => __( 'Min Height (in vw)', 'pl-platform' ),
      'navigation_menus'            => __( 'Navigation / Menus', 'pl-platform' ),
      'no_custom_options_added'     => __( 'No Custom Options Added.', 'pl-platform' ),
      'no_custom_options'           => __( 'No Custom Options', 'pl-platform' ),
      'no_tile'                     => __( 'No Tile', 'pl-platform' ),
      'none'                        => __( 'None', 'pl-platform' ),
      'offset'                      => __( 'Offset', 'pl-platform' ),
      'offset12'                    => __( 'of 12 Offsets', 'pl-platform' ),
      'padding_margin'              => __( 'Padding / Margin', 'pl-platform' ),
      'padding'                     => __( 'Padding', 'pl-platform' ),
      'page_builder'                => __( 'Page Editor', 'pl-platform' ),
      'page_layout'                 => __( 'Page Layout', 'pl-platform' ),
      'reference'                   => __( 'Reference', 'pl-platform' ),
      'relative_to_base'            => __( 'Relative to Base', 'pl-platform' ),
      'remove_from_page'            => __( 'This will remove this section and its settings from this page.', 'pl-platform' ),
      'right'                       => __( 'Right', 'pl-platform' ),
      'save_changes'                => __( 'Save Changes', 'pl-platform' ),
      'scope'                       => __( 'Scope', 'pl-platform' ),
      'section_copy_paste'          => __( 'Section Copy / Paste', 'pl-platform' ),
      'section_info_help'           => __( 'Use this ID in CSS/JS to target this specific section.', 'pl-platform' ),
      'section_info'                => __( 'Section Info', 'pl-platform' ),
      'seperate_space'              => __( 'Seperate classes with a space', 'pl-platform' ),
      'show_in_builder'             => __( 'Show in Editor', 'pl-platform' ),
      'show'                        => __( 'Show', 'pl-platform' ),
      'size_and_scroll_effects'     => __( 'Size and Scroll Effect', 'pl-platform' ),
      'sliders_features'            => __( 'Sliders / Features', 'pl-platform' ),
      'social_local'                => __( 'Social / Local', 'pl-platform' ),
      'taxonomy_archive'            => __( 'Taxonomy Archive', 'pl-platform' ),
      'text_element_align'          => __( 'Text/Element Alignment', 'pl-platform' ),
      'text_element_base_color'     => __( 'Text / Element Base Color', 'pl-platform' ),
      'tile_h'                      => __( 'Tile Horizontal', 'pl-platform' ),
      'tile_v'                      => __( 'Tile Vertical', 'pl-platform' ),
      'tile'                        => __( 'Tile', 'pl-platform' ),
      'using_a_theme'               => __( 'Using a Theme', 'pl-platform' ),
      'region_disabled'             => __( 'This region is disabled', 'pl-platform'),
      'utilities'                   => __( 'Utilities', 'pl-platform' ),
      'widgets_sidebar'             => __( 'Widgets / Sidebar', 'pl-platform' ),
      'window_height'               => __( 'Window Height', 'pl-platform' ),
    );
    $settings['translate'] = $strings;
    return $settings;
  }
}
new Platform_i18n;
