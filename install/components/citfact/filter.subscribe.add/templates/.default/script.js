'use strict';

var FilterSubscribe = function(params) {
  this.targetFilterForm = params.targetFilterForm;
  this.filterSubscribeForm = params.filterSubscribeForm;
  this.sendType = params.sendType || 'get';
  this.sendUrl = params.sendUrl || window.location.pathname;

  if (!this.targetFilterForm || !this.filterSubscribeForm) {
    throw new Error('Undefine params {targetFilterForm} or {filterSubscribeForm}');
  }

  this.addEventHandler();
};

FilterSubscribe.prototype = {
  /**
   * Event linster
   */
  addEventHandler: function() {
    $(this.filterSubscribeForm).on('click', 'input[name=SAVE_FILTER]', $.proxy(this.submit, this));
  },

  /**
   * Sumbmit filter for saved
   */
  submit: function() {
    function success(response) {
      //console.log(response);
    }

    $.ajax({
      type: this.sendType,
      url: this.sendUrl,
      data: this.getDataFilter(),
      success: $.proxy(success, this)
    });

    return false;
  },

  /**
   * Return data target form
   * @return {*}
   */
  getDataFilter: function() {
    var dataFilter = $(this.targetFilterForm).serializeArray();
    dataFilter.push({ name: 'SAVE_FILTER', value: 'Y' });
    dataFilter.push({ name: 'set_filter', value: 'Y' });

    return dataFilter;
  }
};