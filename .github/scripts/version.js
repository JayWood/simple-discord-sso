const {version} = require( '../../package.json' );
const fs = require( 'fs' );

// Replace in .txt file
fs.readFile( "readme.txt", 'utf8', function (err,data) {
  if (err) {
    return console.log(err);
  }

  const result = data.replace(/Stable tag: ([0-9\.]+)/g, 'Stable tag: ' + version );
  fs.writeFile( "readme.txt", result, 'utf8', function (err) {
    if (err) console.log( err ) && process.exit( 1 );
  });
});

// Replace in md file
fs.readFile( "readme.md", 'utf8', function (err,data) {
  if (err) {
    return console.log(err);
  }

  // Has issues with double asterisks so sticking with \W{2} instead
  const result = data.replace(/Stable tag:\W{2} ([0-9\.])+/gi, 'Stable tag:** ' + version );
  console.log( result );
  fs.writeFile( "readme.md", result, 'utf8', function (err) {
    if (err) console.log(err) && process.exit( 1 );
  });
});

