// use boot to create the default cms - main
define(['jquery','app/boot'], function ($, boot) {
  var main = boot.createMain($('#content'));
  
  if (main) { // bei login z.b. gibts kein main und kein javascript
    // load inline scripts, that are bootloaded while loading main.html
    // for example select tab
    main.getLoader().finished();
  }
  
  return main;
});