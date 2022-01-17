import { styles } from './style';
import { css, cx } from '@emotion/css';

const LoginButton = ({ button }) => (
	<>
		<hr className={styles.separator} />
		<a
			href={button.discordAuthLink}
			className={cx(
				styles.button,
				css`
					background-color: ${styles.discordColors[button.bgColor]};
				`
			)}
		>
			<img
				src={
					button.logoBaseUrl +
					'small_logo_' +
					button.logoColor +
					'_RGB.svg'
				}
				alt={button.altText}
				className={styles.logo}
			/>
		</a>
	</>
);

export default LoginButton;
