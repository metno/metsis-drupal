(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.metsisExport = {
    attach: function (context, drupalSettings) {
      $('#exportxml', context).once('metsisExport').each(function () {
        //  $(document).ready(function() {
        var xml = drupalSettings.metsis_export.xml;
        //$('#exportxml').append('<script> download(' + xml +', "export_mmd.xml", "text/xml")</script>')
    function downloadBase64File(contentBase64, fileName) {
    const linkSource = `data:application / pdf;base64,${contentBase64}`;
    const downloadLink = document.createElement('a');
    document.body.appendChild(downloadLink);

    downloadLink.href = linkSource;
    downloadLink.target = '_self';
    downloadLink.download = fileName;
    downloadLink.click();
}

downloadBase64File(xml, 'mmd_export.xml')
      )
      console.log("End of export.js script");

  });
},
};
})(jQuery, Drupal, drupalSettings);
