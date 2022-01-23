const {version} = require( '../../package.json' );
const fs = require( 'fs' );

const files = {
  "readme.txt": "pattern"
};

for( const file in files ) {
  fs.readFile( file, 'utf8', function (err,data) {
    if (err) {
      return console.log(err);
    }
    var result = data.replace(/Stable tag: ([0-9\.]+)/g, 'Stable tag: ' + version );

    fs.writeFile( file, result, 'utf8', function (err) {
      if (err) return console.log(err);
    });
  });
}

