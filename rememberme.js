if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    var warning_id;

    $('#rcmloginuser, #rcmloginpwd, #rcmloginhost').each( function () {
        this.setAttribute('autocomplete', 'on');
    });
    $('form[name=form]').prop('id', 'rcmloginform');
    $('#rcmloginform').append('<input id="rcmloginpwd-hide" type="hidden" name="_pass-hide" autocomplete="off">');

    if (rcmail.env.rememberme_autocheck) $('#rememberme').prop('checked', true);

    rcmail.rememberme_change = function(elem)
    {
        if(elem.checked)
            warning_id = rcmail.display_message(rcmail.get_label('rememberme.rememberme_warning'), 'warning', 3000);
        else
            rcmail.hide_message(warning_id);

        return false;
    };

    rcmail.login_submit = function(elem)
    {
        if ($('#rememberme').is(":not(:checked)"))
            $('#rcmloginpwd').each( function () {
                $('#rcmloginpwd-hide').val($('#rcmloginpwd').val());
                $('#rcmloginpwd').prop('name', '_notused');
                $('#rcmloginpwd').val(new Array($('#rcmloginpwd').val().length + 1).join("*"));
                $('#rcmloginpwd').prop('id', 'rcmloginpwd-notused');
                $('#rcmloginpwd-hide').prop('name', '_pass');
                $('#rcmloginpwd-hide').prop('id', 'rcmloginpwd');
                $('#rcmloginpwd-notused').prop('type', 'text');
            });
            $('#rcmloginuser, #rcmloginpwd, #rcmloginhost, #rcmloginform').each( function () {
                this.setAttribute('autocomplete', 'off');
            });

        $('form').submit();

        return false;
    };

    $('#rcmloginuser, #rcmloginpwd, #rcmloginhost').on("keyup", function(e) {
        if (e.which == 13) {
            if (($('#rcmloginuser').val() != '') && ($('#rcmloginpwd').val() != '')) {
                rcmail.login_submit(this);
                return false;
            }
        }
    });



    input = $('<input />').attr('type', 'button').attr('value', 'Login').addClass('button mainaction').click(function(){
        rcmail.login_submit(this);
    });
    $('p.formbuttons').html('').append(input);


    $('form').append(' \
        <div id="rememberme_container"> \
            <div> \
                <input id="rememberme" type="checkbox" name="_rememberme"  value="1"' + (rcmail.env.rememberme_autocheck ? ' checked="checked"' : ' ') + ' onchange="rcmail.rememberme_change(this)"> \
                <label for="rememberme">' + rcmail.get_label('rememberme.rememberme') + '</label> \
            </div> \
        </div> \
    ');


  });
}
