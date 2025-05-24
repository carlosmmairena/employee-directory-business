<?php 
class EDir_Admin 
{ 
    public static function add_menu_page() 
    { 
        add_menu_page( 'Directorio de Empleados', 'Empleado Directory', 'manage_options', 'employee-directory', [ __CLASS__, 'render_settings_page' ], 'dashicons-groups' );
    }
    
    public static function register_settings()
    {
        register_setting( 'edir_options_group', 'edir_csv_url' );
    } 
    
    public static function render_settings_page() 
    {
        ?>
            <div class="wrap">
            <h1>Configuración del Directorio</h1>

            <form method="post" action="options.php">

            <?php settings_fields( 'edir_options_group' ); ?>

            <table class="form-table">
                <tr valign="top">
                <th scope="row">URL del archivo CSV</th>
                <td><input type="text" name="edir_csv_url" value="<?php echo esc_attr( get_option('edir_csv_url') ); ?>" style="width: 100%;" /></td>
                </tr>
            </table>
                <?php submit_button('Guardar configuración'); ?>
            </form>
            <hr>

                <form method="post">
                    <?php submit_button('🔄 Sincronizar empleados desde CSV', 'secondary', 'edir_sync_csv'); ?>
                </form>

                <?php $log_file = EDIR_PLUGIN_PATH . 'sync-log.txt'; 
                    if (file_exists( $log_file ))
                    {
                        $lines = array_reverse( file( $log_file ) );
                        echo '<h2>📄 Registro de sincronización</h2><textarea rows="10" style="width:100%;" readonly>';
                        echo esc_textarea(implode( '', $lines ));
                        echo '</textarea>';
                    } 
                ?>
            </div>
        <?php


        // Procesar sincronización si se hizo clic
        if ( isset( $_POST['edir_sync_csv'] ) ) {
            EDir_CSV_Importer::sync_from_csv();
            echo '<div class="updated notice"><p>✔️ Sincronización completada.</p></div>';
        }
    }
}
