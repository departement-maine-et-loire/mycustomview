$(document).ready(function () {
  //Déclaration des variables
  var buttonEdit = $("#mcv_edit");
  var buttonSave = $("#mcv_save");
  var buttonCancel = $("#mcv_cancel");
  var buttonHelp = $("#mcv_help");
  var buttonsDelete = $(".mcv_delete");
  var buttonScreenMode = $(".mcv_screenmode");
  var buttonEditTitle = $(".mcv_edit_title");
  var divEditMode = $(".mcv_header_edit_mode");
  var fadeZone = $(".draggable");
  var transparentDiv = $(".mcv_transparent_view");
  var savedSearchTitle = $(".savedsearch_title");
  var mcvTab = $(".mcv_tab");

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
    $(".mcv_title_savedsearch").addClass("absolute_edit_title");
    $(".mcv_content_savedsearch").addClass("absolute_edit_content");
    $('#mcv_hide').addClass('mcv_display_none');
    mcvTab.addClass("resizable");

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
    $(".mcv_title_savedsearch").removeClass("absolute_edit_title");
    $(".mcv_content_savedsearch").removeClass("absolute_edit_content");
    $('#mcv_hide').removeClass('mcv_display_none');
    mcvTab.removeClass("resizable");
    resetHeight();
  }

  $("#mcv_cancel").on("click", function () {
    if ($(this).data("message") == 1) {
      $(".mcv_cancel_message").removeClass("mcv_display_none");
      $(".fullscreen-dark-container").removeClass("mcv_display_none");
    } else {
      actionOnCancel();
    }
  });

  function resetHeight() {
    mcvTab.each(function () {
      var height = $(this).height();
      var dataHeight = $(this).data('height');
      if (height != dataHeight) {
        $(this).css('height', dataHeight + 'px');
      }
    });
  }

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

  // Ouverture de la pop-up d'aide, chargement des images et démarrage du slider
  buttonHelp.on("click", function () {
    $(".mcv_modal_help").removeClass("mcv_display_none");
    $(".fullscreen-dark-container").removeClass("mcv_display_none");
    // Réactivez les 3 lignes dessous pour faire demarrer le slider à chaque clic sur l'aide
    // $(".flexslider").removeData("flexslider");
    // $("ol.flex-control-nav").remove();
    // $("ul.flex-direction-nav").remove();
    $(".flexslider").flexslider({
      start: function () {
        $(".flexImages").show();
      },
    });
  });

  // Clic de la partie grisée lorsqu'une pop-up est ouverte
  $(".fullscreen-dark-container").on("click", function () {
    $(".mcv_modal_help").addClass("mcv_display_none");
    $(".mcv_modal_edit_title").addClass("mcv_display_none");
    $(".fullscreen-dark-container").addClass("mcv_display_none");
  });

  $(".mcv_button_message_cancel").on("click", function () {
    location.reload();
  });

  // JS QUI PERMET DE REGLER LE PROBLEME SUR LE CLIC "VUE PERSONNELLE"
  var clickOnMyView = document.querySelector("[title='Vue personnelle']");
  var clickOnMyView2 = document.querySelector("[title='Tableau de bord']");
  var myCustomView = document.querySelector("[title='Ma vue personnalisée']");
  clickOnMyView.addEventListener("click", function () {
    myCustomView.setAttribute("data-change", 1);
  });
  clickOnMyView2.addEventListener("click", function () {
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

  //On ajoute un indice sur le bouton cancel pour savoir si on a modifié un tableau de la vue
  $(".mcv_tab_container").on("dadDropEnd", function (e, element) {
    buttonCancel.attr("data-message", 1);
  });

  buttonsDelete.on("click", function () {
    buttonCancel.attr("data-message", 1);
  });

  buttonEditTitle.on("click", function () {
    $(".fullscreen-dark-container").removeClass("mcv_display_none");
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

  // Clic sur le redimensionnement d'une fenêtre
  $(".mcv_screenmode").on("click", function () {
    var parentDiv = $(this).parents(".mcv_tab");
    buttonCancel.attr("data-message", 1);

    if (parentDiv.hasClass("w-49")) {
      parentDiv.addClass("w-100").removeClass("w-49");
      $("span", this).html("Fenêtre réduite");
      $("i", this).removeClass("fa-expand").addClass("fa-compress");
      parentDiv.attr("data-screenmode", 1);
    } else if (parentDiv.hasClass("w-100")) {
      parentDiv.addClass("w-49").removeClass("w-100");
      $("span", this).html("Fenêtre large");
      $("i", this).removeClass("fa-compress").addClass("fa-expand");
      parentDiv.attr("data-screenmode", 0);
    }
  });

  $(".order_DESC::before").addClass("mcv_display_none");

  $(".mcv_nb_items option").each(function () {
    var sessionNumberItems = $(".mcv_nb_items").data('session-items-number');
    if ($(this).val() == sessionNumberItems) {
      $(this).attr('selected', true);
    }
  });

  $('#mcv_hide').on("click", function () {
    $(".mcv_manage_tab").fadeOut(500, function () {
      $(".mcv_settings").removeClass('mcv_display_none');
    });

  });

  $('#mcv_show').on("click", function () {
    if ($(".mcv_manage_tab").hasClass("mcv_display_none")) {
      $(".mcv_manage_tab").removeClass("mcv_display_none");
    }
    $(".mcv_manage_tab").fadeIn(500);
    $(".mcv_settings").addClass('mcv_display_none');
  });

});
