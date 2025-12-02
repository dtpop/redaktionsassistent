$(document).on("rex:ready", function (event, container) {
    // Hier wird das select2 auf #tagselect2 angewendet.
    $("#tagselect2").select2({
        tags: true,
        multiple: true,
    });

    $("#tagselect2").on("select2:select", function (e) {
        var data = e.params.data;
        // Der Nachrichtenblock wird zunächst entfernt, falls vorhanden
        $('.tag-info-block').remove();
        // Wenn die Id gleich ist wie der eingegebene Text, wird das gerade hinzugefügte Element zunächst wieder entfernt und die Ajax Funktion ausgeführt
        if (data.id == data.text) {
            $('#tagselect2 option[value="' + data.text + '"]').detach();
            addNewTag(data.text);
        }
    });

    // Ajax Funktion aufrufen
    function addNewTag(tag) {
        // Der Nachrichtenblock wird hier definiert
        var added_text = '<p class="help-block tag-info-block">Tag <strong>'+tag+'</strong> wurde der Datenbank hinzugefügt.</p>';
        // Der code wird zur Steuerung verwendet, ob das hinzugefügte Element in der db ist oder nicht.
        var code = 0;
        $.ajax({
            // Aufruf der Ajax Funktion
            url: "/index.php?rex-api-call=new_ra_tag&tag=" + tag,
            type: "get",
            success: function (response) {
                // Strato lieferte einen String, auf meiner dev habe ich gleich das Objekt bekommen.
                if (typeof response == 'string') {
                    response = JSON.parse(response);
                }
                var id = response.dataId;
                var data = {
                    id: id,
                    text: tag,
                };
                code = response.code;
                if (code == 1) {
                    // Wenn der Tag neu in die Datenbank eingetragen wurde (code = 1) wird dem Select eine neue Option mit selected=true hinzugefügt und change getriggert.
                    var newOption = new Option(data.text+' [id='+data.id+']', data.id, false, true);
                    $("#tagselect2").append(newOption).trigger("change");
                } else if (code == 2) {
                    // Wenn der Tag in der db gefunden wurde, wird die entsprechende Option auf selected gesetzt und change getriggert.
                    $('#tagselect2 option[value="'+data.id+'"]').prop('selected',true);
                    $("#tagselect2").trigger("change");
                }
            },
            error: function (response) {},
            complete: function (response) {
                // Wenn der Tag neu in die Datenbank eingetragen wurde, wird eine entsprechende Meldung unter dem Select angezeigt.
                if (code == 1) {
                    $('#tagselect2').parent().append(added_text);
                }
            },
        }); // EoF Ajax
    } // EoF addNewTag
}); // Ende rex ready