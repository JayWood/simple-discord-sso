=== Simple Discord SSO ( Single Sign-On ) ===
Contributors: jaycodez
Tags: social login, discord, discord login, discord server
Requires at least: 5.0
Tested up to: 5.8.3
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A single sign-on plugin which allows any discord user to sign and/or register on your website with their Discord information.

== Description ==

This plugin allows discord users ( verified or not ) to login to your WordPress website as a subscriber. With a slew of available
hooks developers are able to both listen to, and customize, various events during the login process. Complete with a customizable login button,
or you can code your own and unhook this one.

= Important Caching Information =
This plugin uses a custom rewrite rule for the login button. You may want to ensure the `/discord-login` path in your WordPress
installation is not cached.

= Saved User Data =
When logging in, if the user does not have an account one is created for them automatically using their Username and Discriminator
field. The default scope is `identify email` which allows your site to store their discord information to your database for
other discord-related usage.

The following fields are saved to the `simple_discord_sso` user meta key for all discord users:
* **id**: the user's id
* **avatar**: the user's [avatar hash](https://discord.com/developers/docs/reference#image-formatting)
* **discriminator**: the user's 4-digit discord-tag
* **public_flags**: the public [flags](https://discord.com/developers/docs/resources/user#user-object-user-flags) on a user's account
* **flags**: the [flags](https://discord.com/developers/docs/resources/user#user-object-user-flags) on a user's account
* **banner**: the user's [banner hash](https://discord.com/developers/docs/reference#image-formatting)
* **accent_color**: the user's banner color encoded as an integer representation of hexadecimal color code
* **locale**: the user's chosen language option
* **mfa_enabled**: whether the user has two factor enabled on their account
* **premium_type**: the [type of Nitro subscription](https://discord.com/developers/docs/resources/user#user-object-premium-types) on a user's account
* **verified**: whether the email on this account has been verified
* **hash**: Just an MD5 hash which is used to determine if these fields should be updated on every login.

Various actions and filters are available, should you want to expand on these fields ( if new fields are added ) just look over
the [Discord User Resource](https://discord.com/developers/docs/resources/user) for more fields.

= Actions/Filters =
Various actions and filters are available from changing the redirect URL after login, changing the scope of the discord request,
or even halting the login ( and maybe redirecting after ) depending on the user's discord information. Or, even if you want, you can
listen for the login and hook into right before the redirect. The goal with the slew of actions and filters is to allow developers
to customize the plugin how they see fit.

Think we need more? Awesome, open a ticket on the [GitHub Repository](https://github.com/JayWood/jw-discord-sso) - the plugin is actively maintained so I'd be happy to help out.

== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`.
2. Search for `Simple Discord SSO`. Find and Install `Simple Discord SSO`.
3. Activate the plugin from your Plugins page.

= From WordPress.org =
1. Download Simple Discord SSO.
2. Unzip and upload the `simple-discord-sso` directory to your `/wp-content/plugins/` directory.
3. Activate Simple Discord SSO from your Plugins page.

= Post Installation =
You will now need to create an App on discord. To do that follow the below instructions:
1. Sign-in to discord and create an app on the [Dashboard](https://discord.com/developers/applications).
2. Name your app something obvious ( WordPress SignOn for example )
3. Fill out the initial information for legal reasons, it's suggested ( but not required ) you have a Terms of Service page and a Privacy Policy if you are using this plugin.
4. Click OAuth2 on the left sidebar.
5. Click General under OAuth2.
6. You must add a redirect back to your site. So click Add Redirect and enter your site's full URL to the WordPress install.
7. Copy the Client ID and the Client Secret
8. Now log into your WordPress installation and navigate to WP Admin > Discord SSO
9. Add the Client ID and Client Secret to your settings. Click Save/Update.
10. Your site is now allows Discord users to sign in.

== Frequently Asked Questions ==
= How can I change user roles from subscriber when they sign in =
There's a filter for that. It's `simple_discord_sso/default_role` which defaults to `subscriber` and also receives the user resource array from the Discord API.

= How can I change the URL they're redirected to? =
There's a filter for that. It's `simple_discord_sso/login_redirect` which defaults to `home_url()` and also receives the `WP_User` object.

= How do I allow users to login without using wp-login.php? =
To use the login, you only need to redirect the user to `/discord-login` - this is a custom rewrite to fire off the sign-on
process. Alternatively you can use the query parameter instead `/?discord=1`.

== Screenshots ==

1. The login button.
2. Logo Colors.
3. Button Background Colors.

== Changelog ==

= 1.0.2 =
* Small versioning update for plugin submission.
* Automatically update permalinks if required on init.

= 1.0.1 =
* Small updates to readme

= 1.0.0 =
* Initial release.
