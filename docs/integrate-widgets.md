# Integrate SCE Widgets into your cms

  1. add a hook to your compiler which calls: `doCompileCSWidgetsDir(\Webforge\Common\System\Dir $dir)`, when you are extending `\Psc\CMS\CommonProjectCompiler`
  1. edit your contenstream controller
    add the php template below
  1. integrate hogan
    - npm install grunt-hogan@~0.2 --save-dev
    - require your templates-compiled js while bootstrapping main
    - add `main.getContainer().getTemplatesRenderer().extendWith(templates);`  to your main.js (where templates is the required file) after `loader.finished`
  2. run hogan
  2. reload cms (full)

```php
// contentstream controller hacks


```  

```js
// gruntfile for hogan
hogan: {
      'amd': {
        binderName : "amd",
        templates : "./application/js-src/SCE/**/*.mustache",
        output : "./www/cms/js/templates-compiled.js",
        nameFunc: function(fileName) {
          fileName = nodepath.normalize(fileName);

          var pathParts = fileName.split(nodepath.sep).slice(['application', 'js-src'].length, -1);
          var namespace = pathParts.length > 0 ? pathParts.join('.')+'.' : '';

          var templateName = namespace+nodepath.basename(fileName, nodepath.extname(fileName));
          return templateName;
        }
      }
    },
```
