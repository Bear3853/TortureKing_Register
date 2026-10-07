<?php
require_once 'functions.php';
unset($_SESSION['user']);
redirect('login.php');