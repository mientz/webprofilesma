CKEDITOR.plugins.add( 'fileman', {
    icons: 'tooltipimage',
    init: function( editor ) {
        editor.addCommand( 'insertTimestamp', {
            exec: function( editor ) {
                var now = new Date();
                $('#header-select').modal('show');
                /*editor.insertHtml( 'The current date and time is: <em>' + now.toString() + '</em>' );*/
            }
        });
        editor.ui.addButton( 'Timestamp', {
            label: 'Insert Timestamp',
            command: 'insertTimestamp',
            toolbar: 'insert'
        });
    }
});
