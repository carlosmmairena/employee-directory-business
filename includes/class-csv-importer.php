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
        
        if (!$csv_url)
        {
            self::log_event('❌ Error: No CSV URL configurada.');
            return;
        }
        
        $response = wp_remote_get( $csv_url );
        
        if (is_wp_error( $response ))
        {
            self::log_event('❌ Error al descargar CSV: ' . $response->get_error_message());
            return;
        }

        
        $$data = wp_remote_retrieve_body($response);
        $lines = explode( "\n", $data );
        global $wpdb;

        $table = $wpdb->prefix . 'employee_directory';
        $wpdb->query("TRUNCATE TABLE $table");

        $imported = 0;
        foreach ($lines as $line) {
            $columns = str_getcsv( $line );
            if (count($columns) === 5) {
                list($nombre, $correo, $puesto, $departamento, $extension) = $columns;
                $wpdb->insert( $table, compact('nombre', 'correo', 'puesto', 'departamento', 'extension') );
                $imported++;
            }
        }

        self::log_event("✔️ Sincronización completada. Empleados importados: $imported.");
    }

    public static function log_event( $message )
    {
        $log_file = EDIR_PLUGIN_PATH . 'sync-csv-log.txt';
        $date = date( 'Y-m-d H:i:s' );
        $entry = "[$date] $message\n";

        $lines = file_exists( $log_file ) ? file( $log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) : [];
        $lines[] = $entry;

        // Conservar solo los últimos 15 registros
        $lines = array_slice( $lines, -15 );
        file_put_contents( $log_file, implode( "\n", $lines ) . "\n" );
    }
}
