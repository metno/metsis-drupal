

function clearForm() {
    $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').val('');
    $(':checkbox, :radio').prop('checked', false);
}
//function clearForm($form) {
//    $form.find(':input').not(':button, :submit, :reset, :hidden, .not-reset').val('');
//}

/**
 * form element tooltips
 */

// We'll encapsulate our .qtip() call in your .on() handler method
jQuery(document).ready(function () {
    $(document).on('mouseover', '[data-tooltip!=""]', function (event) {
        // Bind the qTip within the event handler
        $(this).qtip({
            overwrite: false, // Make sure the tooltip won't be overridden once created
            content: {attr: 'data-tooltip'}, // 'I get bound to all my selector elements, past, present and future!',
            position: {
                target: 'mouse', // Position it where the click was...
                adjust: {mouse: false} // ...but don't follow the mouse
            },
            show: {
                event: event.type, // Use the same show event as the one that triggered the event handler
                ready: true // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
            }
        }, event); // Pass through our original event to qTip
    })
            // Store our title attribute in 'oldtitle' attribute instead
            .each(function (i) {
                $.attr(this, 'oldtitle', $.attr(this, 'title'));
                this.removeAttribute('title');
            });
});
