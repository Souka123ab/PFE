<?php
session_start();
session_destroy();
header('Location: /PFE/acceuil.php');
exit;
?>