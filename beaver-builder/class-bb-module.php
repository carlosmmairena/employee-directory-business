<?php
/**
 * Beaver Builder Module — LDAP Employee Directory.
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
			'title'    => __( 'General', 'ldap-employee-directory' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(

						'fields_to_show' => array(
							'type'    => 'select',
							'label'   => __( 'Fields to Display', 'ldap-employee-directory' ),
							'multi-select' => true,
							'default' => array( 'name', 'email', 'title', 'department' ),
							'options' => array(
								'name'       => __( 'Full Name', 'ldap-employee-directory' ),
								'email'      => __( 'Email', 'ldap-employee-directory' ),
								'title'      => __( 'Job Title', 'ldap-employee-directory' ),
								'department' => __( 'Department', 'ldap-employee-directory' ),
							),
						),

						'per_page' => array(
							'type'    => 'unit',
							'label'   => __( 'Items per Page', 'ldap-employee-directory' ),
							'default' => '20',
							'units'   => array( '' ),
						),

						'enable_search' => array(
							'type'    => 'select',
							'label'   => __( 'Enable Search Bar', 'ldap-employee-directory' ),
							'default' => 'true',
							'options' => array(
								'true'  => __( 'Yes', 'ldap-employee-directory' ),
								'false' => __( 'No', 'ldap-employee-directory' ),
							),
						),

						'columns' => array(
							'type'    => 'select',
							'label'   => __( 'Columns', 'ldap-employee-directory' ),
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
			'title'    => __( 'Style', 'ldap-employee-directory' ),
			'sections' => array(
				'colors' => array(
					'title'  => __( 'Colors', 'ldap-employee-directory' ),
					'fields' => array(

						'primary_color' => array(
							'type'    => 'color',
							'label'   => __( 'Primary Color', 'ldap-employee-directory' ),
							'default' => '0073aa',
						),

						'card_bg_color' => array(
							'type'    => 'color',
							'label'   => __( 'Card Background', 'ldap-employee-directory' ),
							'default' => 'ffffff',
						),

						'text_color' => array(
							'type'    => 'color',
							'label'   => __( 'Text Color', 'ldap-employee-directory' ),
							'default' => '3c434a',
						),

					),
				),

				'typography' => array(
					'title'  => __( 'Typography', 'ldap-employee-directory' ),
					'fields' => array(
						'font_size' => array(
							'type'    => 'unit',
							'label'   => __( 'Font Size (px)', 'ldap-employee-directory' ),
							'default' => '14',
							'units'   => array( 'px' ),
						),
					),
				),

				'layout' => array(
					'title'  => __( 'Layout', 'ldap-employee-directory' ),
					'fields' => array(
						'gap' => array(
							'type'    => 'unit',
							'label'   => __( 'Cards Gap (px)', 'ldap-employee-directory' ),
							'default' => '20',
							'units'   => array( 'px' ),
						),
						'border_radius' => array(
							'type'    => 'unit',
							'label'   => __( 'Border Radius (px)', 'ldap-employee-directory' ),
							'default' => '6',
							'units'   => array( 'px' ),
						),
					),
				),
			),
		),

		// ── Advanced tab ─────────────────────────────────────────────────────
		'advanced' => array(
			'title'    => __( 'Advanced', 'ldap-employee-directory' ),
			'sections' => array(
				'custom_css_section' => array(
					'title'  => __( 'Custom CSS', 'ldap-employee-directory' ),
					'fields' => array(
						'custom_css' => array(
							'type'  => 'code',
							'label' => __( 'Custom CSS', 'ldap-employee-directory' ),
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
				'name'            => __( 'LDAP Employee Directory', 'ldap-employee-directory' ),
				'description'     => __( 'Displays an employee directory from an LDAP/LDAPS server.', 'ldap-employee-directory' ),
				'group'           => __( 'General', 'ldap-employee-directory' ),
				'category'        => __( 'Basic', 'ldap-employee-directory' ),
				'dir'             => LDAP_ED_DIR . 'beaver-builder/',
				'url'             => LDAP_ED_URL . 'beaver-builder/',
				'icon'            => 'button.svg',
				'partial_refresh' => true,
			)
		);
	}
}
