(function ($, Drupal, drupalSettings) {

  function plot_ts(url_o, md_ts_id, path, pywps) {
    let loader = '<img class="ts-plot-loader" src="/' + path + '/icons/loader.gif">';
    $('.bokeh-ts-plot[reference="' + md_ts_id + '"]').find('.ts-loader').append(loader);
    var variable = $('.bokeh-ts-plot[reference="' + md_ts_id + '"]').find('#ts-var-list').val();
    if ($('.ts-plot[reference="' + md_ts_id + '"]').html().length > 0) {
      $('.ts-plot[reference="' + md_ts_id + '"]').empty();
    }

    fetch(pywps + '?get=plot&resource_url=' + url_o + '&variable=' + variable + '&axis=' + $('.bokeh-ts-plot[reference="' + md_ts_id + '"]').find('#axis').val())
      .then(function (response) {
        if(response.ok) {
        return response.json();
      }
      else {
          throw new Error("Error fetching opendap from url: " + url_o);
      }
      })
      .then(function (item) {
        var idplaceholder = $('.ts-plot[reference="' + md_ts_id + '"]').attr('id');
        //console.log(idplaceholder);
        item.target_id = idplaceholder;

        Bokeh.embed.embed_item(item);
        $('.bokeh-ts-plot[reference="' + md_ts_id + '"]').find('.ts-loader').empty();
      })
      .catch((error) => {
        console.log("Error fetching opendap from url: " + opendap_url);
        $('.bokeh-ts-plot[reference="' + id + '"]').find('.ts-loader').empty();
        $('.ts-plot[reference="' + id + '"]').append('<div class="w3-panel w3-leftbar w3-border-red w3-pale-red w3-serif"><span> Error fetching opendap from url: ' + opendap_url + '</span></div>');
      });
  }
  Drupal.behaviors.metsisSearchTsPlot = {
    attach: function (context, drupalSettings) {
      var pywpsUrl = drupalSettings.metsis_search_ts_plot.pywps_service;
      var path = drupalSettings.metsis_search_ts_plot.module_path;
      $('.visualise-ts-div').once().each(function () {
        var opendap_url = $('#opendap-url', this).text();
        var id = $('#opendap-id', this).text();

        console.log('Got timeseries id: ' + id);
        $('#ts-plot-button', this).once().each(function () {

/*

          $(this).click(function() {
            console.log("calling ts-plot with url: " + opendap_url);
            //id = id.replace('.', '_');
            console.log('Width  timeseries ID: ' + id);
            if ($('.ts-plot[reference="' + id + '"]').html().length > 0 || $('.bokeh-ts-plot[reference="' + id + '"]').find('.ts-vars').html().length > 0) {
              $('.ts-plot[reference="' + id + '"]').empty();
              $('.bokeh-ts-plot[reference="' + id + '"]').find('.ts-vars').empty();
            } else {
              let loader = '<img class="ts-click-loader" src="/core/misc/throbber-active.gif">';
              $('.bokeh-ts-plot[reference="' + id + '"]').find('.ts-loader').append(loader);
              console.log('fetching varuables');
              fetch(pywpsUrl + '?get=param&resource_url=' + opendap_url)
                .then((response) => {
                  if (response.ok) {
                    return response.json();
                  } else {
                    throw new Error("Error fetching opendap from url: " + opendap_url);
                  }
                })
                .then((data) => {
                  $('.bokeh-ts-plot[reference="' + id + '"]').find('.ts-vars').append(
                    $(document.createElement('input')).prop({
                      id: 'axis',
                      name: 'axis',
                      value: Object.keys(data),
                      type: 'hidden',
                    })
                  ).append(
                    $(document.createElement('select')).prop({
                      id: 'ts-var-list',
                      name: 'var_list',

                    }).append(
                      $(document.createElement('option')).text('Choose variable')
                    )
                  );
                  console.log('looping variables');
                  for (const variable of data[Object.keys(data)]) {
                    var el = document.createElement("option");
                    el.textContent = variable;
                    el.value = variable;
                    $('.bokeh-ts-plot[reference="' + id + '"]').find('#ts-var-list').append(el);
                  }
                  $('.bokeh-ts-plot[reference="' + id + '"]').find('.ts-loader').empty();

                  $('.bokeh-ts-plot[reference="' + id + '"]').find('#ts-var-list').on('change', function() {
                    plot_ts(opendap_url, id, path, pywpsUrl)

                  });

                })
                .catch((error) => {
                  console.log("Error fetching opendap from url: " + opendap_url);
                  $('.bokeh-ts-plot[reference="' + id + '"]').find('.ts-loader').empty();
                  $('.ts-plot[reference="' + id + '"]').append('<div class="w3-panel w3-leftbar w3-border-red w3-pale-red w3-serif"><span> Error fetching opendap from url: ' + opendap_url + '</span></div>');
                });
              //});

              //});
            }
          });
          */
        });
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
