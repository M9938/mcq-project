<?php 
session_start();
if(isset($_SESSION['admin']['adminnakalogin']) == true) header("location:index.php");

 ?>

<?php 

include("login-ui/signup.php");


 ?>


<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/sweetalert.js"></script>