(function($){
  
  $.widget( "pscUI.paging", {
    
    options: {
      pageTemplate: '#paging-page-template',
      skipTemplate: '#paging-skip-template',
      pages: 1,
      currentPage: 1,
      pageLabel: function (pageNum, paging) { return pageNum; },
      pageURL: function (pageNum, paging) { return '?p='.pageNum; }
    },
    
    _create: function() {
      var o = this.options,
          self = this
      
      this.pages = o.pages;
      this.currentPage = o.currentPage;
      this.getPageLabel = o.pageLabel;
      this.getPageURL = o.pageURL;
      this.getPageHTML = this._pageHTML();
      this.getSkipHTML = this._skipHTML();
      
      var html = [];
      this.prevButton = this.getPrevHTML();
      self.element.append(this.prevButton);
      
      this.buttons = {};
      for (var i = 1; i <= this.pages; i++) {
        self.element.append( this._createButton(i) );
      }
      
      this.nextButton = this.getNextHTML();
      self.element.append(this.nextButton);
      
      self.element.bind('click',function (e) {
        var $button = $(e.target);
        
        // fix für chrome, safari
        if ($button.hasClass('ui-button-text') || $button.hasClass('ui-icon')) {
          $button = $button.parent('button');
        }
        
        if ($button.is('button')) {
          e.preventDefault();
          
          var page = $button.attr('rel');
          
          if (self._trigger('changestart',null, {paging: self, oldPage: self.currentPage, page: page}) !== false) {
            self.page(page);
          }
        }
      });
    },
    
    /**
     * Setzt die Anzahl der Seiten neu (rekonstruiert HTML)
     */
    pages: function (num) {
      if (num != this.pages) {
        
        if (num < this.pages) {
          for (var i = num+1; i <= this.pages; i++) {
            this.buttons[i].remove();
            delete this.buttons[i];
          }
        } else {
          for (var i = this.pages+1; i <= num; i++) {
            this._createButton(i,true);
          }
        }
        
        this.pages = num;
      }
    },
    
    /**
     * Setzt die aktuelle Page
     */
    page: function (page) {
      // constrain
      page = Math.max(1,Math.min(this.pages,parseInt(page)));
      
      if (page != this.page && this._trigger('change',null, {paging: this, oldPage: this.currentPage, page: page}) !== false) {
        this.buttons[this.currentPage].removeClass('active');
        this.currentPage = page;
        this.buttons[this.currentPage].addClass('active');

        this.prevButton.attr('rel', Math.max(page-1,1));
        this.nextButton.attr('rel', Math.min(page+1,this.pages));
      }
    },
    
    _createButton: function (i, animated) {
      this.buttons[i] = this.getPageHTML({
          paging: self,
          num: i,
          label: this.getPageLabel(i, self),
          url: this.getPageURL(i, self)
        },
        i == this.currentPage,
        i == 1,
        i == this.pages
      );
      return this.buttons[i];
    },
    
    /**
     * Erstellt den Callback um einen einzelnen PageLink zu erstellen
     */
    _pageHTML: function () {
      var self = this;
      
      if (typeof self.options.pageTemplate == 'string') {
        return (function () {
          var tpl = $(self.options.pageTemplate);
          
          return function (page, isCurrent, isFirst, isLast) {
            var el = tpl.tmpl({
              page: page,
              isCurrent: isCurrent,
              isFirst: isFirst,
              isLast: isLast
            }).addClass('page')
              .attr('rel',page.num);

            if (isCurrent) el.addClass('active');
            if (isFirst) el.addClass('first');
            if (isLast) el.addClass('last');
            
            return el;
          };
        })();
      }
      
      throw 'noch nicht implementiert';
    },
    
    /**
     * Erstellt den Callback um einen einzelnen SkipLink (prev, next) zu erstellen
     */
    _skipHTML: function () {
      var self = this;
      
      if (typeof self.options.skipTemplate == 'string') {
        return (function () {
          var tpl = $(self.options.skipTemplate);
          
          return function (skip, type) {
            return tpl.tmpl({
              type: type,
              skip: skip
            }).addClass('skip')
              .attr('rel',skip.num);
          };
        })();
      }
      
      throw 'noch nicht implementiert';
    },
    
    getPrevHTML: function() {
      var prev = Math.max(1,this.currentPage-1);
      
      return this.getSkipHTML({
          num: prev,
          url: this.getPageURL(prev, this)
        },
        'prev'
      );
    },

    getNextHTML: function() {
      var next = Math.min(this.currentPage+1,this.pages);
      
      return this.getSkipHTML({
          num: next,
          url: this.getPageURL(next, this)
        },
        'next'
      );
    }
  });
})(jQuery);