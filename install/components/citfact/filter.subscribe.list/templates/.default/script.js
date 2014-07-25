'use strict';

var FilterSubscribeUser = function (params) {
  this.target = params.target;
  this.el = params.el;
  this.sendType = params.sendType || 'post';
  this.addEventHandler();
};

FilterSubscribeUser.prototype = {

  /**
   * Event linster
   */
  addEventHandler: function () {
    $(document).on('submit', this.target, $.proxy(this.removeFilter, this));
  },

  /**
   * Remove user filter
   *
   * @param {object} event
   */
  removeFilter: function (event) {
    function success(response) {
      if (response['success']) {
        $(event.target).parents(this.el).remove();
        if ($(this.el).length == 1) {
          $(this.el).removeClass('hidden');
        }
      }
    }

    var currentForm = $(event.target);
    $.ajax({
      type: currentForm.attr('method'),
      url: currentForm.attr('action'),
      data: currentForm.serialize(),
      success: $.proxy(success, this)
    });

    return false;
  }

};