if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    var warning_id;

    rcmail.rememberme_change = function(elem)
    {
        if(elem.checked)
            warning_id = rcmail.display_message(rcmail.get_label('rememberme.rememberme_warning'), 'warning', 3000);
        else
            rcmail.hide_message(warning_id);

        return false;
    }

    rcmail.login_submit = function(elem)
    {
        if ($('#rememberme').is(":not(:checked)"))
            $('#rcmloginuser, #rcmloginpwd, #rcmloginhost').each( function () {
                this.setAttribute('autocomplete', 'off');
            });

        $('form').submit();

        return false;
    }

    $('#rcmloginuser, #rcmloginpwd, #rcmloginhost').on("keyup", function(e) {
        if (e.which == 13) {
            rcmail.login_submit(this);
            return false;
        }
    });

    $('.button[type=submit]').click(function(){
        rcmail.login_submit(this);
    }).get(0).setAttribute('type', 'button');

    $('form').append(' \
        <div id="rememberme_container"> \
            <div> \
                <input id="rememberme" type="checkbox" name="_rememberme"  value="1" onchange="rcmail.rememberme_change(this)"> \
                <label for="rememberme">' + rcmail.get_label('rememberme.rememberme') + '</label> \
            </div> \
        </div> \
    ');

  });
}
