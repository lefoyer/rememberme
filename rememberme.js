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
    };

    rcmail.login_submit = function(elem)
    {
        if ($('#rememberme').is(":not(:checked)"))
            $('#rcmloginuser, #rcmloginpwd, #rcmloginhost').each( function () {
                this.setAttribute('autocomplete', 'off');
            });

        $('form').submit();

        return false;
    };

    $('#rcmloginuser, #rcmloginpwd, #rcmloginhost').on("keyup", function(e) {
        if (e.which == 13) {
            rcmail.login_submit(this);
            return false;
        }
    });


    input = $('<input />').attr('type', 'button').attr('value', 'Login').addClass('button mainaction').click(function(){
        rcmail.login_submit(this);
    });
    $('p.formbuttons').html('').append(input);


    rememberme_container = $("<div />").attr('id', 'rememberme_container');
    inner = $("<div />");
    input = $("<input />").attr('type', 'checkbox').attr('name', '_rememberme').attr('value', 1).change(function() {
        rcmail.rememberme_change(this);
    });
    label = $("<label />").attr('for', 'rememberme').html(rcmail.get_label('rememberme.rememberme'));

    $('form').append(rememberme_container.append(inner.append(input).append(label)));

  });
}
