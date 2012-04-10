Class('Psc.UI.Menu', {
  isa: 'Psc.UI.WidgetWrapper',

  has: {
    widget: { is : 'rw', required: false, isPrivate: false },
    open: { is: '', required: false, isPrivate: true, init: false },
    owner: { is: 'rw', required: false, isPrivate: true }
  },
  
  after: {
    initialize: function(props) {
      if (props.items) {
        this.createWidget(props.items);
      }
      this.checkWidget();
      this.initWidget();
    }
  },

  methods: {
    open: function () {
      this.$$open = true;
      this.widget.slideDown(100);
    },
    close: function() {
      this.$$open = false;
      this.widget.slideUp(100);
    },
    isOpen: function () {
      return this.$$open;
    },
    setOwner: function ($owner) {
      this.$$owner = $owner;
      
      var $menu = this.widget;
      $menu.css('position','absolute');
      $menu.position({
        of: $owner,
        my: 'left bottom',
        at: 'left bottom',
        offset: '10 5',
        collision: 'flip flip'
      });
    },
    getItem: function (search) {
      var item;
      if (search.id) {
        item = this.widget.find('li a[href="#'+search.id+'"]');
      }
      
      return item;
    },
    createWidget: function (items) {
      var html = '', that = this;
      
      this.widget = $('<ul></ul>');
      $.each(items, function(id, item) {
        if (typeof(item) === 'string') {
          item = { label: item };
        }
        
        if ($.isFunction(item.select)) { // das kann dann sogar eine eventsmap sein
          that.unwrap().on('click', 'li a[href="#'+id+'"]', [id,that], item.select); // das geht dann nur für maus, nicht tastatur!
        }
        
        html += '<li><a class="menu-'+id+'" href="#'+id+'">'+item.label+'</a></li>';
      });
      this.widget.append(html);
    },
    initWidget: function() {
      this.widget.menu({
      });
    },
    toString: function() {
      return "[Psc.UI.Menu]";
    }
  }
});