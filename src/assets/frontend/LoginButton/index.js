import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';
import { styles } from './style.js';
import LoginButton from './LoginButton';

domReady(() => {
	const formElement = document.getElementById('loginform');
	if (!formElement) {
		return;
	}

	const discordSettings = window.simpleDiscordSettings || {};
	const node = document.createElement('div');
	node.setAttribute('id', 'simple-discord-sso-login');
	node.setAttribute('class', styles.wrapper);
	formElement.appendChild(node);

	render(
		<LoginButton {...discordSettings} />,
		document.getElementById('simple-discord-sso-login')
	);
});
