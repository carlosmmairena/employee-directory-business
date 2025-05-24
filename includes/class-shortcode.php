<?php 
class EDir_Shortcode
{
    public static function register()
    {
        add_shortcode( 'employee_directory', [ __CLASS__, 'render_directory' ] );
    }
    
    public static function render_directory()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'employee_directory';
        $empleados = $wpdb->get_results( "SELECT * FROM $table" );
        
        ob_start(); 
        echo '<div class="employee-directory">';
        foreach ( $empleados as $emp ) 
        {
            echo '<div class="employee-card">';
            echo '<strong>' . esc_html( $emp->nombre ) . '</strong><br>';
            echo esc_html( $emp->puesto ) . ' – ' . esc_html( $emp->departamento ) . '<br>';
            echo esc_html( $emp->correo ) . ' | Ext. ' . esc_html( $emp->extension );
            echo '</div><hr>';
        } 
        echo '</div>'; 
            
        return ob_get_clean();
    }
} 

EDir_Shortcode::register();
