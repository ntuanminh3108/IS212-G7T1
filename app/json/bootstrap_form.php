
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
		<h1>JSON Web API - Bootstrap</h1>
		<form id='bootstrap-form' action="bootstrap.php" method="post" enctype="multipart/form-data">
			<table>
				<tr>
					<td>Token: </td>
					<td> <input type= "text" name="token" value=''></td>
				</tr>
				<tr>
					<td>Bootstrap file: </td> 
					<td><input id='bootstrap-file' type="file" name="bootstrap-file"><br/></td>
				</tr>
				<tr><td><input type="submit" name="submit" value="Import"></td></tr>
			</table>
		</form>
	</body>
</html>

