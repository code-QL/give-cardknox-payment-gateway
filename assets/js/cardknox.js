"use strict";
var Give_Cardknox_Form = {
  // Initialize UI states
  init: function() {
    this.onIfieldloaded();
  },
  onIfieldloaded: function () {
      setAccount( give_cardknox_params.token_key,"Give CardKnox Gateway", "0.0.1" );
      enableAutoFormatting();
      var card_style = {
        "box-sizing" : "border-box",
        "width" : "100%",
        "border-radius" : "0",
        "outline" : "none",
        "color" : "#333",
        "background-color" : "#fdfdfd",
        "border" : "1px solid #ccc",
        "margin" : "0",
        "padding" : ".5em",
        'font-size': '1.25rem',
        'line-height': '1.7'
      };
      var cvv_style = {
        "box-sizing" : "border-box",
        "width" : "100%",
        "border-radius" : "0",
        "outline" : "none",
        "color" : "#333",
        "background-color" : "#fdfdfd",
        "border" : "1px solid #ccc",
        "margin" : "0",
        "padding" : ".5em",
        'font-size': '1.25rem',
        'line-height': '1.7'
      };
      setIfieldStyle('card-number', card_style);
      setIfieldStyle('cvv', cvv_style);
  }
};
