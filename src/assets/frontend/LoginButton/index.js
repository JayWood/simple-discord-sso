import domReady from "@wordpress/dom-ready";
import {css, cx} from "@emotion/css";
import {render} from "@wordpress/element";
import {styles} from "./style.js";

domReady( () => {
   const formElement = document.getElementById( 'loginform' );
   if ( ! formElement ) {
       return;
   }

   const discordSettings = window?.jwDiscord || {};
   const node = document.createElement( 'div' );
   node.setAttribute( 'id', 'jw-discord-login' );
   node.setAttribute( 'class', styles.wrapper );
   formElement.appendChild( node );

   const LoginButton = ({button}) => {

       return (
           <>
               <hr className={styles.separator} />
               <a
                   href={button.discordAuthLink}
                   className={ cx( styles.button, css`
                        background-color: ${styles.discordColors[button.bgColor]};
                   ` )}
               >
                   <img
                       src={button.logoBaseUrl + "small_logo_" + button.logoColor + "_RGB.svg" }
                       alt={button.altText}
                       className={styles.logo}
                   />
               </a>
           </>
       )
   };

   render( <LoginButton {...discordSettings} />, document.getElementById( 'jw-discord-login' ) );
} );