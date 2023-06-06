(function ($, Drupal, drupalSettings) {

  'use strict';
  console.log( 'start' );
  let i = 0;
  /**
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *
   */
  Drupal.behaviors.home_liguria = {
    attach: function (context) {
      //$('#row-spazi .views-field-field-servizi-accessori .field-content', context);

      const navbar = document.querySelector( '#navbar-main' );
      if( document.body.scrollTop > 50 ) navbar.classList.add( 'bg-transparent' );
      window.onscroll = function() {
        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) navbar.classList.remove( 'bg-transparent' );
        else navbar.classList.add( 'bg-transparent' );
      }

      const divServizi = context.querySelector( '#row-spazi .views-field-field-servizi-accessori .field-content');
      if(divServizi){
        // TODO: eliminare replace in prod
        let textServizi = divServizi.innerHTML.replace( /<\!--.*?-->/g, "" );
        textServizi = textServizi.split( ',' );
        let servizi = '';
        textServizi = textServizi.forEach( servizio => {
          servizi += `<label class="container position-relative pb-2">
          <p class="ms-1">${servizio}</p><input type="checkbox" checked disabled/>  
          <span class="checkmark"></span></label><br/>`;
        });
        divServizi.innerHTML = servizi;
      }

      const div = context.querySelector( '#row-spazi .view-filters' );
      const filtri = context.querySelector( '#row-spazi .filtri' );
      if( div && filtri ){
        const clone = div.cloneNode( true ); // * true means clone all childNodes and all event handlers
        filtri.appendChild( clone );
        div.remove();
        const divForm = context.querySelectorAll( '.form-radios .js-form-item' );
        let input, label;
        divForm.forEach( divForm => {
          input = divForm.querySelector( 'input' );
          label = divForm.querySelector( 'label' );

          input.classList = '';
          input.classList.add( 'btn-check' );
          context.querySelector( '.form-radios' ).appendChild( input );

          label.classList = '';
          label.classList.add( 'btn', 'btn-spazi', 'me-2', 'mb-2' );
          context.querySelector( '.form-radios' ).appendChild( label );
        });

        divForm.forEach( div => {
          div.remove();
        });
      }
    }
  };
  //
  // const dots = document.querySelectorAll( '.dot' );
  // const dot_list = document.querySelectorAll( '.dot-list' );
  // dots.forEach( dot => {
  //   dot.addEventListener( 'click', e => {
  //     const id = e.target.dataset.id;
  //     const num = e.target.dataset.num;
  //     // Rimuovo active a tutti e aggiungo a quello selezionato
  //     let dotsGroup = document.querySelectorAll( `.dot[data-id="${id}"]` );
  //     dotsGroup.forEach( dotG => {
  //       dotG.classList.remove( 'active' );
  //     });
  //     e.target.classList.add( 'active' );
  //     // Faccio comparire gli elementi selezionati
  //     mfSlider( id, num );
  //
  //   })
  // });
  //
  // setInterval( () => {
  //   dot_list.forEach( dot => {
  //     let id = dot.querySelector( '.dot' ).dataset.id;
  //     let num = 0;
  //     let ds = dot.querySelectorAll( '.dot' );
  //     ds.forEach( d => {
  //       if( d.classList.contains( 'active' ) ){
  //         num = d.dataset.num;
  //         d.classList.remove( 'active' );
  //       }
  //     });
  //     num = num == 2 ? 0 : parseInt( num ) + 1;
  //     document.querySelector( `.dot[data-id="${id}"][data-num="${num}"]` ).classList.add( 'active' );
  //     mfSlider( id, num );
  //   });
  // }, 6000 );

})(jQuery, Drupal, drupalSettings);
//
// let mql = window.matchMedia('(max-width: 992px)');
//
// if(mql){
//   function mfSlider( id, num ){
//     const els = document.querySelectorAll( `#${id} > div.row > div` );
//     const elToShow = parseInt( els.length / 9 ) * num;
//     els.forEach( el => el.classList.add( 'd-none' ) )
//     if( els.length > 9 ){
//       els[ elToShow ].classList.add( 'd-block' );
//       els[ elToShow + 1 ].classList.add( 'd-block' );
//       els[ elToShow + 2 ].classList.add( 'd-block' );
//       els[ elToShow ].classList.remove( 'd-none' );
//       els[ elToShow + 1 ].classList.remove( 'd-none' );
//       els[ elToShow + 2 ].classList.remove( 'd-none' );
//     }else{
//       els[ elToShow ].classList.add( 'd-flex' );
//       els[ elToShow ].classList.remove( 'd-none' );
//     }
//   }
// } else {
//
//   function mfSlider( id, num ){
//     const els = document.querySelectorAll( `#${id} > div.row > div` );
//     const elToShow = parseInt( els.length / 3 ) * num;
//     els.forEach( el => el.classList.add( 'd-none' ) )
//     if( els.length > 3 ){
//       els[ elToShow ].classList.add( 'd-block' );
//       els[ elToShow + 1 ].classList.add( 'd-block' );
//       els[ elToShow + 2 ].classList.add( 'd-block' );
//       els[ elToShow ].classList.remove( 'd-none' );
//       els[ elToShow + 1 ].classList.remove( 'd-none' );
//       els[ elToShow + 2 ].classList.remove( 'd-none' );
//     }else{
//       els[ elToShow ].classList.add( 'd-flex' );
//       els[ elToShow ].classList.remove( 'd-none' );
//     }
//   }
//
// }

document.querySelector( '#main' ).classList.remove( 'container' );

/**
 * Sezione rimossa su richiesta cliente 13/04/22
const articles = document.querySelectorAll( '#sezionePPT .ppt-single-element');
articles.forEach( article => {
  let title = article.querySelector( '.node__title span' );
  article.querySelector( '.node__title a' ).remove();
  article.querySelector( '.node__title' ).appendChild( title );

  let taxonomy = article.querySelector( '.taxonomy-term .field' );
  article.querySelector( '.taxonomy-term a' ).remove();
  article.querySelector( '.taxonomy-term' ).appendChild( taxonomy );
});
 */

const formazione = document.querySelectorAll( '#views-bootstrap-formazione-homepage-block-1 .views-field-field-azienda-associata' );
if(formazione){
formazione.forEach( article => {
    let impresa = article.querySelector( 'a' );
    article.querySelector( 'a' ).remove();
    article.querySelector( 'div' ).innerHTML = impresa.textContent;
});
}

const marketing = document.querySelectorAll( '#views-bootstrap-gestione-opportunity-block-1 > div.row > div' );
marketing.forEach( article => {
  let citta = article.querySelector( '.views-field-field-citta .field-content' );
  let supe = article.querySelector( '.views-field-field-superficie-1 .field-content' );
  article.querySelector( '.views-field-field-citta .field-content' ).remove();
  article.querySelector( '.views-field-field-superficie-1 .field-content' ).remove();
  article.querySelector( '.views-field-field-citta span' ).append( citta.textContent );
  article.querySelector( '.views-field-field-superficie-1 span' ).append( supe.textContent );
});

const rowNews = document.querySelectorAll( '#views-bootstrap-gestione-news-homepage-block-1 > div.row > div' );
rowNews.forEach( rowNew => {
  let divTesto = document.createElement('div');
  divTesto.classList.add( 'views-field-field-immagine-testo' );
  divTesto.append( rowNew.querySelector( '.views-field-field-data-inizio-e-fine-evento' ) );
  divTesto.append( rowNew.querySelector( '.views-field-field-categoria-posizione' ) );
  divTesto.append( rowNew.querySelector( '.views-field-title' ) );
  divTesto.append( rowNew.querySelector( '.views-field-body' ) );
  rowNew.append( divTesto );
  let dataPubblicazione = rowNew.querySelector( '.views-field-field-data-inizio-e-fine-evento' );
  if (dataPubblicazione) dataPubblicazione.insertAdjacentHTML( "beforebegin", `<svg data-name="calendar (1)" xmlns="http://www.w3.org/2000/svg" width="22.569" height="22.569" viewBox="0 0 22.569 22.569">
  <path data-name="Tracciato 3319" d="M20.145,2.645H18.558V1.984a1.984,1.984,0,0,0-3.967,0v.661H7.979V1.984a1.984,1.984,0,1,0-3.967,0v.661H2.424A2.427,2.427,0,0,0,0,5.069V20.145a2.427,2.427,0,0,0,2.424,2.424h17.72a2.427,2.427,0,0,0,2.424-2.424V5.069A2.427,2.427,0,0,0,20.145,2.645Zm-4.232-.661a.661.661,0,0,1,1.322,0V4.628a.661.661,0,0,1-1.322,0Zm-10.579,0a.661.661,0,0,1,1.322,0V4.628a.661.661,0,0,1-1.322,0ZM1.322,5.069a1.1,1.1,0,0,1,1.1-1.1H4.011v.661a1.984,1.984,0,0,0,3.967,0V3.967h6.612v.661a1.984,1.984,0,0,0,3.967,0V3.967h1.587a1.1,1.1,0,0,1,1.1,1.1V8.023H1.322ZM21.247,20.145a1.1,1.1,0,0,1-1.1,1.1H2.424a1.1,1.1,0,0,1-1.1-1.1V9.345H21.247Z" fill="#09b5a9"/>
  <path data-name="Tracciato 3320" d="M292.425,243.322h1.035V250.6a.661.661,0,1,0,1.322,0v-7.935a.661.661,0,0,0-.661-.661h-1.7a.661.661,0,0,0,0,1.322Z" transform="translate(-278.903 -231.332)" fill="#09b5a9"/>
  <path data-name="Tracciato 3321" d="M154.409,249.935a1.327,1.327,0,0,1-1.31-1.137,1.349,1.349,0,0,1-.013-.185.661.661,0,0,0-1.322,0,2.643,2.643,0,0,0,2.645,2.645,2.643,2.643,0,0,0,1.747-4.628,2.644,2.644,0,1,0-4.342-2.5.661.661,0,1,0,1.3.255,1.323,1.323,0,1,1,1.3,1.579.661.661,0,0,0,0,1.322,1.322,1.322,0,1,1,0,2.645Z" transform="translate(-145.074 -231.332)" fill="#09b5a9"/>
  </svg>` );
  let categoriaPosizione = rowNew.querySelector( '.views-field-field-categoria-posizione' );
  if (categoriaPosizione) categoriaPosizione.insertAdjacentHTML( "beforebegin", `<svg xmlns="http://www.w3.org/2000/svg" width="20.944" height="25.888" viewBox="0 0 20.944 25.888">
  <g data-name="place" transform="translate(-48.886)">
    <g data-name="Raggruppa 1895" transform="translate(48.886)">
      <g data-name="Raggruppa 1894" transform="translate(0)">
        <path data-name="Tracciato 3322" d="M67.951,4.5a10.453,10.453,0,0,0-17.185,0,10.454,10.454,0,0,0-1.217,9.632,8.233,8.233,0,0,0,1.517,2.505l7.6,8.931a.9.9,0,0,0,1.378,0l7.6-8.929a8.241,8.241,0,0,0,1.517-2.5A10.456,10.456,0,0,0,67.951,4.5Zm-.48,9a6.457,6.457,0,0,1-1.194,1.956l0,0-6.914,8.121L52.44,15.46a6.461,6.461,0,0,1-1.2-1.961,8.647,8.647,0,0,1,1.011-7.968,8.642,8.642,0,0,1,14.207,0A8.649,8.649,0,0,1,67.471,13.5Z" transform="translate(-48.886)" fill="#09b5a9"/>
      </g>
    </g>
    <g data-name="Raggruppa 1897" transform="translate(54.289 5.371)">
      <g data-name="Raggruppa 1896" transform="translate(0)">
        <path data-name="Tracciato 3323" d="M160.823,106.219a5.069,5.069,0,1,0,5.069,5.069A5.075,5.075,0,0,0,160.823,106.219Zm0,8.327a3.259,3.259,0,1,1,3.259-3.259A3.262,3.262,0,0,1,160.823,114.546Z" transform="translate(-155.754 -106.219)" fill="#09b5a9"/>
      </g>
    </g>
  </g>
  </svg>
  ` );
});


jQuery(document).ready(function(){
  $('#views-bootstrap-elenco-imprese-home-block-1 > div.row').slick({
    centerMode: false,
    infinite: true,
    dots: true,
    arrows: false,
    slidesToShow: 3,
    slidesToScroll: 3,
    autoplay: true,
    autoplaySpeed: 6000,
    responsive: [
      {
        breakpoint: 1248,
        settings: {
          centerMode: false,
          slidesToShow: 2,
          slidesToScroll: 2,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      },
      {
        breakpoint: 992,
        settings: {
          centerMode: false,
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      }
    ]
  });

  $('#views-bootstrap-gestione-bandi-new-block-1 > div.row').slick({
    centerMode: false,
    infinite: true,
    dots: true,
    arrows: false,
    slidesToShow: 3,
    slidesToScroll: 3,
    autoplay: true,
    autoplaySpeed: 6000,
    responsive: [
      {
        breakpoint: 1248,
        settings: {
          centerMode: false,
          slidesToShow: 2,
          slidesToScroll: 2,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      },
      {
        breakpoint: 992,
        settings: {
          centerMode: false,
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      }
    ]
  });


  $('#views-bootstrap-formazione-homepage-block-1 > div.row').slick({
    centerMode: false,
    infinite: true,
    dots: true,
    arrows: false,
    slidesToShow: 3,
    slidesToScroll: 3,
    autoplay: true,
    autoplaySpeed: 6000,
    responsive: [
      {
        breakpoint: 1248,
        settings: {
          centerMode: false,
          slidesToShow: 2,
          slidesToScroll: 2,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      },
      {
        centerMode: false,
        breakpoint: 992,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      }
    ]
  });

  $('#views-bootstrap-gestione-opportunity-block-1 > div.row').slick({
    centerMode: false,
    infinite: true,
    dots: true,
    arrows: false,
    slidesToShow: 3,
    slidesToScroll: 3,
    autoplay: true,
    autoplaySpeed: 6000,
    responsive: [
      {
        breakpoint: 1248,
        settings: {
          centerMode: false,
          slidesToShow: 2,
          slidesToScroll: 2,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      },
      {
        breakpoint: 992,
        settings: {
          centerMode: false,
          slidesToShow: 1,
          slidesToScroll: 1,
          autoplay: true,
          autoplaySpeed: 6000,
          infinite: true,
          dots: true
        }
      }
    ]
  });


  $('#views-bootstrap-gestione-news-homepage-block-1 > div.row').slick({
    centerMode: false,
    infinite: true,
    dots: true,
    arrows: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 6000
  });

  $('#views-bootstrap-bacheca-block-1 > div.row').slick({
    centerMode: false,
    infinite: true,
    vertical: true,
    dots: true,
    arrows: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 6000
  });
//accessibility slide
//fine accessibility slide
  window.adattaPalla = function() {
    //console.log('height: '+window.innerHeight); console.log('width: '+window.innerWidth); 
    //Per definirela posizione della palla, aggiustare i valori di right (se diminuito sposta a sx la palla) e width (se aumentato ingrandisce la palla)
    if(window.innerWidth >= 2300){
      document.getElementById("palla-opportunita").style.right = '-365px';
      document.getElementById("palla-opportunita").style.top = '-150px';
      document.getElementById("palla-opportunita").style.width = '70%';
    }    
    if(window.innerWidth >= 1920 && window.innerWidth < 2300){
      document.getElementById("palla-opportunita").style.right = '-340px';
      document.getElementById("palla-opportunita").style.top = '-150px';
      document.getElementById("palla-opportunita").style.width = '70%';
    }
    if((window.innerWidth >= 1800) && (window.innerWidth < 1900)){
      document.getElementById("palla-opportunita").style.right = '-270px';
      document.getElementById("palla-opportunita").style.top = '-140px';
      document.getElementById("palla-opportunita").style.width = '70%';
    }
    if((window.innerWidth >= 1700) && (window.innerWidth < 1800)){
      document.getElementById("palla-opportunita").style.right = '-255px';
      document.getElementById("palla-opportunita").style.top = '-140px';
      document.getElementById("palla-opportunita").style.width = '70%';
    }
    if((window.innerWidth >= 1500) && (window.innerWidth < 1700)){
      document.getElementById("palla-opportunita").style.right = '-235px';
      document.getElementById("palla-opportunita").style.top = '-10px';
      document.getElementById("palla-opportunita").style.width = '71%';
    }
    if((window.innerWidth >= 1300) && (window.innerWidth < 1500)){
      document.getElementById("palla-opportunita").style.right = '-240px';
      document.getElementById("palla-opportunita").style.top = '-0px';
      document.getElementById("palla-opportunita").style.width = '80%';
    }
    if((window.innerWidth >= 1200) && (window.innerWidth < 1300)){
      document.getElementById("palla-opportunita").style.right = '-230px';
      document.getElementById("palla-opportunita").style.top = '-10px';
      document.getElementById("palla-opportunita").style.width = '90%';
    }
    if((window.innerWidth >= 1100) && (window.innerWidth < 1200)){
      document.getElementById("palla-opportunita").style.right = '-230px';
      document.getElementById("palla-opportunita").style.top = '-10px';
      document.getElementById("palla-opportunita").style.width = '95%';
    }
    if((window.innerWidth >= 1100) && (window.innerWidth < 1200)){
      document.getElementById("palla-opportunita").style.right = '-230px';
      document.getElementById("palla-opportunita").style.top = '-10px';
      document.getElementById("palla-opportunita").style.width = '95%';
    }
    if((window.innerWidth >= 1000) && (window.innerWidth < 1100)){
      document.getElementById("palla-opportunita").style.right = '-230px';
      document.getElementById("palla-opportunita").style.top = '-10px';
      document.getElementById("palla-opportunita").style.width = '100%';
    }
  };

  adattaPalla();
  addEventListener('resize', window.adattaPalla);
  
});



const accordionHamburger = () => {

  const items = document.querySelectorAll(`button[data-target="#CollapsingNavbar"]`);
  let sidebar = document.querySelector('#CollapsingNavbar')
  let sidebarOut = document.createElement('div');
  sidebarOut.classList.add('sidebar-out');

  //VERIFICO SE NELL'ACCORDION C'É UNA VOCE ATTIVA ALLORA APRO IN AUTOMATICO L'ACCORDION
  if(items){
    for (i = 0; i < items.length; i++) {
      items[i].setAttribute('aria-expanded', 'false');
    }
  }

  function toggleAccordion() {
    const itemToggle = this.getAttribute('aria-expanded');
    for (i = 0; i < items.length; i++) {
      items[i].setAttribute('aria-expanded', 'false');
      let content = items[i].nextElementSibling;
      content.style.maxHeight = '0px';
    }
    if (itemToggle == 'false') {
      this.setAttribute('aria-expanded', 'true');
      let content = this.nextElementSibling;
      content.style.maxHeight = content.scrollHeight + 'px';
    }
  }
  items.forEach(item => item.addEventListener('click', toggleAccordion))
  // const overlayInterval = setInterval(() => {
  //   let overlay = document.querySelector('.it-region-header-nav #main-menu .overlay')
  //   if(overlay){
  //     clearInterval(overlayInterval)
  //     console.log('test')
  //     overlay.addEventListener('click', toggleAccordion);
  //   }
  //
  // }, 500);

}

accordionHamburger()

console.log( 'end' );

//nascondiamo la freccia dopo lo scroll
$(window).bind('scroll', function() {
     if ($(window).scrollTop() > 500) {
         $('.pulse_anchor').hide();
     }
     else {
         $('.pulse_anchor').show();
     }
});