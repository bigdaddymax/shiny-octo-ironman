/********************************************************/
/*  Putting labels inside inputs, textareas and selects */
/********************************************************/

$(function(){
    function toggleLabel() {
        var input = $(this);
        setTimeout(function() {
            var def = input.attr('title');
            if (!input.val() || (input.val() == def)) {
                input.prev('span').css('visibility', '');
                if (def) {
                    var dummy = $('<label></label>').text(def).css('visibility','hidden').appendTo('body');
                    input.prev('span').css('margin-left', dummy.width() + 3 + 'px');
                    dummy.remove();
                }
            } else {
                input.prev('span').css('visibility', 'hidden');
            }
        }, 0);
    }
    
    function resetField() {
        var def = $(this).attr('title');
        if (!$(this).val() || ($(this).val() == def)) {
            $(this).val(def);
            $(this).prev('span').css('visibility', '');
        }
    }

    $('input').on('keydown', toggleLabel);

    $('body').on('keydown', 'input', toggleLabel);
    $('body').on('paste', 'input', toggleLabel);
    $('body').on('change', 'select', toggleLabel);

    $('body').on('focusin', 'input', function() {
        $(this).prev('span').css('color', '#ccc');
    });
    $('body').on('focusout', 'input, textarea', function() {
        $(this).prev('span').css('color', '#999');
    });

    // set things up as soon as the DOM is ready
    $(function() {
        $('input').each(function() { 
                toggleLabel.call(this);
       });
    });

    // do it again to detect Chrome autofill
    $(window).load(function() {
        setTimeout(function() {
            $('input').each(function() {
                     toggleLabel.call(this);
            });
            $('select').each(function(){
                if ($(this).val()){
                    $(this).prev('span').css('visibility', 'hidden');
                }
            });
        }, 0);
    });
});