use(['Psc.ResponseMetaReader', 'Psc.Response'], function() {
  var responseMetaReader;
  
  module("Psc.ResponseMetaReader", {
    setup: function () {
      var metaJSON = $.toJSON({
        tab: {
          identifier: 17,
          type: 'entities-person',
          label: 'Other Person',
          url: '/entities/person/17',
          data: {
            contextId: 22
          }
        },
        warnings: ['was casted to int', 'was trimmed']
      });
    
      var response = new Psc.Response({
        code: 200,
        body: 'not necessary content',
        header: {
          'X-Psc-Cms-Meta': metaJSON,
          'Content-Type': 'text/html; charset=utf-8'
        }
      });
    
      responseMetaReader = new Psc.ResponseMetaReader({response: response});
    }
  });

  test("hasReturnsTrueOrFalseOnExistingPaths", function() {
    assertTrue(responseMetaReader.has(['tab']), 'has [tab]');
    assertTrue(responseMetaReader.has(['tab','type']), 'has [tab,type]');
    assertTrue(responseMetaReader.has(['warnings']), 'has [warnings]');
    assertFalse(responseMetaReader.has(['none']), 'does not have [none]');
    assertFalse(responseMetaReader.has(['tab','data','none']), 'does not have [tab,data,none]');
  });
    
  test("getReturnsNullOrValueOnExistingPaths", function() {
    assertEquals({
        identifier: 17,
        type: 'entities-person',
        label: 'Other Person',
        url: '/entities/person/17',
        data: {
          contextId: 22
        }
      },
      responseMetaReader.get(['tab']),
      'returns object with full data for tab'
    );
    
    assertEquals(
      22,
      responseMetaReader.get(['tab','data','contextId']),
      'returns 22 for tab.data.contextId'
    );
    
    assertEquals(null, responseMetaReader.get(['tab','banane']));
  });
  
  test("invalidArgumentExceptionOnBullshitInput", function() {
    
    assertException("Psc.InvalidArgumentException", function () {
      responseMetaReader.get('blubb');
    });

    assertException("Psc.InvalidArgumentException", function () {
      responseMetaReader.get(17);
    });

    assertException("Psc.InvalidArgumentException", function () {
      responseMetaReader.get(null);
    });

    assertException("Psc.InvalidArgumentException", function () {
      responseMetaReader.get({0:'eins',1:'zwei'});
    });
  });
});