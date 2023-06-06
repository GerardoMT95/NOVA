/**
 * @file
 * Global utilities.
 *
 */

function sort_func(a, b) {
    return a.toLowerCase().localeCompare(b.toLowerCase());
}

(function ($, Drupal) {

    'use strict';

    Drupal.behaviors.novaliguria = {
        attach: function (context, settings) {
            $('[data-bs-toggle=popover]').popover({
                html: true,
                content: function () {
                    return $('#popover-content').html();
                }

            });
            var myDefaultAllowList = bootstrap.Popover.Default.allowList

            // To allow button elements
            myDefaultAllowList.button = [];
            myDefaultAllowList.time = [];
            $('#show-popover').popover({
                container: 'body',
                html: true,
                placement: 'bottom',
                sanitize: false,
                content: function () {
                    return $('#popover-content').html();
                }
            })
            $('[data-bs-toggle=tooltip]').tooltip();
            //$('[data-bs-toggle=modal]').modal();
            let url = location.href.replace(/\/$/, "");

            if (location.hash) {
                const hash = url.split("#");
                //console.log(hash);
                $('#myTab a[href="#' + hash[1] + '"]').tab("show");
                url = location.href.replace(/\/#/, "#");
                if (document.getElementById("wizard-tab"))
                    document.getElementById("wizard-tab").scrollIntoView({behavior: "smooth"});
                else if (document.getElementsByClassName("tab-content").length > 0)
                    document.getElementsByClassName("tab-content")[0].scrollIntoView({behavior: "smooth"});
                //history.replaceState(null, null, url);
                // setTimeout(() => {
                //     $(window).scrollTop(0);
                // }, 400);
            }

            $('a[data-bs-toggle="pill"]').on("click", function () {
                //console.log('click');
                let newUrl;
                const hash = $(this).attr("href");
                //console.log("hash:"+hash);
                //if (hash == "#wizard-1") {
                //    newUrl = url.split("#")[0];
                //} else {
                newUrl = url.split("#")[0] + hash;
                //}
                newUrl += "/";
                //history.replaceState(null, null, newUrl);
                window.location.href = newUrl;
                if (document.getElementById("wizard-tab"))
                    document.getElementById("wizard-tab").scrollIntoView({behavior: "smooth"});
                else if (document.getElementsByClassName("tab-content").length > 0)
                    document.getElementsByClassName("tab-content")[0].scrollIntoView({behavior: "smooth"});
            });

            //$("span").closest("ul").css({"color": "red", "border": "2px solid red"});
            $('.indent-0').each(function () {
                if ($(this).hasClass('indent-0')) {
                    $(this).closest('td').addClass('indent-0');
                }
            });
            $('.indent-1').each(function () {
                if ($(this).hasClass('indent-1')) {
                    $(this).closest('td').addClass('indent-1');
                }
            });
            $('.indent-2').each(function () {
                if ($(this).hasClass('indent-2')) {
                    $(this).closest('td').addClass('indent-2');
                }
            });

            //FACCIO APRIRE I LINK DELLE CARD DEI SERVIZI IN UN ALTRA FINESTRA PER EVITARE DI PERDERE LA RICERCA NEL WORKFLOW
            $("body.path-trova-il-tuo-servizio .box-search-service a").attr("target", "_blank");

        }
    }; //end behavior
    //a capo dopo '?'
    $(".nav-link .box-content h3:contains('?')").each(function () {
        $(this).html($(this).html().replace('?', '?<br>'));
    });
    $("#draggableviews-table-management-workflow-page-1 .indent-0:contains('?')").each(function () {
        $(this).html($(this).html().replace('?', '?<br>'));
    });
    $(".view-management-servizi .views-field-parent-target-id-1:contains('?')").each(function () {
        $(this).html($(this).html().replace('?', '?<br>'));
    });
    $(".view-categorie-management-servizi .views-field-name:contains('?')").each(function () {
        $(this).html($(this).html().replace('?', '?<br>'));
    });
    $(".view-page-service .area-tree .indent-0:contains('?')").each(function () {
        $(this).html($(this).html().replace('?', '?<br>'));
    });

    let clona_bottoni_wizard_servizi = function () {
        $("body.path-trova-il-tuo-servizio form div[id^=form-wrapper]").each(function () {
            //CLONO E METTO SOPRA IL BOTTONE CONTINUA NEL WORKFLOW PRIMO STEP
            if ($("fieldset.step-1:not(.btn-duplicated) legend > span", $(this)).length > 0) {
                let btn_orig = $("button[id^=edit-next]", $(this));
                let btn_cloned = btn_orig.addClass("step-1-btn-cloned").clone(true).css("margin-bottom", "20px").css("float", "right");
                $("fieldset.step-1 legend > span", $(this)).after(btn_cloned);
                $("fieldset.step-1", $(this)).addClass("btn-duplicated");
                $(".fieldset-wrapper .form-content", $(this)).each(function () {
                    $(this).click(function () {
                        setTimeout(function () {
                            if (btn_orig.is(":visible"))
                                btn_cloned.show();
                            else
                                btn_cloned.hide();
                        }, 50);
                    });
                });
            }
            //CLONO E METTO SOPRA IL BOTTONE CONTINUA E IL BOTTONE INDIETRO NEL WORKFLOW SECONDO STEP
            if ($("fieldset.step-2:not(.btn-duplicated) legend > span", $(this)).length > 0) {
                let btn_orig = $("button[id^=edit-finish]", $(this));
                let btn_cloned = btn_orig.addClass("step-2-btn-cloned").clone(true).css("margin-bottom", "30px");
                $("fieldset.step-2 legend > span", $(this)).after(btn_cloned);
                $("fieldset.step-2", $(this)).addClass("btn-duplicated");
                $(".fieldset-wrapper .form-content", $(this)).each(function () {
                    $(this).click(function () {
                        setTimeout(function () {
                            if (btn_orig.is(":visible"))
                                btn_cloned.show();
                            else
                                btn_cloned.hide();
                        }, 50);
                    });
                });
                let btn_indietro_cloned = $("div[id^=edit-actions]", $(this)).clone(true);
                $("fieldset.step-2 legend", $(this)).before(btn_indietro_cloned);
                $("button[id^=edit-finish]", btn_indietro_cloned).remove();
            }
            //CLONO E METTO SOPRA IL BOTTONE INDIETRO NEL WORKFLOW SECONDO STEP
            if ($("fieldset[id^=edit-service-typology]:not(.btn-duplicated) legend", $(this)).length > 0) {
                $("fieldset[id^=edit-service-typology]", $(this)).addClass("btn-duplicated");
                $("fieldset[id^=edit-service-typology] legend", $(this)).before($("a[id^=edit-previous]", $(this)).clone(true).css("margin-top", "0px").css("margin-bottom", "0px"));
            }
        });

        // //CLONO E METTO SOPRA IL BOTTONE CONTINUA NEL WORKFLOW SECONDO STEP
        // $("body.path-trova-il-tuo-servizio form div[id^=form-wrapper]").each(function() {
        //     $("fieldset.step-2 legend > span",$(this)).after($("button[id^=edit-finish]:not(.duplicated)",$(this)).addClass("duplicated").clone(true).css("margin-bottom","30px"));
        // });
        // //CLONO E METTO SOPRA IL BOTTONE INDIETRO NEL WORKFLOW SECONDO STEP
        // $("body.path-trova-il-tuo-servizio form div[id^=form-wrapper]").each(function() {
        //     $("fieldset.step-2 legend",$(this)).before($("div[id^=edit-actions]:not(.duplicated)",$(this)).addClass("duplicated").clone(true));
        // });

        // //CLONO E METTO SOPRA IL BOTTONE INDIETRO NEL WORKFLOW STEP FINALE
        // $("body.path-trova-il-tuo-servizio form div[id^=form-wrapper]").each(function() {
        //     $("fieldset[id^=edit-service-typology] legend",$(this)).before($("a[id^=edit-previous]:not(.duplicated)",$(this)).addClass("duplicated").clone(true).css("margin-top","0px").css("margin-bottom","0px"));
        // });
    }

    $(document).ready(function () {
        clona_bottoni_wizard_servizi();
    }); //end document.ready

    $(document).ajaxComplete(function (event, xhr, settings) {

        clona_bottoni_wizard_servizi();

        if (!window.inserimento_occhiolino_in_corso && $("body.path-management-servizi-admin").length > 0 && $("i.table-categorie").length == 0) {
            window.inserimento_occhiolino_in_corso = true;
            window.nascondi_categorie = function (obj, table) {
                //obj è il tag <i></i>
                $ = jQuery;
                let righeTableCategorie = $("> tbody", table);
                if (obj.attr("data-show") == "false") {
                    obj.attr("data-show", "true");
                    obj.removeClass("fa-eye").addClass("fa-eye-slash");
                    //se obj è il th della main_table allora devo aggiornare anche i data-show della table_servizi
                    $("i.table-categorie", table).attr("data-show", "true").removeClass("fa-eye").addClass("fa-eye-slash");
                    righeTableCategorie.show();
                } else {
                    obj.attr("data-show", "false");
                    obj.removeClass("fa-eye-slash").addClass("fa-eye");
                    //se obj è il th della main_table allora devo aggiornare anche i data-show della table_servizi
                    $("i.table-categorie", table).attr("data-show", "false").removeClass("fa-eye-slash").addClass("fa-eye");
                    righeTableCategorie.hide();
                }
            }

            //console.log("aggiungo occhiolino servizi");
            //window.aggiungi_occhiolino_servizi
            //hide all row MacroArea,Area,Ambito in view management servizi admin
            if ($("body.path-management-servizi-admin").length > 0) {
                let mainTable = $(".view-id-management_servizi.view-display-id-page_3 > div > div > form > div > table");
                let headerMainTable = $("> thead > tr > th:nth-child(5)", mainTable);
                let tableCategorie = $("> tbody > tr > td .view-id-categorie_management_servizi.view-display-id-block_1 table", mainTable);
                headerMainTable.html(headerMainTable.html() + '&nbsp;&nbsp;<i data-show="true" style="cursor:pointer" onClick="nascondi_categorie(jQuery(this), \'.view-id-categorie_management_servizi.view-display-id-block_1 > div > div > table\');"  class="fa fa-fw fa-eye-slash"></i>');
                let headerTableCategorie = $("> thead > tr > th:nth-child(1)", tableCategorie);
                headerTableCategorie.html(headerTableCategorie.html() + '&nbsp;&nbsp;<i data-show="true" style="cursor:pointer" onClick="nascondi_categorie(jQuery(this), jQuery(this).parent().parent().parent().parent());"  class="table-categorie fa fa-fw fa-eye-slash"></i>');
            }
            window.inserimento_occhiolino_in_corso = false;
        }
        console.log("ajaxcomplete");
        // forzo lo scroll della pagina nel box wizard
        //var hash = window.location.hash;
        // if(!hash){
        //    hash = "#wizard-1"; 
        // }else {
        //     hash = hash.slice(0, -1);
        // }
        //console.log(hash);
        // $('html, body').animate({
        //     scrollTop: $(hash).offset().top
        // }, 1000);
    });

    $(document).ready(function () {

        //GESTIONE BREADCRUMB PERSONALIZZATI SUL RUOLO
        // window.setTimeout(function() {
        //     $("body.page-view-management-annunci.role-administrator #block-breadcrumbs-2 li:nth-child(3)").each(function() {
        //         $(this).html($(this).html().replace("Management Annunci","Gestione Annunci"));
        //     });
        window.setTimeout(function () {
            //ADMIN
            $("body.node--type-avviso.role-administrator #block-breadcrumbs-2 li:nth-child(2), body.node--type-avviso.role-amministratore_portale #block-breadcrumbs-2 li:nth-child(2)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/management-annunci">Annunci</a></li>');
            });
            $("body.node-richiesta.role-administrator #block-breadcrumbs-2 li:nth-child(1), body.node-richiesta.role-amministratore_portale #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/management-richieste-servizi">Richieste Servizi</a></li>');
            });
            $("body.node--type-richiesta-spazio.role-administrator #block-breadcrumbs-2 li:nth-child(1), body.node--type-richiesta-spazio.role-amministratore_portale #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/management-richieste-spazi">Richieste Spazi</a></li>');
            });
            $("body.node--type-richiesta-nuovi-servizi.role-administrator #block-breadcrumbs-2 li:nth-child(1), body.node--type-richiesta-nuovi-servizi.role-amministratore_portale #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/management-richieste-nuovi-servizi">Richieste Nuovi Servizi</a></li>');
            });

            //UTENTE_NOVA
            // $("body.node--type-avviso.role-utente_nova #block-breadcrumbs-2 li:nth-child(2), body.node--type-avviso.role-amministratore_portale #block-breadcrumbs-2 li:nth-child(2)").each(function() {
            //     $(this).after('<li class="breadcrumb-item"><a href="/annunci-impresa">Annunci</a></li>');
            // });
            $("body.node--type-avviso.role-utente_nova #block-breadcrumbs-2 li:nth-child(2)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/annunci-impresa">Annunci</a></li>');
            });
            $("body.node-richiesta.role-utente_nova #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/richieste-servizi-impresa">Richieste Servizi</a></li>');
            });
            $("body.node--type-richiesta-spazio.role-utente_nova #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/richieste-spazi-impresa">Richieste Spazi</a></li>');
            });
            $("body.node--type-richiesta-nuovi-servizi.role-utente_nova #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/richieste-nuovi-servizi-impresa">Richieste Nuovi Servizi</a></li>');
            });

            //STAKEHOLDER
            $("body.node-richiesta.role-stakholder #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/richieste-servizi-stakeholder">Richieste Servizi</a></li>');
            });
            $("body.node--type-richiesta-spazio.role-stakholder #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/richieste-spazi-stakeholder">Richieste Spazi</a></li>');
            });
            $("body.node--type-richiesta-nuovi-servizi.role-stakholder #block-breadcrumbs-2 li:nth-child(1)").each(function () {
                $(this).after('<li class="breadcrumb-item"><a href="/user">Area Utente</a></li><li class="breadcrumb-item"><a href="/richieste-nuovi-servizi-stakeholder">Richieste Nuovi Servizi</a></li>');
            });

            //AGGIUNGO IL BOTTONE VAI AL CARRELLO DOPO IL BOTTONE RIMUOVI NELLA PAGINA DEL SERVIZIO
            $("body.node--type-servizio button.flag-wishlist-servizi > a[href^='/flag/unflag/']").each(function () {
                $(this).parent().after($('<div class="text-center info-contact" style="margin-left: 30px;"><a href="/carrello" class="btn">VAI AL CARRELLO</a></div>'));
            });

        }, 100);



        if (window.location.hash != "") {
            $('a[href="' + window.location.hash.replace("/", "") + '"] button').click();
        }

        window.nascondi_servizi = function (obj, table) {
            //obj è il tag <i></i>
            $ = jQuery;
            let righeTableServizi = $("> tbody, > tbody > tr", table);
            let headerTableServiziStakeholder = $("> thead > tr > th:nth-child(2)", table);
            let headerTableServiziTagI = $("> thead > tr > th:nth-child(1) > i", table);
            if (obj.attr("data-show") == "false") {
                obj.attr("data-show", "true");
                obj.removeClass("fa-eye").addClass("fa-eye-slash");
                //se obj è il th della main_table allora devo aggiornare anche i data-show della table_servizi
                $("i.table-servizi", table).attr("data-show", "true");
                righeTableServizi.show();
                headerTableServiziStakeholder.show();
                headerTableServiziTagI.removeClass("fa-eye").addClass("fa-eye-slash");
            } else {
                obj.attr("data-show", "false");
                obj.removeClass("fa-eye-slash").addClass("fa-eye");
                //se obj è il th della main_table allora devo aggiornare anche i data-show della table_servizi
                $("i.table-servizi", table).attr("data-show", "false");
                righeTableServizi.hide();
                headerTableServiziStakeholder.hide();
                headerTableServiziTagI.removeClass("fa-eye-slash").addClass("fa-eye");
            }
        }

        //hide all row servizi in view management workflow
        if ($("body.path-management-workflow, body.path-ricerca-workflow").length > 0) {
            let mainTable = $(".view-id-management_workflow.view-display-id-page_1 > div > div > form > div > table, .view-id-management_workflow.view-display-id-page_2 > div > div > table");
            let headerMainTable = $("> thead > tr > th:last-child", mainTable);
            let tableServizi = $("> tbody > tr > td .view-id-management_servizi.view-display-id-block_1 table", mainTable);
            headerMainTable.html(headerMainTable.html() + '&nbsp;&nbsp;<i data-show="true" style="cursor:pointer" onClick="nascondi_servizi(jQuery(this), \'.view-id-management_servizi.view-display-id-block_1 > div > div > table\');"  class="fa fa-fw fa-eye-slash"></i>');
            let headerTableServizi = $("> thead > tr > th:nth-child(1)", tableServizi);
            headerTableServizi.html(headerTableServizi.html() + '&nbsp;&nbsp;<i data-show="true" style="cursor:pointer" onClick="nascondi_servizi(jQuery(this), jQuery(this).parent().parent().parent().parent());"  class="table-servizi fa fa-fw fa-eye-slash"></i>');
        }


        if ($("body.path-ricerca-workflow").length > 0) {
            fetch(new Request("/rest/export/json/servizi"))
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${ response.status }`);
                        }
                        return response.json();
                    })
                    .then((response) => {
                        console.log(response);
                        let macroarea_arr = [...new Set(response.map(({macroarea}) => macroarea))];
                        let area_arr = [...new Set(response.map(({area}) => area))];
                        let ambito_arr = [...new Set(response.map(({ambito}) => ambito))];
                        let servizio_arr = [...new Set(response.map(({nome}) => nome.replace(/(<([^>]+)>)/gi, "")))].sort(sort_func);
                        let stakeholder_arr = [...new Set(response.map(({stakeholder}) => stakeholder))].sort(sort_func);
                        console.log(macroarea_arr);
                        console.log(area_arr);
                        console.log(ambito_arr);
                        console.log(servizio_arr);
                        console.log(stakeholder_arr);
                        //   $("#select-macro-area").html('<option selected="selected">- Macro Area -</option>' + macroarea_arr.map(  (nome)=>'<option value="' + nome + '">' + nome + '</option>').join('')).hide();
                        //   $("#select-area").html('<option selected="selected">- Area -</option>' + area_arr.map((nome)=>'<option value="' + nome + '">' + nome + '</option>').join('')).hide();
                        //   $("#select-ambito").html('<option selected="selected">- Ambito -</option>' + ambito_arr.map((nome)=>'<option value="' + nome + '">' + nome + '</option>').join('')).hide();
                        $("#select-stack").html('<option value="---" selected="selected">- Tutti gli Stakeholder -</option>' + stakeholder_arr.map((nome) => '<option value="' + nome + '">' + nome + '</option>').join(''));
                        $("#select-servizio").html('<option value="---" disabled selected="selected">- Lista servizi -</option>' + servizio_arr.map((nome) => '<option value="' + nome + '">' + nome + '</option>').join(''));
                        $('#select-servizio').change(function (event) {
                            console.log(event.target.value);
                            $("#form-search").val(event.target.value);
                            $('#select-servizio').val("---");
                        });
                        $("#btn-reset").click(function () {
                            location.reload();
                        });
                        $("#btn-apply").click(function () {
                            let mainTable = $(".view-id-management_workflow.view-display-id-page_2 > div > div > table");
                            let tableServizi = $("> tbody > tr > td .view-id-management_servizi.view-display-id-block_1 table", mainTable);
                            $("tbody > tr", tableServizi).each(function () {
                                let servizio = $("td:nth-child(1)", $(this)).html().toLowerCase();
                                let stakeholder = $("td:nth-child(2)", $(this)).html().toLowerCase();
                                let servizio_search = $("#form-search").val().toLowerCase();
                                let stakeholder_search = $("#select-stack").val().toLowerCase();
                                if (stakeholder_search == "---")
                                    stakeholder_search = "";
                                if (!servizio_search && !stakeholder_search || servizio_search && !stakeholder_search && servizio.includes(servizio_search) || stakeholder_search && !servizio_search && stakeholder.includes(stakeholder_search) || stakeholder_search && stakeholder.includes(stakeholder_search) && servizio_search && servizio.includes(servizio_search)) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            });
                        });
                    });
        }

        //AGGIUNGO COLONNA INSERIMENTO/AGGIORNAMENTO DEL SERVIZIO NELLA VISTA track-servizi : TROPPE COLONNE : NON CI STANNO!!!
        // $("body.path-track-servizi #block-novaliguria-content table > thead > tr > th#view-revision-uid-table-column").before('<th id="header-servizio-add-or-edit" class="views-field" scope="col">Ins/<br>Agg</th>');
        // $("body.path-track-servizi #block-novaliguria-content table > tbody > tr > td.views-field-revision-uid").before('<td headers="header-servizio-add-or-edit" class="views-field views-servizio-add-or-edit">INS</td>');

        //AGGIUNGO INSERIMENTO/AGGIORNAMENTO DEL SERVIZIO SOTTO IL NOME DEL SERVIZIO NELLA VISTA track-servizi
        // $("body.path-track-servizi #block-novaliguria-content table > tbody > tr td.views-field-title").each(function() {
        //     $(this).html($(this).html() + "<br><br><b>AGG</b>");
        // });

        //NASCONDO RIGHE SENZA MODIFICHE ALLE CATEGORIE DEL SERVIZIO NELLA VISTA track-servizi
        $("body.path-track-servizi #block-novaliguria-content table th#view-vid-table-column, body.path-track-servizi #block-novaliguria-content table > tbody > tr td.views-field-vid").hide();
        var utenti = new Set();
        $("body.path-track-servizi #block-novaliguria-content table > tbody > tr > td.views-field-revision-uid > a").each(function () {
            utenti.add($(this).attr("href"));
        });

        var revisioni = new Set();
        $("body.path-track-servizi #block-novaliguria-content table > tbody > tr > td.views-field-vid").each(function () {
            revisioni.add($(this).html().trim());
        });

        revisioni.forEach((id_revisione) => {
            let $last = null;
            let actual_change = "";
            $("body.path-track-servizi #block-novaliguria-content table > tbody > tr").each(function (index) {
                if ($("td.views-field-vid", $(this)).html().trim() == id_revisione) {
                    //SCRIVO PER TUTTE LE RIGHE "AGGIORNAMENTO"                
                    if (actual_change == $("td.views-field-field-categorie-del-servizio-revision-id", $(this)).html()) {
                        $(this).hide();
                        console.log("nascosto riga: " + (index + 1));
                    } else {
                        actual_change = $("td.views-field-field-categorie-del-servizio-revision-id", $(this)).html();
                        $("td.views-field-title", $(this)).html($("td.views-field-title", $(this)).html() + "<br><br><b><i>AGGIORNAMENTO</i></b>");
                        $last = $("td.views-field-title", $(this));
                    }
                }
            });
            if ($last)
                $last.html($last.html().replace("<br><br><b><i>AGGIORNAMENTO</i></b>", "<br><br><b><i>INSERIMENTO</i></b>"));
        });

        window.setTimeout(function () {
            //AGGIUNGO LINK DI NAVIGAZIONE AL CATALOGO PRODOTTI PER LE AZIENDE CHE HANNO PERMESSO MODIFICA
            $("body.node--type-catalogo-prodotti-progetti-tecno main#content > section > nav > ul.nav-tabs").each(function () {
                if ($("> li > a[href*='/edit']", $(this)).length > 0) {
                    $(this).append("<li class='nav-item'><a href='/catalogo-impresa' class='nav-link' style='background-color: #FAB72B; border-color: #FAB72B; color:#fff;margin-left:15px;'>I miei Prodotti/Servizi, Progetti e Tecnologie</a></li>").append("<li class='nav-item'><a href='/node/add/catalogo_prodotti_progetti_tecno' style='background-color: #FAB72B; border-color: #FAB72B;color:#fff;margin-left:5px;' class='nav-link'>Aggiungi</a></li>");
                }
            });
            //TOLGO LA TAB ELIMINA DAL MENU CONTESTUALE DISPONIBILE NELLA VISUALIZZAZIONE DI UN CONTENUTO
            $("nav > ul.nav-tabs > li > a[href*='/delete']").each(function () {
                $(this).parent().hide();
            });
            //PERSONALIZZO IL MESSAGGIO DI ACCESSO NEGATO SE PROVIENE DA UNA PAGINA SPECIFICA
            $(".alert-danger").each(function () {
                if ($(this).html().includes("Devi autenticarti su NOVA con il tuo SPID")) {
                    let location_decoded = location.href.replace("%3F", "?").replace("%3D", "=").replace("%3F", "?").replace("%3D", "=").replace("%3F", "?").replace("%3D", "=").replace("?destination=/", "?destination=");
                    if (location_decoded.includes("?destination=node/add/richiesta_spazio?display=azienda") || location_decoded.includes("?destination=nova/richiesta-accreditamento") || location_decoded.includes("?destination=node/add/richiesta_spazio?display=azienda") || location_decoded.includes("?destination=node/add/richiesta_nuovi_servizi?display=azienda") || location_decoded.includes("?destination=node/add/richiesta?display=azienda")) {
                        $(this).html($(this).html().replace("Devi autenticarti su NOVA con il tuo SPID", "Per inviare la richiesta devi autenticarti su NOVA con il tuo SPID"));
                    }
                }
            });
            $('select option:contains("- None -")').text("- Altra Impresa -");
            //IMPOSTO IL LINK CORRETTO SUL BOTTONE bottone-modifica-delegati-stakeholder NELL'AREA UTENTE DELLO STAKEHOLDER
            $("#bottone-modifica-delegati-stakeholder").each(function () {
                $(this).attr("href", $("nav > ul.nav-tabs > li > a[href$='/edit']").attr("href"));
            });
        }, 300);

    });
    Drupal.behaviors.vetrina_aziende = {
        attach: function (context, settings) {
            let editPagination = function () {
                $("ul.pagination li").each(function () {
                    if ($(this).html().includes("»") || $(this).html().includes("«"))
                        $(this).hide();
                });
            };
            editPagination();
        }
    };


    /**
     * Remove entity reference ID from "entity_autocomplete" field.
     *
     * @type {{attach: Drupal.behaviors.autocompleteReferenceEntityId.attach}}
     */
    /*
     Drupal.behaviors.autocompleteReferenceEntityId = {
     attach: function (context) {
     // Remove reference IDs for autocomplete elements on init.
     $('.form-autocomplete', context).once('replaceReferenceIdOnInit').each(function () {
     let splitValues = (this.value && this.value !== 'false') ?
     Drupal.autocomplete.splitValues(this.value) : [];
     
     if (splitValues.length > 0) {
     let labelValues = [];
     for (let i in splitValues) {
     let value = splitValues[i].trim();
     let entityIdMatch = value.match(/\s*\((.*?)\)$/);
     if (entityIdMatch) {
     labelValues[i] = value.replace(entityIdMatch[0], '');
     }
     }
     
     if (labelValues.length > 0) {
     $(this).data('real-value', splitValues.join(', '));
     this.value = labelValues.join(', ');
     }
     }
     });
     }
     };
     
     let autocomplete = Drupal.autocomplete.options;
     autocomplete.originalValues = [];
     autocomplete.labelValues = [];
     
     //add custom handler
     autocomplete.select = function (event, ui) {
     autocomplete.labelValues = Drupal.autocomplete.splitValues(event.target.value);
     autocomplete.labelValues.pop();
     autocomplete.labelValues.push(ui.item.label);
     autocomplete.originalValues.push(ui.item.value);
     
     $(event.target).data('real-value', autocomplete.originalValues.join(', '));
     event.target.value = autocomplete.labelValues.join(', ');
     
     return false;
     }
     /**/

})(jQuery, Drupal);

$ = jQuery;
$(document).ready(function () {
    $(window).resize(function () {
        if (window.matchMedia('(min-width: 992px)').matches) {
            var maxHeight = Math.max.apply(null, jQuery(".list-opportunity .op-box").map(function () {
                return jQuery(this).height();
            }).get());
            jQuery(".list-opportunity .op-box").css("min-height", maxHeight);
        } else {
            jQuery(".list-opportunity .op-box").css("min-height", 'inherit');
        }
    }).resize();
});
$(document).ready(function () {
    $(window).resize(function () {
//spazi homepage
        if (window.matchMedia('(min-width: 360px)').matches) {
            var maxHeight = Math.max.apply(null, jQuery(".view-spazi-homepage .field-wrapper-spazi").map(function () {
                return jQuery(this).height();
            }).get());
            jQuery(".view-spazi-homepage .spazi-content .field-wrapper-spazi").css("min-height", maxHeight);
        } else {
            jQuery(".view-spazi-homepage .spazi-content .field-wrapper-spazi").css("min-height", 'inherit');
        }
        if (window.matchMedia('(min-width: 992px)').matches) {
            var maxHeight = Math.max.apply(null, jQuery(".view-spazi-homepage .field-wrapper-spazi h3").map(function () {
                return jQuery(this).height();
            }).get());
            jQuery(".view-spazi-homepage .spazi-content .field-wrapper-spazi h3").css("min-height", maxHeight);
        } else {
            jQuery(".view-spazi-homepage .spazi-content .field-wrapper-spazi h3").css("min-height", 'inherit');
        }
        if (window.matchMedia('(min-width: 992px)').matches) {
            var maxHeight = Math.max.apply(null, jQuery(".view-spazi-homepage .slide__description").map(function () {
                return jQuery(this).height();
            }).get());
            jQuery(".view-spazi-homepage .slide__description").css("min-height", maxHeight);
        } else {
            jQuery(".view-spazi-homepage .slide__description").css("min-height", 'inherit');
        }
    }).resize();

//faccio aprire tutti i link all'interno dell'attività dell'impresa in un'altra finestra
    $("#TabContentImpresa #attivita a").attr("target", "_blank");
//rimuovo il protocollo dal testo di ogni link nell'impresa
    $(".line a").each(function () {
        if ($(this).html().startsWith("http"))
            $(this).html($(this).html().replace("https://", "").replace("http://", "").replace("https", "").replace("http", "").replace("/ ", ""));
    });
    $(".site_name a").each(function () {
        if ($(this).html().startsWith("http"))
            $(this).html($(this).html().replace("https://", "").replace("http://", "").replace("https", "").replace("http", "").replace("/ ", ""));
    });
    $("ul.list-unstyled.list-custom li a").each(function () {
        if ($(this).html().startsWith("http"))
            $(this).html($(this).html().replace("https://", "").replace("http://", "").replace("https", "").replace("http", "").replace("/ ", ""));
    });

    //aggiungo il btn Accedi con SPID
    $("body.page-user-login .user-login-form").each(function () {
        var parameters = location.href.substring(location.href.indexOf("destination"));
        $(this).after('<div class="accedi-con-spid"><a href="/samllogin?' + parameters + '"><button class="btn btn-primary">Accedi con SPID</button></a></div>');
        if (location.href.includes("?staff")) {
            $("body.page-user-login .user-login-form").show();
        } else {
            $("body.page-user-login .user-login-form").hide();
            $("body.page-user-login nav.tabs").hide();
        }
    });

    if ($("#edit-field-nazione").length >= 1) {

        $('#edit-field-nazione').on('change', function () {

            let nazione_selezionata = $(this).children(':selected').text();
            if (nazione_selezionata == 'Italia') {
                $('.ita_field').show();
            } else {
                $('.ita_field').hide();
            }
        });
    }

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

//selezione della prima impresa nel form di richiesta
    if ($("form#node-richiesta-nuovi-servizi-form, form#node-richiesta-form, form#node-richiesta-spazio-form, form#node-avviso-form").length > 0) {
        $("#edit-field-ragione-sociale option:eq(0)").appendTo($("#edit-field-ragione-sociale"));
        console.log('nascondo');
        $(".field--name-field-denominazione").hide();
        $(".field--name-field-codice-fiscale-impresa").hide();
        window.setTimeout(function () {
            console.log('nascondo dopo timeout');
            $(".field--name-field-denominazione").hide();
            $(".field--name-field-codice-fiscale-impresa").hide();
        }, 100);
        $("#edit-field-tipo-richiedente-privato, #edit-field-tipo-richiedente-libero-professionista").click(function () {
            $("#edit-field-ragione-sociale").val('_none');
            $(".field--name-field-denominazione").hide();
            $(".field--name-field-codice-fiscale-impresa").hide();
        });
        $("#edit-field-tipo-richiedente-impresa").click(function () {
            $("#edit-field-ragione-sociale").val($("#edit-field-ragione-sociale option:eq(0)").attr('value'));
            console.log($("#edit-field-ragione-sociale").val());
            if ($("#edit-field-ragione-sociale").val() == '_none') {
                console.log('mostro');
                $(".field--name-field-denominazione").show();
                $(".field--name-field-codice-fiscale-impresa").show();
                window.setTimeout(function () {
                    console.log('mostro dopo timeout');
                    $(".field--name-field-denominazione").show();
                    $(".field--name-field-codice-fiscale-impresa").show();
                }, 100);
            } else {
                console.log('nascondo');
                $(".field--name-field-denominazione").hide();
                $(".field--name-field-codice-fiscale-impresa").hide();
                window.setTimeout(function () {
                    console.log('nascondo dopo timeout');
                    $(".field--name-field-denominazione").hide();
                    $(".field--name-field-codice-fiscale-impresa").hide();
                }, 100);
            }
        });
        $("#edit-field-ragione-sociale").change(function () {
            if ($(this).val() == '_none') {
                console.log('selezionato none: mostro');
                $(".field--name-field-denominazione").show();
                $(".field--name-field-codice-fiscale-impresa").show();
            } else {
                console.log('non selezionato none: nascondo');
                $(".field--name-field-denominazione").hide();
                $(".field--name-field-codice-fiscale-impresa").hide();
            }
        });
    }

    window.setTimeout(function () {
        //CORREGGO IL LINK CONTATTACI NELLA PAGINA TERMINALE DEL WORKFLOW CHE MOSTRA TUTTI I SERVIZI INDIVIDUATI
        $("body.path-trova-il-tuo-servizio a[href='node/add/richiesta_nuovi_servizi?display=azienda']").attr("href", '/node/add/richiesta_nuovi_servizi?display=azienda');

        //COPIO IL BOTTONE SALVA L'ORDINE ANCHE IN FONDO ALLA VISTA
        $("body.path-management-workflow #views-form-management-workflow-page-1").append('<div data-drupal-selector="edit-actions" class="form-actions js-form-wrapper form-wrapper mb-3" id="edit-actions"><button data-drupal-selector="edit-save-order" type="submit" id="edit-save-order" name="op" value="Salva l\'ordine" class="button js-form-submit form-submit btn btn-primary">Salva l\'ordine</button></div>');

        //INSERISCO IL BOTTONE ANNULLA A SINISTRA DEL BOTTONE SALVA NEL VISTA MANAGEMENT WORKFLOW PAGE 1
        $("body.path-management-workflow #views-form-management-workflow-page-1 #edit-actions #edit-save-order").before('<button onclick="location.reload()" id="edit-annulla" value="Annulla" class="button btn btn-primary" style="margin-right: 20px;">Annulla</button>');
    }, 200);

}); //FINE DOCUMENT READY
$(window).on('load', function () {
    $("#overlay").hide();
});

window.onload = function () {
    // var url = document.location.toString();
    // if (url.match('#')) {
    //     $('.nav-pills a[href="#' + url.split('#')[1] + '"]').tab('show');


    //     $('a[data-bs-toggle="pill"]').on("click", function () {
    //         const hash = $(this).attr("href");
    //         //console.log(hash);
    //         // $('html, body').animate({
    //         //     scrollTop: $(hash).offset().top
    //         // }, 1000);
    //     });
    // }

    // //Change hash for page-reload
    // $('.nav-pills a[href="#' + url.split('#')[1] + '"]').on('shown', function (e) {
    //     window.location.hash = e.target.hash;
    // });

};

autocompleteRemoveTid = function () {
    document.querySelectorAll(".form-autocomplete").forEach(element => {
        let val = element.value;
        const match = val.match(/\(([0-9]+)\)$/);
        if (match) {
            element.value = val.replace(" " + match[0], "");
        }
    });
};

autocompleteRemoveTidTimeout = function () {
    console.log("autocompleteRemoveTidTimeout");
    autocompleteRemoveTid();
    setTimeout(autocompleteRemoveTid, 100);
    setTimeout(autocompleteRemoveTid, 200);
    setTimeout(autocompleteRemoveTid, 300);
    setTimeout(autocompleteRemoveTid, 400);
    setTimeout(autocompleteRemoveTid, 500);
    setTimeout(autocompleteRemoveTid, 600);
    setTimeout(autocompleteRemoveTid, 700);
    setTimeout(autocompleteRemoveTid, 800);
    setTimeout(autocompleteRemoveTid, 900);
    setTimeout(autocompleteRemoveTid, 1000);
    setTimeout(autocompleteRemoveTid, 1100);
    setTimeout(autocompleteRemoveTid, 1200);
    setTimeout(autocompleteRemoveTid, 1300);
    setTimeout(autocompleteRemoveTid, 1400);
    setTimeout(autocompleteRemoveTid, 1500);
};

(function ($, Drupal) {

    'use strict';

    // Drupal.behaviors.autocompleteRemoveTid = {
    //     attach: context => {
    //         console.log("autocompleteRemoveTid");
    //         // Remove TID's onload.
    //         Drupal.autocompleteRemoveTid.removeTid(context);

    //         // Remove TID's onchange.
    //         context.querySelectorAll(".form-autocomplete").forEach(element => {
    //             element.addEventListener("change", () => {
    //                 Drupal.autocompleteRemoveTid.removeTid(context);
    //             });
    //         })
    //     }
    // };

    Drupal.autocompleteRemoveTid = {
        removeTid: context => {
            console.log("autocompleteRemoveTid esecuzione");
            context.querySelectorAll(".form-autocomplete").forEach(element => {
                let val = element.value;
                const match = val.match(/\(([0-9]+)\)$/);
                if (match) {
                    element.value = val.replace(" " + match[0], "");
                }
            });
        }
    };
})(jQuery, Drupal);

