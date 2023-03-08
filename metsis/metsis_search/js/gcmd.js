(function ($, Drupal) {
    Drupal.behaviors.gcmdKeywords = {
      attach: function (context, settings) {
          $('#gcmdblock',context).once('gcmdKeywords').each(function () {

        //   $('#gcmd_l1').each(function() {
            //  if($('ul', context).hasClass('facet-active')) {
                //$(this).parent().find('a.facets-soft-limit-link').css('display', 'none');
                //$('ul.facet active ul + .facets-soft-limit-link a',context).css('display', 'none');
                 //ul = $('ul.facet-active',context);
                 //ul.parent().find('a.facets-soft-limit-link').text('bla');
                 $("ul.facet-active ~ a.facets-soft-limit-link").css('display', 'none');
              //   ul.parent().find('.facets-soft-limit-link',context).empty();
          //    }

              //$('.facets-soft-limit-link',context).css('display', 'none');
              $('ul.facet-active a', context ).css('display', 'none');
              $('ul.facet-active a.is-active', context).css('display', 'block');
//            });

/*
            $('#gcmd_l2').each(function() {
              $('.facets-soft-limit-link').css('display', 'none');
              $('ul.facet-active a').css('display', 'none');
              $('ul.facet-active a.is-active').css('display', 'block');
            });
*/

            $('.is-active',context).css('display', 'block');
            $('.is-active', context ).parent().css('display', 'block');
          });
        },
      };
    })(jQuery, Drupal);
