Class('Psc.UI.EffectsManager', {
  
  has: {
    //attribute1: { is : 'rw', required: false, isPrivate: true }
  },

  methods: {
    blink: function ($element, color, callback) {
      if (!color) color = '#880000';
      var oldColor = $element.css('background-color'), time = 80;
      
      $element.animate({
        backgroundColor: color
	  }, time, function () {
        $element.animate({
          backgroundColor: oldColor
        }, time*2, callback);
      });
    },
    toString: function() {
      return "[Psc.UI.EffectsManager]";
    }
  }
});