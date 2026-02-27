<?php
/**
 * Beaver Builder Module — LDAP Staff Directory.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only register when FL Builder is active.
if ( ! class_exists( 'FLBuilder' ) ) {
	return;
}

FLBuilder::register_module(
	'LDAP_ED_BB_Module',
	array(

		// ── General tab ──────────────────────────────────────────────────────
		'general' => array(
			'title'    => __( 'General', 'employee-directory-business' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(

						'fields_to_show' => array(
							'type'    => 'select',
							'label'   => __( 'Fields to Display', 'employee-directory-business' ),
							'multi-select' => true,
							'default' => array( 'name', 'email', 'title', 'department' ),
							'options' => array(
								'name'       => __( 'Full Name', 'employee-directory-business' ),
								'email'      => __( 'Email', 'employee-directory-business' ),
								'title'      => __( 'Job Title', 'employee-directory-business' ),
								'department' => __( 'Department', 'employee-directory-business' ),
								'phone'      => __( 'Phone', 'employee-directory-business' ),
							),
						),

						'per_page' => array(
							'type'    => 'unit',
							'label'   => __( 'Items per Page', 'employee-directory-business' ),
							'default' => '20',
							'units'   => array( '' ),
						),

						'enable_search' => array(
							'type'    => 'select',
							'label'   => __( 'Enable Search Bar', 'employee-directory-business' ),
							'default' => 'true',
							'options' => array(
								'true'  => __( 'Yes', 'employee-directory-business' ),
								'false' => __( 'No', 'employee-directory-business' ),
							),
						),

						'columns' => array(
							'type'    => 'select',
							'label'   => __( 'Columns', 'employee-directory-business' ),
							'default' => '3',
							'options' => array(
								'1' => '1',
								'2' => '2',
								'3' => '3',
								'4' => '4',
							),
						),

					),
				),
			),
		),

		// ── Style tab ────────────────────────────────────────────────────────
		'style' => array(
			'title'    => __( 'Style', 'employee-directory-business' ),
			'sections' => array(
				'colors' => array(
					'title'  => __( 'Colors', 'employee-directory-business' ),
					'fields' => array(

						'primary_color' => array(
							'type'    => 'color',
							'label'   => __( 'Primary Color', 'employee-directory-business' ),
							'default' => '0073aa',
						),

						'card_bg_color' => array(
							'type'    => 'color',
							'label'   => __( 'Card Background', 'employee-directory-business' ),
							'default' => 'ffffff',
						),

						'text_color' => array(
							'type'    => 'color',
							'label'   => __( 'Text Color', 'employee-directory-business' ),
							'default' => '3c434a',
						),

					),
				),

				'typography' => array(
					'title'  => __( 'Typography', 'employee-directory-business' ),
					'fields' => array(
						'font_size' => array(
							'type'    => 'unit',
							'label'   => __( 'Font Size (px)', 'employee-directory-business' ),
							'default' => '14',
							'units'   => array( 'px' ),
						),
					),
				),

				'layout' => array(
					'title'  => __( 'Layout', 'employee-directory-business' ),
					'fields' => array(
						'gap' => array(
							'type'    => 'unit',
							'label'   => __( 'Cards Gap (px)', 'employee-directory-business' ),
							'default' => '20',
							'units'   => array( 'px' ),
						),
						'border_radius' => array(
							'type'    => 'unit',
							'label'   => __( 'Border Radius (px)', 'employee-directory-business' ),
							'default' => '6',
							'units'   => array( 'px' ),
						),
					),
				),
			),
		),

		// ── Advanced tab ─────────────────────────────────────────────────────
		'advanced' => array(
			'title'    => __( 'Advanced', 'employee-directory-business' ),
			'sections' => array(
				'custom_css_section' => array(
					'title'  => __( 'Custom CSS', 'employee-directory-business' ),
					'fields' => array(
						'custom_css' => array(
							'type'  => 'code',
							'label' => __( 'Custom CSS', 'employee-directory-business' ),
							'mode'  => 'css',
							'rows'  => 10,
						),
					),
				),
			),
		),

	)
);

class LDAP_ED_BB_Module extends FLBuilderModule {

	public function __construct() {
		parent::__construct(
			array(
				'name'            => __( 'LDAP Staff Directory', 'employee-directory-business' ),
				'description'     => __( 'Displays an employee directory from an LDAP/LDAPS server.', 'employee-directory-business' ),
				'group'           => __( 'General', 'employee-directory-business' ),
				'category'        => __( 'Basic', 'employee-directory-business' ),
				'dir'             => LDAP_ED_DIR . 'beaver-builder/',
				'url'             => LDAP_ED_URL . 'beaver-builder/',
				'icon'            => 'button.svg',
				'partial_refresh' => true,
			)
		);
	}
}
