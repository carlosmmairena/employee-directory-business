<?php 
class EDir_CSV_Importer 
{ 
    public static function install_db()
    {
        global $wpdb; $table = $wpdb->prefix . 'employee_directory';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table ( id mediumint(9) NOT NULL AUTO_INCREMENT, nombre VARCHAR(100), correo VARCHAR(100), puesto VARCHAR(100), departamento VARCHAR(100), extension VARCHAR(20), PRIMARY KEY (id) ) $charset;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); 
        dbDelta($sql);
    }
    
    
    public static function sync_from_csv()
    {
        $csv_url = get_option('edir_csv_url');
        
        if ( ! $csv_url ) return;
        
        $response = wp_remote_get( $csv_url );
        
        if ( is_wp_error( $response ) ) return;
        
        $data = wp_remote_retrieve_body( $response );
        $lines = explode( "\n", $data );
        
        global $wpdb; $table = $wpdb->prefix . 'employee_directory';
        $wpdb->query( "TRUNCATE TABLE $table" );
        
        foreach ($lines as $line)
        {
            $columns = str_getcsv( $line );
            if ( count($columns) === 5 )
            {
                list($nombre, $correo, $puesto, $departamento, $extension) = $columns;
                $wpdb->insert( $table, compact('nombre', 'correo', 'puesto', 'departamento', 'extension') );
            }
        }
    }
}