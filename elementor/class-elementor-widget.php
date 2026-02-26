<?php
/**
 * Elementor Widget — LDAP Employee Directory.
 *
 * @package LDAP_Employee_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LDAP_ED_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ldap_employee_directory';
	}

	public function get_title() {
		return __( 'LDAP Employee Directory', 'employee-directory-business' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'ldap', 'directory', 'employees', 'staff' );
	}

	// ── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls() {

		// ── Content tab ──────────────────────────────────────────────────────
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'employee-directory-business' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'fields',
			array(
				'label'    => __( 'Fields to Display', 'employee-directory-business' ),
				'type'     => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options'  => array(
					'name'       => __( 'Full Name', 'employee-directory-business' ),
					'email'      => __( 'Email', 'employee-directory-business' ),
					'title'      => __( 'Job Title', 'employee-directory-business' ),
					'department' => __( 'Department', 'employee-directory-business' ),
					'phone'      => __( 'Phone', 'employee-directory-business' ),
				),
				'default' => array( 'name', 'email', 'title', 'department' ),
			)
		);

		$this->add_control(
			'per_page',
			array(
				'label'   => __( 'Items per Page', 'employee-directory-business' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
				'min'     => 1,
				'max'     => 500,
			)
		);

		$this->add_control(
			'enable_search',
			array(
				'label'        => __( 'Enable Search Bar', 'employee-directory-business' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'employee-directory-business' ),
				'label_off'    => __( 'No', 'employee-directory-business' ),
				'return_value' => 'true',
				'default'      => 'true',
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'employee-directory-business' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default' => '3',
			)
		);

		$this->end_controls_section();

		// ── Style tab ────────────────────────────────────────────────────────
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'Cards', 'employee-directory-business' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'primary_color',
			array(
				'label'     => __( 'Primary Color', 'employee-directory-business' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0073aa',
				'selectors' => array(
					'{{WRAPPER}} .ldap-directory-wrap' => '--ldap-primary-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Card Background', 'employee-directory-business' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .ldap-directory-wrap' => '--ldap-card-bg: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text Color', 'employee-directory-business' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#3c434a',
				'selectors' => array(
					'{{WRAPPER}} .ldap-directory-wrap' => '--ldap-text-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'card_typography',
				'label'    => __( 'Typography', 'employee-directory-business' ),
				'selector' => '{{WRAPPER}} .ldap-employee-card',
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Card Padding', 'employee-directory-business' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ldap-employee-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_border_radius',
			array(
				'label'      => __( 'Border Radius', 'employee-directory-business' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 6 ),
				'selectors'  => array(
					'{{WRAPPER}} .ldap-directory-wrap' => '--ldap-card-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'grid_gap',
			array(
				'label'      => __( 'Cards Gap', 'employee-directory-business' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 20 ),
				'selectors'  => array(
					'{{WRAPPER}} .ldap-directory-wrap' => '--ldap-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	// ── Render ────────────────────────────────────────────────────────────────

	protected function render() {
		$settings = $this->get_settings_for_display();
		$fields   = is_array( $settings['fields'] ) ? implode( ',', $settings['fields'] ) : $settings['fields'];
		$columns  = absint( $settings['columns'] ?? 3 );
		$search   = ( 'true' === $settings['enable_search'] ) ? 'true' : 'false';
		$per_page = absint( $settings['per_page'] ?? 20 );

		// Inject column variable scoped to this widget instance.
		printf(
			'<style>.elementor-element-%1$s .ldap-directory-wrap{--ldap-columns:%2$d}</style>',
			esc_attr( $this->get_id() ),
			$columns
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo do_shortcode(
			sprintf(
				'[ldap_directory search="%1$s" per_page="%2$d" fields="%3$s"]',
				esc_attr( $search ),
				$per_page,
				esc_attr( $fields )
			)
		);
	}

	protected function content_template() {
		// Live preview not available for server-side rendering.
		echo '<div class="elementor-alert elementor-alert-info">' . esc_html__( 'LDAP Directory — preview available on the frontend.', 'employee-directory-business' ) . '</div>';
	}
}
