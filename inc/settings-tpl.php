<?php
    $settings = get_option( 'jw_discord_settings' );
?>
<div class="wrap">
    <h2><?php _e( 'Discord SSO Options', 'jw-discord-sso' ); ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields( \com\plugish\discord\sso\Settings::GROUP ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="client_id"><?php _e( 'Client ID', 'jw-discord-sso' ); ?></label></th>
                <td><input type="text" name="jw_discord_settings[key]" id="client_id" value="<?php echo esc_html( $settings['key'] ?? '' ); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="client_secret"><?php _e( 'Client Secret', 'jw-discord-sso' ); ?></label></th>
                <td><input type="text" name="jw_discord_settings[secret]" id="client_secret" value="<?php echo esc_html( $settings['secret'] ?? '' ); ?>"></td>
            </tr>
        </table>
        <input type="submit">
    </form>
</div>