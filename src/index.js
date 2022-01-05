import { registerBlockType } from '@wordpress/blocks';

registerBlockType('jw/discord-sso', {
    apiVersion: 2,

    title: 'Random Image',

    icon: 'format-image',

    category: 'text',
    keywords: ['example', 'test'],
    edit: () => (
        <>
            <p>Help wtf...</p>
        </>
    ),
    save: () => (
        <>
            <p>Saved</p>
        </>
    )
});