<?php

use com\plugish\discord\sso\app\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Naughty naughty...' );
}

$settings = get_option( 'simple_discord_sso_settings' );
?>
<div class="wrap">
	<form method="post" action="options.php">
		<h2><?php esc_html_e( 'Discord SSO Options', 'simple-discord-sso' ); ?></h2>

		<p>
			<?php esc_html_e( 'To use this plugin you will need to make a discord application.' ); ?> <br />
			<?php esc_html_e( 'You can learn more about discord apps here: ' ); ?><a href="https://discord.com/developers/docs/intro" target="_blank">https://discord.com/developers/docs/intro</a>
		</p>

		<?php settings_fields( Settings::GROUP ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="client_id"><?php esc_html_e( 'Client ID', 'simple-discord-sso' ); ?></label></th>
				<td><input type="text" name="simple_discord_sso_settings[key]" id="client_id" value="<?php echo esc_html( $settings['key'] ?? '' ); ?>"></td>
			</tr>Ana
			<tr>
				<th scope="row"><label for="client_secret"><?php esc_html_e( 'Client Secret', 'simple-discord-sso' ); ?></label></th>
				<td><input type="text" name="simple_discord_sso_settings[secret]" id="client_secret" value="<?php echo esc_html( $settings['secret'] ?? '' ); ?>"></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Button Colors', 'simple-discord-sso' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="bgColor"><?php esc_html_e( 'Background Color', 'simple-discord-sso' ); ?></label></th>
				<td>
					<select name="simple_discord_sso_settings[bgColor]" id="bgColor">
						<option value="blurple" <?php selected( $settings['bgColor'] ?? '', 'blurple' ); ?> style="color: black; background-color: #5865f2"><?php esc_html_e( 'blurple', 'simple-discord-sso' ); ?></option>
						<option value="green" <?php selected( $settings['bgColor'] ?? '', 'green' ); ?> style="color: black; background-color: #57f287"><?php esc_html_e( 'green', 'simple-discord-sso' ); ?></option>
						<option value="yellow" <?php selected( $settings['bgColor'] ?? '', 'yellow' ); ?> style="color: black; background-color: #fee75c"><?php esc_html_e( 'yellow', 'simple-discord-sso' ); ?></option>
						<option value="red" <?php selected( $settings['bgColor'] ?? '', 'red' ); ?> style="color: black; background-color: #ed4245"><?php esc_html_e( 'red', 'simple-discord-sso' ); ?></option>
						<option value="fuchsia" <?php selected( $settings['bgColor'] ?? '', 'fuchsia' ); ?> style="color: black; background-color: #eb459e"><?php esc_html_e( 'fuchsia', 'simple-discord-sso' ); ?></option>
						<option value="white" <?php selected( $settings['bgColor'] ?? '', 'white' ); ?> style="color: black; background-color: #ffffff"><?php esc_html_e( 'white', 'simple-discord-sso' ); ?></option>
						<option value="black" <?php selected( $settings['bgColor'] ?? '', 'black' ); ?> style="color: white; background-color: #000000"><?php esc_html_e( 'black', 'simple-discord-sso' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="logoColor"><?php esc_html_e( 'Logo Color', 'simple-discord-sso' ); ?></label></th>
				<td>
					<select name="simple_discord_sso_settings[logoColor]" id="logoColor">
						<option value="black" <?php selected( $settings['logoColor'] ?? '', 'black' ); ?> style="color: white; background-color: #000"><?php esc_html_e( 'black', 'simple-discord-sso' ); ?></option>
						<option value="blurple" <?php selected( $settings['logoColor'] ?? '', 'blurple' ); ?> style="color: black; background-color: #5865f2"><?php esc_html_e( 'blurple', 'simple-discord-sso' ); ?></option>
						<option value="white" <?php selected( $settings['logoColor'] ?? '', 'white' ); ?> style="color: black; background-color: #fff"><?php esc_html_e( 'white', 'simple-discord-sso' ); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<input type="submit" class="button button-primary">
	</form>
</div>
