<?php

namespace Psc;

// @TODO MiniHandler hierraus machen
use Psc\Net\HTTP\Request;
use Psc\Net\RequestMatcher;
use Psc\Net\ServiceRequest;
use Psc\System\File;
use Psc\System\Dir;
use Psc\String AS S;

PSC::getProject()->setTests(TRUE);
$htdocs = PSC::getProject()->getHtdocs();

$req = Request::infer();

$rq = new RequestMatcher(new ServiceRequest($req->getMethod(), $req->getParts(), $req->getBody()));
$rq->matchValue('js');
$rq->matchValue('cms');
$rq->matchValue('tests');

$cms = PSC::getCMS();
$cms->setJSManager($jsManager = new \Psc\JS\ProxyManager('qunit'));
$cms->init();
$page = $cms->createHTMLPage();
$jsManager->enqueue('jquery-tmpl');

/* JS Code */
$tests = NULL;
try {
  $testClass = $rq->qmatchRx('/^([a-z0-9.]+)(Test)?(\.js)?/i',1);
} catch (\Psc\Net\RequestMatchingException $e) {
  
  if (array_key_exists('filter',$query = $req->getQuery())) {
    $testClass = mb_substr($query['filter'], 0,mb_strpos($query['filter'],':'));
    \Psc\URL\Helper::redirect('/js/cms/tests/'.$testClass);
  } else {
    // alle
    $testClass = NULL;
  }
}

$testsDir = Dir::factory(__DIR__.DIRECTORY_SEPARATOR);
$classesDir = $testsDir->sub('../class/');

if ($testClass === NULL) {
  // include all
  $testFiles = $testsDir->getFiles('.js');
} else {
  $testFiles = array($testFile = new File($testsDir.str_replace('.',DIRECTORY_SEPARATOR,$testClass).'Test.js'));
  if (!$testFile->exists()) {
    throw new \Psc\Exception('Test: '.$testFile.' nicht gefunden');
  }
}

$tests = NULL;

foreach ($testFiles as $testFile) {
  $tests .= \Psc\JS\Helper::load($testFile->getUrl($htdocs));
}

$title = 'QUnit Psc-Framework Test: '.($testClass ?: 'Alle');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title><?= $title ?></title>
  <?= $jsManager->load() ?>
  <link rel="stylesheet" href="http://code.jquery.com/qunit/git/qunit.css" type="text/css" media="screen" />
  <link rel="stylesheet" href="/css/jquery-ui/smoothness/jquery-ui-1.8.18.custom.css" type="text/css" media="screen" />
  <script type="text/javascript" src="http://code.jquery.com/qunit/git/qunit.js"></script>
  <script type="text/javascript" src="/js/cms/jquery.simulate.js"></script>

<!-- Templates als Common-Fixtures -->
<script id="tmpl-ui-tabs" type="text/x-jquery-tmpl">
<div class="tabs">
    <ul>
      <li><a href="#tabs-1">Nunc tincidunt</a></li>
	  <li><a href="#tabs-2">Proin dolor</a></li>
	  <li><a href="#tabs-3">Aenean lacinia</a></li>
	</ul>
	<div id="tabs-1">
		<p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>
	</div>
	<div id="tabs-2">
		<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
	</div>
	<div id="tabs-3">
		<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
		<p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
	</div>
</div>
</script>


<script>
$(document).ready(function(){
  
  var ctrl = {
    call: function(url, successAssert) {
      ok(true, 'calling: '+url);
    
      return $.getJSON(url, function(data) {
        successAssert(data);
      })
      .complete(function () {
        start();
      })
      ;
    },
    ok: function(url, assert) {
      ctrl.call(url, function (data) {
        ok(true, 'request hat geklappt');
        assert(data);
      });
    },
    fail: function (url, statusCode, assert) {
      ctrl.call(url, function (data) {
        ok(false, 'request hat geklappt, obwohl er eigentlich fehlschlagen sollte mit: '+statusCode);
      }).error(function (jqXHR, textStatus, errorThrown) {
        ok(true, 'Request gab Fehler aus');
        
        equal(jqXHR.status, statusCode);
        assert(jqXHR.responseText, jqXHR);
      });
    }
  };
  
  var baseAssertions = {
    
  formatMessage: function(message, result) {
    if (message) {
      if (result) {
        return "asserted that "+String(message);
      } else {
        return "failed asserting that "+String(message);
      }
    }
    return null;
  },
  
  //pushDetails: function (result, actual, expected, message) {
  //  var details = {
  //    result: result,
  //    message: message,
  //    actual: actual,
  //    expected: expected
  //  };
  //
  //  message = QUnit.escapeInnerText(message) || (result ? "okay" : "failed");
  //  message = '<span class="test-message">' + message + "</span>";
  //  var output = message;
  //  output += '<table><tr class="test-expected"><th>Expected: </th><td><pre>' + escapeInnerText(QUnit.jsDump.parse(expected)) + '</pre></td></tr>';
  //  output += '<tr class="test-actual"><th>Result: </th><td><pre>' + escapeInnerText(QUnit.jsDump.parse(actual)) + '</pre></td></tr>';
  //  
  //  var source = sourceFromStacktrace();
  //  if (source) {
  //    details.source = source;
  //    output += '<tr class="test-source"><th>Source: </th><td><pre>' + escapeInnerText(source) + '</pre></td></tr>';
  //  }
  //  output += "</table>";
  //  
  //  runLoggingCallbacks( 'log', QUnit, details );
  //  
  //  QUnit.config.current.assertions.push({
  //    result: !!result,
  //    message: output
  //  });
  //},
  //
  debug: function(item) {
    return QUnit.jsDump.parse(item);
  },
  
  fixtures: {
    getHTML: function(name) {
      return $('#tmpl-'+name).tmpl();
    },
    
    loadHTML: function(name) {
      var $html = $(this.getHTML(name));
      
      $('#qunit-fixture').append(
        $html
      );
      
      return $html;
    }
  },
  
  /**
   * Sicherstellen, dass "Exception" vor dieser Funktion geladen wurde (asyncTest?)
   */
  assertException: function(expectedException, block, expectedMessage, debugMessage, assertions) {
    var assert = function assertException(e) {
      var isException = e instanceof Psc.Exception;
      
      //assertTrue(isException, formatMessage("e ist eine (sub-)instanz von Exception", isException));
      QUnit.push( isException, isException, true, formatMessage(debug(e)+" is instanceof Psc.Exception", isException) );
    
      if (isException) {
        assertEquals(expectedException, e.getName(), "Name ist '"+expectedException+"'");
    
        if (expectedMessage) {
          equal(e.getMessage(), expectedMessage, "Message ist '"+expectedMessage+"'");
      }
      
        if (assertions) {
          assertions(e);
        }
        
        return true;
      }
      
      return false;
    };
    
    return raises(block, assert, debugMessage);
    
    ok(false, "Es wurde eine Exception "+expectedException+" erwartet. Aber keine gecatched");
  },
  
  assertEquals: function(expected, actual, message) {
    var result = QUnit.equiv(actual, expected);
    QUnit.push( result, actual, expected, formatMessage(message || "two values are equal", result));
  },

  assertSame: function(expected, actual, message) {
    var result = (actual === expected);
    QUnit.push( result, actual, expected, formatMessage(message || "objects reference the same instance", result));
  },

  assertNotSame: function(expected, actual, message) {
    var result = actual !== expected;
    QUnit.push( result, actual, expected, formatMessage(message || "objects reference not the same instance", result));
  },
  
  assertTrue: function(actual, message) {
    var result = actual === true;
    QUnit.push( result, actual, true, formatMessage(message || debug(actual)+" is true ", result) );
  },

  assertFalse: function(actual, message) {
    var result = actual === false;
    QUnit.push( result, actual, false, formatMessage(message || debug(actual)+" is false ", result) );
  },

  assertNotFalse: function(actual, message) {
    var result = actual !== false;
    QUnit.push( result, actual, undefined, formatMessage(message || debug(actual)+" is not false ", result) );
  },

  fail: function(message) {
    //QUnit.push( false, message , sourceFromStacktrace(2));
    QUnit.pushFailure( "failed: "+message );
  },
  
  assertEmptyObject: function(actual, message) {
    var result = Joose.O.isEmpty(actual);
    return QUnit.push( result, true, true, formatMessage(message || debug(actual)+" is an empty object.", result) );
  },
  
  // expected muss der Constructor sein, kein String!
  assertInstanceOf: function(expected, actual, message) {
    if (!Joose.O.isClass(expected)) {
      fail(debug(expected)+" is NOT a valid Class. Is this a Constructor-Function?");
    }
    if (!Joose.O.isInstance(actual)) {
      fail(debug(actual)+" is not an object-instance");
    }
    
    var result = actual instanceof expected;
    // hier ist das diff überflüssig
    
    return QUnit.push(result, "Instance of Class "+String(actual.meta.name), "Instance of Class "+String(expected), formatMessage(message || String(actual)+" is instanceof '"+String(expected)+"'", result));
    },
  }; // var baseAssertions
  
  $.extend(window.QUnit, baseAssertions);
  $.extend(window, baseAssertions);
});
</script>
<?php print $tests ?>
  
</head>
<body>
 <h1 id="qunit-header"><?= $title ?></h1>
 <h2 id="qunit-banner"></h2>
 <div id="qunit-testrunner-toolbar"></div>
 <h2 id="qunit-userAgent"></h2>
 <ol id="qunit-tests"></ol>
 <div id="qunit-fixture">test markup, will be hidden</div>
</body>
</html>