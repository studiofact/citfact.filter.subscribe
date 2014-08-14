'use strict';

var FilterSubscribe = function(params) {
  this.targetFilterForm = params.targetFilterForm;
  this.filterSubscribeForm = params.filterSubscribeForm;
  this.sendType = params.sendType || 'get';
  this.sendUrl = params.sendUrl || window.location.pathname;
  this.controllFillCount = params.controllFillCount || 4;
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
    $(this.targetFilterForm).on('change', $.proxy(this.checkFilter, this));
    $(this.filterSubscribeForm).on('click', 'input[name=SAVE_FILTER]', $.proxy(this.submit, this));
    $(this.filterSubscribeForm).on('click', 'input[name=CANCEL_FILTER]', $.proxy(this.hideForm, this));
  },

  /**
   * Check whether there is a user filter
   *
   * @param {object} event
   */
  checkFilter: function(event) {
    var targetForm = $(event.target).parents('form')
      , controllFill = 0
      , currentForm = $(this.filterSubscribeForm)
      , container = currentForm.parents('[data-container]');

    targetForm.find(':input:visible').each(function(){
      var control = $(this);
      if (control.is('select')) {
        if (control.find('option:selected').val()) {
          controllFill++;
        }
      } else if (control.is('input')) {
        var isFill = (control.attr('type').toLowerCase() == 'text')
          ? (control.val != '')
          : control.is(':checked');

        if (isFill) {
          controllFill++;
        }
      }
    });

    function success(response) {
      if (typeof response['data'] != 'object') {
        currentForm.find('[data-template=subscribe-info]').show();
        currentForm.find('[data-template=subscribe-success]').hide();
        container.show();
      } else {
        container.hide();
      }
    }

    if (controllFill > this.controllFillCount) {
      $.ajax({
        type: this.sendType,
        url: this.sendUrl,
        data: this.getDataFilter({ name: 'CHECK_FILTER', value: 'Y' }),
        success: $.proxy(success, this)
      });
    } else {
      container.hide();
    }
  },

  /**
   * Sumbmit filter for saved
   */
  submit: function() {
    var currentForm = $(this.filterSubscribeForm)
      , container = currentForm.parents('[data-container]');

    function success() {
      currentForm.find('[data-template=subscribe-info]').hide();
      currentForm.find('[data-template=subscribe-success]').show();
      container.show();
    }

    $.ajax({
      type: this.sendType,
      url: this.sendUrl,
      data: this.getDataFilter({ name: 'SAVE_FILTER', value: 'Y' }),
      success: $.proxy(success, this)
    });

    return false;
  },

  /**
   * Close form subscribe
   */
  hideForm: function() {
    var currentForm = $(this.filterSubscribeForm)
      , container = currentForm.parents('[data-container]');

    currentForm.find('[data-template=subscribe-info]').hide();
    currentForm.find('[data-template=subscribe-success]').hide();
    container.hide();

    return false;
  },

  /**
   * Return data target form
   *
   * @param {object} customParam
   * @return {*}
   */
  getDataFilter: function(customParam) {
    var dataFilter = $(this.targetFilterForm).serializeArray();
    dataFilter.push({ name: 'set_filter', value: 'Y' });

    if (customParam && typeof customParam == 'object') {
      dataFilter.push(customParam);
    }

    return dataFilter;
  }

};