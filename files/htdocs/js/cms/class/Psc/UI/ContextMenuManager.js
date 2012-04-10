Class('Psc.UI.ContextMenuManager', {
  
  use: ['Psc.UI.Menu','Psc.InvalidArgumentException', 'Psc.Exception', 'Psc.Code'],
  
  has: {
    idStorageKey: { required: false, isPrivate: true, init: 'context-menu-manager-id' },
    menus: { required: false, isPrivate: true, init: Joose.I.Array }
  },

  methods: {
    register: function ($owner, menu) {
      if (!Psc.Code.isInstanceOf(menu, Psc.UI.Menu)) {
        throw new Psc.InvalidArgumentException('menu', 'Psc.UI.Menu', menu);
      }
      
      // lets store the index for the menu inside the owner
      var $menu = menu.unwrap();
      
      var index = this.index($owner);
      if (!$.isNumeric(index)) {
        $owner.data(this.$$idStorageKey, index = this.$$menus.length); // store a new index
        menu.setOwner($owner);
      }
      
      this.$$menus[ index ] = menu; // overwrite / store new
      if (!$menu.parents('body').length) {
        $menu.hide();
        $menu.appendTo($('body'));
      }
      
      return this;
    },
    
    closeAll: function() {
      $.each(this.$$menus, function(i, menu) {
        if (menu.isOpen()) {
          menu.close();
        }
      });
      return this;
    },
    toggle: function($owner) {
      var menu = this.get($owner);
      
      if (menu.isOpen()) {
        menu.close();
      } else {
        menu.open();
      }
      
      return menu;
    },
    get: function($owner) {
      var index = this.index($owner);
      
      var menu; 
      if (!$.isNumeric(index) || !(menu = this.$$menus[index]) ) {
        throw new Psc.Exception('Für das Element $owner kann kein Menu gefunden werden. Dieses muss vorher mit register() hinzugefügt werden.');
      }
      
      return menu;
    },
    index: function($owner) {
      return $owner.data(this.$$idStorageKey); // siehe auch register
    },
    toString: function() {
      return "[Psc.UI.ContextMenuManager]";
    }
  }
});