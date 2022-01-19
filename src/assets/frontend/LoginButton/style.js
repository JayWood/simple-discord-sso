import { css } from '@emotion/css';

export const styles = {
	discordColors: {
		blurple: '#5865f2',
		green: '#57f287',
		yellow: '#fee75c',
		red: '#ed4245',
		fuchsia: '#eb459e',
		white: '#ffffff',
		black: '#000000',
	},
	wrapper: css`
		display: inline-flex;
		flex-direction: column;
		clear: both;
		padding-top: 20px;
	`,
	separator: css`
		flex-grow: 1;
		margin-top: 10px;
		margin-bottom: 20px;
		color: #ccc;
	`,
	button: css`
		display: flex;
		margin: 5px 0;
		padding: 10px;
		justify-content: center;
		align-items: center;
		border-radius: 3px;
	`,
	logo: css`
		width: 35%;
		height: auto;
	`,
};
