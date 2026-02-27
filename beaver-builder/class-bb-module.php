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
			'title'    => __( 'General', 'ldap-staff-directory' ),
			'sections' => array(
				'general' => array(
					'title'  => '',
					'fields' => array(

						'fields_to_show' => array(
							'type'    => 'select',
							'label'   => __( 'Fields to Display', 'ldap-staff-directory' ),
							'multi-select' => true,
							'default' => array( 'name', 'email', 'title', 'department' ),
							'options' => array(
								'name'       => __( 'Full Name', 'ldap-staff-directory' ),
								'email'      => __( 'Email', 'ldap-staff-directory' ),
								'title'      => __( 'Job Title', 'ldap-staff-directory' ),
								'department' => __( 'Department', 'ldap-staff-directory' ),
								'phone'      => __( 'Phone', 'ldap-staff-directory' ),
							),
						),

						'per_page' => array(
							'type'    => 'unit',
							'label'   => __( 'Items per Page', 'ldap-staff-directory' ),
							'default' => '20',
							'units'   => array( '' ),
						),

						'enable_search' => array(
							'type'    => 'select',
							'label'   => __( 'Enable Search Bar', 'ldap-staff-directory' ),
							'default' => 'true',
							'options' => array(
								'true'  => __( 'Yes', 'ldap-staff-directory' ),
								'false' => __( 'No', 'ldap-staff-directory' ),
							),
						),

						'columns' => array(
							'type'    => 'select',
							'label'   => __( 'Columns', 'ldap-staff-directory' ),
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
			'title'    => __( 'Style', 'ldap-staff-directory' ),
			'sections' => array(
				'colors' => array(
					'title'  => __( 'Colors', 'ldap-staff-directory' ),
					'fields' => array(

						'primary_color' => array(
							'type'    => 'color',
							'label'   => __( 'Primary Color', 'ldap-staff-directory' ),
							'default' => '0073aa',
						),

						'card_bg_color' => array(
							'type'    => 'color',
							'label'   => __( 'Card Background', 'ldap-staff-directory' ),
							'default' => 'ffffff',
						),

						'text_color' => array(
							'type'    => 'color',
							'label'   => __( 'Text Color', 'ldap-staff-directory' ),
							'default' => '3c434a',
						),

					),
				),

				'typography' => array(
					'title'  => __( 'Typography', 'ldap-staff-directory' ),
					'fields' => array(
						'font_size' => array(
							'type'    => 'unit',
							'label'   => __( 'Font Size (px)', 'ldap-staff-directory' ),
							'default' => '14',
							'units'   => array( 'px' ),
						),
					),
				),

				'layout' => array(
					'title'  => __( 'Layout', 'ldap-staff-directory' ),
					'fields' => array(
						'gap' => array(
							'type'    => 'unit',
							'label'   => __( 'Cards Gap (px)', 'ldap-staff-directory' ),
							'default' => '20',
							'units'   => array( 'px' ),
						),
						'border_radius' => array(
							'type'    => 'unit',
							'label'   => __( 'Border Radius (px)', 'ldap-staff-directory' ),
							'default' => '6',
							'units'   => array( 'px' ),
						),
					),
				),
			),
		),

		// ── Advanced tab ─────────────────────────────────────────────────────
		'advanced' => array(
			'title'    => __( 'Advanced', 'ldap-staff-directory' ),
			'sections' => array(
				'custom_css_section' => array(
					'title'  => __( 'Custom CSS', 'ldap-staff-directory' ),
					'fields' => array(
						'custom_css' => array(
							'type'  => 'code',
							'label' => __( 'Custom CSS', 'ldap-staff-directory' ),
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
				'name'            => __( 'LDAP Staff Directory', 'ldap-staff-directory' ),
				'description'     => __( 'Displays an employee directory from an LDAP/LDAPS server.', 'ldap-staff-directory' ),
				'group'           => __( 'General', 'ldap-staff-directory' ),
				'category'        => __( 'Basic', 'ldap-staff-directory' ),
				'dir'             => LDAP_ED_DIR . 'beaver-builder/',
				'url'             => LDAP_ED_URL . 'beaver-builder/',
				'icon'            => 'button.svg',
				'partial_refresh' => true,
			)
		);
	}
}
