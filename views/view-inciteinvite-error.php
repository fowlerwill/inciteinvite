<?php
if( preg_match('/success/', $errorcode) ) {
    printf('<div class="alert alert-success" role="alert">%s</div>', $errormsg[0]);
} else if( preg_match('/info/', $errorcode) ) {
    printf('<div class="alert alert-info" role="alert">%s</div>', $errormsg[0]);
} else if ( preg_match('/warning/', $errorcode) ) {
    printf('<div class="alert alert-warning" role="alert">%s</div>', $errormsg[0]);
} else if( preg_match('/error/', $errorcode) ) {
    printf('<div class="alert alert-danger" role="alert">%s</div>', $errormsg[0]);
}
