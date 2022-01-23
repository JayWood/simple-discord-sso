const {version} = require( '../../package.json' );
const fs = require( 'fs' );

// Update changelog in .txt from what is in readme.md
fs.readFile( "readme.md", 'utf8', function (err,data) {
  if (err) {
    throw err;
  }

  // Has issues with double asterisks so sticking with \W{2} instead
  const pattern = new RegExp(
    "(?<=### "+version+"\\n)[\\w\\W]*?(?=\\r?\\n###)",
    'gm'
  );

  const matches = data.match( pattern );
  if ( ! matches?.length ) {
    throw `The version ${version} does not exist in the changelog.`
  }
});
