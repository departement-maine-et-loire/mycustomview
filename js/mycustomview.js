$(document).ready(function () {
  var buttonEdit = $("#mcv_edit");
  var buttonSave = $("#mcv_save");
  var dragDropDiv = document.getElementById("mcv_drag_drop");
  var buttonCancel = $("#mcv_cancel");
  var buttonHelp = $("#mcv_help");
  var buttonsDelete = $(".mcv_delete");
  var buttonScreenMode = $(".mcv_screenmode");
  var buttonEditTitle = $(".mcv_edit_title");
  var divEditMode = $(".mcv_header_edit_mode");
  var fadeZone = $(".draggable");
  var transparentDiv = $(".mcv_transparent_view");
  var savedSearchTitle = $(".savedsearch_title");

  if ($("input").hasClass("mcv_delete_default")) {
    var defaults = 0;
    var buttonDefault = $(".mcv_delete_default");
  } else if ($("input").hasClass("mcv_add_default")) {
    var defaults = 1;
    var buttonDefault = $(".mcv_add_default");
  }

  var listMovableElements = document.getElementsByClassName(
    "mcv_movable_items"
  );

  // De base on ne peut pas drag and drop
  var instance = $(".mcv_tab_container").dad({
    // activate the drop and drop plugin
    active: false,

    // custom drag handle
    // e.g. '.my-drag-selector'
    draggable: ".draggable",

    // allows to exchange children.
    exchangeable: true,

    // default CSS classes
    // placeholderClass: "dad-placeholder",
    // targetClass: "dad-target",
    cloneClass: "dad-clone",

    // transition time in ms
    transition: 300,
  });

  function isEditActive() {
    if (buttonEdit.hasClass("active")) {
      return true;
    }
    return false;
  }

  $("#mcv_edit").on("click", function () {
    if (isEditActive()) {
      return;
    }
    // on active le D'n'Drop
    instance.activate();
    buttonSave.removeClass("mcv_display_none");
    buttonCancel.removeClass("mcv_display_none");
    buttonsDelete.removeClass("mcv_display_none");
    buttonEdit.addClass("mcv_display_none");
    buttonEdit.addClass("active");
    buttonHelp.addClass("mcv_display_none");
    fadeZone.removeClass("mcv_display_none");
    transparentDiv.removeClass("mcv_display_none");
    buttonHelp.addClass("mcv_display_none");
    buttonScreenMode.removeClass("mcv_display_none");
    buttonEditTitle.removeClass("mcv_display_none");
    divEditMode.removeClass("mcv_display_none");
    $(".fa-wrench").addClass("mcv_display_none");
  });

  $("#mcv_cancel").on("click", function () {
    if ($(this).data("message") == 1) {
      $(".mcv_cancel_message").removeClass("mcv_display_none");
      $(".fullscreen-dark-container").removeClass("mcv_display_none");
    } else {
      actionOnCancel();
    }
  });

  $(".mcv_modal_close").on("click", function () {
    $(this).closest(".mcv_modal").addClass("mcv_display_none");
    var type = $(this).data("modaltype");
    if ((type = "message")) {
      $(".fullscreen-dark-container").addClass("mcv_display_none");
    }
    if ((type = "help")) {
      $(".fullscreen-dark-container").addClass("mcv_display_none");
    }
  });

  buttonHelp.on("click", function () {
    $(".mcv_modal_help").removeClass("mcv_display_none");
    $(".fullscreen-dark-container").removeClass("mcv_display_none");
  });

  $(".fullscreen-dark-container").on("click", function () {
    $(".mcv_modal_help").addClass("mcv_display_none");
    $(".fullscreen-dark-container").addClass("mcv_display_none");
  });

  $(".mcv_button_message_cancel").on("click", function () {
    location.reload();
  });

  function actionOnCancel() {
    instance.deactivate();
    buttonSave.addClass("mcv_display_none");
    buttonCancel.addClass("mcv_display_none");
    buttonsDelete.addClass("mcv_display_none");
    buttonEdit.removeClass("mcv_display_none");
    buttonEdit.removeClass("active");
    buttonHelp.removeClass("mcv_display_none");
    fadeZone.addClass("mcv_display_none");
    transparentDiv.addClass("mcv_display_none");
    buttonHelp.removeClass("mcv_display_none");
    buttonScreenMode.addClass("mcv_display_none");
    buttonEditTitle.addClass("mcv_display_none");
    divEditMode.addClass("mcv_display_none");
    $(".fa-wrench").removeClass("mcv_display_none");
  }

  // JS QUI PERMET DE REGLER LE PROBLEME SUR LE CLIC "VUE PERSONNELLE"
  var executeClick = true;
  var clickOnMyView = document.querySelector("[title='Vue personnelle']");
  var myCustomView = document.querySelector("[title='Ma vue personnalisée']");
  clickOnMyView.addEventListener("click", function () {
    myCustomView.setAttribute("data-change", 1);
  });

  // Faire disparaitre la modal
  $(".mcv_button_message").on("click", function () {
    $(".mcv_display_message").addClass("mcv_display_none");
  });

  // Faire apparaitre/disparaitre le tableau des recherches sauvegardées
  $(".listToggle").on("click", function () {
    $(".mcv_tab_limit").toggle(800);
    if ($(this).hasClass("toggle_active")) {
      $("span", this).html("Afficher la liste");
      $(this).removeClass("toggle_active");
      $("i", this).removeClass("fa-chevron-up").addClass("fa-chevron-down");
    } else if (!$(this).hasClass("toggle_active")) {
      $("span", this).html("Masquer la liste");
      $(this).addClass("toggle_active");
      $("i", this).removeClass("fa-chevron-down").addClass("fa-chevron-up");
    }
  });

  $(".mcv_tab_container").on("dadDropEnd", function (e, element) {
    console.log(element);
    buttonCancel.attr("data-message", 1);
    buttonSave.addClass("button_shaking");
  });

  buttonsDelete.on("click", function () {
    buttonCancel.attr("data-message", 1);
  });

  buttonEditTitle.on("click", function () {
    $(".mcv_modal_edit_title").removeClass("mcv_display_none");
    var title = $(this).prev("a").text();
    savedSearchTitle.val(title);
    $("input[name='id_savedsearch']").val($(this).data("id"));
  });

  $(function () {
    $(".tab_cadrehov").tablesorter({
      theme: "default",
    });
  });

  $(".mcv_screenmode").on("click", function () {
    var parentDiv = $(this).parents(".mcv_tab");

    if (parentDiv.hasClass("w-49")) {
      parentDiv.addClass("w-100").removeClass("w-49");
      $("span", this).html("Fenêtre réduite");
      $("i", this).removeClass("fa-expand").addClass("fa-compress");
      parentDiv.attr('data-screenmode', 1);
    } else if (parentDiv.hasClass("w-100")) {
      parentDiv.addClass("w-49").removeClass("w-100");
      $("span", this).html("Fenêtre large");
      $("i", this).removeClass("fa-compress").addClass("fa-expand");
      parentDiv.attr('data-screenmode', 0);
    }
  });

  $(".order_DESC::before").addClass("mcv_display_none");

  $(".flexslider").flexslider();
});
