(function($) {
    $(document).ready( function() {
        var undoBox = $("#undo-box");
        undoBox.find('a.show-all').click( function() {
            goToRevisions();
        })

    });
    function goToRevisions() {
        if ( $("#revisionsdiv").is(':hidden') ) {
            $("#revisionsdiv").show();
        }
        location.hash = "";
        location.hash = "#revisionsdiv";
        postboxes.save_state(pagenow);
    }
})(jQuery);