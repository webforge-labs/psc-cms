Class('Psc.UI.WidgetWrapper', {
  
  has: {
    widget: { is : 'rw', required: true, isPrivate: false }
  },

  methods: {
    checkWidget: function() {
      if ($.isEmptyObject(this.widget) || !this.widget.jquery || this.widget.length === 0) {
        throw new Psc.InvalidArgumentException('widget', 'jquery Objekt mit einem gematchten element.');
      }
    },
    unwrap: function () {
      return this.widget;
    },
    toString: function() {
      return "[Psc.UI.WidgetWrapper]";
    }
  }
});