<?php

    $con = mysql_connect('localhost','root','12345678');

    mysql_db_name('we7173',$con);

    $re = mysql_fetch_array('')


/*$con = mysql_connect("localhost","root","12345678");
if (!$con)
{
    die('Could not connect: ' . mysql_error());
}

mysql_select_db("we7173", $con);

$result = mysql_query("SELECT * FROM ims_ewei_shop_member");

while($row = mysql_fetch_array($result))
{
    echo $row['id'] ;
    echo "<br />";
}

mysql_close($con);
*/?>