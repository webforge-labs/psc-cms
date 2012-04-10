/**
 * Ein FormRequest liest z.b. aus dem Formular die Custom-Header aus und übersetzt diese in "richtige" Header vom Ajax
 *
 * sodass wir einfach das Formular mit PHP mit allen Settings erstellen können und mit javascript nur abschicken
 */
Class('Psc.FormRequest', {
  isa: 'Psc.Request',
  
  use: ['Psc.InvalidArgumentException'],

  has: {
    form: { is : 'rw', required: true, isPrivate: true },
    url: { is : 'rw', required: false, isPrivate: true },
    method: { is : 'rw', required: false, isPrivate: true }
  },

  override: {
    setForm: function ($form) {
      var that = this;
      
      $form = $form.is('form') ? $form : $form.find('form');
      if (!$form.length) {
        throw new Psc.InvalidArgumentException('$form','jQuery <form> Element');
      }
      
      // alle header aus dem formular in den request Kopieren
      $form.find('input[class="psc-cms-ui-http-header"]').each(function () {
        var $input = $(this);
        that.setHeaderField($input.attr('name'), $input.attr('value'));
      });

      this.SUPER($form);
    },
    setUrl: function ($url) {
      return this.SUPER($url);
    }
  },
  
  methods: {
    initialize: function (props) {
      this.setForm(props.form);
      
      this.setMethod(props.method = this.expandMethod());
      this.setUrl(props.url = this.expandUrl());
      
      this.SUPER(props);
    },
    expandUrl: function () {
      return this.getForm().attr('action');
    },

    expandMethod: function() {
      var $form = this.getForm();
      
      $methodInput = $form.find('input[name="X-Psc-Cms-Request-Method"]');
      if ($methodInput.length) {
        return $methodInput.val();
      }
      
      return null;
    },
    toString: function() {
      return "[Psc.FormRequest "+this.getMethod()+" "+this.getUrl()+"]";
    }
  }
});