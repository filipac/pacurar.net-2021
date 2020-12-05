<?php
// dd(get_defined_vars(), ->render());die;
$_view = view('generic.comments');
foreach (get_defined_vars() as $name => $val) {
    if($name == '_view') {
        continue;
    }
    $_view->with($name, $val);
}
echo $_view->render();
