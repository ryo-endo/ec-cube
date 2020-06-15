{% if app.session.has('_security_admin') %}

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sidebar/3.3.2/jquery.sidebar.min.js"></script>
<script>
function changeTitleFont(){
  var fontFamily = [
    'inherit',
    '\'ヒラギノ丸ゴ ProN\',\'Hiragino Maru Gothic ProN\',\'sans-serif;',
    '\'ヒラギノ角ゴ StdN\',\'Hiragino Kaku Gothic StdN\',\'sans-serif;',
    '\'Yu Mincho Light\',\'YuMincho\',\'Yu Mincho\',\'游明朝体\',\'sans-serif;'
  ];
  var i = $('#select-customize-title').prop('selectedIndex');

  $('.ec-headerTitle__title > h1 > a').css({
    "font-family": fontFamily[i]
  });
}

function changeFontSize(size) {
  $('.ec-headerTitle__title > h1 > a').css({
    "font-size": size + 'px'
  });
}

</script>

{% endif %}
