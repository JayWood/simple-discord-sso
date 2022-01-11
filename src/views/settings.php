<?php

use com\plugish\discord\sso\app\Settings;

if (  ! defined( 'ABSPATH' ) ) {
    die( 'Naughty naughty...' );
}

$settings = get_option( 'jw_discord_settings' );
?>
<div class="wrap">
    <form method="post" action="options.php">
        <h2><?php _e( 'Discord SSO Options', 'jw-discord-sso' ); ?></h2>

        <p>
            <?php _e( 'To use this plugin you will need to make a discord application.' ); ?> <br />
            <?php _e( 'You can learn more about discord apps here: '); ?><a href="https://discord.com/developers/docs/intro" target="_blank">https://discord.com/developers/docs/intro</a>
        </p>

        <?php settings_fields( Settings::GROUP ); ?>
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

        <h2><?php _e( 'Button Colors', 'jw-discord-sso' ); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="bgColor"><?php _e( 'Background Color', 'jw-discord-sso' ); ?></label></th>
                <td>
                    <select name="jw_discord_settings[bgColor]" id="bgColor">
                        <option value="blurple" <?php selected( $settings['bgColor'] ?? '', 'blurple' ); ?> style="color: black; background-color: #5865f2"><?php _e( 'blurple', 'jw-discord-sso' ); ?></option>
                        <option value="green" <?php selected( $settings['bgColor'] ?? '', 'green' ); ?> style="color: black; background-color: #57f287"><?php _e( 'green', 'jw-discord-sso' ); ?></option>
                        <option value="yellow" <?php selected( $settings['bgColor'] ?? '', 'yellow' ); ?> style="color: black; background-color: #fee75c"><?php _e( 'yellow', 'jw-discord-sso' ); ?></option>
                        <option value="red" <?php selected( $settings['bgColor'] ?? '', 'red' ); ?> style="color: black; background-color: #ed4245"><?php _e( 'red', 'jw-discord-sso' ); ?></option>
                        <option value="fuchsia" <?php selected( $settings['bgColor'] ?? '', 'fuchsia' ); ?> style="color: black; background-color: #eb459e"><?php _e( 'fuchsia', 'jw-discord-sso' ); ?></option>
                        <option value="white" <?php selected( $settings['bgColor'] ?? '', 'white' ); ?> style="color: black; background-color: #ffffff"><?php _e( 'white', 'jw-discord-sso' ); ?></option>
                        <option value="black" <?php selected( $settings['bgColor'] ?? '', 'black' ); ?> style="color: white; background-color: #000000"><?php _e( 'black', 'jw-discord-sso' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="logoColor"><?php _e( 'Logo Color', 'jw-discord-sso' ); ?></label></th>
                <td>
                    <select name="jw_discord_settings[logoColor]" id="logoColor">
                        <option value="black" <?php selected( $settings['logoColor'] ?? '', 'black' ); ?> style="color: white; background-color: #000"><?php _e( 'black', 'jw-discord-sso' ); ?></option>
                        <option value="blurple" <?php selected( $settings['logoColor'] ?? '', 'blurple' ); ?> style="color: black; background-color: #5865f2"><?php _e( 'blurple', 'jw-discord-sso' ); ?></option>
                        <option value="white" <?php selected( $settings['logoColor'] ?? '', 'white' ); ?> style="color: black; background-color: #fff"><?php _e( 'white', 'jw-discord-sso' ); ?></option>
                    </select>
                </td>
            </tr>
        </table>

        <input type="submit" class="button button-primary">
    </form>
</div>