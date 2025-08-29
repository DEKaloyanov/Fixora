<?php
session_start();
session_unset(); // премахва всички променливи от сесията
session_destroy(); // унищожава сесията
header("Location: ../index.php"); // връща към началната страница
exit();
