Class('Psc.UI.Spinner', {
  /**
   * a veryvery Simple Spinner for ajaxLoading
   */
  
  has: {
    image: { is : 'rw', required: false, isPrivate: true, builder: 'initSpinner' },
    stack: { is : 'rw', required: false, isPrivate: true, init: Joose.I.Array },
    container: { is : 'rw', required: false, isPrivate: true, init: null }
  },

  methods: {
    attachToAjax: function () {
      var that = this;
      
      $.ajaxSetup({
        beforeSend: function(xhr) {
          if (xhr.complete) { // complete function is avaible, does this makes sense?
            that.show(xhr); // show spinner
          }
        }
      });
    },
    initSpinner: function () {
      return $('<span class="psc-ui-spinner"><img src="/img/cms/ajax-spinner-small.gif" alt="loading..." /></span>');
    },
    show: function (xhr) {
      var that = this;
      
      this.$$spinnerStack.push(xhr);
      
      xhr.complete(function (xhr) {
        that.remove(xhr);
      });
      
      if (this.$$spinnerContainer === null) {
        this.$$spinnerContainer = $('body .spinner-container');
              
        if (this.$$spinnerContainer.length) { // do we have a container to show a spinner?
          this.$$image.hide();
          this.$$spinnerContainer.append(this.$$image);
        } else {
          this.$$spinnerContainer = false; // prevents from rechecking
        }
      }
      
      this.$$image.fadeIn(100);
    },
    remove: function (xhr) {
      this.$$spinnerStack.pop();
            
      if (this.$$spinnerStack.length == 0) {
        this.$$image.fadeOut(100);
      }
    },
    toString: function() {
      return "[Psc.UI.Spinner]";
    }
  }
});