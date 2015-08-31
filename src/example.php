<?php
include("stacksentinel.php");

install_stack_sentinel();

function cause_divide_by_zero() {
    return 1 / 0;
}

function cause_an_error() {
    return cause_divide_by_zero();
}

cause_an_error();
?>

