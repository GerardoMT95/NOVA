/**
 * @file
 * Global utilities.
 *
 */

 let reduceFontSize = function($element) {
    let a_width = $element.width();
    let h_width = $element.parent().width();
    if (a_width > h_width + 10) {
        console.log($element.html() + " a_width:" + a_width + " h_width: " + h_width);
        let h_new_padding = parseInt($element.parent().css("padding-left")) -3;
        if (h_new_padding <0) h_new_padding = 0;
        $element.parent().css("padding-left", h_new_padding + "px").css("padding-right", h_new_padding + "px");
        let a_new_fontsize = parseInt($element.css("font-size"))-1;
        $element.css("font-size",a_new_fontsize + "px");
        window.setTimeout(function() {
            reduceFontSize($element);
        }, 20); 
    }
};

(function ($, Drupal) {

    'use strict';

    Drupal.behaviors.vetrina_aziende = {
        attach: function (context, settings) {
            let editPagination = function () {
                $("ul.pagination li").each(function () {
                    if ($(this).html().includes("»") || $(this).html().includes("«")) $(this).hide();
                });
            }
            editPagination();

            //HOME VETRINA: RIPOSIZIONO CORRETTAMENTE I FILTRI CHECKBOX SOTTO LA RICERCA LIBERA
            $(".block-facet-blocktipo-di-impresa").insertAfter($(".view-vetrina-imprese-search .view-filters"));

            //ADATTO IL FONT-SIZE DELLE SCRITTE NELLE CARD DELLA VETRINA IN BASE ALLA DIMENSIONE DEL CONTAINER
            $(".view-vetrina-imprese-search a[href*='/vetrina-imprese/imprese/']").each(function() {
                reduceFontSize($(this));
            }); 
            
        }
    };

})(jQuery, Drupal);

$ = jQuery;
$(document).ready(function () {

//    $(window).resize(function() {
//    //non funziona bene
////nodo vetrina imprese
//       
//         if (window.matchMedia('(min-width: 992px)').matches) {
//            var maxHeight = Math.max.apply(null, jQuery("#block-views-block-vetrina-imprese-block-8 .slide__description .field-wrapper").map(function () {
//                return jQuery(this).height();
//            }).get());
//            jQuery("#block-views-block-vetrina-imprese-block-8 .slide__description .field-wrapper").css("min-height", maxHeight);
//        }
//        else {
//            jQuery("#block-views-block-vetrina-imprese-block-8 .slide__description .field-wrapper").css("min-height", 'inherit');
//        }
//    }).resize();
    $(window).resize(function () {
        var maxHeight = 0;

        $("#block-views-block-vetrina-imprese-block-8   .slide__description .field-wrapper").each(function () {
            if ($(this).height() > maxHeight) {
                maxHeight = $(this).height();
            }
        });

        $("#block-views-block-vetrina-imprese-block-8 .slide__description .field-wrapper").height(maxHeight);
    }).resize();

    $(window).resize(function () {
        var maxHeight = 0;

        $("#slick-views-catalogo-impresa-block-block-1-1   .slide__description .field-wrapper").each(function () {
            if ($(this).height() > maxHeight) {
                maxHeight = $(this).height();
            }
        });

        $("#slick-views-catalogo-impresa-block-block-1-1 .slide__description .field-wrapper").height(maxHeight);
    }).resize();

    $(window).resize(function () {
        var maxHeight = 0;

        $("#block-views-block-catalogo-impresa-block-1   .slide__description .field-wrapper").each(function () {
            if ($(this).height() > maxHeight) {
                maxHeight = $(this).height();
            }
        });

        $("#block-views-block-catalogo-impresa-block-1 .slide__description .field-wrapper").height(maxHeight);
    }).resize();

//tolgo lo slash finale nei link del sito delle imprese di vetrina
    $(".line a").each(function () {
        if ($(this).html().endsWith("/")) {
            $(this).html($(this).html().substring(0, $(this).html().length - 1));
        }
    });

    /*if ($("#edit-field-nazione").length >= 1) {

        $('#edit-field-nazione').on('change', function () {

            let nazione_selezionata = $(this).children(':selected').text();
            if (nazione_selezionata == 'Italia') {
                $('.ita_field').show();
            } else {
                $('.ita_field').hide();
            }
        });
    }*/

    if ($("#edit-field-tipo-di-impresa").length >= 1) {
        $('#edit-field-tipo-di-impresa .form-check-input').change(function () {
            let block = false;
            let click_id = $(this).val();
            if (this.checked) {
                $('#edit-field-tipo-di-impresa .form-check-input:checked').each(function () {
                    let temp_id = $(this).val();
                    // console.log(click_id+'--'+temp_id);
                    if (click_id != temp_id && click_id != 667 && temp_id != 667) {
                        // console.log('blocco il click');
                        block = true;
                        return false;
                    }
                });
            }

            if (block) {
                $(this).prop("checked", false)
            }
        });
    }

    window.setTimeout(function() {
    //AGGIUNGO LINK DI NAVIGAZIONE AL CATALOGO PRODOTTI PER LE AZIENDE CHE HANNO PERMESSO MODIFICA
        $("body.role-impresa.node--type-catalogo-prodotti-progetti-tecno main#content > section > nav > ul.nav-tabs").each(function() {
            if ($("> li > a[href*='/edit']", $(this)).length>0) {
                $(this).append("<li class='nav-item'><a href='/catalogo-impresa' class='nav-link' style='background-color: #FAB72B; border-color: #FAB72B; color:#fff;margin-left:15px;'>I miei Prodotti/Servizi, Progetti e Tecnologie</a></li>").append("<li class='nav-item'><a href='/node/add/catalogo_prodotti_progetti_tecno' style='background-color: #FAB72B; border-color: #FAB72B;color:#fff;margin-left:5px;' class='nav-link'>Aggiungi</a></li>");
            }            
        });  
            //AGGIUNGO LINK DI NAVIGAZIONE ALLA GESTIONE AZIENDE PER LE AZIENDE CHE HANNO PERMESSO MODIFICA
        $("body.role-impresa.node--type-impresa main#content > section > nav > ul.nav-tabs").each(function() {
            if ($("> li > a[href*='/edit']", $(this)).length>0) {
                $(this).append("<li class='nav-item'><a href='/gestione-imprese' class='nav-link' style='background-color: #FAB72B; border-color: #FAB72B; color:#fff;margin-left:15px;'>Gestione Imprese</a></li>");
            }            
        });
    //TOLGO LA TAB ELIMINA DAL MENU CONTESTUALE DISPONIBILE NELLA VISUALIZZAZIONE DI UN CONTENUTO
        $("nav > ul.nav-tabs > li > a[href*='/delete']").each(function() {
            $(this).parent().hide();
        });  
    }, 300);

    window.setTimeout(function() {
    //NASCONDO I FILTRI E IL DOPPIO BOTTONE CREA NELLA VISTA DI GESTIONE PRODOTTI/PROGETTI/TECNOLOGIE
        if ($("#block-views-block-management-catalogo-block-1 .view-empty").length>0) {
            $("#block-views-block-management-catalogo-block-1 .view-filters").hide();
            $("#block-views-block-management-catalogo-block-1 .view-header a[href*='/node/add/']").hide();
        }

    //TOLGO LA DESTINATION SUI BOTTONI OPERATIONS DELLA VISTA GESTIONE CATALOGO
        $(".view-management-catalogo .views-field-operations a").each(function() {
            $(this).attr("href",$(this).attr("href").replace("?destination=/catalogo-impresa",""));
        });

    //FIX PER REDIRIGERE ALLA PROPRIA AREA UTENTE (O LOGIN SE SLOGGATI) SE UN UTENTE È ANONIMO E VISITA IL LINK DI UN'IMPRESA CHE NON PUÒ VISUALIZZARE
        //if ($("#block-vetrina-aziende-headeruserlogin").length>0 && $("body.node--type-impresa .tabs ul.nav-tabs > li > a[href*='/vetrina-imprese/imprese/']").length==0) location.href="/user/login?destination=" + location.pathname;
        if ($("body.node--type-impresa").length>0 && $("#block-vetrina-aziende-headeruserlogin").length==0 && $("body.node--type-impresa .tabs ul.nav-tabs > li > a[href*='/vetrina-imprese/imprese/']").length==0) location.href="/user";
        // if ($("body.node--type-impresa.role-anonymous").length>0 && $("body.node--type-impresa .tabs ul.nav-tabs > li > a[href*='/vetrina-imprese/imprese/']").length==0) location.href="/user/login?destination=" + location.pathname;
        // else if ($("body.node--type-impresa").length>0 && $("body.node--type-impresa .tabs ul.nav-tabs > li > a[href*='/vetrina-imprese/imprese/']").length==0) location.href="/user";

    //SE L'UTENTE NON HA NESSUNA IMPRESA PUBBLICATA E NON HA NESSUN PRODOTTO ALLORA NON PUÒ AGGIUNGERE PRODOTTI
        if ($(".view-id-management_catalogo.view-display-id-block_1 .view-empty").length>0) {
            //   /imprese-pubblicate-utente-corrente
            $.get("/imprese-validate-utente-corrente").done(function(data) { 
                if (data && data.length>0) {
                    console.log("almeno un'impresa pubblicata");
                }
                else if (data && data.length==0) {
                    console.log("nessuna impresa pubblicata");
                    $(".view-id-management_catalogo.view-display-id-block_1 .view-empty").html('Nessuna impresa validata, impossibile procedere all\'inserimento di prodotti/serivizi o tecnologie.<br><a href="/node/add/catalogo_prodotti_progetti_tecno?display=azienda" class="btn btn-success disabled mb-2">Aggiungi</a>');
                }
            });
        }

    }, 50);


});
//block-views-block-catalogo-impresa-block-1
$(window).on('load', function () {
$("#overlay").hide();
});

window.onload = function () {

};
//$("#edit-submit").submit(function () {
//$("#overlay").show();
//// Do something!
//        console.log('submit button clicked!');
//});