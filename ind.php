 <?php
session_start();
$servername = 'localhost';
$username = 'user_name';
$password = 'password';
$dbname = 'dbname';
$base_url='http://'; 



$failure = false;
$hit=false;
if(isset($_GET['redirect']) && $_GET['redirect']!="") {
    $slug=urldecode($_GET['redirect']);
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $url= GetRedirectUrl($slug);
    $conn->close();
    header("location:".$url);
    exit;
}

if ( isset($_SESSION['failure']) ) {
    $failure = htmlentities($_SESSION['failure']);
    unset($_SESSION['failure']);
}


if(isset($_GET['url']) && $_GET['url']!="") {
    $url=urldecode($_GET['url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        $slug=GetShortUrl($url);
        $conn->close();
        $x=$base_url.$slug;
        $_SESSION['failure'] = $x;
        header("location:new.php");
        return;
    } 
    else {
        die("$url is not a valid URL");
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" >
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>URL Shortener</title>
<body>
        <center>
<h1>Put Your Url Here</h1>
<form>
<p><input style="width:500px" type="url" name="url" required /></p>
<p><input type="submit" /></p>
</form>
</center>
            );

        }
        
        ?>                
</body>
</html>
<?php
$mysqli =  $conn = new mysqli("localhost", "user_name","password","url_short");

if ($mysqli->connect_error) {
    die('Connect Error (' . 
    $mysqli->connect_errno . ') '. 
    $mysqli->connect_error);
}
  
// SQL query to select data from database
$sql = "SELECT * FROM url_shorten  ";
$x=$mysqli->query('SELECT * FROM url_shorten ORDER BY id DESC LIMIT 1');
$result = $mysqli->query($sql);
$mysqli->close(); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<style>
		table {
			margin: 0 auto;
			font-size: large;
			border: 1px solid black;
		}

		h1 {
			text-align: center;
			color: #006600;
			font-size: xx-large;
			font-family: 'Gill Sans', 'Gill Sans MT',
			' Calibri', 'Trebuchet MS', 'sans-serif';
		}

		td {
			background-color: #E4F5D4;
			border: 1px solid black;
		}

		th,
		td {
			font-weight: bold;
			border: 1px solid black;
			padding: 10px;
			text-align: center;
		}

		td {
			font-weight: lighter;
		}
	</style>
</head>

<body>
	<section>
		<table>
			<tr>
				<th>ID</th>
				<th>URL</th>
				<th>SHORTENED URL</th>
			</tr>
			<?php 
            while($row=$x->fetch_assoc())
            {
            echo " The no of hits are currently ". $row['id'];
            }
				while($rows=$result->fetch_assoc())
				{
			?>
			<tr>
				<!--FETCHING DATA FROM EACH
					ROW OF EVERY COLUMN-->
				<td><?php echo $rows['id'];?></td>
				<td><?php echo $rows['url'];?></td>
				<td><?php echo $rows['short_code'];?></td>
			</tr>
			<?php
				}
               
            ?>
		</table>
	</section>
</body>

</html>


<?php
function GetShortUrl($url){
    global $conn;
    $query = "SELECT * FROM url_shorten WHERE url = '".$url."' "; 
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['short_code'];
    }
    else {
        $short_code = generateUniqueID();
        $sql = "INSERT INTO url_shorten (url, short_code, hits) VALUES ('".$url."', '".$short_code."', '0')";
        if ($conn->query($sql) === TRUE) {
            return $short_code;
        }
        else { 
            die("Unknown Error Occured");
        }
    }
}

function generateUniqueID(){
    global $conn; 
    $token = substr(md5(uniqid(rand(), true)),0,6); $query = "SELECT * FROM url_shorten WHERE short_code = '".$token."'";
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        generateUniqueID();
    }
    else {
        return $token;
    }
}
function GetRedirectUrl($slug){
    global $conn;
    $query = "SELECT * FROM url_shorten WHERE short_code = '".addslashes($slug)."' "; 
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hits=$row['hits']+1;
        $sql = "update url_shorten set hits='".$hits."' where id='".$row['id']."' ";
        $conn->query($sql);
        return $row['url'];
    }
    else { 
        die("Invalid Link!");
    }
}